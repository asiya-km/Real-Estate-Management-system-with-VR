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
$error = "";
$success = "";

// Validate visit ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $visit_id = intval($_GET['id']);
    
    // Get visit details with property information
    $query = "SELECT v.*, p.title, p.pimage, p.location, p.city, p.price, p.type, p.stype 
              FROM visits v 
              JOIN property p ON v.property_id = p.pid 
              WHERE v.id = ? AND v.user_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $visit_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$visit = mysqli_fetch_assoc($result)) {
        header("Location: my_visits.php?error=" . urlencode("Visit not found"));
        exit();
    }
} else {
    header("Location: my_visits.php?error=" . urlencode("Invalid visit ID"));
    exit();
}

// Display success message if set
if (isset($_GET['msg'])) {
    $success = $_GET['msg'];
}

// Get visit history if available
$history_query = "SELECT * FROM visit_history WHERE visit_id = ? ORDER BY created_at DESC";
$history_stmt = mysqli_prepare($con, $history_query);
mysqli_stmt_bind_param($history_stmt, 'i', $visit_id);
mysqli_stmt_execute($history_stmt);
$history_result = mysqli_stmt_get_result($history_stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Visit Details - Remsko Real Estate</title>
    
    <!-- Include CSS files -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/bootstrap-slider.css">
    <link rel="stylesheet" type="text/css" href="css/jquery-ui.css">
    <link rel="stylesheet" type="text/css" href="css/layerslider.css">
    <link rel="stylesheet" type="text/css" href="css/color.css">
    <link rel="stylesheet" type="text/css" href="css/owl.carousel.min.css">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="fonts/flaticon/flaticon.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" type="text/css" href="css/login.css">
    
    <style>
        .visit-details-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .property-summary {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }
        .status-confirmed {
            background-color: #28a745;
            color: white;
        }
        .status-completed {
            background-color: #17a2b8;
            color: white;
        }
        .status-cancelled {
            background-color: #dc3545;
            color: white;
        }
        .visit-details-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .visit-details-header {
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            border-radius: 8px 8px 0 0;
        }
        .visit-details-body {
            padding: 20px;
        }
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 2px;
            background-color: #e9ecef;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        .timeline-item:before {
            content: '';
            position: absolute;
            left: -34px;
            top: 0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #007bff;
        }
        .timeline-date {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 5px;
        }
        .timeline-content {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <?php include("include/header.php"); ?>
    
    <!-- Banner -->
    <div class="banner-full-row page-banner" style="background-image:url('images/breadcromb.jpg');">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>Visit Details</b></h2>
                </div>
                <div class="col-md-6">
                    <nav aria-label="breadcrumb" class="float-left float-md-right">
                        <ol class="breadcrumb bg-transparent m-0 p-0">
                            <li class="breadcrumb-item text-white"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item text-white"><a href="user_dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item text-white"><a href="my_visits.php">My Visits</a></li>
                            <li class="breadcrumb-item active">Visit Details</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content -->
    <div class="full-row">
        <div class="container">
            <div class="visit-details-container">
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-8">
                        <h3 class="mb-4">Visit Details</h3>
                    </div>
                    <div class="col-md-4 text-right">
                        <span class="status-badge status-<?php echo $visit['status']; ?>">
                            <?php echo ucfirst($visit['status']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="property-summary">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="admin/property/<?php echo htmlspecialchars($visit['pimage']); ?>" alt="Property" class="img-fluid rounded">
                        </div>
                        <div class="col-md-8">
                            <h4 class="text-secondary"><?php echo htmlspecialchars($visit['title']); ?></h4>
                            <p><i class="fas fa-map-marker-alt text-success"></i> <?php echo htmlspecialchars($visit['location']); ?>, <?php echo htmlspecialchars($visit['city']); ?></p>
                            <p class="text-success h5">ETB <?php echo number_format($visit['price']); ?></p>
                            <p><strong>Type:</strong> <?php echo htmlspecialchars($visit['type']); ?> | <strong>Status:</strong> For <?php echo htmlspecialchars($visit['stype']); ?></p>
                            
                            <div class="mt-3">
                                <a href="propertydetail.php?pid=<?php echo $visit['property_id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View Property
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="visit-details-card">
                            <div class="visit-details-header">
                                <h5 class="mb-0">Visit Information</h5>
                            </div>
                            <div class="visit-details-body">
                                <div class="row mb-3">
                                    <div class="col-md-5 text-muted">Visit ID:</div>
                                    <div class="col-md-7">#<?php echo str_pad($visit['id'], 6, '0', STR_PAD_LEFT); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-5 text-muted">Visit Date:</div>
                                    <div class="col-md-7"><?php echo date('F j, Y', strtotime($visit['visit_date'])); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-5 text-muted">Visit Time:</div>
                                    <div class="col-md-7"><?php echo date('h:i A', strtotime($visit['visit_time'])); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-5 text-muted">Requested On:</div>
                                    <div class="col-md-7"><?php echo date('F j, Y', strtotime($visit['request_date'])); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-5 text-muted">Status:</div>
                                    <div class="col-md-7">
                                        <span class="status-badge status-<?php echo $visit['status']; ?>" style="font-size: 12px; padding: 3px 10px;">
                                            <?php echo ucfirst($visit['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                <?php if (!empty($visit['admin_notes'])): ?>
                                <div class="row">
                                    <div class="col-md-5 text-muted">Admin Notes:</div>
                                    <div class="col-md-7"><?php echo htmlspecialchars($visit['admin_notes']); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="visit-details-card">
                            <div class="visit-details-header">
                                <h5 class="mb-0">Contact Information</h5>
                            </div>
                            <div class="visit-details-body">
                                <div class="row mb-3">
                                    <div class="col-md-5 text-muted">Your Name:</div>
                                    <div class="col-md-7"><?php echo htmlspecialchars($visit['name']); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-5 text-muted">Email:</div>
                                    <div class="col-md-7"><?php echo htmlspecialchars($visit['email']); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-5 text-muted">Phone:</div>
                                    <div class="col-md-7"><?php echo htmlspecialchars($visit['phone']); ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5 text-muted">Message:</div>
                                    <div class="col-md-7"><?php echo htmlspecialchars($visit['message']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (mysqli_num_rows($history_result) > 0): ?>
                <div class="visit-details-card mt-4">
                    <div class="visit-details-header">
                        <h5 class="mb-0">Visit History</h5>
                    </div>
                    <div class="visit-details-body">
                        <div class="timeline">
                            <?php while($history = mysqli_fetch_assoc($history_result)): ?>
                            <div class="timeline-item">
                                <div class="timeline-date">
                                    <?php echo date('F j, Y h:i A', strtotime($history['created_at'])); ?>
                                </div>
                                <div class="timeline-content">
                                    <h6>Visit Rescheduled</h6>
                                    <p><strong>Previous Date:</strong> <?php echo date('F j, Y', strtotime($history['previous_date'])); ?></p>
                                                                        <p><strong>Previous Time:</strong> <?php echo date('h:i A', strtotime($history['previous_time'])); ?></p>
                                    <?php if(!empty($history['reason'])): ?>
                                    <p><strong>Reason:</strong> <?php echo htmlspecialchars($history['reason']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="my_visits.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left mr-2"></i> Back to My Visits
                            </a>
                        </div>
                        <div class="col-md-6 text-right">
                            <?php if($visit['status'] == 'pending' || $visit['status'] == 'confirmed'): ?>
                            <a href="reschedule_visit.php?id=<?php echo $visit_id; ?>" class="btn btn-warning">
                                <i class="fas fa-calendar-alt mr-2"></i> Reschedule Visit
                            </a>
                            <a href="cancel_visit.php?id=<?php echo $visit_id; ?>" class="btn btn-danger ml-2" onclick="return confirm('Are you sure you want to cancel this visit?');">
                                <i class="fas fa-times mr-2"></i> Cancel Visit
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include("include/footer.php"); ?>
    
    <!-- JavaScript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/bootstrap-slider.min.js"></script>
    <script src="js/jquery-ui.min.js"></script>
    <script src="js/layerslider.kreaturamedia.jquery.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/wow.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
