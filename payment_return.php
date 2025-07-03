<?php
session_start();
require("config.php");

// Get booking ID from URL
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

// If no booking ID, redirect to dashboard
if ($booking_id <= 0) {
    header("Location: user_dashboard.php");
    exit();
}

// Get booking status
$stmt = mysqli_prepare($con, "SELECT b.status, b.payment_status, p.title 
                            FROM bookings b 
                            JOIN property p ON b.property_id = p.pid 
                            WHERE b.id = ?");
mysqli_stmt_bind_param($stmt, 'i', $booking_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$booking = mysqli_fetch_assoc($result);

// Set session variables for notification if not already set
if (!isset($_SESSION['payment_completed'])) {
    $_SESSION['payment_completed'] = true;
    $_SESSION['payment_success'] = ($booking['payment_status'] === 'completed');
    $_SESSION['payment_booking_id'] = $booking_id;
    $_SESSION['payment_property_title'] = $booking['title'] ?? '';
    $_SESSION['show_payment_modal'] = true;
}

// Redirect to my_payments page instead of user dashboard
header("Location: my_payments.php");
exit();
?>
