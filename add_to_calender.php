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

// Format for Google Calendar
$start_time = $start_datetime->format('Ymd\THis');
$end_time = $end_datetime->format('Ymd\THis');

// Create Google Calendar URL
$google_url = "https://calendar.google.com/calendar/render?action=TEMPLATE";
$google_url .= "&text=" . urlencode($title);
$google_url .= "&dates=" . $start_time . "/" . $end_time;
$google_url .= "&details=" . urlencode($description);
$google_url .= "&location=" . urlencode($location);
$google_url .= "&sf=true&output=xml";

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

// Create Outlook URL (using webcal protocol)
$outlook_url = "webcal://outlook.office.com/owa/?path=/calendar/action/compose&rru=addevent";
$outlook_url .= "&subject=" . urlencode($title);
$outlook_url .= "&startdt=" . urlencode($start_datetime->format('Y-m-d\TH:i:s'));
$outlook_url .= "&enddt=" . urlencode($end_datetime->format('Y-m-d\TH:i:s'));
$outlook_url .= "&body=" . urlencode($description);
$outlook_url .= "&location=" . urlencode($location);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Add to Calendar - Admin Dashboard</title>
    <!-- Include CSS files -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .calendar-option {
            text-align: center;
            margin-bottom: 30px;
        }
        .calendar-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .google-icon { color: #DB4437; }
        .outlook-icon { color: #0078D4; }
        .ical-icon { color: #5F6368; }
    </style>
</head>
<body>
    <?php include("header.php"); ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include("sidebar.php"); ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Add Visit to Calendar</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="visit_details.php?id=<?php echo $visit_id; ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Visit Details
                        </a>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Visit Information</h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p><strong>Property:</strong> <?php echo htmlspecialchars($visit['title']); ?></p>
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($visit['location'] . ', ' . $visit['city']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Customer:</strong> <?php echo htmlspecialchars($visit['name']); ?></p>
                                <p><strong>Visit Date:</strong> <?php echo date('F j, Y', strtotime($visit['visit_date'])); ?></p>
                                <p><strong>Visit Time:</strong> <?php echo date('h:i A', strtotime($visit['visit_time'])); ?></p>
                            </div>
                        </div>
                        
                        <h5 class="mb-4">Choose Calendar Option</h5>
                        
                        <div class="row">
                            <div class="col-md-4 calendar-option">
                                <div class="calendar-icon google-icon">
                                    <i class="fab fa-google"></i>
                                </div>
                                <h5>Google Calendar</h5>
                                <p>Add this visit to your Google Calendar</p>
                                <a href="<?php echo $google_url; ?>" class="btn btn-primary" target="_blank">
                                    <i class="fas fa-plus-circle"></i> Add to Google Calendar
                                </a>
                            </div>
                            
                            <div class="col-md-4 calendar-option">
                                <div class="calendar-icon outlook-icon">
                                    <i class="fab fa-microsoft"></i>
                                </div>
                                <h5>Outlook Calendar</h5>
                                <p>Add this visit to your Outlook Calendar</p>
                                <a href="<?php echo $outlook_url; ?>" class="btn btn-primary" target="_blank">
                                    <i class="fas fa-plus-circle"></i> Add to Outlook
                                </a>
                            </div>
                            
                            <div class="col-md-4 calendar-option">
                                <div class="calendar-icon ical-icon">
                                    <i class="far fa-calendar-alt"></i>
                                </div>
                                <h5>iCal File</h5>
                                <p>Download iCal file for other calendar apps</p>
                                <a href="download_ical.php?id=<?php echo $visit_id; ?>" class="btn btn-primary">
                                    <i class="fas fa-download"></i> Download iCal File
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
