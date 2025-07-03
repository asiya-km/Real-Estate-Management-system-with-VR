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

// Validate visit ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $visit_id = intval($_GET['id']);
    
    // Get visit details
    $query = "SELECT * FROM visits WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $visit_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$visit = mysqli_fetch_assoc($result)) {
        header("Location: my_visits.php?error=" . urlencode("Visit not found"));
        exit();
    }
    
    // Check if visit can be cancelled
    if ($visit['status'] !== 'pending' && $visit['status'] !== 'confirmed') {
        header("Location: visit_details.php?id=" . $visit_id . "&error=" . urlencode("This visit cannot be cancelled"));
        exit();
    }
    
    // Get current visit data before updating (for history)
    $current_date = $visit['visit_date'];
    $current_time = $visit['visit_time'];
    
    // Update visit status to cancelled
    $update_query = "UPDATE visits SET 
                    status = 'cancelled', 
                    updated_at = NOW() 
                    WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($con, $update_query);
    mysqli_stmt_bind_param($stmt, 'ii', $visit_id, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Add to visit history
        $check_table = mysqli_query($con, "SHOW TABLES LIKE 'visit_history'");
        if(mysqli_num_rows($check_table) > 0) {
            $history_query = "INSERT INTO visit_history (visit_id, previous_date, previous_time, reason) 
                            VALUES (?, ?, ?, ?)";
            $history_stmt = mysqli_prepare($con, $history_query);
            $reason = "Visit cancelled by customer";
            mysqli_stmt_bind_param($history_stmt, 'isss', $visit_id, $current_date, $current_time, $reason);
            mysqli_stmt_execute($history_stmt);
        }
        
        // Redirect with success message
        header("Location: my_visits.php?success=" . urlencode("Your visit has been cancelled successfully"));
        exit();
    } else {
        // Redirect with error message
        header("Location: visit_details.php?id=" . $visit_id . "&error=" . urlencode("Failed to cancel visit: " . mysqli_error($con)));
        exit();
    }
} else {
    header("Location: my_visits.php?error=" . urlencode("Invalid visit ID"));
    exit();
}
?>
