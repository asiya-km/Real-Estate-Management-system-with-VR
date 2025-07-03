<?php
session_start();
include("../config.php");
include("permission.php");

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="visits_export_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add CSV header row
fputcsv($output, array(
    'Visit ID',
    'Property',
    'Location',
    'Customer Name',
    'Customer Email',
    'Customer Phone',
    'Visit Date',
    'Visit Time',
    'Status',
    'Request Date',
    'Admin Notes'
));

// Get all visits
$query = "SELECT v.*, u.uname, p.title, p.location, p.city 
          FROM visits v 
          JOIN user u ON v.user_id = u.uid 
          JOIN property p ON v.property_id = p.pid 
          ORDER BY v.visit_date DESC, v.visit_time ASC";

$result = mysqli_query($con, $query);

// Add data rows
while($visit = mysqli_fetch_assoc($result)) {
    fputcsv($output, array(
        $visit['id'],
        $visit['title'],
        $visit['location'] . ', ' . $visit['city'],
        $visit['name'],
        $visit['email'],
        $visit['phone'],
        date('Y-m-d', strtotime($visit['visit_date'])),
        date('H:i', strtotime($visit['visit_time'])),
        $visit['status'],
        date('Y-m-d H:i:s', strtotime($visit['request_date'])),
        $visit['admin_notes']
    ));
}

// Close the output stream
fclose($output);
exit;
?>