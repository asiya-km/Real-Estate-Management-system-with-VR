<?php
session_start();
require("config.php");

if (!isset($_SESSION['auser'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Invalid CSRF token";
        header("Location: admin_bookings.php");
        exit();
    }

    $booking_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    
    if (!$booking_id) {
        $_SESSION['error'] = "Invalid booking ID";
        header("Location: admin_bookings.php");
        exit();
    }

    // Start transaction
    mysqli_begin_transaction($con);

    try {
        // Get property ID from booking
        $stmt = mysqli_prepare($con, "SELECT property_id FROM bookings WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $booking_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $booking = mysqli_fetch_assoc($result);
        
        if (!$booking) {
            throw new Exception("Booking not found");
        }

        // Delete booking
        $stmt = mysqli_prepare($con, "DELETE FROM bookings WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $booking_id);
        mysqli_stmt_execute($stmt);

        // Update property status back to available
        $stmt = mysqli_prepare($con, "UPDATE property SET status = 'available' WHERE pid = ?");
        mysqli_stmt_bind_param($stmt, "i", $booking['property_id']);
        mysqli_stmt_execute($stmt);

        mysqli_commit($con);
        $_SESSION['success'] = "Booking deleted successfully";
    } catch (Exception $e) {
        mysqli_rollback($con);
        error_log("Delete error: " . $e->getMessage());
        $_SESSION['error'] = "Error deleting booking";
    }

    header("Location: admin_bookings.php");
    exit();
}