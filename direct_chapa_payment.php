<?php
session_start();
require("config.php");

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login1.php");
    exit();
}

// Validate parameters
if (!isset($_GET['booking_id']) || !isset($_GET['tx_ref'])) {
    header("Location: my_bookings.php");
    exit();
}

$booking_id = intval($_GET['booking_id']);
$tx_ref = $_GET['tx_ref'];
$user_id = $_SESSION['uid'];

// Verify that the transaction reference matches the one in session
if (!isset($_SESSION['chapa_tx_ref']) || $_GET['tx_ref'] !== $_SESSION['chapa_tx_ref']) {
    header("Location: payment.php?booking_id=" . $booking_id);
    exit();
}

// Get booking details
$stmt = mysqli_prepare($con, "SELECT b.*, p.title, p.price FROM bookings b 
                            JOIN property p ON b.property_id = p.pid 
                            WHERE b.id = ? AND b.user_id = ?");
mysqli_stmt_bind_param($stmt, 'ii', $booking_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$booking = mysqli_fetch_assoc($result)) {
    header("Location: my_bookings.php");
    exit();
}

// Calculate deposit amount (20% of property price)
$full_price = $booking['price'];
$deposit_amount = $full_price * 0.2;
$deposit_amount = round($deposit_amount, 2);

// Split name into first and last name
$name_parts = explode(' ', $booking['name']);
$first_name = $name_parts[0];
$last_name = count($name_parts) > 1 ? end($name_parts) : '';

// Chapa API credentials
$chapa_secret_key = 'CHASECK_TEST-Xs7pj3pn2TsaqopHnHQVr9HhVmHbcDGz'; // Replace with your actual secret key

// Prepare data for Chapa API
$data = [
    'amount' => $deposit_amount,
    'currency' => 'ETB',
    'email' => $booking['email'],
    'first_name' => $first_name,
    'last_name' => $last_name,
    'tx_ref' => $tx_ref,
    'callback_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/chapa_verify.php',
    'return_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/payment_success.php?tx_ref=' . $tx_ref,
    'customization' => [
        'title' => 'Property Booking Payment',
        'description' => 'Deposit payment for ' . $booking['title']
    ]
];

// Initialize cURL session
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://api.chapa.co/v1/transaction/initialize',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $chapa_secret_key,
        'Content-Type: application/json'
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

// Log the response for debugging
$log_file = 'chapa_log.txt';
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Chapa Initialize Response: " . $response . "\n", FILE_APPEND);

if ($err) {
    // Log the error
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Chapa API Error: " . $err . "\n", FILE_APPEND);
    
    // Show error
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Payment Error</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                text-align: center;
                padding: 50px;
                background-color: #f8f9fa;
            }
            .error-container {
                max-width: 600px;
                margin: 0 auto;
                background-color: #fff;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
            }
            .error-icon {
                color: #dc3545;
                font-size: 60px;
                margin-bottom: 20px;
            }
            .btn {
                display: inline-block;
                padding: 10px 20px;
                background-color: #6c757d;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">❌</div>
            <h2>Payment Gateway Error</h2>
            <p>' . htmlspecialchars($err) . '</p>
            <a href="javascript:window.close();" class="btn">Close Window</a>
        </div>
    </body>
    </html>';
    exit();
}

$result = json_decode($response, true);

if (isset($result['status']) && $result['status'] === 'success' && isset($result['data']['checkout_url'])) {
    // Redirect directly to Chapa checkout URL
    header("Location: " . $result['data']['checkout_url']);
    exit();
} else {
    // Log the error
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Chapa API Error: " . print_r($result, true) . "\n", FILE_APPEND);
    
    // Show error
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Payment Error</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                text-align: center;
                padding: 50px;
                background-color: #f8f9fa;
            }
            .error-container {
                max-width: 600px;
                margin: 0 auto;
                background-color: #fff;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
            }
            .error-icon {
                color: #dc3545;
                font-size: 60px;
                margin-bottom: 20px;
            }
            .btn {
                display: inline-block;
                padding: 10px 20px;
                background-color: #6c757d;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">❌</div>
            <h2>Payment Initialization Failed</h2>
            <p>' . htmlspecialchars($result['message'] ?? 'Unknown error') . '</p>
            <p>Please check with your administrator or try again later.</p>
            <a href="javascript:window.close();" class="btn">Close Window</a>
        </div>
    </body>
    </html>';
    exit();
}
?>
