<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Form submitted: " . print_r($_POST, true));
}
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

// Initialize variables
$msg = "";
$error = "";
$user_id = $_SESSION['uid'];

// Validate property ID
if (isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
    $property_id = intval($_REQUEST['id']);
    
    // Get property details
    $stmt = mysqli_prepare($con, "SELECT * FROM property WHERE pid = ?");
    mysqli_stmt_bind_param($stmt, 'i', $property_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$property = mysqli_fetch_assoc($result)) {
        $msg = "<p class='alert alert-danger'>Property not found</p>";
        header("Location: property.php?msg=" . urlencode($msg));
        exit();
    }
} else {
    $msg = "<p class='alert alert-danger'>Invalid property ID</p>";
    header("Location: property.php?msg=" . urlencode($msg));
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_visit'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid form submission";
    } else {
        // Validate form data
        $name = mysqli_real_escape_string($con, trim($_POST['name']));
        $email = mysqli_real_escape_string($con, trim($_POST['email']));
        $phone = mysqli_real_escape_string($con, trim($_POST['phone']));
        $visit_date = mysqli_real_escape_string($con, trim($_POST['visit_date']));
        $visit_time = mysqli_real_escape_string($con, trim($_POST['visit_time']));
        $message = mysqli_real_escape_string($con, trim($_POST['message']));
        
        // Validate required fields
        if (empty($name) || empty($email) || empty($phone) || empty($visit_date) || empty($visit_time)) {
            $error = "All required fields must be filled";
        } else {
            // Insert visit request
            $insert_query = "INSERT INTO visits (property_id, user_id, name, email, phone, visit_date, visit_time, message, status, request_date) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
            $stmt = mysqli_prepare($con, $insert_query);
            mysqli_stmt_bind_param($stmt, 'iissssss', $property_id, $user_id, $name, $email, $phone, $visit_date, $visit_time, $message);
            
            if (mysqli_stmt_execute($stmt)) {
                // Set success message
                $_SESSION['visit_scheduled'] = true;
                $_SESSION['visit_property_title'] = $property['title'];
                
                // Redirect to my visits page
                header("Location: my_visits.php?success=1");
                exit();
            } else {
                $error = "Failed to schedule visit: " . mysqli_error($con);
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
    <title>Schedule Visit - Real Estate PHP</title>
    
    <!-- Include CSS files -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        .visit-container {
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
                    <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>Schedule Visit</b></h2>
                </div>
                <div class="col-md-6">
                    <nav aria-label="breadcrumb" class="float-left float-md-right">
                        <ol class="breadcrumb bg-transparent m-0 p-0">
                            <li class="breadcrumb-item text-white"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Schedule Visit</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content -->
    <div class="full-row">
        <div class="container">
            <div class="visit-container">
                <?php if (!empty($msg)): ?>
                    <div class="alert alert-success"><?php echo $msg; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <h3 class="mb-4 text-secondary">Schedule a Visit</h3>
                
                <div class="property-summary">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="admin/property/<?php echo htmlspecialchars($property['pimage']); ?>" alt="Property" class="img-fluid rounded">
                        </div>
                        <div class="col-md-8">
                            <h4 class="text-secondary"><?php echo htmlspecialchars($property['title']); ?></h4>
                            <p><i class="fas fa-map-marker-alt text-success"></i> <?php echo htmlspecialchars($property['location']); ?>, <?php echo htmlspecialchars($property['city']); ?></p>
                            <p class="text-success h5">ETB <?php echo number_format($property['price']); ?></p>
                            <p><strong>Type:</strong> <?php echo htmlspecialchars($property['type']); ?> | <strong>Status:</strong> For <?php echo htmlspecialchars($property['stype']); ?></p>
                        </div>
                    </div>
                </div>
                
                <form method="post" action="" id="schedule_visit_form">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="required-field">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['uname']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="required-field">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['uemail']); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone" class="required-field">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['uphone']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="visit_date" class="required-field">Preferred Visit Date</label>
                                <input type="date" class="form-control date-picker" id="visit_date" name="visit_date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="required-field">Preferred Time</label>
                        <div class="time-slots">
                            <div class="time-slot">
                                <input type="radio" id="time_9am" name="visit_time" value="09:00" required checked>
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
                        <label for="message">Additional Message (Optional)</label>
                        <textarea class="form-control" id="message" name="message" rows="4" placeholder="Any specific requirements or questions..."></textarea>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" name="schedule_visit" class="btn btn-success btn-lg">Schedule Visit</button>
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
              // Add calendar export option
        document.getElementById('schedule_visit_form').addEventListener('submit', function(e) {
            // Don't prevent form submission, just add calendar functionality
            const visitDate = document.getElementById('visit_date').value;
            const visitTime = document.querySelector('input[name="visit_time"]:checked').value;
            const propertyTitle = "<?php echo htmlspecialchars($property['title']); ?>";
            const propertyLocation = "<?php echo htmlspecialchars($property['location'] . ', ' . $property['city']); ?>";
            
            // Store visit details in localStorage for potential calendar export after successful submission
            localStorage.setItem('lastVisitDate', visitDate);
            localStorage.setItem('lastVisitTime', visitTime);
            localStorage.setItem('lastVisitProperty', propertyTitle);
            localStorage.setItem('lastVisitLocation', propertyLocation);
        });
        
        // Check if we should offer calendar export (this would be on the success page)
        if (window.location.search.includes('success=1')) {
            const visitDate = localStorage.getItem('lastVisitDate');
            const visitTime = localStorage.getItem('lastVisitTime');
            
            if (visitDate && visitTime) {
                // Create calendar links
                const startDateTime = new Date(visitDate + 'T' + visitTime);
                const endDateTime = new Date(startDateTime.getTime() + 60*60*1000); // 1 hour later
                
                const title = "Property Visit: " + localStorage.getItem('lastVisitProperty');
                const location = localStorage.getItem('lastVisitLocation');
                
                // Format for Google Calendar
                const startTimeStr = startDateTime.toISOString().replace(/-|:|\.\d+/g, '');
                const endTimeStr = endDateTime.toISOString().replace(/-|:|\.\d+/g, '');
                
                const googleCalendarUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE' +
                    '&text=' + encodeURIComponent(title) +
                    '&dates=' + startTimeStr + '/' + endTimeStr +
                    '&location=' + encodeURIComponent(location) +
                    '&details=' + encodeURIComponent('Property visit scheduled through Remsko Real Estate');
                
                // Add calendar export buttons to success message
                const successAlert = document.querySelector('.alert-success');
                if (successAlert) {
                    const calendarLinks = document.createElement('div');
                    calendarLinks.className = 'mt-3';
                    calendarLinks.innerHTML = '<p>Add this visit to your calendar:</p>' +
                        '<a href="' + googleCalendarUrl + '" target="_blank" class="btn btn-sm btn-outline-primary mr-2">' +
                        '<i class="fas fa-calendar-plus"></i> Google Calendar</a>';
                    
                    successAlert.appendChild(calendarLinks);
                    
                    // Clear localStorage
                    localStorage.removeItem('lastVisitDate');
                    localStorage.removeItem('lastVisitTime');
                    localStorage.removeItem('lastVisitProperty');
                    localStorage.removeItem('lastVisitLocation');
                }
            }
        }
    </script>
</body>
</html>
