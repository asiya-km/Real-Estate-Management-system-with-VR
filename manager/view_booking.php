<?php
session_start();
include("config.php");

if (!isset($_SESSION['auser'])) {
    header("location:../login1.php");
    exit();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validate booking ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid booking ID";
    header("Location: admin_bookings.php");
    exit();
}

$booking_id = intval($_GET['id']);

// Fetch booking details with property and user information
$query = "SELECT b.*, p.title, p.pimage, p.location, p.city, p.type, p.stype, p.bedroom, p.bathroom, p.price,
          u.uname, u.uemail, u.uphone, u.uimage
          FROM bookings b 
          JOIN property p ON b.property_id = p.pid 
          JOIN user u ON b.user_id = u.uid
          WHERE b.id = ?";

$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'i', $booking_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$booking = mysqli_fetch_assoc($result)) {
    $_SESSION['error'] = "Booking not found";
    header("Location: admin_bookings.php");
    exit();
}

// Handle booking status updates
if (isset($_POST['update_status'])) {
    $status = mysqli_real_escape_string($con, $_POST['status']);
    
    // Validate status
    $valid_statuses = ['confirmed', 'cancelled', 'completed', 'pending'];
    if (in_array($status, $valid_statuses)) {
        // Update booking status
        $update_query = "UPDATE bookings SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt, 'si', $status, $booking_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // If confirming booking, update property status to booked
            if ($status === 'confirmed') {
                $property_query = "UPDATE property SET status = 'booked' WHERE pid = ?";
                $stmt = mysqli_prepare($con, $property_query);
                mysqli_stmt_bind_param($stmt, 'i', $booking['property_id']);
                mysqli_stmt_execute($stmt);
            }
            
            // If cancelling booking, update property status to available
            if ($status === 'cancelled') {
                $property_query = "UPDATE property SET status = 'available' WHERE pid = ?";
                $stmt = mysqli_prepare($con, $property_query);
                mysqli_stmt_bind_param($stmt, 'i', $booking['property_id']);
                mysqli_stmt_execute($stmt);
            }
            
            $_SESSION['success'] = "Booking status updated successfully";
            header("Location: view_booking.php?id=" . $booking_id);
            exit();
        } else {
            $_SESSION['error'] = "Failed to update booking status: " . mysqli_error($con);
        }
    } else {
        $_SESSION['error'] = "Invalid status value";
    }
}

