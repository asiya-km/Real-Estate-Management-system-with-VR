<?php
session_start();
require("config.php");

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

if (!isset($_SESSION['auser'])) {
    header("location:../login1.php");
    exit();
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle user update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Invalid CSRF token";
        header("Location: userlist.php");
        exit();
    }

    // Validate and sanitize input
    $uid = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $utype = filter_input(INPUT_POST, 'utype', FILTER_SANITIZE_STRING);

    if (!$uid || !in_array($utype, ['user', 'agent', 'manager'])) {
        $_SESSION['error'] = "Invalid input parameters";
        header("Location: userlist.php");
        exit();
    }

    // Update user type using prepared statement
    $sql = "UPDATE user SET utype = ? WHERE uid = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "si", $utype, $uid);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['msg'] = "User updated successfully";
    } else {
        $_SESSION['error'] = "Error updating user: " . mysqli_error($con);
    }
    
    mysqli_stmt_close($stmt);
    header("Location: userlist.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
        <title> Homes | Admin</title>
		
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
		
		<!--[if lt IE 9]>
			<script src="assets/js/html5shiv.min.js"></script>
			<script src="assets/js/respond.min.js"></script>
		<![endif]-->
</head>
<body>
<?php include("header.php"); ?>
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        
                        <p>..</p>
                        <h3 class="page-title">User</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">User</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">User List</h4>
                            <?php
                            if (isset($_SESSION['msg'])) {
                                echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['msg']) . '</div>';
                                unset($_SESSION['msg']);
                            }
                            if (isset($_SESSION['error'])) {
                                echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
                                unset($_SESSION['error']);
                            }
                            ?>
                        </div>
                        <div class="card-body">
                            <table id="basic-datatable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Contact</th>
                                        <th>Utype</th>
                                        <th>Image</th>
                                        <th>Edit</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = mysqli_query($con, "SELECT * FROM user WHERE utype='user'");
                                    $cnt = 1;
                                    while ($row = mysqli_fetch_assoc($query)) {
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($cnt) ?></td>
                                        <td><?= htmlspecialchars($row['uname']) ?></td>
                                        <td><?= htmlspecialchars($row['uemail']) ?></td>
                                        <td><?= htmlspecialchars($row['uphone']) ?></td>
                                        <td><?= htmlspecialchars($row['utype']) ?></td>
                                        <td>
                                            <img src="user/<?= htmlspecialchars($row['uimage']) ?>" 
                                                 height="50" width="50" alt="User image">
                                        </td>
                                        <td>
                                            <form method="post" class="status-form">
                                                <input type="hidden" name="csrf_token" 
                                                       value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                <input type="hidden" name="id" 
                                                       value="<?= htmlspecialchars($row['uid']) ?>">
                                                <select name="utype" class="form-control">
                                                    <option value="user" <?= $row['utype'] === 'user' ? 'selected' : '' ?>>User</option>
                                                 
                                                    <option value="manager" <?= $row['utype'] === 'manager' ? 'selected' : '' ?>>Manager</option>
                                                </select>
                                                <button type="submit" name="update_user" class="btn btn-sm btn-primary mt-1">
                                                    Update
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <form method="post" action="userdelete.php" 
                                                  onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                <input type="hidden" name="csrf_token" 
                                                       value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                <input type="hidden" name="id" 
                                                       value="<?= htmlspecialchars($row['uid']) ?>">
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php
                                    $cnt++;
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

    <!-- Scripts -->
    <script src="assets/js/jquery-3.2.1.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="assets/plugins/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>