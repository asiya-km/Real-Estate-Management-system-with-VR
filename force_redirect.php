<?php
session_start();

// Set the success message for the payment history page
$_SESSION['payment_success_message'] = "Your payment has been processed successfully. Thank you for your transaction!";

// Redirect immediately
header("Location: my_payments.php");
exit();
?>
