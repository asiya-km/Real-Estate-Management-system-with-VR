<?php
session_start();
require("config.php");

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login1.php");
    exit();
}

// Verify CSRF token if it exists
if (isset($_POST['csrf_token']) && isset($_SESSION['csrf_token']) && $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Invalid form submission";
    header("Location: user_dashboard.php");
    exit();
}

// Initialize variables
$user_id = $_SESSION['uid'];
$payment_method = isset($_POST['payment_method']) ? mysqli_real_escape_string($con, trim($_POST['payment_method'])) : '';
$phone = isset($_POST['phone']) ? mysqli_real_escape_string($con, trim($_POST['phone'])) : '';
$booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
$transaction_id = isset($_POST['transaction_id']) ? mysqli_real_escape_string($con, trim($_POST['transaction_id'])) : '';

// Validate required fields
if (empty($payment_method) || $booking_id <= 0) {
    $_SESSION['error'] = "Missing required payment information";
    header("Location: payment.php?booking_id=" . $booking_id);
    exit();
}

// Phone is required for all payment methods except Chapa
if ($payment_method !== 'chapa' && empty($phone)) {
    $_SESSION['error'] = "Phone number is required for this payment method";
    header("Location: payment.php?booking_id=" . $booking_id);
    exit();
}

// Check if booking exists and belongs to the user
$query = "SELECT b.*, p.title, p.price FROM bookings b 
          JOIN property p ON b.property_id = p.pid 
          WHERE b.id = ? AND b.user_id = ? AND b.payment_status != 'paid'";

$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'ii', $booking_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$booking = mysqli_fetch_assoc($result)) {
    $_SESSION['error'] = "Booking not found, unauthorized access, or already paid";
    header("Location: user_dashboard.php");
    exit();
}

// Get user information
$user_query = "SELECT * FROM user WHERE uid = ?";
$user_stmt = mysqli_prepare($con, $user_query);
mysqli_stmt_bind_param($user_stmt, 'i', $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user = mysqli_fetch_assoc($user_result);

// Generate a transaction reference
$tx_ref = 'REMSKO-' . time() . '-' . $booking_id;

// Set fixed booking charge
$payment_amount = 50;

// Process different payment methods
switch ($payment_method) {
    case 'chapa':
        // Set payment status to pending initially
        $payment_status = 'pending';
        
        // Update the database with the payment information
        $update_query = "UPDATE bookings SET 
                        payment_method = ?, 
                        payment_status = 'pending', 
                        payment_amount = ?,
                        payment_reference = ?,
                        payment_date = NOW() 
                        WHERE id = ?";
        $update_stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'sdsi', $payment_method, $payment_amount, $tx_ref, $booking_id);
        mysqli_stmt_execute($update_stmt);
        
        // Chapa API credentials
        $chapaPublicKey = "CHAPUBK_TEST-oqowKcFGmRs0Fu58i0iSDqpcVQxy4bEZ"; 
        $chapaSecretKey = "CHASECK_TEST-iDlfbB3j1hWCdrxVwriJpXCsN0SX1Lwu"; 
        
        // Determine protocol (http or https)
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        
        // Prepare data for Chapa
        $data = [
            'amount' => $payment_amount,
            'currency' => 'ETB',
            'email' => $user['uemail'],
            'first_name' => $user['uname'],
            'last_name' => '',
            'tx_ref' => $tx_ref,
            'callback_url' => $protocol . "://" . $_SERVER['HTTP_HOST'] . "/remsko/chapa_verify.php",
            'return_url' => $protocol . "://" . $_SERVER['HTTP_HOST'] . "/remsko/payment_success.php?booking_id=" . $booking_id
        ];
        
        // Send request to Chapa
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.chapa.co/v1/transaction/initialize');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $chapaSecretKey,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['status']) && $result['status'] === 'success') {
            // Direct redirect to Chapa checkout URL
            header("Location: " . $result['data']['checkout_url']);
            exit();
        } else {
            $_SESSION['error'] = "Payment initialization failed. Please try again.";
            header("Location: payment.php?booking_id=" . $booking_id);
            exit();
        }
        break;
        
    // For Telebirr payments
    case 'telebirr':
        // Transaction ID is required for Telebirr
        if (empty($transaction_id)) {
            $_SESSION['error'] = "Transaction ID is required for Telebirr payments";
            header("Location: payment.php?booking_id=" . $booking_id);
            exit();
        }
        
        // Update booking with payment information
        $update_query = "UPDATE bookings SET 
                        payment_method = ?,
                        payment_phone = ?,
                        payment_status = 'pending',
                        payment_amount = ?,
                        payment_reference = ?,
                        payment_transaction_id = ?,
                        payment_date = NOW()
                        WHERE id = ?";
        
        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt, 'ssdssi', $payment_method, $phone, $payment_amount, $tx_ref, $transaction_id, $booking_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Your payment has been submitted. We will verify your payment and update your booking status.";
            header("Location: booking_confirmation.php?booking_id=" . $booking_id);
            exit();
        } else {
            $_SESSION['error'] = "Failed to process payment: " . mysqli_error($con);
            header("Location: payment.php?booking_id=" . $booking_id);
            exit();
        }
        break;
        
    // For other payment methods (CBEBirr, Bank Transfer)
    case 'cbebirr':
    case 'bank':
        // Update booking with payment information
        $update_query = "UPDATE bookings SET 
                        payment_method = ?,
                        payment_phone = ?,
                        payment_status = 'pending',
                        payment_amount = ?,
                        payment_reference = ?,
                        payment_date = NOW()
                        WHERE id = ?";
        
        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt, 'ssdsi', $payment_method, $phone, $payment_amount, $tx_ref, $booking_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Your payment request has been submitted. We will verify your payment and update your booking status.";
            header("Location: booking_confirmation.php?booking_id=" . $booking_id);
            exit();
        } else {
            $_SESSION['error'] = "Failed to process payment: " . mysqli_error($con);
            header("Location: payment.php?booking_id=" . $booking_id);
            exit();
        }
        break;
}

// If execution reaches here, something went wrong
$_SESSION['error'] = "An unexpected error occurred during payment processing";
header("Location: payment.php?booking_id=" . $booking_id);
exit();
?>
