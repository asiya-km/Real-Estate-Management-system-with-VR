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
$query = "SELECT v.*, u.uname, u.uemail, p.title, p.location, p.city 
          FROM visits v 
          JOIN user u ON v.user_id = u.uid 
          JOIN property p ON v.property_id = p.pid 
          WHERE v.id = ? AND v.status = 'confirmed'";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'i', $visit_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(!$visit = mysqli_fetch_assoc($result)) {
    $_SESSION['error'] = "Visit not found or not in confirmed status.";
    header("Location: manage_visits.php");
    exit();
}

// Process form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = mysqli_real_escape_string($con, $_POST['subject']);
    $message = mysqli_real_escape_string($con, $_POST['message']);
    
    // Send email
    $to = $visit['uemail'];
    $headers = "From: noreply@remsko.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    if(mail($to, $subject, $message, $headers)) {
        // Log the reminder
        $log_query = "INSERT INTO reminder_logs (visit_id, subject, message, sent_at) VALUES (?, ?, ?, NOW())";
        $log_stmt = mysqli_prepare($con, $log_query);
        mysqli_stmt_bind_param($log_stmt, 'iss', $visit_id, $subject, $message);
        mysqli_stmt_execute($log_stmt);
        
        $_SESSION['success'] = "Reminder sent successfully!";
        header("Location: visit_details.php?id=" . $visit_id);
        exit();
    } else {
        $error = "Failed to send reminder email.";
    }
}

// Default reminder template
$visit_date = date('l, F j, Y', strtotime($visit['visit_date']));
$visit_time = date('h:i A', strtotime($visit['visit_time']));
$default_subject = "Reminder: Your Property Visit on " . $visit_date;
$default_message = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #28a745; color: white; padding: 10px 20px; text-align: center; }
        .content { padding: 20px; border: 1px solid #ddd; border-top: none; }
        .footer { font-size: 12px; text-align: center; margin-top: 20px; color: #777; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>Property Visit Reminder</h2>
        </div>
        <div class='content'>
            <p>Dear " . htmlspecialchars($visit['uname']) . ",</p>
            
            <p>This is a friendly reminder about your upcoming property visit:</p>
            
            <p><strong>Property:</strong> " . htmlspecialchars($visit['title']) . "<br>
            <strong>Address:</strong> " . htmlspecialchars($visit['location'] . ', ' . $visit['city']) . "<br>
            <strong>Date:</strong> " . $visit_date . "<br>
            <strong>Time:</strong> " . $visit_time . "</p>
            
            <p>Our agent will be waiting for you at the property. Please arrive on time.</p>
            
            <p>If you need to reschedule or have any questions, please contact us as soon as possible.</p>
            
            <p>Thank you for choosing Remsko Real Estate.</p>
            
            <p>Best regards,<br>
            Remsko Real Estate Team</p>
        </div>
        <div class='footer'>
            <p>This email was sent to you because you scheduled a property visit with Remsko Real Estate.</p>
        </div>
    </div>
</body>
</html>
";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Send Visit Reminder - Admin Dashboard</title>
    <!-- Include CSS files -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css">
</head>
<body>
    <?php include("header.php"); ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include("sidebar.php"); ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Send Visit Reminder</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="visit_details.php?id=<?php echo $visit_id; ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Visit Details
                        </a>
                    </div>
                </div>
                
                <?php if(isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Visit Information</h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p><strong>Customer:</strong> <?php echo htmlspecialchars($visit['uname']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($visit['uemail']); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($visit['phone']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Property:</strong> <?php echo htmlspecialchars($visit['title']); ?></p>
                                <p><strong>Visit Date:</strong> <?php echo date('F j, Y', strtotime($visit['visit_date'])); ?></p>
                                <p><strong>Visit Time:</strong> <?php echo date('h:i A', strtotime($visit['visit_time'])); ?></p>
                            </div>
                        </div>
                        
                        <form method="post">
                            <div class="form-group">
                                <label for="subject">Email Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" value="<?php echo htmlspecialchars($default_subject); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="message">Email Message</label>
                                <textarea class="form-control summernote" id="message" name="message" rows="10" required><?php echo $default_message; ?></textarea>
                            </div>
                            
                            <div class="text-center mt-4">
                                                               <button type="submit" class="btn btn-primary">
                                    <i class="far fa-envelope"></i> Send Reminder
                                </button>
                                <a href="visit_details.php?id=<?php echo $visit_id; ?>" class="btn btn-secondary ml-2">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.summernote').summernote({
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    </script>
</body>
</html>
