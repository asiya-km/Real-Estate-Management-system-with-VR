<?php
session_start();
include("config.php");

// Check admin authentication
if (!isset($_SESSION['auser'])) {
    header("Location: ../login1.php");
    exit();
}

// Validate property ID
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    $_SESSION['message'] = "<p class='alert alert-danger'>Invalid property ID</p>";
    header("Location: propertyview.php");
    exit();
}

$pid = (int)$_GET['id'];

try {
    // Use prepared statement to prevent SQL injection
    $stmt = mysqli_prepare($con, "DELETE FROM property WHERE pid = ?");
    mysqli_stmt_bind_param($stmt, 'i', $pid);
    
    if (mysqli_stmt_execute($stmt)) {
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        if ($affected_rows > 0) {
            $_SESSION['message'] = "<p class='alert alert-success'>Property Deleted Successfully</p>";
        } else {
            $_SESSION['message'] = "<p class='alert alert-warning'>No property found with that ID</p>";
        }
    } else {
        throw new Exception(mysqli_error($con));
    }
} catch (Exception $e) {
    $_SESSION['message'] = "<p class='alert alert-danger'>Error deleting property: " . htmlspecialchars($e->getMessage()) . "</p>";
} finally {
    // Cleanup
    if (isset($stmt)) {
        mysqli_stmt_close($stmt);
    }
    mysqli_close($con);
}

// Redirect back
header("Location: propertyview.php");
exit();
?>