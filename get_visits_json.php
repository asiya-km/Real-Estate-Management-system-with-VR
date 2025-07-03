<?php
session_start();
include("../config.php");
include("permission.php");

// Get all visits for calendar
$query = "SELECT v.id, v.visit_date, v.visit_time, v.status, u.uname, p.title 
          FROM visits v 
          JOIN user u ON v.user_id = u.uid 
          JOIN property p ON v.property_id = p.pid";

$result = mysqli_query($con, $query);
$events = array();

while($visit = mysqli_fetch_assoc($result)) {
    // Combine date and time
    $start = $visit['visit_date'] . ' ' . $visit['visit_time'];
    
    // Calculate end time (1 hour after start)
    $end_time = date('H:i:s', strtotime($visit['visit_time'] . ' +1 hour'));
    $end = $visit['visit_date'] . ' ' . $end_time;
    
    // Set color based on status
    $color = '';
    switch($visit['status']) {
        case 'pending':
            $color = '#ffb100';
            break;
        case 'confirmed':
            $color = '#28a745';
            break;
        case 'completed':
            $color = '#17a2b8';
            break;
        case 'cancelled':
            $color = '#dc3545';
            break;
        default:
            $color = '#6c757d';
    }
    
    // Create event object
    $event = array(
        'id' => $visit['id'],
        'title' => $visit['uname'] . ' - ' . $visit['title'],
        'start' => $start,
        'end' => $end,
        'backgroundColor' => $color,
        'borderColor' => $color,
        'extendedProps' => array(
            'status' => $visit['status']
        )
    );
    
    $events[] = $event;
}

// Return JSON
header('Content-Type: application/json');
echo json_encode($events);
?>
