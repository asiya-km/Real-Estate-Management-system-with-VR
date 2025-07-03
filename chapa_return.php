<?php
session_start();
require("config.php");

// Get transaction reference and status from URL parameters
$tx_ref = isset($_GET['tx_ref']) ? $_GET['tx_ref'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$transaction_id = isset($_GET['transaction_id']) ? $_GET['transaction_id'] : '';

// Store these values in session for later use
$_SESSION['chapa_tx_ref'] = $tx_ref;
$_SESSION['chapa_status'] = $status;
$_SESSION['chapa_transaction_id'] = $transaction_id;

// Set success message for my_payments.php
$_SESSION['payment_success_message'] = "Your payment has been processed successfully. Thank you for your transaction!";

// Update database immediately
if (!empty($tx_ref) && ($status === 'success' || $status === 'successful') && !empty($transaction_id)) {
    $query = "SELECT id, user_id FROM bookings WHERE payment_reference = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 's', $tx_ref);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($booking = mysqli_fetch_assoc($result)) {
        $booking_id = $booking['id'];
        $user_id = $booking['user_id'];
        
        // Update booking payment status
        $update_query = "UPDATE bookings SET payment_status = 'paid', transaction_id = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'si', $transaction_id, $booking_id);
        mysqli_stmt_execute($update_stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Processing - Remsko Real Estate</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .processing-container {
            max-width: 500px;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .spinner {
            width: 70px;
            text-align: center;
            margin: 20px auto;
        }
        .spinner > div {
            width: 18px;
            height: 18px;
            background-color: #28a745;
            border-radius: 100%;
            display: inline-block;
            animation: sk-bouncedelay 1.4s infinite ease-in-out both;
        }
        .spinner .bounce1 {
            animation-delay: -0.32s;
        }
        .spinner .bounce2 {
            animation-delay: -0.16s;
        }
        @keyframes sk-bouncedelay {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1.0); }
        }
        .countdown {
            font-size: 16px;
            color: #6c757d;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="processing-container">
        <h3>Payment Processed Successfully</h3>
        <p>Please save or download your receipt from Chapa if needed.</p>
        
        <div class="spinner">
            <div class="bounce1"></div>
            <div class="bounce2"></div>
            <div class="bounce3"></div>
        </div>
        
        <div class="countdown">
            Redirecting to payment history in <span id="timer">15</span> seconds...
        </div>
        
        <p class="mt-3">If you're not redirected automatically, <a href="my_payments.php">click here</a>.</p>
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
