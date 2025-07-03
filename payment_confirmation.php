<?php
session_start();
require("config.php");

// Get transaction reference and status from URL parameters
$tx_ref = isset($_GET['tx_ref']) ? $_GET['tx_ref'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$transaction_id = isset($_GET['transaction_id']) ? $_GET['transaction_id'] : '';

// Validate transaction reference
if (empty($tx_ref)) {
    header("Location: user_dashboard.php");
    exit();
}

// Get booking details from transaction reference
$query = "SELECT b.*, p.title, p.price, p.location, p.city, p.pimage, p.type, p.stype, u.uname, u.uemail, u.uphone 
          FROM bookings b 
          JOIN property p ON b.property_id = p.pid 
          JOIN user u ON b.user_id = u.uid
          WHERE b.payment_reference = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 's', $tx_ref);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$booking = mysqli_fetch_assoc($result)) {
    header("Location: user_dashboard.php");
    exit();
}

$booking_id = $booking['id'];
$payment_success = ($status === 'success' || $status === 'successful');

// Update booking status if payment was successful
if ($payment_success && !empty($transaction_id)) {
    $update_query = "UPDATE bookings SET 
                    payment_status = 'paid', 
                    transaction_id = ? 
                    WHERE id = ? AND payment_status != 'paid'";
    $update_stmt = mysqli_prepare($con, $update_query);
    mysqli_stmt_bind_param($update_stmt, 'si', $transaction_id, $booking_id);
    mysqli_stmt_execute($update_stmt);
    
    // Create notification in database
    $message = "Your payment for booking #" . str_pad($booking_id, 6, '0', STR_PAD_LEFT) . " has been confirmed.";
    $notification_query = "INSERT INTO notifications (user_id, message, type, booking_id, created_at) 
                          VALUES (?, ?, 'payment_success', ?, NOW())";
    $notification_stmt = mysqli_prepare($con, $notification_query);
    $user_id = $booking['user_id'];
    $notification_type = 'payment_success';
    mysqli_stmt_bind_param($notification_stmt, 'isi', $user_id, $message, $booking_id);
    mysqli_stmt_execute($notification_stmt);
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
            text-align: center;
        }
        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }
        .pending-icon {
            font-size: 80px;
            color: #ffc107;
            margin-bottom: 20px;
        }
        .property-summary {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            text-align: left;
        }
        .receipt-box {
            border: 1px dashed #ccc;
            padding: 20px;
            margin: 20px 0;
            background-color: #f9f9f9;
        }
        .action-buttons {
            margin-top: 30px;
        }
        .action-buttons .btn {
            margin: 0 10px;
        }
        .countdown {
            font-size: 18px;
            color: #6c757d;
            margin-top: 15px;
        }
        @media print {
            .no-print {
                display: none;
            }
            .receipt-box {
                border: 1px solid #ccc;
            }
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
                <?php if ($payment_success): ?>
                    <i class="fas fa-check-circle success-icon"></i>
                    <h2>Payment Successful!</h2>
                    <p class="lead">Thank you for your payment. Your booking has been confirmed.</p>
                    
                    <div class="receipt-box">
                        <h4>Payment Receipt</h4>
                        <div class="row mt-4">
                            <div class="col-md-6 text-left">
                                <p><strong>Booking Reference:</strong> #<?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></p>
                                <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($transaction_id); ?></p>
                                <p><strong>Date:</strong> <?php echo date('d M Y, h:i A'); ?></p>
                            </div>
                            <div class="col-md-6 text-right">
                                <p><strong>Customer:</strong> <?php echo htmlspecialchars($booking['uname']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['uemail']); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($booking['uphone']); ?></p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-8 text-left">
                                <p><strong>Property:</strong> <?php echo htmlspecialchars($booking['title']); ?></p>
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($booking['location']); ?>, <?php echo htmlspecialchars($booking['city']); ?></p>
                            </div>
                            <div class="col-md-4 text-right">
                                <h5>Amount Paid</h5>
                                <h4 class="text-success">ETB <?php echo number_format($booking['price']); ?></h4>
                            </div>
                        </div>
                    </div>
                    
                    <div class="no-print">
                        <button onclick="window.print()" class="btn btn-outline-secondary mt-3">
                            <i class="fas fa-print mr-2"></i> Print Receipt
                        </button>
                        
                        <div class="countdown mt-4" id="countdown">
                            Redirecting to dashboard in <span id="timer">10</span> seconds...
                        </div>
                    </div>
                <?php else: ?>
                    <i class="fas fa-clock pending-icon"></i>
                    <h2>Payment Processing</h2>
                    <p class="lead">Your payment is being processed. We'll update you once it's confirmed.</p>
                    
                    <div class="property-summary">
                        <div class="row">
                            <div class="col-md-4">
                                <img src="admin/property/<?php echo htmlspecialchars($booking['pimage']); ?>" alt="Property" class="img-fluid rounded">
                            </div>
                            <div class="col-md-8">
                                <h4 class="text-secondary"><?php echo htmlspecialchars($booking['title']); ?></h4>
                                <p><i class="fas fa-map-marker-alt text-success"></i> <?php echo htmlspecialchars($booking['location']); ?>, <?php echo htmlspecialchars($booking['city']); ?></p>
                                <p class="text-success h5">ETB <?php echo number_format($booking['price']); ?></p>
                                <p><strong>Booking Reference:</strong> #<?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="countdown mt-4" id="countdown">
                        Redirecting to dashboard in <span id="timer">10</span> seconds...
                    </div>
                <?php endif; ?>
                
                <div class="action-buttons no-print">
                    <a href="booking_confirmation.php?booking_id=<?php echo $booking_id; ?>" class="btn btn-primary">
                        <i class="fas fa-file-alt mr-2"></i> View Booking Details
                    </a>
                    <a href="user_dashboard.php#bookings" class="btn btn-secondary" id="dashboard-link">
                        <i class="fas fa-home mr-2"></i> Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <?php include("include/footer.php"); ?>
    
    <!-- JavaScript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        // Countdown timer for auto-redirect
        var timeLeft = 10;
        var timerId = setInterval(countdown, 1000);
        
        function countdown() {
            if (timeLeft == 0) {
                clearTimeout(timerId);
                window.location.href = "user_dashboard.php#bookings";
            } else {
                document.getElementById("timer").innerHTML = timeLeft;
                timeLeft--;
            }
        }
        
        // Stop countdown if user clicks on any link
        document.getElementById("dashboard-link").addEventListener("click", function() {
            clearTimeout(timerId);
        });
    </script>
</body>
</html>