<?php
require("config.php");

// Get transaction reference from the callback data
$transaction_reference = $_GET['tx_ref'] ?? '';
$tx_ref_parts = explode('-', $transaction_reference);
$booking_id = intval(end($tx_ref_parts));

// Verify the transaction with Chapa
$chapa_secret_key = 'YOUR_CHAPA_SECRET_KEY'; // Replace with your actual key
$verify_url = 'https://api.chapa.co/v1/transaction/verify/' . $transaction_reference;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $verify_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $chapa_secret_key,
    'Content-Type: application/json'
]);
$response = curl_exec($ch);
curl_close($ch);

$verification = json_decode($response, true);

// Process the verification result
if (isset($verification['status']) && $verification['status'] === 'success') {
    // Payment was successful
    $amount_paid = $verification['data']['amount'];
    
    // Get booking details to verify the amount
    $stmt = mysqli_prepare($con, "SELECT b.*, p.price FROM bookings b JOIN property p ON b.property_id = p.pid WHERE b.id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $booking_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $booking = mysqli_fetch_assoc($result);
    
    if ($booking) {
        // Calculate expected deposit amount (20% of property price)
        $full_price = $booking['price'];
        $expected_deposit = round($full_price * 0.2, 2);
        
        // Verify the amount paid matches the expected deposit
        if (abs($amount_paid - $expected_deposit) < 0.01) { // Allow for tiny rounding differences
            // Update booking status
            $update_query = "UPDATE bookings SET 
                            status = 'confirmed', 
                            payment_status = 'deposit_paid',
                            payment_date = NOW(),
                            payment_amount = ?,
                            payment_method = 'Chapa',
                            payment_transaction_id = ?,
                            deposit_paid = 1,
                            remaining_balance = ?
                            WHERE id = ?";
            
            $remaining_balance = $full_price - $amount_paid;
            
            $stmt = mysqli_prepare($con, $update_query);
            mysqli_stmt_bind_param($stmt, 'dsdi', $amount_paid, $transaction_reference, $remaining_balance, $booking_id);
            mysqli_stmt_execute($stmt);
            
            // Update property status
            $update_property_query = "UPDATE property SET status = 'booked' WHERE pid = ?";
            $stmt = mysqli_prepare($con, $update_property_query);
            mysqli_stmt_bind_param($stmt, 'i', $booking['property_id']);
            mysqli_stmt_execute($stmt);
            
            // Set session variables for receipt page
            session_start();
            $_SESSION['payment_completed'] = true;
            $_SESSION['payment_success'] = true;
            $_SESSION['payment_booking_id'] = $booking_id;
            $_SESSION['payment_transaction_id'] = $transaction_reference;
            $_SESSION['payment_amount'] = $amount_paid;
            $_SESSION['payment_is_deposit'] = true;
            $_SESSION['payment_remaining_balance'] = $remaining_balance;
            $_SESSION['show_payment_modal'] = true;
            
            // Redirect to receipt page
            header("Location: payment_receipt.php?booking_id=" . $booking_id);
            exit();
        } else {
            // Amount mismatch
            error_log("Payment amount mismatch for booking ID: $booking_id, Expected: $expected_deposit, Received: $amount_paid");
            
            // Still record the payment but flag it for review
            $update_query = "UPDATE bookings SET 
                            payment_status = 'review_needed',
                            payment_date = NOW(),
                            payment_amount = ?,
                            payment_method = 'Chapa',
                            payment_transaction_id = ?,
                            admin_notes = CONCAT(IFNULL(admin_notes, ''), 'Amount mismatch: Expected $expected_deposit, Received $amount_paid. ')
                            WHERE id = ?";
            
            $stmt = mysqli_prepare($con, $update_query);
            mysqli_stmt_bind_param($stmt, 'dsi', $amount_paid, $transaction_reference, $booking_id);
            mysqli_stmt_execute($stmt);
        }
    } else {
        // Booking not found
        error_log("Booking not found for ID: $booking_id during payment verification");
    }
} else {
    // Payment verification failed
    $update_query = "UPDATE bookings SET 
                    payment_status = 'failed',
                    payment_transaction_id = ?
                    WHERE id = ?";
    
    $stmt = mysqli_prepare($con, $update_query);
    mysqli_stmt_bind_param($stmt, 'si', $transaction_reference, $booking_id);
    mysqli_stmt_execute($stmt);
    
    // Log failed payment
    error_log("Payment verification failed for booking ID: $booking_id, Response: " . json_encode($verification));
}

if (isset($verification['status']) && $verification['status'] === 'success') {
    // Set success message for my_payments.php
    session_start();
    $_SESSION['payment_success_message'] = "Your payment has been processed successfully. Thank you for your transaction!";
    header("Location: payment_success.php?tx_ref=" . $transaction_reference);
} else {
    header("Location: payment_failed.php?booking_id=" . $booking_id);
}
exit();
