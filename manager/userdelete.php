<?php
session_start(); // Add session start
include("config.php");

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Content-Security-Policy: default-src 'self'");

// Check if user is logged in
if (!isset($_SESSION['auser'])) {
    header("location:../login1.php");
    exit();
}

// Validate CSRF token
if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
    $_SESSION['error'] = "Invalid security token";
    header("Location: userlist.php");
    exit();
}

// Validate and sanitize the user ID
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid user ID";
    header("Location: userlist.php");
    exit();
}

$uid = intval($_GET['id']);

// First retrieve the image filename using prepared statement
$stmt = mysqli_prepare($con, "SELECT uimage FROM user WHERE uid = ?");
mysqli_stmt_bind_param($stmt, "i", $uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if($row = mysqli_fetch_array($result)) {
    $img = $row["uimage"];
    
    // Delete the image file if it exists
    $image_path = 'admin/user/' . $img;
    if(!empty($img) && file_exists($image_path)) {
        unlink($image_path);
    }
}
mysqli_stmt_close($stmt);

// Delete the user using prepared statement
$stmt = mysqli_prepare($con, "DELETE FROM user WHERE uid = ?");
mysqli_stmt_bind_param($stmt, "i", $uid);
$success = mysqli_stmt_execute($stmt);

if($success) {
    $_SESSION['msg'] = "User deleted successfully";
} else {
    $_SESSION['error'] = "Error deleting user: " . mysqli_error($con);
}

mysqli_stmt_close($stmt);
mysqli_close($con);

header("Location: userlist.php");
exit();
?>
