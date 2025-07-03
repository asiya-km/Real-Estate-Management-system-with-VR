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
$stmt = mysqli_prepare($con, "SELECT b.*, p.title FROM bookings b 
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

// Chapa API credentials
$chapaSecretKey = "CHASECK_TEST-iDlfbB3j1hWCdrxVwriJpXCsN0SX1Lwu"; // Replace with your actual secret key
$chapaEndpoint = "https://api.chapa.co/v1/transaction/initialize";

// Get payment details from form
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
$email = isset($_POST['email']) ? $_POST['email'] : $booking['email'];
$first_name = isset($_POST['first_name']) ? $_POST['first_name'] : '';
$last_name = isset($_POST['last_name']) ? $_POST['last_name'] : '';

// Validate amount
if ($amount <= 0) {
    $_SESSION['error'] = "Invalid payment amount";
    header("Location: payment.php?booking_id=" . $booking_id);
    exit();
}

// Generate a unique transaction reference
$tx_ref = 'REMSKO-' . date('YmdHis') . '-' . $booking_id;

// Prepare data for Chapa API
$data = [
    'amount' => $amount,
    'currency' => 'ETB',
    'email' => $email,
    'first_name' => $first_name,
    'last_name' => $last_name,
    'tx_ref' => $tx_ref,
    'callback_url' => 'https://remsko.com/chapa_verify.php',
    'return_url' => 'https://remsko.com/payment_success.php?tx_ref=' . $tx_ref,
    'customization' => [
        'title' => 'Property Booking Payment',
        'description' => 'Deposit payment for ' . $booking['title'],
        'logo' => 'https://remsko.com/images/logo.png'
    ]
];

// Initialize cURL session
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $chapaEndpoint);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $chapaSecretKey,
    "Content-Type: application/json"
]);

// Execute cURL session
$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

// Log the API response
$log_data = "Chapa Initialize Response: $response\n";
file_put_contents('chapa_initialize.log', $log_data, FILE_APPEND);

if ($err) {
    $_SESSION['error'] = "Payment gateway error: " . $err;
    header("Location: payment.php?booking_id=" . $booking_id);
    exit();
}

$result = json_decode($response, true);

// Check if initialization was successful
if (isset($result['status']) && $result['status'] === 'success' && isset($result['data']['checkout_url'])) {
    // Update booking with transaction reference
    $update_query = "UPDATE bookings SET payment_tx_ref = ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $update_query);
    mysqli_stmt_bind_param($stmt, 'si', $tx_ref, $booking_id);
    mysqli_stmt_execute($stmt);
    
    // Redirect to Chapa checkout page
    header("Location: " . $result['data']['checkout_url']);
    exit();
} else {
    // Payment initialization failed
    $error_message = isset($result['message']) ? $result['message'] : "Payment initialization failed";
    $_SESSION['error'] = "Payment gateway error: " . $error_message;
    header("Location: payment.php?booking_id=" . $booking_id);
    exit();
}
?>
