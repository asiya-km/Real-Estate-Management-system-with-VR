<?php
session_start();
include("../config.php");
include("permission.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if visit ID is provided
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_visits.php");
    exit();
}

$visit_id = intval($_GET['id']);

// Get visit details
$query = "SELECT v.*, u.uname, u.uemail, u.uphone, p.title, p.pimage, p.location, p.city, p.price, p.type, p.stype 
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

// Handle visit status updates
if(isset($_POST['update_visit_status'])) {
    $status = mysqli_real_escape_string($con, $_POST['status']);
    $admin_notes = mysqli_real_escape_string($con, $_POST['admin_notes']);

    // Get current visit data before updating (for history)
    $get_current = "SELECT visit_date, visit_time FROM visits WHERE id = ?";
    $current_stmt = mysqli_prepare($con, $get_current);
    mysqli_stmt_bind_param($current_stmt, 'i', $visit_id);
    mysqli_stmt_execute($current_stmt);
    $current_result = mysqli_stmt_get_result($current_stmt);
    $current_visit = mysqli_fetch_assoc($current_result);

    // Proceed with the update...
    $update_query = "UPDATE visits SET 
                    status = ?, 
                    admin_notes = ?, 
                    updated_at = NOW() 
                    WHERE id = ?";
    $stmt = mysqli_prepare($con, $update_query);
    mysqli_stmt_bind_param($stmt, 'ssi', $status, $admin_notes, $visit_id);        
    if(mysqli_stmt_execute($stmt)) {
        // If status changed to completed or cancelled, log in history
        if ($status == 'completed' || $status == 'cancelled') {
            $check_table = mysqli_query($con, "SHOW TABLES LIKE 'visit_history'");
            if(mysqli_num_rows($check_table) > 0) {
                $history_query = "INSERT INTO visit_history (visit_id, previous_date, previous_time, reason) 
                                VALUES (?, ?, ?, ?)";
                $history_stmt = mysqli_prepare($con, $history_query);
                $reason = "Status changed to " . $status . " by admin";
                mysqli_stmt_bind_param($history_stmt, 'isss', $visit_id, $current_visit['visit_date'], $current_visit['visit_time'], $reason);
                mysqli_stmt_execute($history_stmt);
            }
        }

        // Send notification to user
        $to = $visit['email'];
        $subject = "Visit Status Update - Remsko Real Estate";
        $message = "Dear " . $visit['name'] . ",\n\n";
        $message .= "Your visit request for property \"" . $visit['title'] . "\" has been " . $status . ".\n\n";

        if($status == 'confirmed') {
            $message .= "Visit Details:\n";
            $message .= "Date: " . date('F j, Y', strtotime($visit['visit_date'])) . "\n";
            $message .= "Time: " . date('h:i A', strtotime($visit['visit_time'])) . "\n\n";
            $message .= "Please be on time. Our agent will be waiting for you at the property.\n\n";
        } elseif($status == 'cancelled') {
            $message .= "We're sorry for any inconvenience. If you'd like to reschedule, please log in to your account.\n\n";
        }

        $message .= "Admin Notes: " . $admin_notes . "\n\n";
        $message .= "Thank you for choosing Remsko Real Estate.\n\n";
        $message .= "Best regards,\nRemsko Real Estate Team";

        $headers = "From: noreply@remsko.com";

        mail($to, $subject, $message, $headers);

        header("Location: visit_details.php?id=" . $visit_id . "&success=1");
        exit();
    } else {
        $error = "Failed to update visit status: " . mysqli_error($con);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Remsko Real Estate | Visit Details</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    
    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    
    <!-- Feathericon CSS -->
    <link rel="stylesheet" href="assets/css/feathericon.min.css">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!--[if lt IE 9]>
        <script src="assets/js/html5shiv.min.js"></script>
        <script src="assets/js/respond.min.js"></script>
    <![endif]-->
    
    <style>
        .status-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending {
            background-color: #ffb100;
            color: #fff;
        }
        .status-confirmed {
            background-color: #28a745;
            color: #fff;
        }
        .status-completed {
            background-color: #17a2b8;
            color: #fff;
        }
        .status-cancelled {
            background-color: #dc3545;
            color: #fff;
        }
        .timeline {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .timeline:before {
            content: '';
            position: absolute;
            left: 28px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        .timeline-item {
            position: relative;
            padding-left: 70px;
            padding-bottom: 20px;
        }
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        .timeline-date {
            font-size: 0.875rem;
            color: #6c757d;
        }
        .timeline-icon {
            position: absolute;
            left: 0;
            top: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            text-align: center;
            line-height: 40px;
        }
        .timeline-content {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <?php include("header.php"); ?>
        
        <!-- Page Wrapper -->
        <div class="page-wrapper">
            <div class="content container-fluid">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="row">
                        <div class="col-sm-12">
                            <h3 class="page-title">Visit Details</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="manage_visits.php">Manage Visits</a></li>
                                <li class="breadcrumb-item active">Visit Details</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /Page Header -->
                
                <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    Visit status updated successfully!
                </div>
                <?php endif; ?>
                
                <?php if(isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Visit Information</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Visit ID</label>
                                            <input type="text" class="form-control" value="#<?php echo str_pad($visit['id'], 4, '0', STR_PAD_LEFT); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Status</label>
                                            <div>
                                                <span class="status-badge status-<?php echo $visit['status']; ?>">
                                                    <?php echo ucfirst($visit['status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Visit Date</label>
                                            <input type="text" class="form-control" value="<?php echo date('F j, Y', strtotime($visit['visit_date'])); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Visit Time</label>
                                            <input type="text" class="form-control" value="<?php echo date('h:i A', strtotime($visit['visit_time'])); ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Customer Name</label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($visit['name']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Customer Phone</label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($visit['phone']); ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Customer Email</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($visit['email']); ?>" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label>Customer Message</label>
                                    <textarea class="form-control" rows="3" readonly><?php echo htmlspecialchars($visit['message']); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label>Request Date</label>
                                    <input type="text" class="form-control" value="<?php echo date('F j, Y h:i A', strtotime($visit['request_date'])); ?>" readonly>
                                </div>
                                
                                <hr>
                                
                                <form method="post" action="">
                                    <div class="form-group">
                                        <label>Update Status</label>
                                        <select class="form-control" name="status" required>
                                            <option value="pending" <?php echo $visit['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $visit['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="completed" <?php echo $visit['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $visit['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <!-- <?php if($visit['status'] == 'cancelled'): ?>
                                        <div class="text-danger mt-2">
                                            <i class="fe fe-alert-triangle"></i> This visit was cancelled by the customer and cannot be reactivated. Please create a new visit if needed.
                                        </div>
                                        <?php endif; ?> -->
                                    </div>
                                    
                                                                       <div class="form-group">
                                        <label>Admin Notes</label>
                                        <textarea class="form-control" name="admin_notes" rows="4"><?php echo htmlspecialchars($visit['admin_notes']); ?></textarea>
                                        <small class="form-text text-muted">These notes will be included in the email sent to the customer.</small>
                                    </div>
                                    
                                    <div class="text-right">
                                     
<button type="submit" name="update_visit_status" class="btn btn-primary">Update</button>


                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <?php if($history_result && mysqli_num_rows($history_result) > 0): ?>
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Visit History</h4>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    <?php while($history = mysqli_fetch_assoc($history_result)): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-icon">
                                            <i class="fe fe-clock"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="timeline-date">
                                                <?php echo date('M d, Y h:i A', strtotime($history['created_at'])); ?>
                                            </div>
                                            <h6>Visit Rescheduled</h6>
                                            <p>Previous Date: <?php echo date('F j, Y', strtotime($history['previous_date'])); ?></p>
                                            <p>Previous Time: <?php echo date('h:i A', strtotime($history['previous_time'])); ?></p>
                                            <?php if(!empty($history['reason'])): ?>
                                            <p>Reason: <?php echo htmlspecialchars($history['reason']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Property Information</h4>
                            </div>
                            <div class="card-body">
                                <div class="property-img mb-4">
                                    <img src="../admin/property/<?php echo htmlspecialchars($visit['pimage']); ?>" class="img-fluid rounded" alt="Property Image">
                                </div>
                                
                                <h5><?php echo htmlspecialchars($visit['title']); ?></h5>
                                <p class="text-muted">
                                    <i class="fe fe-map-pin mr-1"></i> <?php echo htmlspecialchars($visit['location']); ?>, <?php echo htmlspecialchars($visit['city']); ?>
                                </p>
                                
                                <div class="property-details">
                                    <div class="row">
                                        <div class="col-6">
                                            <p><strong>Price:</strong></p>
                                        </div>
                                        <div class="col-6 text-right">
                                            <p>ETB <?php echo number_format($visit['price']); ?></p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <p><strong>Type:</strong></p>
                                        </div>
                                        <div class="col-6 text-right">
                                            <p><?php echo htmlspecialchars($visit['type']); ?></p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <p><strong>Status:</strong></p>
                                        </div>
                                        <div class="col-6 text-right">
                                            <p>For <?php echo htmlspecialchars($visit['stype']); ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <a href="../propertydetail.php?pid=<?php echo $visit['property_id']; ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="fe fe-external-link mr-1"></i> View Property
                                    </a>
                                    <a href="propertyview.php" class="btn btn-outline-secondary btn-sm ml-2">
                                        <i class="fe fe-list mr-1"></i> All Properties
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Customer Information</h4>
                            </div>
                            <div class="card-body">
                                <div class="customer-details">
                                    <div class="row">
                                        <div class="col-6">
                                            <p><strong>Name:</strong></p>
                                        </div>
                                        <div class="col-6 text-right">
                                            <p><?php echo htmlspecialchars($visit['uname']); ?></p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <p><strong>Email:</strong></p>
                                        </div>
                                        <div class="col-6 text-right">
                                            <p><?php echo htmlspecialchars($visit['uemail']); ?></p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <p><strong>Phone:</strong></p>
                                        </div>
                                        <div class="col-6 text-right">
                                            <p><?php echo htmlspecialchars($visit['uphone']); ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <a href="userlist.php" class="btn btn-outline-primary btn-sm">
                                        <i class="fe fe-users mr-1"></i> View All Users
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Quick Actions</h4>
                            </div>
                            <div class="card-body">
                                <div class="quick-actions">
                                    <a href="mailto:<?php echo $visit['email']; ?>" class="btn btn-block btn-outline-primary mb-2">
                                        <i class="fe fe-mail mr-1"></i> Email Customer
                                    </a>
                                    <a href="tel:<?php echo $visit['phone']; ?>" class="btn btn-block btn-outline-info mb-2">
                                        <i class="fe fe-phone mr-1"></i> Call Customer
                                    </a>
                                    <a href="manage_visits.php" class="btn btn-block btn-outline-secondary">
                                        <i class="fe fe-list mr-1"></i> All Visits
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Page Wrapper -->
    </div>
    <!-- /Main Wrapper -->
    
    <!-- jQuery -->
    <script src="assets/js/jquery-3.2.1.min.js"></script>
    
    <!-- Bootstrap Core JS -->
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    
    <!-- Slimscroll JS -->
    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
</body>
</html>
