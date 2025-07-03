<?php
session_start();
require("config.php");

// Check admin authentication
if(!isset($_SESSION['auser'])) {
    header("location:../login1.php");
    exit();
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Process status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Invalid CSRF token";
        header("Location: admin_bookings.php");
        exit();
    }

    $booking_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $status = $_POST['status'] ?? '';
    
    // Validate input
    if (!$booking_id) {
        $_SESSION['error'] = "Invalid booking ID";
        header("Location: update_booking.php");
        exit();
    }

    $allowed_statuses = ['pending', 'confirmed', 'canceled'];
    if(!in_array($status, $allowed_statuses)) {
        $_SESSION['error'] = "Invalid status value";
        header("Location: update_booking.php");
        exit();
    }

    // Verify booking exists
    $stmt = mysqli_prepare($con, "SELECT id FROM bookings WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $booking_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if(mysqli_stmt_num_rows($stmt) === 0) {
        $_SESSION['error'] = "Booking not found";
        header("Location: update_booking.php");
        exit();
    }
    mysqli_stmt_close($stmt);

    // Update status
    $stmt = mysqli_prepare($con, "UPDATE bookings SET status=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "si", $status, $booking_id);
    
    if(mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Booking status updated successfully!";
    } else {
        error_log("Booking update error: " . mysqli_error($con));
        $_SESSION['error'] = "Error updating booking";
    }
    mysqli_stmt_close($stmt);
    
    header("Location: update_booking.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Remsko - Manage Bookings</title>
		
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
    <link rel="stylesheet" href="assets/plugins/datatables/select.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/plugins/datatables/buttons.bootstrap4.min.css">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include("header.php"); ?>
    <!-- Main Wrapper -->
  <div>
    <div class="main-wrapper">
        <!-- Header -->
        
        
        <!-- Page Wrapper -->
        <div class="page-wrapper">
            <div class="content container-fluid">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="row">
                        
                        <div class="col">
                            
                            <p>..</p>
                            <h3 class="page-title">Booking Management</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Manage Bookings</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /Page Header -->
                
                <!-- Messages -->
                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= htmlspecialchars($_SESSION['success']); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= htmlspecialchars($_SESSION['error']); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">All Bookings</h4>
                                
                                <div class="table-responsive">
                                    <table id="booking-table" class="table table-striped table-bordered dt-responsive nowrap">
                                        <thead>
                                            <tr>
                                                <th>Booking ID</th>
                                                <th>Property</th>
                                                <th>User</th>
                                                <th>Booking Date</th>
                                                <th>Status</th>
                                                <th>Amount</th>
                                                <th>Actions</th>
                                                <th>  </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query = "SELECT b.*, p.title, u.uname 
                                                    FROM bookings b
                                                    JOIN property p ON b.property_id = p.pid
                                                    JOIN user u ON b.user_id = u.uid
                                                    ORDER BY b.booking_date DESC";
                                            $result = mysqli_query($con, $query);
                                            
                                            if (!$result) {
                                                echo '<tr><td colspan="7">Error: ' . mysqli_error($con) . '</td></tr>';
                                            } else if (mysqli_num_rows($result) == 0) {
                                                echo '<tr><td colspan="7" class="text-center">No bookings found</td></tr>';
                                            } else {
                                                while($row = mysqli_fetch_assoc($result)):
                                                    // Determine status badge color
                                                    $statusClass = '';
                                                    switch($row['status']) {
                                                        case 'confirmed':
                                                            $statusClass = 'success';
                                                            break;
                                                        case 'pending':
                                                            $statusClass = 'warning';
                                                            break;
                                                        case 'canceled':
                                                            $statusClass = 'danger';
                                                            break;
                                                        default:
                                                            $statusClass = 'secondary';
                                                    }
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['id']) ?></td>
                                                <td><?= htmlspecialchars($row['title']) ?></td>
                                                <td><?= htmlspecialchars($row['uname']) ?></td>
                                                <td><?= date('M d, Y', strtotime($row['booking_date'])) ?></td>
                                                <td>
                                                    <form method="post" class="status-form">
                                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                        <div class="input-group">
                                                            <select name="status" class="form-control form-control-sm">
                                                                <option value="pending" <?= $row['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                                <option value="confirmed" <?= $row['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                                <option value="canceled" <?= $row['status'] == 'canceled' ? 'selected' : '' ?>>Canceled</option>
                                                            </select>
                                                            <div class="input-group-append">
                                                                <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                                                            </div>
                                                        </div>
                                                        <span class="badge badge-<?= $statusClass ?> mt-2">
                                                            <?= ucfirst(htmlspecialchars($row['status'])) ?>
                                                        </span>
                                                    </form>
                                                </td>
                                                <td><?= isset($row['amount']) ? number_format($row['amount'], 2) : 'N/A' ?></td>
                                                <td>
                                                    <form method="post" action="booking_delete.php" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                            onclick="return confirm('Are you sure you want to delete this booking?')">
                                                            <i class="fa fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php 
                                                endwhile; 
                                            }
                                            ?>
                                        </tbody>
                                    </table>
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

    <!-- JavaScript files -->
    <script src="assets/js/jquery-3.2.1.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    
    <!-- Datatables JS -->
    <script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="assets/plugins/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="assets/plugins/datatables/dataTables.responsive.min.js"></script>
    <script src="assets/plugins/datatables/responsive.bootstrap4.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            if (!$.fn.DataTable.isDataTable('#booking-table')) {
                $('#booking-table').DataTable({
                    responsive: true,
                    order: [[3, 'desc']], // Sort by booking date
                    columnDefs: [
                        { orderable: false, targets: [4, 6] } // Disable sorting for status and actions columns
                    ]
                });
            }
            
            // Auto-close alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
</body>
</html>
