<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require("config.php");

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login1.php");
    exit();
}

// Initialize variables
$user_id = $_SESSION['uid'];
$booking_id = isset($_REQUEST['booking_id']) ? intval($_REQUEST['booking_id']) : 0;
$error = "";
$success = "";

// Validate booking ID
if ($booking_id <= 0) {
    header("Location: user_dashboard.php");
    exit();
}

// Get booking details
$query = "SELECT b.*, p.title, p.price, p.location, p.city, p.pimage, p.type, p.stype 
          FROM bookings b 
          JOIN property p ON b.property_id = p.pid 
          WHERE b.id = ? AND b.user_id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'ii', $booking_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$booking = mysqli_fetch_assoc($result)) {
    header("Location: user_dashboard.php");
    exit();
}

// Display error message if set
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Display success message if set
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Payment Confirmation - Remsko Real Estate</title>
    
    <!-- Include CSS files -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        .confirmation-container {
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
        .payment-status {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .status-pending {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
        }
        .status-completed {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .status-failed {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
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
                    <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>Payment Confirmation</b></h2>
                </div>
                <div class="col-md-6">
                    <nav aria-label="breadcrumb" class="float-left float-md-right">
                        <ol class="breadcrumb bg-transparent m-0 p-0">
                            <li class="breadcrumb-item text-white"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="user_dashboard.php#bookings">My Bookings</a></li>
                            <li class="breadcrumb-item active">Payment Confirmation</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content -->
    <div class="full-row">
        <div class="container">
            <div class="confirmation-container">
                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <h3 class="mb-4">Payment Confirmation</h3>
                
                <div class="property-summary">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="admin/property/<?php echo htmlspecialchars($booking['pimage']); ?>" alt="Property" class="img-fluid rounded">
                        </div>
                        <div class="col-md-8">
                            <h4 class="text-secondary"><?php echo htmlspecialchars($booking['title']); ?></h4>
                            <p><i class="fas fa-map-marker-alt text-success"></i> <?php echo htmlspecialchars($booking['location']); ?>, <?php echo htmlspecialchars($booking['city']); ?></p>
                            <p class="text-success h5">ETB <?php echo number_format($booking['price']); ?></p>
                            <p><strong>Booking Reference:</strong> #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></p>
                        </div>
                    </div>
                </div>
                
                <?php 
                $statusClass = '';
                $statusTitle = '';
                
                if ($booking['payment_status'] == 'pending') {
                    $statusClass = 'status-pending';
                    $statusTitle = 'Payment Pending Verification';
                } elseif ($booking['payment_status'] == 'completed') {
                    $statusClass = 'status-completed';
                    $statusTitle = 'Payment Completed';
                } elseif ($booking['payment_status'] == 'failed') {
                    $statusClass = 'status-failed';
                    $statusTitle = 'Payment Failed';
                }
                ?>
                
                <div class="payment-status <?php echo $statusClass; ?>">
                    <h4><?php echo $statusTitle; ?></h4>
                    
                                        <?php if ($booking['payment_status'] == 'pending'): ?>
                    <p>Your payment is currently pending verification. We will review your payment and update your booking status shortly.</p>
                    <p><strong>Payment Method:</strong> <?php echo ucfirst($booking['payment_method']); ?></p>
                    <p><strong>Payment Reference:</strong> <?php echo $booking['payment_reference']; ?></p>
                    <p><strong>Payment Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($booking['payment_date'])); ?></p>
                    
                    <div class="alert alert-info mt-3">
                        <h5>What happens next?</h5>
                        <p>Our team will verify your payment in our Telebirr account. This typically takes 1-24 hours. Once verified, your booking status will be updated automatically.</p>
                    </div>
                    
                    <?php elseif ($booking['payment_status'] == 'completed'): ?>
                    <p>Your payment has been verified and your booking is confirmed. Thank you!</p>
                    <p><strong>Payment Method:</strong> <?php echo ucfirst($booking['payment_method']); ?></p>
                    <p><strong>Payment Reference:</strong> <?php echo $booking['payment_reference']; ?></p>
                    <p><strong>Payment Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($booking['payment_date'])); ?></p>
                    
                    <?php elseif ($booking['payment_status'] == 'failed'): ?>
                    <p>Unfortunately, we could not verify your payment. This could be due to:</p>
                    <ul>
                        <li>The payment was not completed in Telebirr</li>
                        <li>The reference number was missing in the payment description</li>
                        <li>The payment amount did not match the booking amount</li>
                    </ul>
                    <p>Please try again or contact our support team for assistance.</p>
                    <div class="text-center mt-4">
                        <a href="payment.php?booking_id=<?php echo $booking_id; ?>" class="btn btn-primary">Try Again</a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="text-center mt-4">
                    <a href="user_dashboard.php#bookings" class="btn btn-secondary">Back to My Bookings</a>
                </div>
            </div>
        </div>
    </div>
     
    <?php include("include/footer.php"); ?>
    
    <!-- JavaScript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>

