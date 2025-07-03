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

// Check if payment is completed or deposit paid
if ($booking['payment_status'] !== 'deposit_paid' && $booking['payment_status'] !== 'completed') {
    header("Location: booking_confirmation.php?booking_id=" . $booking_id);
    exit();
}

// Format dates
$payment_date = date('F j, Y', strtotime($booking['payment_date']));
$receipt_number = 'REMSKO-' . date('Ymd', strtotime($booking['payment_date'])) . '-' . str_pad($booking_id, 6, '0', STR_PAD_LEFT);

// Get company details
$company_name = "Remsko Real Estate";
$company_address = "Addis Ababa, Ethiopia";
$company_phone = "+251 911 123 456";
$company_email = "info@remsko.com";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Payment Receipt - Remsko Real Estate</title>
    
    <!-- Include CSS files -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f1f1f1;
        }
        .receipt-logo {
            max-width: 150px;
            margin-bottom: 15px;
        }
        .receipt-title {
            font-size: 24px;
            color: #28a745;
            margin-bottom: 5px;
        }
        .receipt-number {
            font-size: 16px;
            color: #6c757d;
        }
        .receipt-date {
            font-size: 14px;
            color: #6c757d;
            margin-top: 5px;
        }
        .receipt-section {
            margin-bottom: 30px;
        }
        .receipt-section-title {
            font-size: 18px;
            color: #343a40;
            margin-bottom: 15px;
            border-bottom: 1px solid #f1f1f1;
            padding-bottom: 5px;
        }
        .receipt-table {
            width: 100%;
        }
        .receipt-table th {
            text-align: left;
            padding: 8px 0;
            color: #6c757d;
            font-weight: normal;
            width: 40%;
        }
        .receipt-table td {
            text-align: right;
            padding: 8px 0;
            font-weight: 500;
        }
        .receipt-total {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .receipt-total-table {
            width: 100%;
        }
        .receipt-total-table th {
            text-align: left;
            padding: 8px 0;
            color: #6c757d;
            font-weight: normal;
        }
        .receipt-total-table td {
            text-align: right;
            padding: 8px 0;
            font-weight: 500;
        }
        .receipt-total-row {
            border-top: 1px solid #dee2e6;
            font-weight: bold;
        }
        .receipt-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #f1f1f1;
            color: #6c757d;
            font-size: 14px;
        }
        .receipt-actions {
            text-align: center;
            margin-top: 30px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
                margin: 0;
            }
            .receipt-container {
                box-shadow: none;
                padding: 15px;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <?php include("include/header.php"); ?>
        
        <!-- Banner -->
        <div class="banner-full-row page-banner" style="background-image:url('images/breadcromb.jpg');">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>Payment Receipt</b></h2>
                    </div>
                    <div class="col-md-6">
                        <nav aria-label="breadcrumb" class="float-left float-md-right">
                            <ol class="breadcrumb bg-transparent m-0 p-0">
                                <li class="breadcrumb-item text-white"><a href="index.php">Home</a></li>
                                <li class="breadcrumb-item text-white"><a href="my_bookings.php">My Bookings</a></li>
                                <li class="breadcrumb-item active">Payment Receipt</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content -->
    <div class="full-row">
        <div class="container">
            <div class="receipt-container">
                <div class="receipt-header">
                    <img src="images/logo.png" alt="Remsko Real Estate" class="receipt-logo">
                    <h1 class="receipt-title">Payment Receipt</h1>
                    <div class="receipt-number">Receipt #: <?php echo $receipt_number; ?></div>
                    <div class="receipt-date">Date: <?php echo $payment_date; ?></div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="receipt-section">
                            <div class="receipt-section-title">From</div>
                            <p>
                                <strong><?php echo $company_name; ?></strong><br>
                                <?php echo $company_address; ?><br>
                                Phone: <?php echo $company_phone; ?><br>
                                Email: <?php echo $company_email; ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                                                   <div class="receipt-section-title">To</div>
                            <p>
                                <strong><?php echo htmlspecialchars($booking['name']); ?></strong><br>
                                Email: <?php echo htmlspecialchars($booking['email']); ?><br>
                                Phone: <?php echo htmlspecialchars($booking['phone']); ?><br>
                                Booking ID: <?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="receipt-section">
                    <div class="receipt-section-title">Property Details</div>
                    <table class="receipt-table">
                        <tr>
                            <th>Property:</th>
                            <td><?php echo htmlspecialchars($booking['title']); ?></td>
                        </tr>
                        <tr>
                            <th>Location:</th>
                            <td><?php echo htmlspecialchars($booking['location']); ?>, <?php echo htmlspecialchars($booking['city']); ?></td>
                        </tr>
                        <tr>
                            <th>Type:</th>
                            <td>For <?php echo htmlspecialchars($booking['stype']); ?></td>
                        </tr>
                        <tr>
                            <th>Move-in Date:</th>
                            <td><?php echo date('F j, Y', strtotime($booking['move_in_date'])); ?></td>
                        </tr>
                        <tr>
                            <th>Lease Term:</th>
                            <td><?php echo htmlspecialchars($booking['lease_term']); ?> months</td>
                        </tr>
                    </table>
                </div>
                
                <div class="receipt-section">
                    <div class="receipt-section-title">Payment Details</div>
                    <table class="receipt-table">
                        <tr>
                            <th>Payment Method:</th>
                            <td><?php echo htmlspecialchars($booking['payment_method']); ?></td>
                        </tr>
                        <tr>
                            <th>Transaction ID:</th>
                            <td><?php echo htmlspecialchars($booking['payment_transaction_id']); ?></td>
                        </tr>
                        <tr>
                            <th>Payment Date:</th>
                            <td><?php echo $payment_date; ?></td>
                        </tr>
                        <tr>
                            <th>Payment Status:</th>
                            <td>
                                <?php if ($booking['payment_status'] == 'completed'): ?>
                                    <span class="text-success">Fully Paid</span>
                                <?php else: ?>
                                    <span class="text-warning">Deposit Paid</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="receipt-total">
                        <table class="receipt-total-table">
                            <tr>
                                <th>Property Price:</th>
                                <td>ETB <?php echo number_format($booking['price']); ?></td>
                            </tr>
                            <?php if ($booking['payment_status'] == 'deposit_paid'): ?>
                                <tr>
                                    <th>Deposit Amount (20%):</th>
                                    <td>ETB <?php echo number_format($booking['payment_amount']); ?></td>
                                </tr>
                                <tr>
                                    <th>Remaining Balance:</th>
                                    <td>ETB <?php echo number_format($booking['remaining_balance']); ?></td>
                                </tr>
                                <tr class="receipt-total-row">
                                    <th>Amount Paid:</th>
                                    <td>ETB <?php echo number_format($booking['payment_amount']); ?></td>
                                </tr>
                            <?php else: ?>
                                <tr class="receipt-total-row">
                                    <th>Total Amount Paid:</th>
                                    <td>ETB <?php echo number_format($booking['price']); ?></td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
                
                <div class="receipt-footer">
                    <p>Thank you for choosing Remsko Real Estate!</p>
                    <p>This is a computer-generated receipt and does not require a signature.</p>
                </div>
                
                <div class="receipt-actions no-print">
                    <button onclick="window.print();" class="btn btn-success mr-2">
                        <i class="fas fa-print mr-2"></i> Print Receipt
                    </button>
                    <a href="my_bookings.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left mr-2"></i> Back to My Bookings
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="no-print">
        <?php include("include/footer.php"); ?>
    </div>
    
    <!-- JavaScript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>
