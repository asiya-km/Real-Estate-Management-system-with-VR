<?php
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
$payment_method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
$payment_status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';

// Handle Chapa callback parameters
$tx_ref = isset($_REQUEST['tx_ref']) ? $_REQUEST['tx_ref'] : '';
$chapa_status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$transaction_id = isset($_REQUEST['transaction_id']) ? $_REQUEST['transaction_id'] : '';

// If this is a Chapa callback, set the payment method and status
if (!empty($tx_ref) && !empty($chapa_status)) {
    $payment_method = 'chapa';
    $payment_status = ($chapa_status === 'successful') ? 'success' : 'pending';
    
    // Extract booking_id from tx_ref if it's in the format "booking-{id}-{timestamp}"
    if (empty($booking_id) && preg_match('/booking-(\d+)-/', $tx_ref, $matches)) {
        $booking_id = intval($matches[1]);
    }
}

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

// If this is a Chapa callback with successful payment, update the booking status in the database
if ($payment_method === 'chapa' && $payment_status === 'success' && !empty($transaction_id)) {
    $update_query = "UPDATE bookings SET payment_status = 'paid', payment_reference = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($con, $update_query);
    mysqli_stmt_bind_param($update_stmt, 'si', $transaction_id, $booking_id);
    mysqli_stmt_execute($update_stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Payment Confirmation - Real Estate PHP</title>
    
    <!-- Include CSS files -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        .thank-you-container {
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
        .payment-instructions {
            background-color: #e9f7ef;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            text-align: left;
        }
        .action-buttons {
            margin-top: 30px;
        }
        .action-buttons .btn {
            margin: 0 10px;
        }
        /* Modal styling */
        .confirmation-modal .modal-header {
            border-bottom: none;
            padding-bottom: 0;
        }
        .confirmation-modal .modal-body {
            padding-top: 0;
        }
        .confirmation-modal .modal-icon {
            font-size: 60px;
            margin-bottom: 15px;
        }
        .confirmation-modal .success-icon {
            color: #28a745;
        }
        .confirmation-modal .pending-icon {
            color: #ffc107;
        }
        .chapa-badge {
            display: inline-block;
            background-color: #0066cc;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            margin-left: 5px;
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
            <div class="thank-you-container">
                <?php if ($payment_status === 'success'): ?>
                    <i class="fas fa-check-circle success-icon"></i>
                    <h2>Payment Successful!</h2>
                    <p class="lead">Thank you for your payment. Your booking has been confirmed.</p>
                <?php else: ?>
                    <i class="fas fa-clock pending-icon"></i>
                    <h2>Payment Processing</h2>
                    <p class="lead">Your payment is being processed. We'll update you once it's confirmed.</p>
                <?php endif; ?>
                
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
                            <p>
                                <strong>Payment Method:</strong> 
                                <?php if ($payment_method === 'chapa'): ?>
                                    Chapa <span class="chapa-badge">Online Payment</span>
                                    <?php if (!empty($transaction_id)): ?>
                                        <br><small class="text-muted">Transaction ID: <?php echo htmlspecialchars($transaction_id); ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php echo ucfirst(htmlspecialchars($payment_method)); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <?php if ($payment_method === 'bank' && $payment_status === 'pending'): ?>
                <div class="payment-instructions">
                    <h4 class="mb-3">Bank Transfer Instructions</h4>
                    <p>Please transfer the total amount to the following bank account:</p>
                    <p><strong>Bank Name:</strong> Commercial Bank of Ethiopia</p>
                    <p><strong>Account Name:</strong> Remsko Real Estate</p>
                    <p><strong>Account Number:</strong> 1000123456789</p>
                    <p><strong>Reference:</strong> Booking #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></p>
                    <p class="mt-3"><strong>Important:</strong> Please include your booking reference number in the payment reference to help us match your payment.</p>
                </div>
                <?php elseif (($payment_method === 'telebirr' || $payment_method === 'cbe') && $payment_status === 'pending'): ?>
                <div class="payment-instructions">
                    <h4 class="mb-3"><?php echo ucfirst($payment_method); ?> Payment Instructions</h4>
                    <p>Your payment request has been sent to your mobile number. Please follow these steps to complete your payment:</p>
                    <ol class="text-left">
                        <li>Check your phone for a payment notification</li>
                        <li>Open your <?php echo ucfirst($payment_method); ?> app</li>
                        <li>Approve the payment request</li>
                        <li>Enter your PIN to confirm</li>
                    </ol>
                    <p class="mt-3"><strong>Note:</strong> If you don't receive a notification, please check your payment status in your account dashboard.</p>
                </div>
                <?php elseif ($payment_method === 'chapa' && $payment_status === 'pending'): ?>
                <div class="payment-instructions">
                    <h4 class="mb-3">Chapa Payment</h4>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>A payment window has been opened in a new tab.</strong>
                        <p>Please complete your payment there. If you closed the payment window, you can click the button below to reopen it.</p>
                        <?php if (isset($_SESSION['chapa_checkout_url'])): ?>
                        <a href="<?php echo htmlspecialchars($_SESSION['chapa_checkout_url']); ?>" target="_blank" class="btn btn-primary mt-3">
                            <i class="fas fa-external-link-alt mr-2"></i> Reopen Payment Window
                        </a>
                        <?php endif; ?>
                    </div>
                    <p class="mt-3"><strong>Note:</strong> Once your payment is complete, your booking status will be updated automatically.</p>
                </div>
                <?php endif; ?>
                
                <div class="action-buttons">
                    <a href="booking_confirmation.php?booking_id=<?php echo $booking_id; ?>" class="btn btn-primary">
                        <i class="fas fa-file-alt mr-2"></i> View Booking Details
                    </a>
                    <a href="user_dashboard.php#bookings" class="btn btn-secondary">
                        <i class="fas fa-home mr-2"></i> Go to Dashboard
                    </a>
                </div>
                
                <?php if ($payment_status === 'pending'): ?>
                <div class="mt-5">
                    <p class="text-muted">Having trouble with your payment? <a href="contact.php">Contact our support team</a></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Payment Confirmation Modal -->
    <div class="modal fade confirmation-modal" id="paymentConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="paymentConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <?php if ($payment_status === 'success'): ?>
                        <i class="fas fa-check-circle modal-icon success-icon"></i>
                        <h3 class="mb-3">Payment Successful!</h3>
                        <p>Your booking #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?> has been confirmed.</p>
                        <?php if ($payment_method === 'chapa' && !empty($transaction_id)): ?>
                            <p class="text-muted">Chapa Transaction ID: <?php echo htmlspecialchars($transaction_id); ?></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <i class="fas fa-clock modal-icon pending-icon"></i>
                        <h3 class="mb-3">Payment Processing</h3>
                        <p>Your payment for booking #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?> is being processed.</p>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <a href="booking_confirmation.php?booking_id=<?php echo $booking_id; ?>" class="btn btn-primary">
                            View Booking Details
                        </a>
                        <a href="user_dashboard.php#bookings" class="btn btn-secondary ml-2">
                            Go to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include("include/footer.php"); ?>
    
    <!-- JavaScript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Show the payment confirmation modal automatically when the page loads
            $('#paymentConfirmationModal').modal('show');
            
            // Auto-close the modal after 8 seconds if payment was successful
            <?php if ($payment_status === 'success'): ?>
            setTimeout(function() {
                $('#paymentConfirmationModal').modal('hide');
            }, 8000);
            <?php endif; ?>
        });
    </script>
</body>
</html>
