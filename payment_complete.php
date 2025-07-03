<?php
// At the top of the file
file_put_contents('payment_debug.log', date('Y-m-d H:i:s') . " - Payment complete page accessed\n", FILE_APPEND);
file_put_contents('payment_debug.log', date('Y-m-d H:i:s') . " - GET params: " . print_r($_GET, true) . "\n", FILE_APPEND);
file_put_contents('payment_debug.log', date('Y-m-d H:i:s') . " - SESSION before: " . print_r($_SESSION, true) . "\n", FILE_APPEND);

// Rest of the code...

// Before exit
file_put_contents('payment_debug.log', date('Y-m-d H:i:s') . " - SESSION after: " . print_r($_SESSION, true) . "\n", FILE_APPEND);
?>

<?php
session_start();

// Set success message
$_SESSION['payment_success_message'] = "Your payment has been processed successfully. Thank you for your transaction!";

// Store transaction reference if available
if (isset($_GET['tx_ref'])) {
    $_SESSION['last_transaction_ref'] = $_GET['tx_ref'];
}

// Output a simple HTML page with JavaScript redirect
echo '<!DOCTYPE html>
<html>
<head>
    <title>Payment Complete</title>
</head>
<body>
    <h2>Payment Complete</h2>
    <p>Your payment has been processed successfully.</p>
    <p>Redirecting to payment history...</p>
    
    <script>
    // Redirect immediately
    window.location.href = "my_payments.php";
    </script>
</body>
</html>';
exit;
?>
