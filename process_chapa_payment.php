<?php
session_start();
require("config.php");

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    echo "<script>window.close(); window.opener.location.href = 'login1.php';</script>";
    exit();
}

// Validate form data
if (!isset($_POST['booking_id']) || !isset($_POST['amount']) || !isset($_POST['email']) || !isset($_POST['name'])) {
    echo "<script>window.close(); window.opener.location.href = 'my_bookings.php';</script>";
    exit();
}

$booking_id = intval($_POST['booking_id']);
$amount = floatval($_POST['amount']);
$email = $_POST['email'];
$name = $_POST['name'];
$property_title = $_POST['property_title'];

// Split name into first and last name
$name_parts = explode(' ', $name);
$first_name = $name_parts[0];
$last_name = count($name_parts) > 1 ? end($name_parts) : '';

// Generate a unique transaction reference
$tx_ref = 'REMSKO_BOOKING_' . $booking_id . '_' . time();

// Store transaction reference in session for verification
$_SESSION['chapa_tx_ref'] = $tx_ref;
$_SESSION['chapa_booking_id'] = $booking_id;

// Chapa API credentials
$chapa_secret_key = 'CHASECK_TEST-Xs7pj3pn2TsaqopHnHQVr9HhVmHbcDGz'; // Replace with your actual secret key

// Prepare data for Chapa API
$data = [
    'amount' => $amount,
    'currency' => 'ETB',
    'email' => $email,
    'first_name' => $first_name,
    'last_name' => $last_name,
    'tx_ref' => $tx_ref,
    'callback_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/chapa_verify.php',
    'return_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/payment_success.php?tx_ref=' . $tx_ref,
    'customization' => [
        'title' => 'Property Booking Payment',
        'description' => 'Deposit payment for ' . $property_title
    ]
];

// Show loading message while we process the payment
echo '<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to Payment...</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding-top: 50px;
        }
        .loader {
            border: 16px solid #f3f3f3;
            border-radius: 50%;
            border-top: 16px solid #28a745;
            width: 120px;
            height: 120px;
            animation: spin 2s linear infinite;
            margin: 0 auto;
            margin-bottom: 30px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="loader"></div>
    <h2>Redirecting to Payment Gateway...</h2>
    <p>Please wait while we connect you to our secure payment provider.</p>
    <p>Do not close this window.</p>
</body>
</html>';
// Flush the output to show the loading message
flush();

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
    
    // Show error and close window
    echo "<div style='text-align:center; padding:20px; background-color:#f8d7da; color:#721c24; margin:20px;'>";
    echo "<h3>Error connecting to payment gateway</h3>";
    echo "<p>" . htmlspecialchars($err) . "</p>";
    echo "<button onclick='window.close()'>Close Window</button>";
    echo "</div>";
    exit();
}

$result = json_decode($response, true);

if (isset($result['status']) && $result['status'] === 'success' && isset($result['data']['checkout_url'])) {
    // Redirect to Chapa checkout URL
    echo "<script>window.location.href = '" . $result['data']['checkout_url'] . "';</script>";
    exit();
} else {
    // Log the error
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Chapa API Error: " . print_r($result, true) . "\n", FILE_APPEND);
    
    // Show error and close window
    echo "<div style='text-align:center; padding:20px; background-color:#f8d7da; color:#721c24; margin:20px;'>";
    echo "<h3>Failed to initialize payment</h3>";
    echo "<p>" . htmlspecialchars($result['message'] ?? 'Unknown error') . "</p>";
    echo "<p>Please check with your administrator or try the simulated payment option for testing.</p>";
    echo "<button onclick='window.close()'>Close Window</button>";
    echo "</div>";
    exit();
}
?>
