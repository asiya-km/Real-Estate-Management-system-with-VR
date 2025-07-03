<?php
session_start();
require("config.php");

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login1.php");
    exit();
}

// Validate booking ID
if (!isset($_GET['booking_id']) || !is_numeric($_GET['booking_id'])) {
    header("Location: my_bookings.php");
    exit();
}

$booking_id = intval($_GET['booking_id']);
$user_id = $_SESSION['uid'];

// Get booking details
$stmt = mysqli_prepare($con, "SELECT b.*, p.title, p.pimage, p.location, p.city, p.price, p.stype 
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

// Format dates
$booking_date = date('F j, Y', strtotime($booking['booking_date']));
$move_in_date = date('F j, Y', strtotime($booking['move_in_date']));
$end_date = !empty($booking['end_date']) ? date('F j, Y', strtotime($booking['end_date'])) : 'Not specified';

// Calculate deposit amount (20% of property price)
$full_price = $booking['price'];
$deposit_amount = $full_price * 0.2;
$deposit_amount = round($deposit_amount, 2);

// Calculate remaining balance
$remaining_balance = $full_price - $deposit_amount;

// Get payment status message
$payment_status_message = '';
$payment_status_class = '';
$show_payment_button = false;

switch ($booking['payment_status']) {
    case 'pending':
        $payment_status_message = 'Your booking has been submitted. Please proceed with the payment to secure your booking.';
        $payment_status_class = 'alert-warning';
        $show_payment_button = true;
        break;
    case 'pending_verification':
        $payment_status_message = 'Your payment is pending verification. We will update your booking status once the payment is verified.';
        $payment_status_class = 'alert-info';
        break;
    case 'scheduled':
        $payment_status_message = 'Your cash payment is scheduled for ' . date('F j, Y', strtotime($booking['scheduled_payment_date'])) . '. Please visit our office on the scheduled date with the required amount.';
        $payment_status_class = 'alert-info';
        break;
    case 'deposit_paid':
        $payment_status_message = 'Your deposit payment has been received. The remaining balance will be due upon contract signing.';
        $payment_status_class = 'alert-success';
        break;
    case 'completed':
        $payment_status_message = 'Your payment has been completed. Thank you for your booking!';
        $payment_status_class = 'alert-success';
        break;
    case 'failed':
        $payment_status_message = 'Your payment has failed. Please try again or contact our support team for assistance.';
        $payment_status_class = 'alert-danger';
        $show_payment_button = true;
        break;
    default:
        $payment_status_message = 'Your booking status is being processed.';
        $payment_status_class = 'alert-info';
}

// Check for success message
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['success_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Booking Confirmation - Remsko Real Estate</title>
    
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
        .booking-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .booking-id {
            font-size: 16px;
            color: #6c757d;
            margin-top: 5px;
        }
        .property-summary {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .booking-details {
            margin-bottom: 30px;
        }
        .booking-section-title {
            font-size: 18px;
            color: #343a40;
            margin-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
        }
        .booking-table {
            width: 100%;
        }
        .booking-table th {
            text-align: left;
            padding: 8px 0;
            color: #6c757d;
            font-weight: normal;
            width: 40%;
        }
        .booking-table td {
            text-align: right;
            padding: 8px 0;
            font-weight: 500;
        }
        .payment-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .next-steps {
            margin-top: 30px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
        }
        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }
        .status-confirmed {
            background-color: #28a745;
            color: #fff;
        }
        .status-cancelled {
            background-color: #dc3545;
            color: #fff;
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
                    <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>Booking Confirmation</b></h2>
                </div>
                <div class="col-md-6">
                    <nav aria-label="breadcrumb" class="float-left float-md-right">
                        <ol class="breadcrumb bg-transparent m-0 p-0">
                            <li class="breadcrumb-item text-white"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item text-white"><a href="my_bookings.php">My Bookings</a></li>
                            <li class="breadcrumb-item active">Confirmation</li>
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
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <div class="booking-header">
                    <?php if ($booking['status'] == 'confirmed'): ?>
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h3>Booking Confirmed</h3>
                    <?php elseif ($booking['status'] == 'pending'): ?>
                        <i class="fas fa-clock fa-4x text-warning mb-3"></i>
                        <h3>Booking Pending</h3>
                    <?php elseif ($booking['status'] == 'cancelled'): ?>
                        <i class="fas fa-times-circle fa-4x text-danger mb-3"></i>
                        <h3>Booking Cancelled</h3>
                    <?php endif; ?>
                    <div class="booking-id">Booking ID: <?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></div>
                    <div>Booking Date: <?php echo $booking_date; ?></div>
                </div>
                
                <div class="alert <?php echo $payment_status_class; ?>">
                    <?php echo $payment_status_message; ?>
                </div>
                
                <div class="property-summary">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="admin/property/<?php echo htmlspecialchars($booking['pimage']); ?>" alt="Property" class="img-fluid rounded">
                        </div>
                        <div class="col-md-8">
                            <h4 class="text-secondary"><?php echo htmlspecialchars($booking['title']); ?></h4>
                            <p><i class="fas fa-map-marker-alt text-success"></i> <?php echo htmlspecialchars($booking['location']); ?>, <?php echo htmlspecialchars($booking['city']); ?></p>
                            <p class="text-success h5">ETB <?php echo number_format($booking['price']); ?></p>
                            <p><strong>Type:</strong> For <?php echo htmlspecialchars($booking['stype']); ?></p>
                            <div class="mt-2">
                                <span class="status-badge <?php echo 'status-' . $booking['status']; ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="booking-details">
                    <div class="booking-section-title">Booking Details</div>
                    <table class="booking-table">
                        <tr>
                            <th>Name:</th>
                            <td><?php echo htmlspecialchars($booking['name']); ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?php echo htmlspecialchars($booking['email']); ?></td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td><?php echo htmlspecialchars($booking['phone']); ?></td>
                        </tr>
                        <tr>
                            <th>Move-in Date:</th>
                            <td><?php echo $move_in_date; ?></td>
                        </tr>
                        <tr>
                            <th>Lease Term:</th>
                            <td><?php echo htmlspecialchars($booking['lease_term']); ?> months</td>
                        </tr>
                        <tr>
                            <th>End Date:</th>
                            <td><?php echo $end_date; ?></td>
                        </tr>
                        <?php if (!empty($booking['message'])): ?>
                        <tr>
                            <th>Additional Information:</th>
                            <td><?php echo htmlspecialchars($booking['message']); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
                
                <div class="payment-info">
                    <div class="booking-section-title">Payment Information</div>
                    <table class="booking-table">
                        <tr>
                            <th>Total Property Price:</th>
                            <td>ETB <?php echo number_format($booking['price']); ?></td>
                        </tr>
                        <tr>
                            <th>Deposit Amount (20%):</th>
                            <td>ETB <?php echo number_format($deposit_amount); ?></td>
                        </tr>
                        <tr>
                            <th>Remaining Balance:</th>
                            <td>ETB <?php echo number_format($remaining_balance); ?></td>
                        </tr>
                        <tr>
                            <th>Payment Status:</th>
                            <td>
                                <?php if ($booking['payment_status'] == 'completed'): ?>
                                    <span class="text-success">Fully Paid</span>
                                <?php elseif ($booking['payment_status'] == 'deposit_paid'): ?>
                                    <span class="text-warning">Deposit Paid</span>
                                <?php elseif ($booking['payment_status'] == 'pending_verification'): ?>
                                    <span class="text-info">Pending Verification</span>
                                <?php elseif ($booking['payment_status'] == 'scheduled'): ?>
                                    <span class="text-info">Payment Scheduled</span>
                                <?php elseif ($booking['payment_status'] == 'failed'): ?>
                                    <span class="text-danger">Payment Failed</span>
                                <?php else: ?>
                                    <span class="text-danger">Pending Payment</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if (!empty($booking['payment_method'])): ?>
                        <tr>
                            <th>Payment Method:</th>
                            <td><?php echo htmlspecialchars($booking['payment_method']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($booking['payment_date'])): ?>
                        <tr>
                            <th>Payment Date:</th>
                            <td><?php echo date('F j, Y', strtotime($booking['payment_date'])); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($booking['payment_transaction_id'])): ?>
                        <tr>
                            <th>Transaction ID:</th>
                            <td><?php echo htmlspecialchars($booking['payment_transaction_id']); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
                
                <div class="next-steps">
                    <div class="booking-section-title">Next Steps</div>
                    <ol>
                                               <?php if ($booking['payment_status'] == 'pending' || $booking['payment_status'] == 'failed'): ?>
                            <li>Complete the deposit payment to secure your booking.</li>
                            <li>Once your payment is verified, we will update your booking status.</li>
                            <li>Our team will contact you to schedule a contract signing appointment.</li>
                            <li>Bring the remaining balance and required documents for contract signing.</li>
                        <?php elseif ($booking['payment_status'] == 'pending_verification'): ?>
                            <li>Your payment is being verified by our team.</li>
                            <li>Once verified, we will update your booking status.</li>
                            <li>Our team will contact you to schedule a contract signing appointment.</li>
                            <li>Bring the remaining balance and required documents for contract signing.</li>
                        <?php elseif ($booking['payment_status'] == 'scheduled'): ?>
                            <li>Visit our office on the scheduled payment date.</li>
                            <li>Make the deposit payment to secure your booking.</li>
                            <li>Our team will schedule a contract signing appointment.</li>
                            <li>Bring the remaining balance and required documents for contract signing.</li>
                        <?php elseif ($booking['payment_status'] == 'deposit_paid'): ?>
                            <li>Your deposit has been received and your booking is confirmed.</li>
                            <li>Our team will contact you to schedule a contract signing appointment.</li>
                            <li>Bring the remaining balance of ETB <?php echo number_format($remaining_balance); ?> for contract signing.</li>
                            <li>Prepare necessary identification documents for contract signing.</li>
                        <?php elseif ($booking['payment_status'] == 'completed'): ?>
                            <li>Your payment has been completed and your booking is confirmed.</li>
                            <li>Our team will contact you to schedule a contract signing appointment.</li>
                            <li>Prepare necessary identification documents for contract signing.</li>
                            <li>Get ready to move in on your scheduled date: <?php echo $move_in_date; ?></li>
                        <?php endif; ?>
                    </ol>
                </div>
                
                <div class="text-center mt-4">
                    <?php if ($show_payment_button): ?>
                        <a href="payment.php?booking_id=<?php echo $booking_id; ?>" class="btn btn-success mr-2">
                            <i class="fas fa-credit-card mr-2"></i> Make Payment
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($booking['payment_status'] == 'deposit_paid' || $booking['payment_status'] == 'completed'): ?>
                        <a href="payment_receipt.php?booking_id=<?php echo $booking_id; ?>" class="btn btn-info mr-2">
                            <i class="fas fa-file-invoice-dollar mr-2"></i> View Receipt
                        </a>
                    <?php endif; ?>
                    
                    <a href="my_bookings.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left mr-2"></i> Back to My Bookings
                    </a>
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
