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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_mobile_money'])) {
    // Validate form data
    $mobile_service = mysqli_real_escape_string($con, trim($_POST['mobile_service']));
    $mobile_number = mysqli_real_escape_string($con, trim($_POST['mobile_number']));
    $transaction_id = mysqli_real_escape_string($con, trim($_POST['mobile_transaction_id']));
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    $payment_method = '';
    
    // Handle QR code upload for CBE and Telebirr
    $qr_receipt = '';
    if (($mobile_service == 'CBE Birr' || $mobile_service == 'Telebirr') && isset($_POST['payment_type']) && $_POST['payment_type'] == 'qr_code') {
        if (isset($_FILES['qr_receipt']) && $_FILES['qr_receipt']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['qr_receipt']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (!in_array(strtolower($ext), $allowed)) {
                $_SESSION['error'] = "Invalid file format. Only JPG, JPEG, and PNG are allowed.";
                header("Location: payment.php?booking_id=" . $booking_id);
                exit();
            }
            
            $new_filename = 'QR_RECEIPT_' . $booking_id . '_' . time() . '.' . $ext;
            $upload_dir = 'uploads/receipts/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if (move_uploaded_file($_FILES['qr_receipt']['tmp_name'], $upload_dir . $new_filename)) {
                $qr_receipt = $new_filename;
                $payment_method = $mobile_service . ' QR Payment';
            } else {
                $_SESSION['error'] = "Failed to upload QR receipt";
                header("Location: payment.php?booking_id=" . $booking_id);
                exit();
            }
        } else {
            $_SESSION['error'] = "QR receipt is required for QR code payments";
            header("Location: payment.php?booking_id=" . $booking_id);
            exit();
        }
    } else {
        // Regular mobile money payment
        $payment_method = $mobile_service . ' Mobile Money';
    }
    
    // Validate required fields
    if (empty($mobile_service) || empty($mobile_number) || empty($transaction_id)) {
        $_SESSION['error'] = "All fields are required";
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
                    payment_status = 'pending_verification',
                    payment_amount = ?,
                    payment_date = NOW(),
                    payment_method = ?,
                    payment_transaction_id = ?,
                    mobile_number = ?,
                    payment_receipt = ?,
                    remaining_balance = ?
                    WHERE id = ?";
    
    $stmt = mysqli_prepare($con, $update_query);
    mysqli_stmt_bind_param($stmt, 'dssssdi', $deposit_amount, $payment_method, $transaction_id, $mobile_number, $qr_receipt, $remaining_balance, $booking_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Set success message
        $_SESSION['success_message'] = "Your mobile payment has been submitted for verification. We will review it shortly and update your booking status.";
        
        // Redirect to booking details page
        header("Location: booking_confirmation.php?booking_id=" . $booking_id);
        exit();
    } else {
        $_SESSION['error'] = "Failed to process payment: " . mysqli_error($con);
        header("Location: payment.php?booking_id=" . $booking_id);
        exit();
    }
} else {
    // Invalid request
    header("Location: payment.php?booking_id=" . $booking_id);
    exit();
}
?>
