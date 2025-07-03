<?php
session_start();
// Set success message
$_SESSION['payment_success_message'] = "Your payment has been processed successfully. Thank you for your transaction!";
// Force redirect using PHP header
header("Location: my_payments.php");
exit();
?>
