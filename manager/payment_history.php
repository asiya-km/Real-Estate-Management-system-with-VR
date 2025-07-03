<?php
session_start();
require("../config.php");

// Check if admin is logged in
if (!isset($_SESSION['auser'])) {
    header("location:../login1.php");
    exit();
}

// Pagination variables
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = " AND (u.uname LIKE '%".mysqli_real_escape_string($con, $search)."%' OR 
                              p.title LIKE '%".mysqli_real_escape_string($con, $search)."%' OR 
                              b.payment_transaction_id LIKE '%".mysqli_real_escape_string($con, $search)."%')";
}

// Filter by payment status
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$status_condition = '';
if (!empty($status_filter)) {
    $status_condition = " AND b.payment_status = '".mysqli_real_escape_string($con, $status_filter)."'";
}

// Get total records for pagination
$total_query = "SELECT COUNT(*) as total FROM bookings b 
                JOIN user u ON b.user_id = u.uid 
                JOIN property p ON b.property_id = p.pid 
                WHERE b.payment_status IS NOT NULL".$search_condition.$status_condition;
$total_result = mysqli_query($con, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Get payment records
$query = "SELECT b.*, u.uname, u.uemail, p.title as property_title, p.price as property_price 
          FROM bookings b 
          JOIN user u ON b.user_id = u.uid 
          JOIN property p ON b.property_id = p.pid 
          WHERE b.payment_status IS NOT NULL".$search_condition.$status_condition." 
          ORDER BY b.payment_date DESC 
          LIMIT $offset, $limit";
$result = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>LM HOMES | Payment History</title>
    
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
                            <p>.</p>
                            <h3 class="page-title">Payment History</h3>
                        </div>
                    </div>
                </div>
                <!-- /Page Header -->
                
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-md-6">
                                        <form method="GET" class="form-inline">
                                            <div class="form-group mr-2">
                                                <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                                            </div>
                                            <div class="form-group mr-2">
                                                <select name="status" class="form-control">
                                                    <option value="">All Statuses</option>
                                                    <option value="completed" <?php if($status_filter == 'completed') echo 'selected'; ?>>Completed</option>
                                                    <option value="pending" <?php if($status_filter == 'pending') echo 'selected'; ?>>Pending</option>
                                                    <option value="failed" <?php if($status_filter == 'failed') echo 'selected'; ?>>Failed</option>
                                                </select>
                                            </div>
                                            <button type="submit" class="btn btn-primary mr-2">Filter</button>
                                            <a href="payment_history.php" class="btn btn-secondary">Reset</a>
                                        </form>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <a href="export_payments.php" class="btn btn-success">Export to Excel</a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Customer</th>
                                                <th>Property</th>
                                                <th>Amount</th>
                                                <th>Payment Method</th>
                                                <th>Transaction ID</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if (mysqli_num_rows($result) > 0) {
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    $status_class = '';
                                                    switch ($row['payment_status']) {
                                                        case 'completed':
                                                            $status_class = 'success';
                                                            break;
                                                        case 'pending':
                                                            $status_class = 'warning';
                                                            break;
                                                        case 'failed':
                                                            $status_class = 'danger';
                                                            break;
                                                        default:
                                                            $status_class = 'secondary';
                                                    }
                                            ?>
                                            <tr>
                                                <td><?php echo $row['id']; ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($row['uname']); ?><br>
                                                    <small><?php echo htmlspecialchars($row['uemail']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['property_title']); ?></td>
                                                <td>ETB <?php echo number_format(isset($row['payment_amount']) ? $row['payment_amount'] : 50, 2); ?></td>
                                                <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                                                <td><?php echo htmlspecialchars($row['payment_transaction_id']); ?></td>
                                                <td><?php echo date('M d, Y H:i', strtotime($row['payment_date'])); ?></td>
                                                <td><span class="badge badge-<?php echo $status_class; ?>"><?php echo ucfirst($row['payment_status']); ?></span></td>
                                                <td>
                                                    <a href="view_payment.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">View</a>
                                                    <?php if ($row['payment_status'] != 'completed'): ?>
                                                    <a href="update_payment.php?id=<?php echo $row['id']; ?>&status=completed" class="btn btn-success btn-sm" onclick="return confirm('Mark this payment as completed?')">Mark Completed</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php
                                                }
                                            } else {
                                            ?>
                                            <tr>
                                                <td colspan="9" class="text-center">No payment records found</td>
                                            </tr>
                                            <?php
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination -->
                                <?php if ($total_pages > 1): ?>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>"><?php echo $i; ?></a>
                                        </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                                <?php endif; ?>
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
    <script src="assets/plugins/tinymce/tinymce.min.js"></script>
    <script src="assets/plugins/tinymce/init-tinymce.min.js"></script>
    
    <!-- Bootstrap Core JS -->
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    
    <!-- Slimscroll JS -->
    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
</body>
</html>
