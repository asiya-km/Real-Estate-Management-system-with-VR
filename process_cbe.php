<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require("config.php");

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login1.php");
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Security validation failed. Please try again.";
    header("Location: user_dashboard.php");
    exit();
}

// Get booking ID, phone number and transaction ID
$booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
$phone = isset($_POST['phone']) ? mysqli_real_escape_string($con, $_POST['phone']) : '';
$transaction_id = isset($_POST['transaction_id']) ? mysqli_real_escape_string($con, $_POST['transaction_id']) : '';

// Validate booking ID
if ($booking_id <= 0) {
    $_SESSION['error'] = "Invalid booking reference.";
    header("Location: user_dashboard.php");
    exit();
}

// Validate phone number (simple validation)
if (empty($phone) || !preg_match('/^09[0-9]{8}$/', $phone)) {
    $_SESSION['error'] = "Please provide a valid phone number.";
    header("Location: payment.php?booking_id=" . $booking_id);
    exit();
}

// Validate transaction ID
if (empty($transaction_id)) {
    $_SESSION['error'] = "Please provide the transaction ID from your CBE payment.";
    header("Location: payment.php?booking_id=" . $booking_id);
    exit();
}

// Get user ID
$user_id = $_SESSION['uid'];

// Get booking details
$query = "SELECT b.*, p.title, p.price 
          FROM bookings b 
          JOIN property p ON b.property_id = p.pid 
          WHERE b.id = ? AND b.user_id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'ii', $booking_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$booking = mysqli_fetch_assoc($result)) {
    $_SESSION['error'] = "Booking not found.";
    header("Location: user_dashboard.php");
    exit();
}

// Get booking ID, phone number, transaction ID and amount
$booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
$phone = isset($_POST['phone']) ? mysqli_real_escape_string($con, $_POST['phone']) : '';
$transaction_id = isset($_POST['transaction_id']) ? mysqli_real_escape_string($con, $_POST['transaction_id']) : '';
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

// After fetching booking details, verify the amount is correct
// Calculate 5% of property price
$expected_amount = $booking['price'] * 0.05;

// Optional: Verify the submitted amount matches the expected amount
if (abs($amount - $expected_amount) > 0.01) { // Allow for small floating point differences
    $_SESSION['error'] = "Invalid payment amount.";
    header("Location: payment.php?booking_id=" . $booking_id);
    exit();
}

// Update booking with payment information
$update_query = "UPDATE bookings SET 
                payment_method = 'cbe',
                payment_phone = ?,
                payment_status = 'pending',
                payment_reference = ?,
                payment_transaction_id = ?,
                payment_date = NOW(),
                payment_amount = ?
                WHERE id = ? AND user_id = ?";

// Generate a unique reference
$reference = 'CBE' . date('Ymd') . str_pad($booking_id, 6, '0', STR_PAD_LEFT);

$update_stmt = mysqli_prepare($con, $update_query);
mysqli_stmt_bind_param($update_stmt, 'sssdii', $phone, $reference, $transaction_id, $amount, $booking_id, $user_id);
$update_result = mysqli_stmt_execute($update_stmt);

if ($update_result) {
    // Send notification to admin (optional)
    $admin_email = "admin@example.com"; // Replace with actual admin email
    $subject = "New CBE Payment Pending Verification";
    $message = "A new CBE payment is pending verification:\n\n";
    $message .= "Booking ID: #" . str_pad($booking_id, 6, '0', STR_PAD_LEFT) . "\n";
    $message .= "Property: " . $booking['title'] . "\n";
    $message .= "Amount: ETB " . number_format(50) . "\n";
    $message .= "Customer Phone: " . $phone . "\n";
    $message .= "Transaction ID: " . $transaction_id . "\n";
    $message .= "Reference: " . $reference . "\n";
    $message .= "Date: " . date('Y-m-d H:i:s') . "\n\n";
    $message .= "Please verify this payment in your CBE account.";
    
    mail($admin_email, $subject, $message);
    
    // Set success message and redirect to my_payments.php
    $_SESSION['success'] = "Your CBE payment is pending verification. We will confirm your payment shortly.";
    header("Location: my_payments.php");
    exit();
} else {
    $_SESSION['error'] = "Failed to process payment. Please try again.";
    header("Location: payment.php?booking_id=" . $booking_id);
    exit();
}
?>
