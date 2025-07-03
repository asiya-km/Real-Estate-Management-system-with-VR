<?php
session_start();
include("config.php");
$payment_success_message = "";
if (isset($_SESSION['payment_success_message'])) {
    $payment_success_message = $_SESSION['payment_success_message'];
    unset($_SESSION['payment_success_message']);
}
if (!isset($_SESSION['uid'])) {
    header("location:login1.php");
    exit();
}

$user_id = $_SESSION['uid'];

// Fetch user's payment history
$query = "SELECT b.*, p.title, p.pimage, p.location, p.city, p.price 
          FROM bookings b 
          JOIN property p ON b.property_id = p.pid 
          WHERE b.user_id = ? AND b.payment_status IS NOT NULL
          ORDER BY b.payment_date DESC";
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
    <title>My Payments - Remsko Real Estate</title>
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
        .payment-card {
            transition: all 0.3s;
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .payment-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        .payment-image {
            height: 200px;
            object-fit: cover;
        }
        .payment-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            background-color: #28a745;
            color: white;
        }
        .payment-details {
            padding: 15px;
            border-top: 1px solid #eee;
            background-color: #f8f9fa;
        }
        .payment-details-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .payment-details-label {
            font-weight: bold;
            color: #6c757d;
        }
        .payment-details-value {
            text-align: right;
        }
        .payment-receipt-btn {
            margin-top: 10px;
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
                    <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>My Payments</b></h2>
                </div>
                <div class="col-md-6">
                    <nav aria-label="breadcrumb" class="float-left float-md-right">
                        <ol class="breadcrumb bg-transparent m-0 p-0">
                            <li class="breadcrumb-item text-white"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item text-white"><a href="user_dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">My Payments</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content -->
    <div class="full-row">
        <div class="container">
            <?php if (!empty($payment_success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong><i class="fas fa-check-circle"></i> Success!</strong> <?php echo $payment_success_message; ?>
                </div>
            <?php endif; ?>
            
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
                            <h4 class="double-down-line-left text-secondary position-relative pb-4 mb-4">My Payment History</h4>
                        </div>
                    </div>
                    
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($payment = mysqli_fetch_assoc($result)): ?>
                            <div class="payment-card">
                                <div class="card">
                                    <div class="row no-gutters">
                                        <div class="col-md-4 position-relative">
                                            <img src="admin/property/<?php echo htmlspecialchars($payment['pimage']); ?>" class="payment-image w-100" alt="Property">
                                            <?php if ($payment['payment_status'] == 'completed' && isset($payment['admin_confirmed']) && $payment['admin_confirmed'] == 1): ?>
                                                <span class="payment-badge" style="background-color: #28a745;">Confirmed</span>
                                            <?php elseif ($payment['payment_status'] == 'completed'): ?>
                                                <span class="payment-badge" style="background-color: #17a2b8;">Paid</span>
                                            <?php elseif ($payment['payment_status'] == 'pending'): ?>
                                                <span class="payment-badge" style="background-color: #ffc107; color: #212529;">Pending</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="card-body">
                                                <h5 class="card-title">
                                                    <a href="propertydetail.php?pid=<?php echo $payment['property_id']; ?>" class="text-secondary">
                                                        <?php echo htmlspecialchars($payment['title']); ?>
                                                    </a>
                                                </h5>
                                                <p class="card-text text-muted">
                                                    <i class="fas fa-map-marker-alt text-success mr-2"></i>
                                                    <?php echo htmlspecialchars($payment['location']); ?>, <?php echo htmlspecialchars($payment['city']); ?>
                                                </p>
                                                
                                                <div class="payment-details">
                                                    <div class="payment-details-row">
                                                        <div class="payment-details-label">Payment Date:</div>
                                                        <div class="payment-details-value">
                                                            <?php echo date('M d, Y', strtotime($payment['payment_date'])); ?>
                                                        </div>
                                                    </div>
                                                    <div class="payment-details-row">
                                                        <div class="payment-details-label">Amount Paid:</div>
                                                        <div class="payment-details-value">
                                                            ETB <?php echo number_format($payment['payment_amount']); ?>
                                                        </div>
                                                    </div>
                                                    <div class="payment-details-row">
                                                        <div class="payment-details-label">Payment Method:</div>
                                                        <div class="payment-details-value">
                                                            <?php echo htmlspecialchars($payment['payment_method']); ?>
                                                        </div>
                                                    </div>
                                                    <div class="payment-details-row">
                                                        <div class="payment-details-label">Status:</div>
                                                        <div class="payment-details-value">
                                                            <?php if ($payment['payment_status'] == 'completed' && isset($payment['admin_confirmed']) && $payment['admin_confirmed'] == 1): ?>
                                                                <span class="badge badge-success">Confirmed by Admin</span>
                                                            <?php elseif ($payment['payment_status'] == 'completed'): ?>
                                                                <span class="badge badge-info">Paid </span>
                                                            <?php elseif ($payment['payment_status'] == 'pending'): ?>
                                                                <span class="badge badge-warning">Pending</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <?php if (!empty($payment['payment_transaction_id'])): ?>
                                                    <div class="payment-details-row">
                                                        <div class="payment-details-label">Transaction ID:</div>
                                                        <div class="payment-details-value">
                                                            <?php echo htmlspecialchars($payment['payment_transaction_id']); ?>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="text-center payment-receipt-btn">
                                                        <?php if ($payment['payment_status'] == 'completed'): ?>
                                                            <a href="payment_receipt.php?booking_id=<?php echo $payment['id']; ?>" class="btn btn-sm btn-success">
                                                                <i class="fas fa-file-invoice mr-2"></i> View Receipt
                                                            </a>
                                                            <?php if (!(isset($payment['admin_confirmed']) && $payment['admin_confirmed'] == 1)): ?>
                                                                <p class="text-muted mt-2 small"></p>
                                                            <?php endif; ?>
                                                        <?php elseif ($payment['payment_status'] == 'pending'): ?>
                                                            <span class="text-warning">
                                                                <i class="fas fa-clock mr-2"></i> Payment verification in progress
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i> You haven't made any payments yet.
                            <div class="mt-3">
                                <a href="my_bookings.php" class="btn btn-primary">View My Bookings</a>
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
