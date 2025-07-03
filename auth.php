<?php
if (!isset($_SESSION['uid'])) {
    // Store the current URL as the intended destination
    $_SESSION['intended_redirect'] = $_SERVER['REQUEST_URI'];
    header("location:login1.php");
    exit();
}
?>
