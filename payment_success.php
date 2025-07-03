<?php
session_start();
require("config.php");

// Get transaction reference from URL
$tx_ref = isset($_GET['tx_ref']) ? $_GET['tx_ref'] : '';
$transaction_id = isset($_GET['transaction_id']) ? $_GET['transaction_id'] : '';

// If transaction reference is provided, update the database
if (!empty($tx_ref)) {
    // Find the booking associated with this transaction
    $query = "SELECT id, property_id, user_id FROM bookings WHERE payment_reference = ? OR payment_transaction_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 'ss', $tx_ref, $tx_ref);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($booking = mysqli_fetch_assoc($result)) {
        $booking_id = $booking['id'];
        $property_id = $booking['property_id'];
        
        // Update booking status if not already completed
        $update_query = "UPDATE bookings SET 
                        payment_status = 'completed', 
                        payment_date = NOW(), 
                        payment_amount = (SELECT price FROM property WHERE pid = ?),
                        payment_method = 'Chapa',
                        payment_transaction_id = COALESCE(?, payment_transaction_id)
                        WHERE id = ? AND payment_status != 'completed'";
        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt, 'isi', $property_id, $transaction_id, $booking_id);
        mysqli_stmt_execute($stmt);
        
        // Update property status
        $update_property = "UPDATE property SET status = 'booked' WHERE pid = ?";
        $stmt = mysqli_prepare($con, $update_property);
        mysqli_stmt_bind_param($stmt, 'i', $property_id);
        mysqli_stmt_execute($stmt);
    }
}

// Set success message for my_payments.php
$_SESSION['payment_success_message'] = "Your payment has been processed successfully. Thank you for your transaction!";
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
        <?php if (!empty($tx_ref)): ?>
        <p>Transaction Reference: <?php echo htmlspecialchars($tx_ref); ?></p>
        <?php endif; ?>
        
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
