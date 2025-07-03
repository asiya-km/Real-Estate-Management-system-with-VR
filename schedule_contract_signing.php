<?php
session_start();
require("config.php");

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login1.php");
    exit();
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validate booking ID
if (!isset($_GET['booking_id']) || !is_numeric($_GET['booking_id'])) {
    header("Location: my_bookings.php");
    exit();
}

$booking_id = intval($_GET['booking_id']);
$user_id = $_SESSION['uid'];

// Get booking details
$stmt = mysqli_prepare($con, "SELECT b.*, p.title, p.pimage, p.location, p.city, p.price 
                            FROM bookings b 
                            JOIN property p ON b.property_id = p.pid 
                            WHERE b.id = ? AND b.user_id = ?");
mysqli_stmt_bind_param($stmt, 'ii', $booking_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$booking = mysqli_fetch_assoc($result)) {
    header("Location: my_bookings.php");
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_signing'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid form submission";
    } else {
        // Validate form data
        $preferred_date = mysqli_real_escape_string($con, trim($_POST['preferred_date']));
        $preferred_time = mysqli_real_escape_string($con, trim($_POST['preferred_time']));
        $notes = mysqli_real_escape_string($con, trim($_POST['notes']));
        
        // Validate required fields
        if (empty($preferred_date) || empty($preferred_time)) {
            $error = "Please select both date and time";
        } else {
            // Update booking with contract signing appointment
            $query = "UPDATE bookings SET 
                    contract_signing_date = ?, 
                    contract_signing_time = ?, 
                    contract_signing_notes = ?,
                    contract_signing_status = 'scheduled',
                    agreement_accepted = 1,
                    agreement_date = NOW()
                    WHERE id = ? AND user_id = ?";
            
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, 'sssii', $preferred_date, $preferred_time, $notes, $booking_id, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                // Set success message
                $_SESSION['success_message'] = "Contract signing appointment scheduled successfully!";
                
                // Redirect to payment page
                header("Location: payment.php?booking_id=" . $booking_id);
                exit();
            } else {
                $error = "Failed to schedule appointment: " . mysqli_error($con);
            }
        }
    }
}

// Get user details
$stmt = mysqli_prepare($con, "SELECT * FROM user WHERE uid = ?");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($user_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Schedule Contract Signing - Remsko Real Estate</title>
    
    <!-- Include CSS files -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        .schedule-container {
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
                    <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>Schedule Contract Signing</b></h2>
                </div>
                <div class="col-md-6">
                    <nav aria-label="breadcrumb" class="float-left float-md-right">
                        <ol class="breadcrumb bg-transparent m-0 p-0">
                                                        <li class="breadcrumb-item text-white"><a href="my_bookings.php">My Bookings</a></li>
                            <li class="breadcrumb-item text-white"><a href="agreement.php?booking_id=<?php echo $booking_id; ?>">Agreement</a></li>
                            <li class="breadcrumb-item active">Schedule Signing</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content -->
    <div class="full-row">
        <div class="container">
            <div class="schedule-container">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                                <h3 class="mb-4 text-secondary">Schedule Contract Signing Appointment</h3>
                
                                <div class="property-summary">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="admin/property/<?php echo htmlspecialchars($booking['pimage']); ?>" alt="Property" class="img-fluid rounded">
                        </div>
                        <div class="col-md-8">
                            <h4 class="text-secondary"><?php echo htmlspecialchars($booking['title']); ?></h4>
                            <p><i class="fas fa-map-marker-alt text-success"></i> <?php echo htmlspecialchars($booking['location']); ?>, <?php echo htmlspecialchars($booking['city']); ?></p>
                            <p class="text-success h5">ETB <?php echo number_format($booking['price']); ?></p>
                            <p><strong>Booking ID:</strong> <?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></p>
                        </div>
                    </div>
                </div>
                
                <form method="post" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="required-field">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['uname']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="required-field">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['uemail']); ?>" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone" class="required-field">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['uphone']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="preferred_date" class="required-field">Preferred Date</label>
                                <input type="date" class="form-control" id="preferred_date" name="preferred_date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="required-field">Preferred Time</label>
                        <div class="time-slots">
                            <div class="time-slot">
                                <input type="radio" id="time_9am" name="preferred_time" value="09:00" required>
                                <label for="time_9am">9:00 AM</label>
                            </div>
                            <div class="time-slot">
                                <input type="radio" id="time_10am" name="preferred_time" value="10:00">
                                <label for="time_10am">10:00 AM</label>
                            </div>
                            <div class="time-slot">
                                <input type="radio" id="time_11am" name="preferred_time" value="11:00">
                                <label for="time_11am">11:00 AM</label>
                            </div>
                            <div class="time-slot">
                                <input type="radio" id="time_12pm" name="preferred_time" value="12:00">
                                <label for="time_12pm">12:00 PM</label>
                            </div>
                            <div class="time-slot">
                                <input type="radio" id="time_1pm" name="preferred_time" value="13:00">
                                <label for="time_1pm">1:00 PM</label>
                            </div>
                            <div class="time-slot">
                                <input type="radio" id="time_2pm" name="preferred_time" value="14:00">
                                <label for="time_2pm">2:00 PM</label>
                            </div>
                            <div class="time-slot">
                                <input type="radio" id="time_3pm" name="preferred_time" value="15:00">
                                <label for="time_3pm">3:00 PM</label>
                            </div>
                            <div class="time-slot">
                                <input type="radio" id="time_4pm" name="preferred_time" value="16:00">
                                <label for="time_4pm">4:00 PM</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Additional Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Any specific requirements or questions..."></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i> Our representative will contact you to confirm the appointment. Please ensure your contact information is correct.
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="agreement.php?booking_id=<?php echo $booking_id; ?>" class="btn btn-outline-secondary mr-2">Back</a>
                        <button type="submit" name="schedule_signing" class="btn btn-success btn-lg">
                            <i class="fas fa-calendar-check mr-2"></i> Schedule Appointment
                        </button>
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
        // Disable past dates and weekends in the date picker
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            const minDate = tomorrow.toISOString().split('T')[0];
            document.getElementById('preferred_date').setAttribute('min', minDate);
            
            // Disable weekends
            document.getElementById('preferred_date').addEventListener('input', function() {
                const selectedDate = new Date(this.value);
                const day = selectedDate.getDay();
                
                // 0 is Sunday, 6 is Saturday
                if (day === 0 || day === 6) {
                    alert('Please select a weekday (Monday to Friday) for your appointment.');
                    this.value = '';
                }
            });
        });
    </script>
</body>
</html>
