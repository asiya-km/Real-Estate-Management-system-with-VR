<?php
session_start();
include("config.php");

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header("location:login1.php");
    exit();
}

// Validate booking ID
if (!isset($_GET['booking_id']) || !is_numeric($_GET['booking_id'])) {
    header("location:index.php");
    exit();
}

$booking_id = intval($_GET['booking_id']);
$user_id = $_SESSION['uid'];

// Get booking details
$stmt = mysqli_prepare($con, "SELECT b.*, p.title, p.pimage, p.location, p.city 
                           FROM bookings b 
                           JOIN property p ON b.property_id = p.pid 
                           WHERE b.id = ? AND b.user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $booking_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$booking = mysqli_fetch_assoc($result)) {
    header("location:index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Meta Tags -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Real Estate PHP">
    <meta name="keywords" content="">
    <meta name="author" content="Unicoder">
    <link rel="shortcut icon" href="images/favicon.ico">

    <!-- Include your CSS files here -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">

    <title>Payment Error - Remsko</title>
    
    <style>
        .error-container {
            text-align: center;
            padding: 50px 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .error-icon {
            color: #dc3545;
            font-size: 80px;
            margin-bottom: 20px;
        }
        .booking-details {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            text-align: left;
        }
        .property-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <?php include("include/header.php"); ?>
    
    <div class="container mt-5 mb-5">
        <div class="error-container">
            <div class="error-icon">
                <i class="fa fa-times-circle"></i>
            </div>
            <h1>Payment Failed</h1>
            <p class="lead">We couldn't process your payment. Your booking is still pending.</p>
            
            <div class="booking-details">
                <div class="row">
                    <div class="col-md-6">
                        <img src="admin/property/<?php echo htmlspecialchars($booking['pimage']); ?>" alt="Property" class="property-image">
                    </div>
                    <div class="col-md-6">
                        <h4><?php echo htmlspecialchars($booking['title']); ?></h4>
                        <p><i class="fa fa-map-marker"></i> <?php echo htmlspecialchars($booking['location']); ?>, <?php echo htmlspecialchars($booking['city']); ?></p>
                        
                        <h5 class="mt-4">Booking Information</h5>
                        <p><strong>Booking ID:</strong> #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></p>
                        <p><strong>Booking Date:</strong> <?php echo date('d M, Y', strtotime($booking['booking_date'])); ?></p>
                        <?php if(isset($booking['preferred_date']) && !empty($booking['preferred_date'])): ?>
                        <p><strong>Preferred Date:</strong> <?php echo date('d M, Y', strtotime($booking['preferred_date'])); ?></p>
                        <?php endif; ?>
                        <p><strong>Payment Status:</strong> 
                            <span class="badge badge-danger">Failed</span>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <a href="propertydetail.php?pid=<?php echo $booking['property_id']; ?>" class="btn btn-primary">Try Again</a>
                <a href="user_dashboard.php" class="btn btn-outline-secondary ml-2">My Bookings</a>
            </div>
        </div>
    </div>
    
    <?php include("include/footer.php"); ?>
    
    <!-- Include your JS files here -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>
