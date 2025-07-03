<?php
session_start();
include("config.php");

if (!isset($_SESSION['uid'])) {
    header("location:login1.php");
    exit();
}

$user_id = $_SESSION['uid'];
$msg = "";
$error = "";

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = mysqli_real_escape_string($con, $_POST['current_password']);
    $new_password = mysqli_real_escape_string($con, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($con, $_POST['confirm_password']);
    
    // Validate input
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All fields are required";
    } elseif ($new_password !== $confirm_password) {
        $error = "New password and confirm password do not match";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        // Verify current password using SHA1 (to match login1.php)
        $current_password_hashed = sha1($current_password);
        
        $query = "SELECT upass FROM user WHERE uid = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        
        if ($current_password_hashed !== $user['upass']) {
            $error = "Current password is incorrect";
        } else {
            // Hash the new password using SHA1 (to match login1.php)
            $new_password_hashed = sha1($new_password);
            
            // Update password
            $update_query = "UPDATE user SET upass = ? WHERE uid = ?";
            $stmt = mysqli_prepare($con, $update_query);
            mysqli_stmt_bind_param($stmt, 'si', $new_password_hashed, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $msg = "Password changed successfully";
            } else {
                $error = "Failed to change password: " . mysqli_error($con);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Remsko Real Estate</title>
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
</head>
<body>
    <?php include("include/header.php"); ?>
    
    <!-- Banner -->
    <div class="banner-full-row page-banner" style="background-image:url('images/breadcromb.jpg');">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>Change Password</b></h2>
                </div>
                <div class="col-md-6">
                    <nav aria-label="breadcrumb" class="float-left float-md-right">
                        <ol class="breadcrumb bg-transparent m-0 p-0">
                            <li class="breadcrumb-item text-white"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item text-white"><a href="user_dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Change Password</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content -->
    <div class="full-row">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <div class="mb-4">
                        <a href="user_dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                        </a>
                    </div>
                    <?php include("include/user_sidebar.php"); ?>
                </div>
                
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Change Password</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($msg)): ?>
                                <div class="alert alert-success"><?php echo $msg; ?></div>
                            <?php endif; ?>
                            
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            
                            <form method="post" action="">
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <small class="form-text text-muted">Password must be at least 6 characters long</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <button type="submit" name="change_password" class="btn btn-success">Change Password</button>
                            </form>
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
</body>
</html>
