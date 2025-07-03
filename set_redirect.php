<?php
session_start();

// Get the redirect URL from the query parameter
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';

if (!empty($redirect)) {
    // Store the redirect URL in the session
    $_SESSION['redirect_after_login'] = $redirect;
}

// Redirect to the login page
header("Location: login1.php");
exit();
?>
