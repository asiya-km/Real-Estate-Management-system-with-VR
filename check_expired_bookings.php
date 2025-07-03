<?php
require("config.php");

// Get current date
$current_date = date('Y-m-d');

// Expire bookings where:
// 1. End date has passed and status is still 'active'
// 2. Booking was made more than 5 minutes ago and payment is not completed
$query1 = "UPDATE bookings 
           SET status = 'expired', property_status = 'completed' 
           WHERE 
           (
               status = 'active' AND end_date <= ?
           )
           OR
           (
               (payment_status IS NULL OR payment_status != 'completed')
               AND booking_time < DATE_SUB(NOW(), INTERVAL 5 MINUTE)
               AND (status IS NULL OR status = 'active')
           )";

// Prepare and execute the query
$stmt = mysqli_prepare($con, $query1);
mysqli_stmt_bind_param($stmt, "s", $current_date);
mysqli_stmt_execute($stmt);
$expired_count = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);

// Update property status to 'available' for expired bookings
$query2 = "UPDATE property p 
           JOIN bookings b ON p.pid = b.property_id 
           SET p.status = 'available' 
           WHERE b.status = 'expired' AND p.status = 'booked'";
$result = mysqli_query($con, $query2);
$property_count = mysqli_affected_rows($con);

echo "Expired bookings check completed. $expired_count bookings expired. $property_count properties updated to available.";
?>
