<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session and include configuration
session_start();
require("config.php");

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login1.php");
    exit();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize variables
$msg = "";
$error = "";
$user_id = $_SESSION['uid'];

// Validate visit ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $visit_id = intval($_GET['id']);
    
    // Get visit details
    $stmt = mysqli_prepare($con, "SELECT v.*, p.title, p.pimage, p.location, p.city 
                                FROM visits v 
                                JOIN property p ON v.property_id = p.pid 
                                WHERE v.id = ? AND v.user_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $visit_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$visit = mysqli_fetch_assoc($result)) {
        $msg = "<p class='alert alert-danger'>Visit not found</p>";
        header("Location: my_visits.php?msg=" . urlencode($msg));
        exit();
    }
    
    // Check if visit can be rescheduled
    if ($visit['status'] !== 'pending' && $visit['status'] !== 'confirmed') {
        $msg = "<p class='alert alert-danger'>This visit cannot be rescheduled</p>";
        header("Location: visit_details.php?id=" . $visit_id . "&msg=" . urlencode($msg));
        exit();
    }
} else {
    $msg = "<p class='alert alert-danger'>Invalid visit ID</p>";
    header("Location: my_visits.php?msg=" . urlencode($msg));
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reschedule_visit'])) {
    // Log form submission for debugging
    error_log("Reschedule form submitted: " . print_r($_POST, true));
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid form submission";
    } else {
        // Validate form data
        $visit_date = isset($_POST['visit_date']) ? mysqli_real_escape_string($con, trim($_POST['visit_date'])) : '';
        $visit_time = isset($_POST['visit_time']) ? mysqli_real_escape_string($con, trim($_POST['visit_time'])) : '';
        $message = isset($_POST['message']) ? mysqli_real_escape_string($con, trim($_POST['message'])) : '';
        
        // Store the old visit date and time before updating
        $old_visit_date = $visit['visit_date'];
        $old_visit_time = $visit['visit_time'];
        
        // Validate required fields
        if (empty($visit_date) || empty($visit_time)) {
            $error = "Visit date and time are required";
        } else {
            // Update visit request
            $update_query = "UPDATE visits SET 
                            visit_date = ?, 
                            visit_time = ?, 
                            message = ?, 
                            status = 'pending', 
                            updated_at = NOW() 
                            WHERE id = ?";
            $stmt = mysqli_prepare($con, $update_query);
            mysqli_stmt_bind_param($stmt, 'sssi', $visit_date, $visit_time, $message, $visit_id);
            
            if (mysqli_stmt_execute($stmt)) {
                // Try to log the rescheduling in visit_history
                try {
                    // Check if visit_history table exists
                    $check_table = mysqli_query($con, "SHOW TABLES LIKE 'visit_history'");
                    if(mysqli_num_rows($check_table) > 0) {
                        // Log the rescheduling in visit_history
                        $history_query = "INSERT INTO visit_history (visit_id, previous_date, previous_time, reason) 
                                        VALUES (?, ?, ?, ?)";
                        $history_stmt = mysqli_prepare($con, $history_query);
                        mysqli_stmt_bind_param($history_stmt, 'isss', $visit_id, $old_visit_date, $old_visit_time, $message);
                        mysqli_stmt_execute($history_stmt);
                    } else {
                        // Create visit_history table if it doesn't exist
                        $create_table_query = "CREATE TABLE IF NOT EXISTS visit_history (
                            id int(11) NOT NULL AUTO_INCREMENT,
                            visit_id int(11) NOT NULL,
                            previous_date date NOT NULL,
                            previous_time time NOT NULL,
                            reason text,
                            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                            PRIMARY KEY (id),
                            KEY visit_id (visit_id)
                        )";
                        mysqli_query($con, $create_table_query);
                        
                        // Try to insert again
                        $history_query = "INSERT INTO visit_history (visit_id, previous_date, previous_time, reason) 
                                        VALUES (?, ?, ?, ?)";
                        $history_stmt = mysqli_prepare($con, $history_query);
                        mysqli_stmt_bind_param($history_stmt, 'isss', $visit_id, $old_visit_date, $old_visit_time, $message);
                        mysqli_stmt_execute($history_stmt);
                    }
                } catch (Exception $e) {
                    // Just log the error but continue with the process
                    error_log("Error logging visit history: " . $e->getMessage());
                }
                
                // Success - redirect to my_visits page with confirmation message
                $msg = "Visit rescheduled successfully! We will contact you to confirm the new time.";
                header("Location: my_visits.php?success=" . urlencode($msg));
                exit();            } else {
                $error = "Failed to reschedule visit: " . mysqli_error($con);
                error_log("Database error: " . mysqli_error($con));
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Reschedule Visit - Remsko Real Estate</title>
    
    <!-- Include CSS files -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        .reschedule-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .property-summary {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .required-field::after {
            content: "*";
            color: red;
            margin-left: 4px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .time-slots {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .time-slot {
            flex: 0 0 auto;
        }
        .time-slot input[type="radio"] {
            display: none;
        }
        .time-slot label {
            display: block;
            padding: 8px 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .time-slot input[type="radio"]:checked + label {
            background-color: #28a745;
            color: white;
            border-color: #28a745;
        }
        .time-slot label:hover {
            background-color: #e9ecef;
        }
    </style>
</head>
<body>
    <?php include("include/header.php"); ?>
    
    <!-- Banner -->
    <div class="banner-full-row page-banner" style="background-image:url('images/breadcromb.jpg');">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>Reschedule Visit</b></h2>
                </div>
                <div class="col-md-6">
                    <nav aria-label="breadcrumb" class="float-left float-md-right">
                        <ol class="breadcrumb bg-transparent m-0 p-0">
                            <li class="breadcrumb-item text-white"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item text-white"><a href="my_visits.php">My Visits</a></li>
                            <li class="breadcrumb-item text-white"><a href="visit_details.php?id=<?php echo $visit_id; ?>">Visit Details</a></li>
                            <li class="breadcrumb-item active">Reschedule Visit</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content -->
    <div class="full-row">
        <div class="container">
            <div class="reschedule-container">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <h3 class="mb-4 text-secondary">Reschedule Your Visit</h3>
                
                <div class="property-summary">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="admin/property/<?php echo htmlspecialchars($visit['pimage']); ?>" alt="Property" class="img-fluid rounded">
                        </div>
                        <div class="col-md-8">
                            <h4 class="text-secondary"><?php echo htmlspecialchars($visit['title']); ?></h4>
                            <p><i class="fas fa-map-marker-alt text-success"></i> <?php echo htmlspecialchars($visit['location']); ?>, <?php echo htmlspecialchars($visit['city']); ?></p>
                            <p><strong>Current Visit Date:</strong> <?php echo date('F j, Y', strtotime($visit['visit_date'])); ?></p>
                            <p><strong>Current Visit Time:</strong> <?php echo date('h:i A', strtotime($visit['visit_time'])); ?></p>
                        </div>
                    </div>
                </div>
                
                <form method="post" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="form-group">
                        <label for="visit_date" class="required-field">New Visit Date</label>
                        <input type="date" class="form-control" id="visit_date" name="visit_date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                        <small class="form-text text-muted">Please select a date at least one day in advance.</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="required-field">New Visit Time</label>
                        <div class="time-slots">
                            <div class="time-slot">
                                <input type="radio" id="time_9am" name="visit_time" value="09:00" required>
                                <label for="time_9am">9:00 AM</label>
                            </div>
                            <div class="time-slot">
                                <input type="radio" id="time_10am" name="visit_time" value="10:00">
                                <label for="time_10am">10:00 AM</label>
                            </div>
                            <div class="time-slot">
                                <input type="radio" id="time_11am" name="visit_time" value="11:00">
                                <label for="time_11am">11:00 AM</label>
                            </div>
                            <div class="time-slot">
                                <input type="radio" id="time_12pm" name="visit_time" value="12:00">
                                <label for="time_12pm">12:00 PM</label>
                            </div>
                            <div class="time-slot">
                                <input type="radio" id="time_1pm" name="visit_time" value="13:00">
                                <label for="time_1pm">1:00 PM</label>
                            </div>
                            <div class="time-slot">
                                <input type="radio" id="time_2pm" name="visit_time" value="14:00">
                                <label for="time_2pm">2:00 PM</label>
                            </div>
                            <div class="time-slot">
                                <input type="radio" id="time_3pm" name="visit_time" value="15:00">
                                <label for="time_3pm">3:00 PM</label>
                            </div>
                            <div class="time-slot">
                                <input type="radio" id="time_4pm" name="visit_time" value="16:00">
                                <label for="time_4pm">4:00 PM</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Reason for Rescheduling (Optional)</label>
                        <textarea class="form-control" id="message" name="message" rows="4" placeholder="Please let us know why you need to reschedule..."><?php echo htmlspecialchars($visit['message']); ?></textarea>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" name="reschedule_visit" class="btn btn-success btn-lg">
                            <i class="fas fa-calendar-alt mr-2"></i> Reschedule Visit
                        </button>
                        <a href="visit_details.php?id=<?php echo $visit_id; ?>" class="btn btn-outline-secondary btn-lg ml-2">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include("include/footer.php"); ?>
    
    <!-- JavaScript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        // Disable past dates in the date picker
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        const minDate = tomorrow.toISOString().split('T')[0];
        document.getElementById('visit_date').setAttribute('min', minDate);
        
        // Disable weekends (optional)
        document.getElementById('visit_date').addEventListener('input', function() {
            const selectedDate = new Date(this.value);
            const day = selectedDate.getDay();
            
            // 0 is Sunday, 6 is Saturday
            if (day === 0 || day === 6) {
                alert('Please select a weekday (Monday to Friday) for your visit.');
                this.value = '';
            }
        });
    </script>
</body>
</html>
