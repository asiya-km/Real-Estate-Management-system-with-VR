<?php
session_start();
require("config.php");

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login1.php");
    exit();
}

// Initialize variables
$user_id = $_SESSION['uid'];

// Validate booking ID
if (isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
    $booking_id = intval($_REQUEST['id']);
    
    // Start transaction
    mysqli_begin_transaction($con);
    
    try {
        // Check if booking exists and belongs to the user
        $check_query = "SELECT b.*, p.pid FROM bookings b 
                        JOIN property p ON b.property_id = p.pid 
                        WHERE b.id = ? AND b.user_id = ? AND b.status = 'pending' 
                        FOR UPDATE";
        
        $stmt = mysqli_prepare($con, $check_query);
        mysqli_stmt_bind_param($stmt, 'ii', $booking_id, $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (!$booking = mysqli_fetch_assoc($result)) {
            throw new Exception("Booking not found, unauthorized access, or cannot be cancelled");
        }
        
        // Update booking status
        $update_query = "UPDATE bookings SET status = 'cancelled' WHERE id = ?";
        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt, 'i', $booking_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to cancel booking: " . mysqli_error($con));
        }
        
        // Update property status back to available
        $update_property = "UPDATE property SET status = 'available' WHERE pid = ?";
        $stmt = mysqli_prepare($con, $update_property);
        mysqli_stmt_bind_param($stmt, 'i', $booking['property_id']);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to update property status: " . mysqli_error($con));
        }
        
        // Commit transaction
        mysqli_commit($con);
        
        $msg = "<p class='alert alert-success'>Booking cancelled successfully</p>";
        header("Location: user_dashboard.php?msg=" . urlencode($msg) . "#bookings");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($con);
        
        $msg = "<p class='alert alert-danger'>" . $e->getMessage() . "</p>";
        header("Location: user_dashboard.php?msg=" . urlencode($msg) . "#bookings");
        exit();
    }
} else {
    $msg = "<p class='alert alert-danger'>Invalid booking ID</p>";
    header("Location: user_dashboard.php?msg=" . urlencode($msg) . "#bookings");
    exit();
}
?>
