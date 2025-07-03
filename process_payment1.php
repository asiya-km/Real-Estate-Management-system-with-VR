<?php


ini_set('session.cache_limiter','public');
session_cache_limiter(false);
session_start();
include("config.php");

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Content-Security-Policy: default-src 'self'");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Validate session and CSRF token

if (!isset($_SESSION['uid'])) //|| !isset($_POST['booking_id']) || !isset($_POST['csrf_token'])) 
{
//if (!isset($_SESSION['uid']) || !isset($_POST['booking_id']) || !isset($_POST['csrf_token'])) {
   // $_SESSION['error'] = "Invalid request";
    header("Location: login.php");
    exit();
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['error'] = "Invalid CSRF token";
    header("Location: booking.php");
    exit();
}

// Sanitize inputs
$booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
$user_id = $_SESSION['uid'];
$payment_method = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING);
$pin = filter_input(INPUT_POST, 'pin', FILTER_SANITIZE_STRING);

// Hardcoded recipient number
$recipient_phone = '0904340273'; // Fixed recipient number

try {
    $con->begin_transaction();

    // Verify booking and get amount
    $stmt = $con->prepare("SELECT p.price, b.id 
                         FROM bookings b
                         JOIN property p ON b.property_id = p.pid
                         WHERE b.id = ? AND b.user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Invalid booking request");
    }

    $booking = $result->fetch_assoc();
    $amount = $booking['price'];

    // Generate unique transaction ID
    $transaction_id = 'CBEBIRR' . time() . bin2hex(random_bytes(4));

    // CBE Birr API Configuration
    $api_url = 'https://api.cbebirr.com/v1/payments';
    $api_key = 'YOUR_API_KEY'; // Store securely
    
    $payload = [
        'amount' => $amount,
        'currency' => 'ETB',
        'customer_phone' => $recipient_phone, // Hardcoded recipient
        'transaction_ref' => $transaction_id,
        'pin' => $pin, // User's PIN from input
        'callback_url' => 'https://yourdomain.com/payment-callback'
    ];

    $ch = curl_init($api_url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $api_key,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        throw new Exception('Payment gateway error: ' . curl_error($ch));
    }
    
    curl_close($ch);

    $response_data = json_decode($response, true);

    if ($http_code !== 200 || $response_data['status'] !== 'success') {
        throw new Exception('Payment failed: ' . ($response_data['message'] ?? 'Unknown error'));
    }

    // Update booking record
    $update_stmt = $con->prepare("UPDATE bookings SET 
                                payment_method = ?,
                                payment_status = 'completed',
                                payment_date = NOW(),
                                transaction_id = ?
                                WHERE id = ?");
    $update_stmt->bind_param("ssi", 
        $payment_method,
        $transaction_id,
        $booking_id
    );

    if (!$update_stmt->execute()) {
        throw new Exception('Failed to update booking: ' . $update_stmt->error);
    }

    $con->commit();
    
    // Send confirmation
    header("Location: payment_success.php?booking_id=" . $booking_id);
    exit();

} catch (Exception $e) {
    $con->rollback();
    error_log('Payment Error: ' . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
    header("Location: payment_failed.php?booking_id=" . $booking_id);
    exit();
}
?>