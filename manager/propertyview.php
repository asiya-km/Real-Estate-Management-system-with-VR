<?php
session_start();
require("config.php");

// Enhanced authentication check
if (!isset($_SESSION['auser'])) {
    header("Location: ../login1.php");
    exit(); // Always exit after header redirect
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Remsko - Property Management</title>
    
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
    <!-- Main Wrapper -->
    
        <?php include("header.php"); ?>
        
        <!-- Page Wrapper -->
        <div class="page-wrapper">
            <div class="content container-fluid">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="row">
                        <div class="col">
                            
                            <h3 class="page-title">Property Management</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Properties</li>
                            </ul>
                        </div>
                        <div class="col-auto">
                        
                            <a href="propertyadd.php" class="btn btn-primary">
                                <i class="fa fa-plus"></i> Add New Property
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /Page Header -->

                <!-- Property Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Property Listings</h4>
                                
                                <?php 
                                // Session-based messages
                                if (isset($_SESSION['message'])) {
                                    echo $_SESSION['message'];
                                    unset($_SESSION['message']);
                                }
                                ?>

                                <div class="table-responsive">
                                    <!-- Add class "custom-datatable" instead of using the ID that might be initialized elsewhere -->
                                    <table class="table table-striped table-bordered dt-responsive nowrap custom-datatable">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Type</th>
                                                <th>S/R</th>
                                                <th>Price</th>
                                                <th>Location</th>
                                                <th>City</th>
                                                <th>Status</th>
                                                <th>Added Date</th>
                                                <th>Actions</th>
                                                <th>   </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Secure database query with error handling
                                            $query = "SELECT * FROM property ORDER BY date DESC";
                                            $result = mysqli_query($con, $query);
                                            
                                            if (!$result) {
                                                echo '<div class="alert alert-danger">Database error: ' . mysqli_error($con) . '</div>';
                                            } else {
                                                if (mysqli_num_rows($result) == 0) {
                                                    echo '<tr><td colspan="9" class="text-center">No properties found</td></tr>';
                                                } else {
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                                <td><?php echo htmlspecialchars(ucfirst($row['type'])); ?></td>
                                                <td><?php echo htmlspecialchars(ucfirst($row['stype'])); ?></td>
                                                <td><?php echo number_format($row['price']); ?></td>
                                                <td><?php echo htmlspecialchars($row['location']); ?></td>
                                                <td><?php echo htmlspecialchars($row['city']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $row['status'] === 'available' ? 'success' : 'danger'; ?>">
                                                        <?php echo ucfirst(htmlspecialchars($row['status'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                                <td>
                                                    <div class="actions">
                                                        <a href="propertyedit.php?id=<?php echo (int)$row['pid']; ?>" class="btn btn-sm btn-info" title="Edit">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                        <!--<a href="propertydetail1.php?id=<?php echo (int)$row['pid']; ?>" class="btn btn-sm btn-primary" title="View">
                                                            <i class="fa fa-eye"></i>
                                                        </a>-->
                                                        <a href="propertydelete.php?id=<?php echo (int)$row['pid']; ?>" 
                                                        class="btn btn-sm btn-danger" title="Delete"
                                                        onclick="return confirm('Are you sure you want to delete this property?')">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php 
                                                    }
                                                }
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
    
    <script src="assets/plugins/datatables/dataTables.select.min.js"></script>
    <script src="assets/plugins/datatables/dataTables.buttons.min.js"></script>
    <script src="assets/plugins/datatables/buttons.bootstrap4.min.js"></script>
    <script src="assets/plugins/datatables/buttons.html5.min.js"></script>
    <script src="assets/plugins/datatables/buttons.flash.min.js"></script>
    <script src="assets/plugins/datatables/buttons.print.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
    
    <script>
        $(document).ready(function() {
            // Check if DataTable is already initialized
            if (!$.fn.DataTable.isDataTable('.custom-datatable')) {
                $('.custom-datatable').DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print'
                    ],
                    responsive: true,
                    order: [[7, 'desc']] // Sort by date column (index 7) in descending order
                });
            }
        });
    </script>
</body>
</html>
