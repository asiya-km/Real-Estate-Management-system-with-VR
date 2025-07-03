<?php
session_start();
include("config.php");

$booking_success_message = "";
if (isset($_GET['success']) && $_GET['success'] == 1 && isset($_SESSION['booking_message'])) {
    $booking_success_message = $_SESSION['booking_message'];
    unset($_SESSION['booking_message']);
}
if (!isset($_SESSION['uid'])) {
    header("location:login1.php");
    exit();
}

$user_id = $_SESSION['uid'];

// Near the top of my_bookings.php after session_start() and authentication check
$show_success_message = false;
$success_message = "";
$highlight_booking_id = 0;

if (isset($_GET['success']) && $_GET['success'] == '1' && isset($_GET['booking_id'])) {
    $show_success_message = true;
    $highlight_booking_id = intval($_GET['booking_id']);
    $success_message = $_SESSION['booking_message'] ?? "Your booking has been successfully created.";
    unset($_SESSION['booking_message']);
}

// Or if using session variables
if (isset($_SESSION['booking_success']) && $_SESSION['booking_success']) {
    $show_success_message = true;
    $highlight_booking_id = $_SESSION['booking_id'] ?? 0;
    $success_message = $_SESSION['booking_message'] ?? "Your booking has been successfully created.";
    unset($_SESSION['booking_success']);
    unset($_SESSION['booking_message']);
}

// Fetch user's bookings
$query = "SELECT b.*, p.title, p.pimage, p.location, p.city, p.price 
          FROM bookings b 
          JOIN property p ON b.property_id = p.pid 
          WHERE b.user_id = ? 
          ORDER BY b.booking_date DESC";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Remsko Real Estate</title>
    <!-- Include CSS files -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/bootstrap-slider.css">
    <link rel="stylesheet" type="text/css" href="css/jquery-ui.css">
    <link rel="stylesheet" type="text/css" href="css/layerslider.css">
    <link rel="stylesheet" type="text/css" href="css/color.css">
    <link rel="stylesheet" type="text/css" href="css/owl.carousel.min.css">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="fonts/flaticon/flaticon.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" type="text/css" href="css/login.css">
    <style>
        .booking-card {
            transition: all 0.3s;
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .booking-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        .booking-image {
            height: 200px;
            object-fit: cover;
        }
        .booking-status {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-confirmed {
            background-color: #28a745;
            color: white;
        }
        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }
        .status-cancelled {
            background-color: #dc3545;
            color: white;
        }
        .status-expired {
            background-color: #6c757d;
            color: white;
        }
        .payment-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-top: 5px;
        }
        .payment-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .payment-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .payment-failed {
            background-color: #f8d7da;
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
                    <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>My Bookings</b></h2>
                </div>
                <div class="col-md-6">
                    <nav aria-label="breadcrumb" class="float-left float-md-right">
                        <ol class="breadcrumb bg-transparent m-0 p-0">
                            <li class="breadcrumb-item text-white"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item text-white"><a href="user_dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">My Bookings</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content -->
     <?php if (!empty($booking_success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong><i class="fas fa-check-circle"></i> Success!</strong> <?php echo $booking_success_message; ?>
    </div>
<?php endif; ?>
    <div class="full-row">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <div class="mb-4">
                        <a href="user_dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                        </a>
                    </div>
                    <?php include("include/user_sidebar.php"); ?>
                </div>
                
                <div class="col-lg-8">
                    <div class="row">
                        <div class="col-lg-12">
                            <h4 class="double-down-line-left text-secondary position-relative pb-4 mb-4">My Property Bookings</h4>
                        </div>
                    </div>
                    
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($booking = mysqli_fetch_assoc($result)): ?>
                            <div class="booking-card">
                                <div class="card">
                                    <div class="row no-gutters">
                                        <div class="col-md-4 position-relative">
                                            <img src="admin/property/<?php echo htmlspecialchars($booking['pimage']); ?>" class="booking-image w-100" alt="Property">
                                            
                                            <!-- Status Badge -->
                                            <span class="booking-status status-<?php echo strtolower($booking['status']); ?>">
                                                <?php echo ucfirst($booking['status']); ?>
                                            </span>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="card-body">
                                                <h5 class="card-title">
                                                    <a href="propertydetail.php?pid=<?php echo $booking['property_id']; ?>" class="text-secondary">
                                                        <?php echo htmlspecialchars($booking['title']); ?>
                                                    </a>
                                                </h5>
                                                <p class="card-text text-muted">
                                                    <i class="fas fa-map-marker-alt text-success mr-2"></i>
                                                    <?php echo htmlspecialchars($booking['location']); ?>, <?php echo htmlspecialchars($booking['city']); ?>
                                                </p>
                                                <p class="card-text">
                                                    <strong>Price:</strong> ETB <?php echo number_format($booking['price']); ?>
                                                </p>
                                                <p class="card-text">
                                                    <strong>Booking Date:</strong> <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?>
                                                </p>
                                                <?php if (isset($booking['move_in_date'])): ?>
                                                <p class="card-text">
                                                    <strong>Move-in Date:</strong> <?php echo date('M d, Y', strtotime($booking['move_in_date'])); ?>
                                                </p>
                                                <?php endif; ?>
                                                
                                                <!-- Payment Status -->
                                                <div class="payment-status payment-<?php echo strtolower($booking['payment_status'] ?? 'pending'); ?>">
                                                    Payment: <?php echo ucfirst($booking['payment_status'] ?? 'Pending'); ?>
                                                </div>
                                                
                                                <?php if ($booking['payment_status'] == 'deposit_paid'): ?>
                                                    <div class="alert alert-info mt-2">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <strong>Deposit Paid:</strong> ETB <?php echo number_format($booking['payment_amount'], 2); ?> (20%)
                                                                <br>
                                                                <small>Remaining Balance: ETB <?php echo number_format($booking['remaining_balance'], 2); ?></small>
                                                            </div>
                                                            <?php if ($booking['status'] == 'confirmed'): ?>
                                                                <a href="pay_remaining.php?booking_id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-success">
                                                                    Pay Remaining
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="mt-3">
                                                    <a href="booking_confirmation.php?booking_id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> View Details
                                                    </a>
                                                    
                                                    <?php if (($booking['payment_status'] ?? 'pending') != 'completed' && $booking['status'] != 'cancelled'): ?>
                                                        <a href="agreement.php?booking_id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-success">
                                                            <i class="fas fa-credit-card"></i> Pay Now
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($booking['status'] == 'pending'): ?>
                                                        <a href="cancel_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this booking?')">
                                                            <i class="fas fa-times"></i> Cancel
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i> You haven't made any property bookings yet.
                            <div class="mt-3">
                                <a href="property.php" class="btn btn-primary">Browse Properties</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    
    <!-- JavaScript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>
