<?php
session_start();
require("config.php");

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login1.php");
    exit();
}

// Check if booking ID is provided
if (!isset($_GET['booking_id']) || !is_numeric($_GET['booking_id'])) {
    header("Location: user_dashboard.php");
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
    header("Location: user_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Payment Failed - Remsko Real Estate</title>
    
    <!-- Include CSS files -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        .failed-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
            text-align: center;
        }
        .failed-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        .failed-title {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .failed-message {
            margin-bottom: 30px;
            color: #6c757d;
        }
        .property-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: left;
        }
    </style>
</head>
<body>
    <?php include("include/header.php"); ?>
    
    <!-- Content -->
    <div class="full-row">
        <div class="container">
            <div class="failed-container">
                <i class="fas fa-times-circle failed-icon"></i>
                <h2 class="failed-title">Payment Failed</h2>
                <p class="failed-message">We couldn't process your payment. Please try again or contact customer support for assistance.</p>
                
                <div class="property-info">
                    <h5><?php echo htmlspecialchars($booking['title']); ?></h5>
                    <p><i class="fas fa-map-marker-alt text-success"></i> <?php echo htmlspecialchars($booking['location']); ?>, <?php echo htmlspecialchars($booking['city']); ?></p>
                    <p><strong>Booking ID:</strong> <?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></p>
                    <p><strong>Amount:</strong> ETB <?php echo number_format($booking['price']); ?></p>
                </div>
                
                <div class="mt-4">
                    <a href="payment.php?booking_id=<?php echo $booking_id; ?>" class="btn btn-primary">
                        <i class="fas fa-redo mr-2"></i> Try Again
                    </a>
                    <a href="my_bookings.php" class="btn btn-secondary ml-2">
                        <i class="fas fa-list mr-2"></i> My Bookings
                    </a>
                </div>
                
                <div class="mt-4">
                    <p class="small text-muted">If you continue to experience issues, please contact our support team at support@remsko.com</p>
                </div>
            </div>
        </div>
    </div>
    
       <?php include("include/footer.php"); ?>
    
    <!-- JavaScript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        // Redirect to my_bookings.php after 60 seconds
        setTimeout(function() {
            window.location.href = 'my_bookings.php';
        }, 60000);
    </script>
</body>
</html>

