<?php
session_start();
require("config.php");
// Check if booking_id is provided in URL but not in session
if (!isset($_SESSION['payment_booking_id']) && isset($_GET['booking_id'])) {
    $booking_id = intval($_GET['booking_id']);
    
    // Fetch booking details
    $query = "SELECT b.*, p.title, p.price, u.uname as customer_name, u.uemail as customer_email, u.uphone as customer_phone 
              FROM bookings b 
              JOIN property p ON b.property_id = p.pid 
              JOIN user u ON b.user_id = u.uid
              WHERE b.id = ? AND b.user_id = ?";
    
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $booking_id, $_SESSION['uid']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($booking = mysqli_fetch_assoc($result)) {
        // Set session variables
        $_SESSION['payment_property_id'] = $booking['property_id'];
        $_SESSION['payment_booking_id'] = $booking_id;
        $_SESSION['payment_amount'] = $booking['price'];
        $_SESSION['payment_property_name'] = $booking['title'];
        $_SESSION['payment_customer_name'] = $booking['customer_name'];
        $_SESSION['payment_customer_email'] = $booking['customer_email'];
        $_SESSION['payment_customer_phone'] = $booking['customer_phone'];
    } else {
        // Invalid booking
        $_SESSION['error'] = "Invalid booking or access denied.";
        header("Location: user_dashboard.php");
        exit();
    }
}

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login1.php");
    exit();
}

// Check if payment data is set in session
if (!isset($_SESSION['payment_booking_id']) || !isset($_SESSION['payment_amount'])) {
    header("Location: user_dashboard.php");
    exit();
}
// Chapa API credentials
$chapaPublicKey = "CHAPUBK_TEST-oqowKcFGmRs0Fu58i0iSDqpcVQxy4bEZ"; // Replace with your actual public key
$chapaSecretKey = "CHASECK_TEST-iDlfbB3j1hWCdrxVwriJpXCsN0SX1Lwu"; // Replace with your actual secret key

// Get payment data from session
$bookingId = $_SESSION['payment_booking_id'];
$propertyId = $_SESSION['payment_property_id'];
$amount = $_SESSION['payment_amount'];
$propertyName = $_SESSION['payment_property_name'];
$customerName = $_SESSION['payment_customer_name'];
$customerEmail = $_SESSION['payment_customer_email'];
$customerPhone = $_SESSION['payment_customer_phone'];

// Generate unique transaction reference
$txRef = 'REMSKO-' . time() . '-' . $bookingId;

// Store transaction reference in database for verification later
$update_query = "UPDATE bookings SET payment_reference = ? WHERE id = ?";
$stmt = mysqli_prepare($con, $update_query);
mysqli_stmt_bind_param($stmt, 'si', $txRef, $bookingId);
mysqli_stmt_execute($stmt);
// Determine protocol (http or https)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";

// Prepare data for Chapa
$data = [
    'amount' => $amount,
    'currency' => 'ETB',
    'email' => $customerEmail,
    'first_name' => $customerName,
    'last_name' => '',
    'tx_ref' => $txRef,
    'callback_url' => $protocol . "://" . $_SERVER['HTTP_HOST'] . "/chapa_verify.php",
    // 'return_url' => $protocol . "://" . $_SERVER['HTTP_HOST'] . "/thank_you.php?booking_id=" . $bookingId . "&method=chapa&status=success",
    'customization' => [
        'title' => 'Property Payment',
        'description' => $propertyName
    ]
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
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    // Log error
    error_log("Chapa API Error: " . $err);
    
    $_SESSION['error'] = "Payment gateway error. Please try again later.";
    header("Location: payment.php?booking_id=" . $bookingId);
    exit();
}

$result = json_decode($response, true);

if (isset($result['status']) && $result['status'] === 'success') {
    // Log success
    error_log("Chapa API Success: " . json_encode($result));
    
    // Store checkout URL in session
    $_SESSION['chapa_checkout_url'] = $result['data']['checkout_url'];
    
    // Output JavaScript to open in a new tab
    echo '<script>
        window.open("' . $result['data']['checkout_url'] . '", "_blank");
        window.location.href = "payment_processing.php?booking_id=' . $bookingId . '";
    </script>';
    exit();
}
    // Log error
    error_log("Chapa API Response Error: " . json_encode($result));
    
    $_SESSION['error'] = "Payment initialization failed: " . ($result['message'] ?? "Please try again later.");
    header("Location: payment.php?booking_id=" . $bookingId);
    exit();

?>
