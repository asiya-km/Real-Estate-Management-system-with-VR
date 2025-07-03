<?php
session_start();
require("../config.php");

// Check if admin is logged in
if (!isset($_SESSION['auser'])) {
    header("location:index.php");
    exit();
}

// Check if payment ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("location:payment_history.php");
    exit();
}

$payment_id = intval($_GET['id']);

// Get payment details
$query = "SELECT b.*, u.uname, u.uemail, u.uphone, p.title as property_title, p.price as property_price, 
          p.location, p.city, p.pimage 
          FROM bookings b 
          JOIN user u ON b.user_id = u.uid 
          JOIN property p ON b.property_id = p.pid 
          WHERE b.id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'i', $payment_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$payment = mysqli_fetch_assoc($result)) {
    header("location:payment_history.php");
    exit();
}

// Handle form submission for adding admin notes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_notes'])) {
    $admin_notes = $_POST['admin_notes'];
    
    $update_query = "UPDATE bookings SET admin_notes = ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $update_query);
    mysqli_stmt_bind_param($stmt, 'si', $admin_notes, $payment_id);
    mysqli_stmt_execute($stmt);
    
    // Redirect to refresh the page
    header("location:view_payment.php?id=" . $payment_id . "&updated=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>LM HOMES | View Payment</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    
    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    
    <!-- Feathericon CSS -->
    <link rel="stylesheet" href="assets/css/feathericon.min.css">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        .payment-details {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .payment-property {
            margin-bottom: 20px;
        }
        .property-image {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }
        .detail-row {
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .detail-label {
            font-weight: bold;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <?php include("header.php"); ?>
        
        <div class="page-wrapper">
            <div class="content container-fluid">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="row">
                        <div class="col"><p>.</p>
                            <h3 class="page-title">Payment Details</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="payment_history.php">Payment History</a></li>
                                <li class="breadcrumb-item active">Payment Details</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /Page Header -->
                
                <?php if (isset($_GET['updated'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <strong>Success!</strong> Payment details have been updated.
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h4 class="card-title">Payment #<?php echo $payment_id; ?></h4>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <a href="payment_history.php" class="btn btn-sm btn-default">
                                            <i class="fa fa-arrow-left"></i> Back to Payment History
                                        </a>
                                        <?php if ($payment['payment_status'] != 'completed'): ?>
                                        <a href="update_payment.php?id=<?php echo $payment_id; ?>&status=completed" class="btn btn-sm btn-success" onclick="return confirm('Mark this payment as completed?')">
                                            <i class="fa fa-check"></i> Mark as Completed
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="payment-details">
                                            <h4>Payment Information</h4>
                                            <div class="detail-row">
                                                <div class="detail-label">Status</div>
                                                <div>
                                                    <?php 
                                                    $status_class = '';
                                                    switch ($payment['payment_status']) {
                                                        case 'completed':
                                                            $status_class = 'success';
                                                            break;
                                                        case 'pending':
                                                            $status_class = 'warning';
                                                            break;
                                                        case 'failed':
                                                            $status_class = 'danger';
                                                            break;
                                                        default:
                                                            $status_class = 'default';
                                                    }
                                                    ?>
                                                    <span class="badge badge-<?php echo $status_class; ?>"><?php echo ucfirst($payment['payment_status']); ?></span>
                                                </div>
                                            </div>
                                            <div class="detail-row">
                                                <div class="detail-label">Amount</div>
                                                <div>ETB <?php echo number_format($payment['payment_amount'], 2); ?></div>
                                            </div>
                                            <div class="detail-row">
                                                <div class="detail-label">Payment Method</div>
                                                <div><?php echo htmlspecialchars($payment['payment_method']); ?></div>
                                            </div>
                                            <div class="detail-row">
                                                <div class="detail-label">Transaction ID</div>
                                                <div><?php echo htmlspecialchars($payment['payment_transaction_id']); ?></div>
                                            </div>
                                            <div class="detail-row">
                                                <div class="detail-label">Payment Date</div>
                                                <div><?php echo date('F d, Y H:i:s', strtotime($payment['payment_date'])); ?></div>
                                            </div>
                                        </div>
                                        
                                        <div class="payment-details">
                                            <h4>Customer Information</h4>
                                            <div class="detail-row">
                                                <div class="detail-label">Name</div>
                                                <div><?php echo htmlspecialchars($payment['uname']); ?></div>
                                            </div>
                                            <div class="detail-row">
                                                <div class="detail-label">Email</div>
                                                <div><?php echo htmlspecialchars($payment['uemail']); ?></div>
                                            </div>
                                            <div class="detail-row">
                                                <div class="detail-label">Phone</div>
                                                <div><?php echo htmlspecialchars($payment['uphone']); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="payment-property">
                                            <h4>Property Information</h4>
                                            <img src="../admin/property/<?php echo htmlspecialchars($payment['pimage']); ?>" alt="Property" class="property-image">
                                            <div class="detail-row">
                                                <div class="detail-label">Title</div>
                                                <div><?php echo htmlspecialchars($payment['property_title']); ?></div>
                                            </div>
                                            <div class="detail-row">
                                                <div class="detail-label">Location</div>
                                                <div><?php echo htmlspecialchars($payment['location']); ?>, <?php echo htmlspecialchars($payment['city']); ?></div>
                                            </div>
                                            <div class="detail-row">
                                                <div class="detail-label">Price</div>
                                                <div>ETB <?php echo number_format($payment['property_price'], 2); ?></div>
                                            </div>
                                            <div class="detail-row">
                                                <div class="detail-label">Booking Date</div>
                                                <div><?php echo date('F d, Y', strtotime($payment['date'])); ?></div>
                                            </div>
                                        </div>
                                        
                                        <div class="payment-details">
                                            <h4>Admin Notes</h4>
                                            <form method="post">
                                                <div class="form-group">
                                                    <textarea name="admin_notes" class="form-control" rows="5"><?php echo htmlspecialchars($payment['admin_notes'] ?? ''); ?></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Save Notes</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Actions -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Payment Actions</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <a href="print_receipt.php?id=<?php echo $payment_id; ?>" target="_blank" class="btn btn-primary btn-block">
                                            <i class="fa fa-print"></i> Print Receipt
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="email_receipt.php?id=<?php echo $payment_id; ?>" class="btn btn-info btn-block" onclick="return confirm('Send receipt to customer email?')">
                                            <i class="fa fa-envelope"></i> Email Receipt to Customer
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <?php if ($payment['payment_status'] == 'completed'): ?>
                                        <a href="update_payment.php?id=<?php echo $payment_id; ?>&status=refunded" class="btn btn-warning btn-block" onclick="return confirm('Process refund for this payment?')">
                                            <i class="fa fa-undo"></i> Process Refund
                                        </a>
                                        <?php else: ?>
                                        <a href="update_payment.php?id=<?php echo $payment_id; ?>&status=completed" class="btn btn-success btn-block" onclick="return confirm('Mark this payment as completed?')">
                                            <i class="fa fa-check"></i> Mark as Completed
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="assets/js/jquery-3.2.1.min.js"></script>
    
    <!-- Bootstrap Core JS -->
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    
    <!-- Slimscroll JS -->
    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
</body>
</html>
