<?php
session_start();
include("config.php");

if (!isset($_SESSION['auser'])) {
    header("location:../login1.php");
    exit();
}

// Fetch all bookings with complete information
$query = "SELECT b.id, b.property_id, b.user_id, b.booking_date, b.status, b.payment_status, 
          p.title, p.pimage, 
          u.uname, u.uemail, u.uphone 
          FROM bookings b 
          JOIN property p ON b.property_id = p.pid 
          JOIN user u ON b.user_id = u.uid
          ORDER BY b.booking_date DESC";
$result = mysqli_query($con, $query);

// Check for query errors
if (!$result) {
    $error = "Database query failed: " . mysqli_error($con);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings</title>
    
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
    <!-- Include header only once -->
    <?php include("header.php"); ?>
    
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">Manage Bookings</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Bookings</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">All Bookings</h4>
                            <div class="table-responsive">
                                <table id="bookings-table" class="table table-hover table-center mb-0 datatable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Property</th>
                                            <th>User</th>
                                            <th>Booking Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if(isset($result) && mysqli_num_rows($result) > 0) {
                                            while($row = mysqli_fetch_assoc($result)): 
                                                // Determine status class for badge
                                                $statusClass = '';
                                                switch($row['status']) {
                                                    case 'confirmed': $statusClass = 'success'; break;
                                                    case 'pending': $statusClass = 'warning'; break;
                                                    case 'canceled': case 'cancelled': $statusClass = 'danger'; break;
                                                    case 'completed': $statusClass = 'info'; break;
                                                    default: $statusClass = 'secondary';
                                                }
                                        ?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                                            <td><?php echo htmlspecialchars($row['uname']); ?></td>
                                            <td><?php echo date('d M Y', strtotime($row['booking_date'])); ?></td>
                                            <td>
                                                <span class="badge badge-pill badge-<?php echo $statusClass; ?>">
                                                    <?php echo ucfirst(htmlspecialchars($row['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="update_booking.php?id=<?php echo $row['id']; ?>&status=confirmed" class="btn btn-sm btn-success">Confirm</a>
                                                <a href="update_booking.php?id=<?php echo $row['id']; ?>&status=canceled" class="btn btn-sm btn-danger">Cancel</a>
                                                <a href="view_booking.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">View</a>
                                            </td>
                                        </tr>
                                        <?php 
                                            endwhile; 
                                        } else {
                                            echo '<tr><td colspan="6" class="text-center">No bookings found</td></tr>';
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
    
    <script>
        $(document).ready(function() {
            $('#bookings-table').DataTable({
                responsive: true,
                order: [[3, 'desc']], // Sort by booking date by default
                columnDefs: [
                    { orderable: false, targets: [5] } // Disable sorting for action column
                ]
            });
        });
    </script>
    
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
</body>
</html>
