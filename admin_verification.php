<?php
session_start();
require("../config.php");

// Check if admin is logged in
if (!isset($_SESSION['auser'])) {
    header("Location: index.php");
    exit();
}

// Process verification action
if (isset($_POST['action']) && isset($_POST['booking_id'])) {
    $booking_id = intval($_POST['booking_id']);
    $action = $_POST['action'];
    
    if ($action === 'verify') {
        // Update booking status to completed
        $update_query = "UPDATE bookings SET payment_status = 'completed', payment_verified_date = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt, 'i', $booking_id);
        mysqli_stmt_execute($stmt);
        
        // Send notification to user (optional)
        $user_query = "SELECT u.uemail, b.payment_reference 
                      FROM bookings b 
                      JOIN user u ON b.user_id = u.uid 
                      WHERE b.id = ?";
        $user_stmt = mysqli_prepare($con, $user_query);
        mysqli_stmt_bind_param($user_stmt, 'i', $booking_id);
        mysqli_stmt_execute($user_stmt);
        $user_result = mysqli_stmt_get_result($user_stmt);
        
        if ($user_data = mysqli_fetch_assoc($user_result)) {
            $to = $user_data['uemail'];
            $subject = "Payment Verified - Booking #" . str_pad($booking_id, 6, '0', STR_PAD_LEFT);
            $message = "Dear Customer,\n\n";
            $message .= "Your payment (Reference: " . $user_data['payment_reference'] . ") has been verified and your booking is now confirmed.\n\n";
            $message .= "Thank you for choosing Remsko Real Estate.\n\n";
            $message .= "Regards,\nRemsko Real Estate Team";
            
            mail($to, $subject, $message);
        }
        
        $_SESSION['success'] = "Payment verified successfully.";
    } elseif ($action === 'reject') {
        // Update booking status to failed
        $update_query = "UPDATE bookings SET payment_status = 'failed', payment_verified_date = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt, 'i', $booking_id);
        mysqli_stmt_execute($stmt);
        
        // Send notification to user (optional)
        $user_query = "SELECT u.uemail, b.payment_reference 
                      FROM bookings b 
                      JOIN user u ON b.user_id = u.uid 
                      WHERE b.id = ?";
        $user_stmt = mysqli_prepare($con, $user_query);
        mysqli_stmt_bind_param($user_stmt, 'i', $booking_id);
        mysqli_stmt_execute($user_stmt);
        $user_result = mysqli_stmt_get_result($user_stmt);
        
        if ($user_data = mysqli_fetch_assoc($user_result)) {
            $to = $user_data['uemail'];
            $subject = "Payment Rejected - Booking #" . str_pad($booking_id, 6, '0', STR_PAD_LEFT);
            $message = "Dear Customer,\n\n";
            $message .= "We could not verify your payment (Reference: " . $user_data['payment_reference'] . ").\n\n";
            $message .= "This could be due to one of the following reasons:\n";
            $message .= "- The payment was not completed in Telebirr\n";
            $message .= "- The reference number was missing in the payment description\n";
            $message .= "- The payment amount did not match the booking amount\n\n";
            $message .= "Please try again or contact our support team for assistance.\n\n";
            $message .= "Regards,\nRemsko Real Estate Team";
            
            mail($to, $subject, $message);
        }
        
        $_SESSION['success'] = "Payment rejected.";
    }
    
    header("Location: verify_telebirr.php");
    exit();
}

// Get pending Telebirr payments
$query = "SELECT b.*, p.title, p.price, u.uname, u.uemail, u.uphone 
          FROM bookings b 
          JOIN property p ON b.property_id = p.pid 
          JOIN user u ON b.user_id = u.uid 
          WHERE b.payment_method = 'telebirr' AND b.payment_status = 'pending' 
          ORDER BY b.payment_date DESC";
$result = mysqli_query($con, $query);

// Get recently verified payments
$verified_query = "SELECT b.*, p.title, p.price, u.uname, u.uemail, u.uphone 
                  FROM bookings b 
                  JOIN property p ON b.property_id = p.pid 
                  JOIN user u ON b.user_id = u.uid 
                  WHERE b.payment_method = 'telebirr' AND (b.payment_status = 'completed' OR b.payment_status = 'failed') 
                  ORDER BY b.payment_verified_date DESC 
                  LIMIT 10";
$verified_result = mysqli_query($con, $verified_query);

// Display error/success messages
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['error'], $_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Verify Telebirr Payments - Admin Dashboard</title>
    <!-- Include your admin CSS files -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .payment-table {
            width: 100%;
            border-collapse: collapse;
        }
        .payment-table th, .payment-table td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .payment-table th {
            background-color: #f5f5f5;
        }
        .status-pending {
            color: #ff9800;
            font-weight: bold;
        }
        .status-completed {
            color: #4CAF50;
            font-weight: bold;
        }
        .status-failed {
            color: #f44336;
            font-weight: bold;
        }
        .action-buttons form {
            display: inline-block;
            margin-right: 5px;
        }
        .verify-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
        .reject-btn {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include("include/header.php"); ?>
    
    <div class="container-fluid" id="main-content">
        <div class="row">
            <?php include("include/sidebar.php"); ?>
            
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h2 class="mb-4">Verify Telebirr Payments</h2>
                
                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Pending Telebirr Payments</h5>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                        <div class="table-responsive">
                            <table class="payment-table">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Property</th>
                                        <th>Amount</th>
                                        <th>Customer</th>
                                        <th>Phone</th>
                                        <th>Reference</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td>#<?php echo str_pad($row['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td>ETB <?php echo number_format($row['price']); ?></td>
                                        <td><?php echo htmlspecialchars($row['uname']); ?></td>
                                        <td><?php echo htmlspecialchars($row['payment_phone'] ?: $row['uphone']); ?></td>
                                        <td><?php echo htmlspecialchars($row['payment_reference']); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($row['payment_date'])); ?></td>
                                        <td class="action-buttons">
                                            <form method="post">
                                                <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="action" value="verify">
                                                <button type="submit" class="verify-btn">Verify</button>
                                            </form>
                                            <form method="post">
                                                <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="reject-btn">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                                                        </table>
                        </div>
                        <?php else: ?>
                        <p>No pending Telebirr payments found.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5>Recently Verified Payments</h5>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($verified_result) > 0): ?>
                        <div class="table-responsive">
                            <table class="payment-table">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Property</th>
                                        <th>Amount</th>
                                        <th>Customer</th>
                                        <th>Reference</th>
                                        <th>Status</th>
                                        <th>Verified Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($verified_result)): ?>
                                    <tr>
                                        <td>#<?php echo str_pad($row['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td>ETB <?php echo number_format($row['price']); ?></td>
                                        <td><?php echo htmlspecialchars($row['uname']); ?></td>
                                        <td><?php echo htmlspecialchars($row['payment_reference']); ?></td>
                                        <td class="<?php echo $row['payment_status'] == 'completed' ? 'status-completed' : 'status-failed'; ?>">
                                            <?php echo ucfirst($row['payment_status']); ?>
                                        </td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($row['payment_verified_date'])); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <p>No verified payments found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Include your admin JS files -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>

