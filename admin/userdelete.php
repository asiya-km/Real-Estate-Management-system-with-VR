<?php
include("config.php");

// Check if uid is provided
if (!isset($_GET['uid']) || empty($_GET['uid'])) {
    $msg = "<p class='alert alert-warning'>Invalid user ID</p>";
    header("Location:userlist.php?msg=$msg");
    exit;
}

// Sanitize the uid parameter
$uid = mysqli_real_escape_string($con, $_GET['uid']);

// First, get the user image filename
$sql = "SELECT uimage FROM user WHERE uid=?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "s", $uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_array($result)) {
    $img = $row["uimage"];
    
    // Delete the user image file if it exists and is not the default image
    if (!empty($img) && $img != 'default-user.jpg' && file_exists('user/'.$img)) {
        unlink('user/'.$img);
    }
    
    // Now delete the user from the database
    $sql = "DELETE FROM user WHERE uid = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "s", $uid);
    
    if (mysqli_stmt_execute($stmt)) {
        $msg = "<p class='alert alert-success'>User Deleted</p>";
    } else {
        $msg = "<p class='alert alert-warning'>User not Deleted: " . mysqli_error($con) . "</p>";
    }
} else {
    $msg = "<p class='alert alert-warning'>User not found</p>";
}

// Close the database connection
mysqli_close($con);

// Redirect back to the user list
header("Location:userlist.php?msg=$msg");
exit;
?>
