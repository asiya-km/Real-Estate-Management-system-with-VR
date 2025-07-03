<?php
session_start();
require("../config.php");

// Check if admin is logged in
if (!isset($_SESSION['auser'])) {
    header("location:index.php");
    exit();
}

// Check if payment ID and status are provided
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['status'])) {
    header("location:payment_history.php");
    exit();
}

$payment_id = intval($_GET['id']);
$new_status = $_GET['status'];

// Validate status
$valid_statuses = ['pending', 'completed', 'failed', 'refunded'];
if (!in_array($new_status, $valid_statuses)) {
    header("location:payment_history.php");
    exit();
}

// Get current payment details
$query = "SELECT b.*, u.uemail, p.title as property_title 
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

// Handle transaction ID update
if (isset($_POST['update_transaction_id']) && isset($_POST['booking_id']) && isset($_POST['transaction_id'])) {
    $booking_id = intval($_POST['booking_id']);
    $transaction_id = mysqli_real_escape_string($con, trim($_POST['transaction_id']));
    
    $update_query = "UPDATE bookings SET 
                    payment_transaction_id = ?,
                    payment_status = 'completed'
                    WHERE id = ?";
    $update_stmt = mysqli_prepare($con, $update_query);
    mysqli_stmt_bind_param($update_stmt, 'si', $transaction_id, $booking_id);
    
    if (mysqli_stmt_execute($update_stmt)) {
        $_SESSION['success'] = "Transaction ID has been updated successfully";
    } else {
        $_SESSION['error'] = "Failed to update transaction ID: " . mysqli_error($con);
    }
    
    header("location:view_payment.php?id=" . $booking_id);
    exit();
}

// Update payment status
$update_query = "UPDATE bookings SET payment_status = ?, admin_notes = CONCAT(IFNULL(admin_notes, ''), '\nStatus updated to \"".mysqli_real_escape_string($con, $new_status)."\" by admin on ".date('Y-m-d H:i:s')."') WHERE id = ?";
$stmt = mysqli_prepare($con, $update_query);
mysqli_stmt_bind_param($stmt, 'si', $new_status, $payment_id);
$success = mysqli_stmt_execute($stmt);

// If status is changed to completed, update property status
if ($success && $new_status == 'completed') {
    $update_property = "UPDATE property SET status = 'booked' WHERE pid = ?";
    $stmt = mysqli_prepare($con, $update_property);
    mysqli_stmt_bind_param($stmt, 'i', $payment['property_id']);
    mysqli_stmt_execute($stmt);
}

// If status is changed to refunded, update property status
if ($success && $new_status == 'refunded') {
    $update_property = "UPDATE property SET status = 'available' WHERE pid = ?";
    $stmt = mysqli_prepare($con, $update_property);
    mysqli_stmt_bind_param($stmt, 'i', $payment['property_id']);
    mysqli_stmt_execute($stmt);
}

// Send email notification to customer
if ($success) {
    $to = $payment['uemail'];
    $subject = "Payment Status Update - Remsko Real Estate";
    
    $message = "
    <html>
    <head>
        <title>Payment Status Update</title>
    </head>
    <body>
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd;'>
            <h2 style='color: #333;'>Payment Status Update</h2>
            <p>Dear Customer,</p>
            <p>The status of your payment for <strong>".htmlspecialchars($payment['property_title'])."</strong> has been updated to <strong>".ucfirst($new_status)."</strong>.</p>
            <p><strong>Booking ID:</strong> ".$payment_id."</p>
            <p><strong>Transaction ID:</strong> ".htmlspecialchars($payment['payment_transaction_id'])."</p>
            <p><strong>Amount:</strong> ETB ".number_format($payment['payment_amount'], 2)."</p>
            <p>If you have any questions, please contact our support team.</p>
            <p>Thank you for choosing Remsko Real Estate.</p>
        </div>
    </body>
    </html>
    ";
    
    // Set content-type header for sending HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Remsko Real Estate <noreply@remsko.com>" . "\r\n";
    
    // Send email
    mail($to, $subject, $message, $headers);
}

// Redirect back to payment details page
header("location:view_payment.php?id=" . $payment_id . "&updated=1");
exit();
?>
