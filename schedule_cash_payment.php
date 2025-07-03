<?php
session_start();
require("config.php");

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login1.php");
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Invalid form submission";
    header("Location: payment.php?booking_id=" . $_POST['booking_id']);
    exit();
}

// Validate booking ID
if (!isset($_POST['booking_id']) || !is_numeric($_POST['booking_id'])) {
    $_SESSION['error'] = "Invalid booking ID";
    header("Location: my_bookings.php");
    exit();
}
$booking_id = intval($_POST['booking_id']);
$user_id = $_SESSION['uid'];

// Get booking details
$stmt = mysqli_prepare($con, "SELECT b.*, p.title, p.price, p.pid FROM bookings b 
                            JOIN property p ON b.property_id = p.pid 
                            WHERE b.id = ? AND b.user_id = ?");
mysqli_stmt_bind_param($stmt, 'ii', $booking_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$booking = mysqli_fetch_assoc($result)) {
    $_SESSION['error'] = "Booking not found";
    header("Location: my_bookings.php");
    exit();
}

// Check if payment is already completed
if ($booking['payment_status'] === 'completed' || $booking['payment_status'] === 'deposit_paid') {
    header("Location: payment_receipt.php?booking_id=" . $booking_id);
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_cash_payment'])) {
    // Validate form data
    $visit_date = mysqli_real_escape_string($con, trim($_POST['visit_date']));
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    
    // Validate required fields
    if (empty($visit_date)) {
        $_SESSION['error'] = "Visit date is required";
        header("Location: payment.php?booking_id=" . $booking_id);
        exit();
    }
    
    // Validate amount
    if ($amount <= 0) {
        $_SESSION['error'] = "Invalid payment amount";
        header("Location: payment.php?booking_id=" . $booking_id);
        exit();
    }
    
    // Calculate deposit amount (20% of property price)
    $full_price = $booking['price'];
    $deposit_amount = $full_price * 0.2;
    $deposit_amount = round($deposit_amount, 2);
    
    // Calculate remaining balance
    $remaining_balance = $full_price - $deposit_amount;
    
    // Update booking with payment information
    $update_query = "UPDATE bookings SET 
                    payment_status = 'scheduled',
                    payment_amount = ?,
                    payment_method = 'Cash',
                    scheduled_payment_date = ?,
                    remaining_balance = ?
                    WHERE id = ?";
    
    $stmt = mysqli_prepare($con, $update_query);
    mysqli_stmt_bind_param($stmt, 'dsdi', $deposit_amount, $visit_date, $remaining_balance, $booking_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Set success message
        $_SESSION['success_message'] = "Your cash payment visit has been scheduled for " . date('F j, Y', strtotime($visit_date)) . ". Please visit our office on the scheduled date with the required amount.";
        
        // Redirect to booking details page
        header("Location: booking_confirmation.php?booking_id=" . $booking_id);
        exit();
    } else {
        $_SESSION['error'] = "Failed to schedule payment: " . mysqli_error($con);
        header("Location: payment.php?booking_id=" . $booking_id);
        exit();
    }
} else {
    // Invalid request
    header("Location: payment.php?booking_id=" . $booking_id);
    exit();
}
?>
