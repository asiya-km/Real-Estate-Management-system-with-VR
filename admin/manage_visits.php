<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include("../config.php");
include("permission.php");

// Handle visit status updates
if (isset($_POST['update_visit_status'])) {
    $visit_id = isset($_POST['visit_id']) ? intval($_POST['visit_id']) : 0;
    $new_status = mysqli_real_escape_string($con, $_POST['status']);
    $admin_notes = mysqli_real_escape_string($con, $_POST['admin_notes']);

    // Check current status
    $check_query = "SELECT status FROM visits WHERE id = ?";
    $check_stmt = mysqli_prepare($con, $check_query);
    mysqli_stmt_bind_param($check_stmt, 'i', $visit_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_bind_result($check_stmt, $current_status);
    mysqli_stmt_fetch($check_stmt);
    mysqli_stmt_close($check_stmt);

    // Normalize both values
    $normalized_current_status = strtolower(trim($current_status));
    $blocked_status = strtolower("Cancelled by customer");

    if ($normalized_current_status === $blocked_status) {
        echo "<div class='alert alert-warning'>You cannot update a visit that was cancelled by the customer.</div>";
    } else {
        // Proceed with update
        $update_query = "UPDATE visits SET 
                        status = ?, 
                        admin_notes = ? 
                        WHERE id = ?";
        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt, 'ssi', $new_status, $admin_notes, $visit_id);

        if (mysqli_stmt_execute($stmt)) {
            echo "<div class='alert alert-success'>Status updated successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error updating status: " . mysqli_error($con) . "</div>";
        }
    }

    // Debug info
    echo "<pre>Current Status from DB: " . htmlspecialchars($current_status) . "</pre>";
    echo "<pre>POST data: ";
    print_r($_POST);
    echo "</pre>";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Remsko Real Estate | Manage Visits</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    
    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    
    <!-- Feathericon CSS -->
    <link rel="stylesheet" href="assets/css/feathericon.min.css">
    
    <!-- Datatables CSS -->
    <link rel="stylesheet" href="assets/plugins/datatables/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/plugins/datatables/responsive.bootstrap4.min.css">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
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
        .visit-details-row {
            display: none;
            background-color: #f9f9f9;
        }
        .visit-details-content {
            padding: 15px;
        }
        .toggle-details {
            cursor: pointer;
        }
        .calendar-view {
            display: none;
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
                        <div class="col-sm-12"><p>.</p>
                            <h3 class="page-title">Manage Property Visits</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Manage Visits</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /Page Header -->
                
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
                
                <div class="row">
                    <div class="col-md-12">
                        <!-- Filter and Search -->
                        <div class="card">
                            <div class="card-body">
                                <form method="get" class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Search</label>
                                            <input type="text" class="form-control" name="search" placeholder="Search by name, property, phone..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select class="form-control" name="status">
                                                <option value="">All Statuses</option>
                                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Visit Date</label>
                                            <input type="text" class="form-control date-picker" name="date" placeholder="Select date" value="<?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <div class="form-group mb-0">
                                            <button type="submit" class="btn btn-primary mr-2">Filter</button>
                                            <a href="manage_visits.php" class="btn btn-secondary">Reset</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- View Toggle Buttons -->
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h4 class="card-title">Property Visits</h4>
                                        <p class="text-muted">Manage and track all property visit requests</p>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <div class="btn-group mb-3">
                                            <button type="button" class="btn btn-primary active" id="view-list-btn">
                                                <i class="fe fe-list"></i> List View
                                            </button>
                                            <button type="button" class="btn btn-light" id="view-calendar-btn">
                                                <i class="fe fe-calendar"></i> Calendar View
                                            </button>
                                        </div>
                                        <a href="export_visits.php" class="btn btn-success">
                                            <i class="fe fe-download"></i> Export
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- List View -->
                                <div id="list-view">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-center mb-0">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Property</th>
                                                    <th>Customer</th>
                                                    <th>Visit Date</th>
                                                    <th>Visit Time</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if(mysqli_num_rows($result) > 0): ?>
                                                    <?php while($visit = mysqli_fetch_assoc($result)): ?>
                                                        <tr class="toggle-details" data-visit-id="<?php echo $visit['id']; ?>">
                                                            <td>#<?php echo str_pad($visit['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                                            <td>
                                                                <h2 class="table-avatar">
                                                                    <?php echo htmlspecialchars($visit['title']); ?>
                                                                    <span><?php echo htmlspecialchars($visit['location'] . ', ' . $visit['city']); ?></span>
                                                                </h2>
                                                            </td>
                                                            <td>
                                                                <h2 class="table-avatar">
                                                                    <?php echo htmlspecialchars($visit['name']); ?>
                                                                    <span><?php echo htmlspecialchars($visit['phone']); ?></span>
                                                                </h2>
                                                            </td>
                                                            <td><?php echo date('M d, Y', strtotime($visit['visit_date'])); ?></td>
                                                            <td><?php echo date('h:i A', strtotime($visit['visit_time'])); ?></td>
                                                            <td>
                                                                                                                          <span class="status-badge status-<?php echo $visit['status']; ?>">
                                                                    <?php echo ucfirst($visit['status']); ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="actions">
                                                                    <a href="#" class="btn btn-sm bg-success-light mr-2 update-status" data-toggle="modal" data-target="#update-status-modal" data-id="<?php echo $visit['id']; ?>" data-status="<?php echo $visit['status']; ?>" data-notes="<?php echo htmlspecialchars($visit['admin_notes']); ?>">
                                                                        <i class="fe fe-edit"></i> Update Status
                                                                    </a>
                                                                    <a href="visit_details.php?id=<?php echo $visit['id']; ?>" class="btn btn-sm bg-info-light">
                                                                        <i class="fe fe-eye"></i> View
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr class="visit-details-row" id="details-<?php echo $visit['id']; ?>">
                                                            <td colspan="7">
                                                                <div class="visit-details-content">
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <h5>Visit Details</h5>
                                                                            <p><strong>Requested on:</strong> <?php echo date('M d, Y h:i A', strtotime($visit['request_date'])); ?></p>
                                                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($visit['email']); ?></p>
                                                                            <p><strong>Message:</strong> <?php echo htmlspecialchars($visit['message']); ?></p>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <h5>Admin Notes</h5>
                                                                            <p><?php echo !empty($visit['admin_notes']) ? htmlspecialchars($visit['admin_notes']) : 'No notes added yet.'; ?></p>
                                                                            
                                                                            <?php if($visit['status'] == 'pending'): ?>
                                                                                <div class="mt-3">
                                                                                    <button type="button" class="btn btn-success btn-sm quick-status" data-id="<?php echo $visit['id']; ?>" data-status="confirmed">
                                                                                        <i class="fe fe-check"></i> Confirm Visit
                                                                                    </button>
                                                                                    <button type="button" class="btn btn-danger btn-sm quick-status" data-id="<?php echo $visit['id']; ?>" data-status="cancelled">
                                                                                        <i class="fe fe-x"></i> Cancel Visit
                                                                                    </button>
                                                                                </div>
                                                                            <?php elseif($visit['status'] == 'confirmed'): ?>
                                                                                <div class="mt-3">
                                                                                    <button type="button" class="btn btn-info btn-sm quick-status" data-id="<?php echo $visit['id']; ?>" data-status="completed">
                                                                                        <i class="fe fe-check-circle"></i> Mark as Completed
                                                                                    </button>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center">No visit requests found</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <!-- Pagination -->
                                    <?php if($total_pages > 1): ?>
                                        <div class="row mt-4">
                                            <div class="col-md-12">
                                                <div class="pagination justify-content-center">
                                                    <nav aria-label="Page navigation">
                                                        <ul class="pagination">
                                                            <?php if($page > 1): ?>
                                                                <li class="page-item">
                                                                    <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.$search : ''; ?><?php echo !empty($status_filter) ? '&status='.$status_filter : ''; ?><?php echo !empty($date_filter) ? '&date='.$date_filter : ''; ?>" aria-label="Previous">
                                                                        <span aria-hidden="true">&laquo;</span>
                                                                    </a>
                                                                </li>
                                                            <?php endif; ?>
                                                            
                                                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.$search : ''; ?><?php echo !empty($status_filter) ? '&status='.$status_filter : ''; ?><?php echo !empty($date_filter) ? '&date='.$date_filter : ''; ?>">
                                                                        <?php echo $i; ?>
                                                                    </a>
                                                                </li>
                                                            <?php endfor; ?>
                                                            
                                                            <?php if($page < $total_pages): ?>
                                                                <li class="page-item">
                                                                    <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.$search : ''; ?><?php echo !empty($status_filter) ? '&status='.$status_filter : ''; ?><?php echo !empty($date_filter) ? '&date='.$date_filter : ''; ?>" aria-label="Next">
                                                                        <span aria-hidden="true">&raquo;</span>
                                                                    </a>
                                                                </li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </nav>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Calendar View -->
                                <div id="calendar-view" class="calendar-view">
                                    <div id="visits-calendar"></div>
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
    
    <!-- Update Status Modal -->
    <div class="modal fade" id="update-status-modal" tabindex="-1" role="dialog" aria-labelledby="update-status-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="update-status-modal-title">Update Visit Status</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <input type="hidden" name="visit_id" id="modal-visit-id">
                        
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="status" id="modal-status" required>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Admin Notes</label>
                            <textarea class="form-control" name="admin_notes" id="modal-admin-notes" rows="4" placeholder="Add notes about this visit..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="update_visit_status" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="assets/js/jquery-3.2.1.min.js"></script>
    
    <!-- Bootstrap Core JS -->
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    
    <!-- Slimscroll JS -->
    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    
    <!-- Datatables JS -->
    <script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="assets/plugins/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="assets/plugins/datatables/dataTables.responsive.min.js"></script>
    <script src="assets/plugins/datatables/responsive.bootstrap4.min.js"></script>
    
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
    
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize date picker
            $(".date-picker").flatpickr({
                dateFormat: "Y-m-d",
                allowInput: true
            });
            
            // Toggle visit details
            $(".toggle-details").click(function() {
                var visitId = $(this).data("visit-id");
                $("#details-" + visitId).toggle();
            });
            
            // Update status modal
            $(".update-status").click(function() {
                var visitId = $(this).data("id");
                var status = $(this).data("status");
                var notes = $(this).data("notes");
                
                $("#modal-visit-id").val(visitId);
                $("#modal-status").val(status);
                $("#modal-admin-notes").val(notes);
            });
            
            // Quick status update
            $(".quick-status").click(function() {
                var visitId = $(this).data("id");
                var status = $(this).data("status");
                
                $("#modal-visit-id").val(visitId);
                $("#modal-status").val(status);
                
                // Add default notes based on status
                var notes = "";
                if(status === "confirmed") {
                    notes = "Visit confirmed by admin. An agent will be available at the scheduled time.";
                } else if(status === "cancelled") {
                    notes = "Visit cancelled by admin.";
                } else if(status === "completed") {
                    notes = "Visit completed successfully.";
                }
                
                $("#modal-admin-notes").val(notes);
                $("#update-status-modal").modal("show");
            });
            
            // View toggle
            $("#view-list-btn").click(function() {
                $(this).addClass("active").removeClass("btn-light").addClass("btn-primary");
                $("#view-calendar-btn").removeClass("active").removeClass("btn-primary").addClass("btn-light");
                $("#list-view").show();
                $("#calendar-view").hide();
            });
            
            $("#view-calendar-btn").click(function() {
                $(this).addClass("active").removeClass("btn-light").addClass("btn-primary");
                $("#view-list-btn").removeClass("active").removeClass("btn-primary").addClass("btn-light");
                $("#list-view").hide();
                $("#calendar-view").show();
                
                // Initialize calendar if not already done
                if(!window.calendar) {
                    initializeCalendar();
                }
            });
            
            // Initialize calendar
            function initializeCalendar() {
                var calendarEl = document.getElementById('visits-calendar');
                
                window.calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    events: 'get_visits_json.php',
                    eventClick: function(info) {
                        window.location.href = 'visit_details.php?id=' + info.event.id;
                    },
                    eventClassNames: function(arg) {
                        return ['fc-event-' + arg.event.extendedProps.status];
                    }
                });
                
                window.calendar.render();
                
                // Add custom styles for event status colors
                $("<style>")
                    .prop("type", "text/css")
                    .html(`
                        .fc-event-pending { background-color: #ffb100; border-color: #ffb100; }
                        .fc-event-confirmed { background-color: #28a745; border-color: #28a745; }
                        .fc-event-completed { background-color: #17a2b8; border-color: #17a2b8; }
                        .fc-event-cancelled { background-color: #dc3545; border-color: #dc3545; }
                    `)
                    .appendTo("head");
            }
        });
    </script>
</body>
</html>
