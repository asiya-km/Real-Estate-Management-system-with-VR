<?php
session_start();
include("../config.php");
include("permission.php");

// Get visit ID
$visit_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($visit_id <= 0) {
    header("Location: manage_visits.php");
    exit();
}

// Get visit details
$query = "SELECT v.*, u.uname, p.title, p.location, p.city 
          FROM visits v 
          JOIN user u ON v.user_id = u.uid 
          JOIN property p ON v.property_id = p.pid 
          WHERE v.id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'i', $visit_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(!$visit = mysqli_fetch_assoc($result)) {
    header("Location: manage_visits.php");
    exit();
}

// Format data for calendar
$title = "Property Visit: " . $visit['title'];
$description = "Customer: " . $visit['name'] . "\nPhone: " . $visit['phone'] . "\nEmail: " . $visit['email'];
if(!empty($visit['message'])) {
    $description .= "\n\nCustomer Message: " . $visit['message'];
}
$location = $visit['location'] . ", " . $visit['city'];

// Create start and end times (1 hour duration)
$start_datetime = new DateTime($visit['visit_date'] . ' ' . $visit['visit_time']);
$end_datetime = clone $start_datetime;
$end_datetime->modify('+1 hour');

// Create iCal file content
$ical_content = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Remsko Real Estate//Property Visit//EN
CALSCALE:GREGORIAN
BEGIN:VEVENT
DTSTART:" . $start_datetime->format('Ymd\THis\Z') . "
DTEND:" . $end_datetime->format('Ymd\THis\Z') . "
SUMMARY:" . $title . "
DESCRIPTION:" . str_replace("\n", "\\n", $description) . "
LOCATION:" . $location . "
STATUS:CONFIRMED
SEQUENCE:0
END:VEVENT
END:VCALENDAR";

// Set headers for file download
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="property_visit_' . $visit_id . '.ics"');

// Output the file content
echo $ical_content;
exit;
?>
