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
    
    if ($property['status'] !== 'available') {
        $msg = "<p class='alert alert-danger'>Property is not available for booking</p>";
        header("Location: property.php?msg=" . urlencode($msg));
        exit();
    }
} else {
    $msg = "<p class='alert alert-danger'>Invalid property ID</p>";
    header("Location: property.php?msg=" . urlencode($msg));
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_booking'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid form submission";
    } else {
        // Validate form data
        $name = mysqli_real_escape_string($con, trim($_POST['name']));
        $email = mysqli_real_escape_string($con, trim($_POST['email']));
        $phone = mysqli_real_escape_string($con, trim($_POST['phone']));
        $move_in_date = mysqli_real_escape_string($con, trim($_POST['move_in_date']));
        $message = mysqli_real_escape_string($con, trim($_POST['message']));
        
        // Set fixed lease term based on property type
        // You can customize this logic based on your business rules
        $lease_term = 12; // Default to 12 months (1 year)
        
        if ($property['type'] == 'apartment' || $property['type'] == 'flat') {
            $lease_term = 12; // 1 year for apartments and flats
        } elseif ($property['type'] == 'office') {
            $lease_term = 24; // 2 years for offices
        } elseif ($property['type'] == 'villa') {
            $lease_term = 36; // 3 years for villas
        }
        
        // Calculate end date based on move-in date and lease term
        $end_date = date('Y-m-d', strtotime($move_in_date . ' + ' . $lease_term . ' months'));
        
        // Validate required fields
        if (empty($name) || empty($email) || empty($phone) || empty($move_in_date)) {
            $error = "All required fields must be filled";
        } else {
            // Handle ID document upload
            $id_document = "";
            if (isset($_FILES['id_document']) && $_FILES['id_document']['error'] == 0) {
                $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
                $filename = $_FILES['id_document']['name'];
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                
                if (!in_array(strtolower($ext), $allowed)) {
                    $error = "Invalid file format. Only PDF, JPG, JPEG, and PNG are allowed.";
                } else {
                    $new_filename = 'ID_' . $user_id . '_' . time() . '.' . $ext;
                    $upload_dir = 'uploads/documents/';
                    
                    // Create directory if it doesn't exist
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    if (move_uploaded_file($_FILES['id_document']['tmp_name'], $upload_dir . $new_filename)) {
                        $id_document = $new_filename;
                    } else {
                        $error = "Failed to upload document";
                    }
                }
            } else {
                $error = "ID document is required";
            }
            
if (empty($error)) {
    // Start transaction
    mysqli_begin_transaction($con);
    
    try {
        // Insert booking record
        $insert_query = "INSERT INTO bookings (property_id, user_id, name, email, phone, move_in_date, lease_term, end_date, message, id_document, status, booking_date) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
        $stmt = mysqli_prepare($con, $insert_query);
        mysqli_stmt_bind_param($stmt, 'iissssisss', $property_id, $user_id, $name, $email, $phone, $move_in_date, $lease_term, $end_date, $message, $id_document);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to create booking: " . mysqli_error($con));
        }
        
        $booking_id = mysqli_insert_id($con);
        
        // Update property status
        $update_query = "UPDATE property SET status = 'pending' WHERE pid = ?";
        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt, 'i', $property_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to update property status: " . mysqli_error($con));
        }
        
        // Commit transaction
        mysqli_commit($con);
        
        // Store booking ID in session
$_SESSION['booking_id'] = $booking_id;
$_SESSION['booking_success'] = true;
$_SESSION['booking_message'] = "You have successfully booked the property. Please proceed to payment to confirm your booking.";
// Redirect to my_bookings.php with success message
header("Location: my_bookings.php?success=1&booking_id=" . $booking_id);
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($con);
        $error = $e->getMessage();
    }
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

// Determine lease term based on property type for display
$lease_term_text = "12 months (1 year)"; // Default
if ($property['type'] == 'apartment' || $property['type'] == 'flat') {
    $lease_term_text = "12 months (1 year)";
} elseif ($property['type'] == 'office') {
    $lease_term_text = "24 months (2 years)";
} elseif ($property['type'] == 'villa') {
    $lease_term_text = "36 months (3 years)";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Book Property - Real Estate PHP</title>
    
    <!-- Include CSS files -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        .booking-container {
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
        .lease-term-info {
            background-color: #e9f7ef;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
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
                    <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>Book Property</b></h2>
                </div>
                <div class="col-md-6">
                    <nav aria-label="breadcrumb" class="float-left float-md-right">
                        <ol class="breadcrumb bg-transparent m-0 p-0">
                            <li class="breadcrumb-item text-white"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item text-white"><a href="propertydetail.php?pid=<?php echo $property_id; ?>">Property Detail</a></li>
                            <li class="breadcrumb-item active">Book Property</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content -->
    <div class="full-row">
        <div class="container">
            <div class="booking-container">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <h3 class="mb-4 text-secondary">Book This Property</h3>
                
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
                
                <div class="lease-term-info">
                    <h5><i class="fas fa-info-circle text-success mr-2"></i>Lease Term Information</h5>
                    <p class="mb-0">This property is available with a standard lease term of <strong><?php echo $lease_term_text; ?></strong>. This is the fixed duration for which you will be booking the property.</p>
                </div>
                
                <form method="post" action="" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="required-field">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['uname'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="required-field">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['uemail'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone" class="required-field">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['uphone'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="move_in_date" class="required-field">Move-in Date</label>
                                <input type="date" class="form-control" id="move_in_date" name="move_in_date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                                <small class="form-text text-muted">Please select your preferred move-in date</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="id_document" class="required-field">Upload ID Document (National ID, Passport, Driver's License)</label>
                        <input type="file" class="form-control" id="id_document" name="id_document" required>
                        <small class="form-text text-muted">Accepted formats: PDF, JPG, JPEG, PNG (Max size: 5MB)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Additional Information</label>
                        <textarea class="form-control" id="message" name="message" rows="4" placeholder="Any specific requirements or questions..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I confirm that all the information provided is accurate and I agree to the lease term of <?php echo $lease_term_text; ?>.
                            </label>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" name="submit_booking" class="btn btn-success btn-lg">Submit Booking Request</button>
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
        document.getElementById('move_in_date').setAttribute('min', minDate);
        
        // Validate move-in date
        document.getElementById('move_in_date').addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            
            // Ensure date is not in the past
            if (selectedDate < today) {
                alert('Please select a future date for move-in.');
                this.value = '';
            }
            
            // Optional: Restrict to specific days of the week
            // For example, only allow move-ins on weekdays
            const day = selectedDate.getDay();
            if (day === 0 || day === 6) {
                alert('Move-in dates are only available on weekdays (Monday to Friday).');
                this.value = '';
            }
        });
    </script>
</body>
</html>
