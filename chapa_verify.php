<?php
session_start();
require("config.php");

// Get the transaction reference
$tx_ref = isset($_GET['tx_ref']) ? $_GET['tx_ref'] : '';

// If no tx_ref in GET, check POST data (Chapa might send it in POST)
if (empty($tx_ref) && isset($_POST['tx_ref'])) {
    $tx_ref = $_POST['tx_ref'];
}

// Log the request for debugging
file_put_contents('chapa_verify_debug.log', date('Y-m-d H:i:s') . " - Verify endpoint accessed\n", FILE_APPEND);
file_put_contents('chapa_verify_debug.log', date('Y-m-d H:i:s') . " - GET: " . print_r($_GET, true) . "\n", FILE_APPEND);
file_put_contents('chapa_verify_debug.log', date('Y-m-d H:i:s') . " - POST: " . print_r($_POST, true) . "\n", FILE_APPEND);

// Verify the transaction with Chapa
$chapaSecretKey = "CHASECK_TEST-iDlfbB3j1hWCdrxVwriJpXCsN0SX1Lwu"; // Replace with your actual secret key

if (!empty($tx_ref)) {
    // Verify transaction with Chapa API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.chapa.co/v1/transaction/verify/' . $tx_ref);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $chapaSecretKey,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    file_put_contents('chapa_verify_debug.log', date('Y-m-d H:i:s') . " - API Response: " . $response . "\n", FILE_APPEND);

    if ($err) {
        file_put_contents('chapa_verify_debug.log', date('Y-m-d H:i:s') . " - API Error: " . $err . "\n", FILE_APPEND);
    } else {
        $result = json_decode($response, true);

        if (isset($result['status']) && $result['status'] === 'success') {
            // Payment was successful
            // Find the booking ID from the transaction reference
            $query = "SELECT id, property_id, user_id FROM bookings WHERE payment_reference = ?";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, 's', $tx_ref);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($booking = mysqli_fetch_assoc($result)) {
                $booking_id = $booking['id'];
                $property_id = $booking['property_id'];
                $user_id = $booking['user_id'];
                
                // Get property price
                $price_query = "SELECT price FROM property WHERE pid = ?";
                $price_stmt = mysqli_prepare($con, $price_query);
                mysqli_stmt_bind_param($price_stmt, 'i', $property_id);
                mysqli_stmt_execute($price_stmt);
                $price_result = mysqli_stmt_get_result($price_stmt);
                $property_data = mysqli_fetch_assoc($price_result);
                $amount = $property_data['price'];
                
                // Update booking status with comprehensive payment details
                $update_query = "UPDATE bookings SET 
                                payment_status = 'completed', 
                                payment_date = NOW(), 
                                payment_amount = ?,
                                payment_method = 'Chapa',
                                payment_transaction_id = ?,
                                status = 'confirmed'
                                WHERE id = ?";
                $stmt = mysqli_prepare($con, $update_query);
                mysqli_stmt_bind_param($stmt, 'dsi', $amount, $tx_ref, $booking_id);
                mysqli_stmt_execute($stmt);
                
                // Update property status
                $update_property = "UPDATE property SET status = 'booked' WHERE pid = ?";
                $stmt = mysqli_prepare($con, $update_property);
                mysqli_stmt_bind_param($stmt, 'i', $property_id);
                mysqli_stmt_execute($stmt);
                
                // Insert into payment_history table for better tracking
                $insert_payment = "INSERT INTO payment_history (booking_id, user_id, property_id, amount, payment_method, transaction_id, payment_date, status) 
                                  VALUES (?, ?, ?, ?, 'Chapa', ?, NOW(), 'completed')";
                $payment_stmt = mysqli_prepare($con, $insert_payment);
                mysqli_stmt_bind_param($payment_stmt, 'iiids', $booking_id, $user_id, $property_id, $amount, $tx_ref);
                mysqli_stmt_execute($payment_stmt);
                
                // Set success message
                $_SESSION['payment_success_message'] = "Your payment has been processed successfully. Thank you for your transaction!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Successful - Remsko Real Estate</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px 20px;
            background-color: #f8f9fa;
        }
        .success-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }
        h2 {
            color: #28a745;
            margin-bottom: 20px;
        }
        .countdown {
            font-size: 18px;
            margin: 30px 0;
            color: #6c757d;
        }
        .btn {
            display: inline-block;
            padding: 10px 25px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 10px;
        }
        .btn:hover {
            background-color: #218838;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">✓</div>
        <h2>Payment Successful!</h2>
        <p>Your payment has been processed successfully.</p>
        <p>Transaction Reference: <?php echo htmlspecialchars($tx_ref); ?></p>
        
        <div class="countdown">
            Redirecting to payment history in <span id="timer">15</span> seconds...
        </div>
        
        <a href="my_payments.php" class="btn">View Payment History Now</a>
    </div>
    
    <script>
        // Countdown timer for auto-redirect
        var timeLeft = 15;
        var timerId = setInterval(countdown, 1000);
        
        function countdown() {
            if (timeLeft == 0) {
                clearTimeout(timerId);
                window.location.href = "my_payments.php";
            } else {
                document.getElementById("timer").innerHTML = timeLeft;
                timeLeft--;
            }
        }
    </script>
</body>
</html>