// Handle payment status updates
if (isset($_POST['update_payment'])) {
    $payment_status = mysqli_real_escape_string($con, $_POST['payment_status']);
    
    // Validate payment status
    $valid_payment_statuses = ['completed', 'pending', 'failed'];
    if (in_array($payment_status, $valid_payment_statuses)) {
        // Update payment status
        $update_query = "UPDATE bookings SET payment_status = ?, payment_date = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt, 'si', $payment_status, $booking_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Payment status updated successfully";
            header("Location: view_booking.php?id=" . $booking_id);
            exit();
        } else {
            $_SESSION['error'] = "Failed to update payment status: " . mysqli_error($con);
        }
    } else {
        $_SESSION['error'] = "Invalid payment status value";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Remsko - Booking Details</title>
    
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
        .booking-details {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .property-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
        }
        .user-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <?php include("header.php"); ?>
        
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">Booking Details</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="admin_bookings.php">Bookings</a></li>
                            <li class="breadcrumb-item active">Booking Details</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Display messages -->
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['success']); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['error']); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Booking #<?= str_pad($booking['id'], 6, '0', STR_PAD_LEFT) ?></h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <img src="property/<?= htmlspecialchars($booking['pimage']) ?>" alt="Property" class="property-image mb-3">
                                </div>
                                <div class="col-md-6">
                                    <h4><?= htmlspecialchars($booking['title']) ?></h4>
                                    <p><i class="fa fa-map-marker"></i> <?= htmlspecialchars($booking['location']) ?>, <?= htmlspecialchars($booking['city']) ?></p>
                                    <p><strong>Type:</strong> <?= htmlspecialchars($booking['type']) ?> for <?= htmlspecialchars($booking['stype']) ?></p>
                                    <p><strong>Bedrooms:</strong> <?= htmlspecialchars($booking['bedroom']) ?> | <strong>Bathrooms:</strong> <?= htmlspecialchars($booking['bathroom']) ?></p>
                                    <h5 class="text-success">ETB <?= number_format($booking['price']) ?></h5>
                                    <a href="../propertydetail.php?pid=<?= $booking['property_id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-eye"></i> View Property
                                    </a>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Booking Information</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Booking Date:</strong></td>
                                            <td><?= date('d M, Y', strtotime($booking['booking_date'])) ?></td>
                                        </tr>
                                        <?php if(isset($booking['preferred_date']) && !empty($booking['preferred_date'])): ?>
                                        <tr>
                                            <td><strong>Preferred Date:</strong></td>
                                            <td><?= date('d M, Y', strtotime($booking['preferred_date'])) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <td><strong>Booking Status:</strong></td>
                                            <td>
                                                <?php 
                                                $statusClass = '';
                                                switch($booking['status']) {
                                                    case 'confirmed': $statusClass = 'success'; break;
                                                    case 'pending': $statusClass = 'warning'; break;
                                                    case 'cancelled': $statusClass = 'danger'; break;
                                                    case 'completed': $statusClass = 'info'; break;
                                                    default: $statusClass = 'secondary';
                                                }
                                                ?>
                                                <span class="badge badge-<?= $statusClass ?>">
                                                    <?= ucfirst(htmlspecialchars($booking['status'])) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php if(isset($booking['message']) && !empty($booking['message'])): ?>
                                        <tr>
                                            <td><strong>Customer Message:</strong></td>
                                            <td><?= nl2br(htmlspecialchars($booking['message'])) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h5>Payment Information</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Payment Status:</strong></td>
                                            <td>
                                                <?php 
                                                $paymentStatusClass = '';
                                                switch($booking['payment_status'] ?? 'pending') {
                                                    case 'completed': $paymentStatusClass = 'success'; break;
                                                    case 'pending': $paymentStatusClass = 'warning'; break;
                                                    case 'failed': $paymentStatusClass = 'danger'; break;
                                                    default: $paymentStatusClass = 'secondary';
                                                }
                                                ?>
                                                <span class="badge badge-<?= $paymentStatusClass ?>">
                                                    <?= ucfirst(htmlspecialchars($booking['payment_status'] ?? 'pending')) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php if(isset($booking['payment_method']) && !empty($booking['payment_method'])): ?>
                                        <tr>
                                            <td><strong>Payment Method:</strong></td>
                                            <td><?= htmlspecialchars(ucfirst($booking['payment_method'])) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if(isset($booking['payment_date']) && !empty($booking['payment_date'])): ?>
                                        <tr>
                                            <td><strong>Payment Date:</strong></td>
                                            <td><?= date('d M, Y', strtotime($booking['payment_date'])) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php if(isset($booking['transaction_id']) && !empty($booking['transaction_id'])): ?>
                                        <tr>
                                            <td><strong>Transaction ID:</strong></td>
                                            <td><?= htmlspecialchars($booking['transaction_id']) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <!-- Customer Information -->
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Customer Information</h4>
                        </div>
                        <div class="card-body text-center">
                            <img src="user/<?= $booking['uimage'] ?: 'avatar.jpg' ?>" alt="Customer" class="user-image mb-3">
                            <h5><?= htmlspecialchars($booking['uname']) ?></h5>
                            <p><i class="fa fa-envelope"></i> <?= htmlspecialchars($booking['uemail']) ?></p>
                            <p><i class="fa fa-phone"></i> <?= htmlspecialchars($booking['uphone']) ?></p>
                            <div class="mt-3">
                                <a href="mailto:<?= htmlspecialchars($booking['uemail']) ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fa fa-envelope"></i> Email Customer
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Update Status -->
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Update Status</h4>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <div class="form-group">
                                    <label>Booking Status</label>
                                    <select name="status" class="form-control">
                                        <option value="pending" <?= ($booking['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                                        <option value="confirmed" <?= ($booking['status'] == 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
                                        <option value="completed" <?= ($booking['status'] == 'completed') ? 'selected' : '' ?>>Completed</option>
                                        <option value="cancelled" <?= ($booking['status'] == 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </div>
                                <button type="submit" name="update_status" class="btn btn-primary btn-block">Update Booking Status</button>
                            </form>
                            
                            <hr>
                            
                            <form method="post" action="">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <div class="form-group">
                                    <label>Payment Status</label>
                                    <select name="payment_status" class="form-control">
                                        <option value="pending" <?= ($booking['payment_status'] ?? 'pending') == 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="completed" <?= ($booking['payment_status'] ?? '') == 'completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="failed" <?= ($booking['payment_status'] ?? '') == 'failed' ? 'selected' : '' ?>>Failed</option>
                                    </select>
                                </div>
                                </div>
                                <button type="submit" name="update_payment" class="btn btn-success btn-block">Update Payment Status</button>
                            </form>
                            
                            <hr>
                            
                            <form method="post" action="booking_delete.php" onsubmit="return confirm('Are you sure you want to delete this booking? This action cannot be undone.');">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="id" value="<?= $booking_id ?>">
                                <button type="submit" class="btn btn-danger btn-block">
                                    <i class="fa fa-trash"></i> Delete Booking
                                </button>
                            </form>
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
    
    <script>
        // Auto-close alerts after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    </script>
</body>
</html>
