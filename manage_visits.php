<?php
session_start();
include("../config.php");
include("permission.php");

// Handle visit status updates
if(isset($_POST['update_visit_status'])) {
    $visit_id = intval($_POST['visit_id']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    $admin_notes = mysqli_real_escape_string($con, $_POST['admin_notes']);
    
    $update_query = "UPDATE visits SET 
                    status = ?, 
                    admin_notes = ?, 
                    updated_at = NOW() 
                    WHERE id = ?";
    $stmt = mysqli_prepare($con, $update_query);
    mysqli_stmt_bind_param($stmt, 'ssi', $status, $admin_notes, $visit_id);
    
    if(mysqli_stmt_execute($stmt)) {
        // Send notification to user
        $get_visit = "SELECT v.*, u.uemail, u.uname, p.title 
                      FROM visits v 
                      JOIN user u ON v.user_id = u.uid 
                      JOIN property p ON v.property_id = p.pid 
                      WHERE v.id = ?";
        $visit_stmt = mysqli_prepare($con, $get_visit);
        mysqli_stmt_bind_param($visit_stmt, 'i', $visit_id);
        mysqli_stmt_execute($visit_stmt);
        $visit_result = mysqli_stmt_get_result($visit_stmt);
        $visit = mysqli_fetch_assoc($visit_result);
        
        // Send email notification
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
        
        $success = "Visit status updated successfully!";
    } else {
        $error = "Failed to update visit status: " . mysqli_error($con);
    }
}

// Get all visits with pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($con, $_GET['status']) : '';
$date_filter = isset($_GET['date']) ? mysqli_real_escape_string($con, $_GET['date']) : '';

// Build query with filters
$query = "SELECT v.*, u.uname, p.title, p.location, p.city 
          FROM visits v 
          JOIN user u ON v.user_id = u.uid 
          JOIN property p ON v.property_id = p.pid 
          WHERE 1=1";

if(!empty($search)) {
    $query .= " AND (u.uname LIKE '%$search%' OR p.title LIKE '%$search%' OR v.phone LIKE '%$search%')";
}

if(!empty($status_filter)) {
    $query .= " AND v.status = '$status_filter'";
}

if(!empty($date_filter)) {
    $query .= " AND DATE(v.visit_date) = '$date_filter'";
}

$query .= " ORDER BY v.visit_date DESC, v.visit_time ASC LIMIT $offset, $limit";

$result = mysqli_query($con, $query);

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM visits v 
                JOIN user u ON v.user_id = u.uid 
                JOIN property p ON v.property_id = p.pid 
                WHERE 1=1";

if(!empty($search)) {
    $count_query .= " AND (u.uname LIKE '%$search%' OR p.title LIKE '%$search%' OR v.phone LIKE '%$search%')";
}

if(!empty($status_filter)) {
    $count_query .= " AND v.status = '$status_filter'";
}

if(!empty($date_filter)) {
    $count_query .= " AND DATE(v.visit_date) = '$date_filter'";
}

$count_result = mysqli_query($con, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Manage Visits - Admin Dashboard</title>
    <!-- Include CSS files -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .visit-card {
            margin-bottom: 20px;
            border-left: 5px solid #ccc;
        }
        .visit-card.pending {
            border-left-color: #ffc107;
        }
        .visit-card.confirmed {
            border-left-color: #28a745;
        }
        .visit-card.completed {
            border-left-color: #17a2b8;
        }
        .visit-card.cancelled {
            border-left-color: #dc3545;
        }
        .visit-date {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .calendar-view {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .calendar-day {
            border: 1px solid #ddd;
            padding: 10px;
            min-height: 100px;
            background-color: #fff;
        }
        .calendar-day.has-visits {
            background-color: #e9f7ef;
        }
        .calendar-day.today {
            background-color: #f8f9fa;
            border: 2px solid #28a745;
        }
        .calendar-day-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .visit-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
        }
        .visit-dot.pending {
            background-color: #ffc107;
        }
        .visit-dot.confirmed {
            background-color: #28a745;
        }
        .visit-dot.completed {
            background-color: #17a2b8;
        }
        .visit-dot.cancelled {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <?php include("header.php"); ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include("sidebar.php"); ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                                        <h1 class="h2">Manage Property Visits</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="view-list-btn">
                                <i class="fas fa-list"></i> List View
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="view-calendar-btn">
                                <i class="fas fa-calendar-alt"></i> Calendar View
                            </button>
                            <a href="export_visits.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-download"></i> Export
                            </a>
                        </div>
                    </div>
                </div>
                
                <?php if(isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?php echo $success; ?>
                </div>
                <?php endif; ?>
                
                <?php if(isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <!-- Filter and Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="get" class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" placeholder="Search by name, property, phone..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="date" class="form-label">Visit Date</label>
                                <input type="text" class="form-control date-picker" id="date" name="date" placeholder="Select date" value="<?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : ''; ?>">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Filter</button>
                                <a href="manage_visits.php" class="btn btn-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Calendar View -->
                <div id="calendar-view" class="calendar-view" style="display: none;">
                    <?php
                    // Get current month and year
                    $month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
                    $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
                    
                    // Get first day of the month
                    $first_day = mktime(0, 0, 0, $month, 1, $year);
                    $title = date('F Y', $first_day);
                    $day_of_week = date('N', $first_day);
                    $days_in_month = date('t', $first_day);
                    
                    // Get visits for the month
                    $month_visits_query = "SELECT v.id, v.visit_date, v.status, COUNT(*) as count 
                                          FROM visits v 
                                          WHERE MONTH(v.visit_date) = $month AND YEAR(v.visit_date) = $year 
                                          GROUP BY DATE(v.visit_date), v.status";
                    $month_visits_result = mysqli_query($con, $month_visits_query);
                    $month_visits = [];
                    
                    while($visit = mysqli_fetch_assoc($month_visits_result)) {
                        $date = date('j', strtotime($visit['visit_date']));
                        if(!isset($month_visits[$date])) {
                            $month_visits[$date] = [];
                        }
                        $month_visits[$date][$visit['status']] = $visit['count'];
                    }
                    ?>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3><?php echo $title; ?></h3>
                        <div>
                            <a href="?month=<?php echo $month == 1 ? 12 : $month - 1; ?>&year=<?php echo $month == 1 ? $year - 1 : $year; ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                            <a href="?month=<?php echo date('n'); ?>&year=<?php echo date('Y'); ?>" class="btn btn-sm btn-outline-secondary">
                                Today
                            </a>
                            <a href="?month=<?php echo $month == 12 ? 1 : $month + 1; ?>&year=<?php echo $month == 12 ? $year + 1 : $year; ?>" class="btn btn-sm btn-outline-secondary">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="row mb-2">
                        <div class="col">Mon</div>
                        <div class="col">Tue</div>
                        <div class="col">Wed</div>
                        <div class="col">Thu</div>
                        <div class="col">Fri</div>
                        <div class="col">Sat</div>
                        <div class="col">Sun</div>
                    </div>
                    
                    <div class="row">
                        <?php
                        // Add empty cells for days before the first day of the month
                        for($i = 1; $i < $day_of_week; $i++) {
                            echo '<div class="col calendar-day"></div>';
                        }
                        
                        // Add cells for each day of the month
                        for($day = 1; $day <= $days_in_month; $day++) {
                            $date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
                            $is_today = ($date == date('Y-m-d'));
                            $has_visits = isset($month_visits[$day]);
                            
                            $class = 'calendar-day';
                            if($is_today) $class .= ' today';
                            if($has_visits) $class .= ' has-visits';
                            
                            echo '<div class="col ' . $class . '">';
                            echo '<div class="calendar-day-header">' . $day . '</div>';
                            
                            if($has_visits) {
                                echo '<div>';
                                foreach($month_visits[$day] as $status => $count) {
                                    echo '<div><span class="visit-dot ' . $status . '"></span>';
                                    echo $count . ' ' . ucfirst($status);
                                    echo '</div>';
                                }
                                echo '</div>';
                                echo '<a href="?date=' . $date . '" class="btn btn-sm btn-link">View</a>';
                            }
                            
                            echo '</div>';
                            
                            // Start a new row after Saturday
                            if(($day_of_week + $day - 1) % 7 == 0) {
                                echo '</div><div class="row">';
                            }
                        }
                        
                        // Add empty cells for days after the last day of the month
                        $remaining_days = 7 - (($day_of_week + $days_in_month - 1) % 7);
                        if($remaining_days < 7) {
                            for($i = 0; $i < $remaining_days; $i++) {
                                echo '<div class="col calendar-day"></div>';
                            }
                        }
                        ?>
                    </div>
                </div>
                
                <!-- List View -->
                <div id="list-view">
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($visit = mysqli_fetch_assoc($result)): ?>
                            <div class="card visit-card <?php echo $visit['status']; ?>">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5 class="card-title"><?php echo htmlspecialchars($visit['title']); ?></h5>
                                            <p class="card-text">
                                                <i class="fas fa-map-marker-alt text-secondary"></i> 
                                                <?php echo htmlspecialchars($visit['location'] . ', ' . $visit['city']); ?>
                                            </p>
                                            <p class="card-text">
                                                <strong>Customer:</strong> <?php echo htmlspecialchars($visit['name']); ?><br>
                                                <strong>Phone:</strong> <?php echo htmlspecialchars($visit['phone']); ?><br>
                                                <strong>Email:</strong> <?php echo htmlspecialchars($visit['email']); ?>
                                            </p>
                                            <?php if(!empty($visit['message'])): ?>
                                                <p class="card-text">
                                                    <strong>Message:</strong> <?php echo htmlspecialchars($visit['message']); ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if(!empty($visit['admin_notes'])): ?>
                                                <p class="card-text">
                                                    <strong>Admin Notes:</strong> <?php echo htmlspecialchars($visit['admin_notes']); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-4 text-md-right">
                                            <div class="visit-date mb-2">
                                                <i class="far fa-calendar-alt"></i> 
                                                <?php echo date('F j, Y', strtotime($visit['visit_date'])); ?>
                                            </div>
                                            <div class="mb-2">
                                                <i class="far fa-clock"></i> 
                                                <?php echo date('h:i A', strtotime($visit['visit_time'])); ?>
                                            </div>
                                            <div class="mb-3">
                                                <span class="badge badge-<?php 
                                                    echo $visit['status'] == 'pending' ? 'warning' : 
                                                        ($visit['status'] == 'confirmed' ? 'success' : 
                                                            ($visit['status'] == 'completed' ? 'info' : 'danger')); 
                                                ?>">
                                                    <?php echo ucfirst($visit['status']); ?>
                                                </span>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#updateVisitModal<?php echo $visit['id']; ?>">
                                                Update Status
                                            </button>
                                            <a href="visit_details.php?id=<?php echo $visit['id']; ?>" class="btn btn-sm btn-outline-secondary mt-2">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Update Visit Modal -->
                            <div class="modal fade" id="updateVisitModal<?php echo $visit['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="updateVisitModalLabel<?php echo $visit['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="updateVisitModalLabel<?php echo $visit['id']; ?>">Update Visit Status</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form method="post">
                                            <div class="modal-body">
                                                <input type="hidden" name="visit_id" value="<?php echo $visit['id']; ?>">
                                                
                                                <div class="form-group">
                                                    <label for="status<?php echo $visit['id']; ?>">Status</label>
                                                    <select class="form-control" id="status<?php echo $visit['id']; ?>" name="status" required>
                                                        <option value="pending" <?php echo $visit['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="confirmed" <?php echo $visit['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                        <option value="completed" <?php echo $visit['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                        <option value="cancelled" <?php echo $visit['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="admin_notes<?php echo $visit['id']; ?>">Admin Notes</label>
                                                    <textarea class="form-control" id="admin_notes<?php echo $visit['id']; ?>" name="admin_notes" rows="3"><?php echo htmlspecialchars($visit['admin_notes'] ?? ''); ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <button type="submit" name="update_visit_status" class="btn btn-primary">Save changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        
                        <!-- Pagination -->
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($date_filter) ? '&date=' . urlencode($date_filter) : ''; ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($date_filter) ? '&date=' . urlencode($date_filter) : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($date_filter) ? '&date=' . urlencode($date_filter) : ''; ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No visits found.
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        $(document).ready(function() {
            // Initialize date picker
            flatpickr(".date-picker", {
                dateFormat: "Y-m-d"
            });
            
            // Toggle between list and calendar views
            $("#view-list-btn").click(function() {
                $("#list-view").show();
                $("#calendar-view").hide();
                $(this).addClass("active");
                $("#view-calendar-btn").removeClass("active");
            });
            
            $("#view-calendar-btn").click(function() {
                $("#calendar-view").show();
                $("#list-view").hide();
                $(this).addClass("active");
                $("#view-list-btn").removeClass("active");
            });
        });
    </script>
</body>
</html>

