<?php
session_start();
require("config.php");

if(!isset($_SESSION['auser'])) {
    header("location:../login1.php");
    exit(); // Always exit after redirect
}

// Initialize variables
$error_message = null;
$success_message = null;

// Handle password change
if(isset($_POST['update_pwd'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $auser = $_SESSION['auser'];
    
    // Validate input
    if(empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "All password fields are required";
    } else if($new_password !== $confirm_password) {
        $error_message = "New password and confirm password do not match";
    } else {
        // Verify old password
        $sql = "SELECT * FROM admin WHERE auser='$auser'";
        $result = mysqli_query($con, $sql);
        
        if($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);
            $stored_password = $row[3]; // Assuming password is at index 3
            
            if($old_password == $stored_password) { // Simple comparison for now
                // Update password
                $update_sql = "UPDATE admin SET apass='$new_password' WHERE auser='$auser'";
                if(mysqli_query($con, $update_sql)) {
                    $success_message = "Password updated successfully";
                } else {
                    $error_message = "Failed to update password: " . mysqli_error($con);
                }
            } else {
                $error_message = "Current password is incorrect";
            }
        } else {
            $error_message = "Admin user not found";
        }
    }
}

// Fetch admin data
$id = $_SESSION['auser'];
$sql = "SELECT * FROM admin WHERE (aemail='$id' OR auser='$id')";
$result = mysqli_query($con, $sql);

if (!$result) {
    $error_message = "Error retrieving profile data: " . mysqli_error($con);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Remsko - Admin Profile</title>
    
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
</head>
<body>
    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Header -->
        <?php include("header.php"); ?>
        <!-- /Header -->
        
        <!-- Page Wrapper -->
        <div class="page-wrapper">
            <div class="content container-fluid">
                
                <!-- Page Header -->
                <div class="page-header">
                    <div class="row">
                        <div class="col">
							<p>.</p>
                            <h3 class="page-title">Profile</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Profile</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /Page Header -->
                
                <!-- Messages -->
                <?php if($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $error_message; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $success_message; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <?php
                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_array($result)) {
                    ?>
                    <div class="col-md-12">
                        <div class="profile-header">
                            <div class="row align-items-center">
                                <div class="col-auto profile-image">
                                    <a href="#">
                                        <img class="rounded-circle" alt="User Image" src="assets/img/profiles/avatar-01.png">
                                    </a>
                                </div>
                                <div class="col ml-md-n2 profile-user-info">
                                    <h4 class="user-name mb-2 text-uppercase"><?php echo htmlspecialchars($row['1']); ?></h4>
                                    <h6 class="text-muted"><?php echo htmlspecialchars($row['2']); ?></h6>
                                    <div class="user-Location"><i class="fa fa-id-badge" aria-hidden="true"></i>
                                        <?php echo htmlspecialchars($row['4']); ?></div>
                                    <div class="about-text"></div>
                                </div>
                            </div>
                        </div>
                        <div class="profile-menu">
                            <ul class="nav nav-tabs nav-tabs-solid">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#per_details_tab">About</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#password_tab">Password</a>
                                </li>
                            </ul>
                        </div>  
                        <div class="tab-content profile-tab-cont">
                            
                            <!-- Personal Details Tab -->
                            <div class="tab-pane fade show active" id="per_details_tab">
                            
                                <!-- Personal Details -->
                                <div class="row">
                                    <div class="col-lg-9">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title d-flex justify-content-between">
                                                    <span>Personal Details</span>
                                                </h5>
                                                <div class="row">
                                                    <p class="col-sm-3 text-muted text-sm-right mb-0 mb-sm-3">Name</p>
                                                    <p class="col-sm-9"><?php echo htmlspecialchars($row['1']); ?></p>
                                                </div>
                                                <div class="row">
                                                    <p class="col-sm-3 text-muted text-sm-right mb-0 mb-sm-3">Date of Birth</p>
                                                    <p class="col-sm-9"><?php echo htmlspecialchars($row['4']); ?></p>
                                                </div>
                                                <div class="row">
                                                    <p class="col-sm-3 text-muted text-sm-right mb-0 mb-sm-3">Email ID</p>
                                                    <p class="col-sm-9"><a href="mailto:<?php echo htmlspecialchars($row['2']); ?>"><?php echo htmlspecialchars($row['2']); ?></a></p>
                                                </div>
                                                <div class="row">
                                                    <p class="col-sm-3 text-muted text-sm-right mb-0 mb-sm-3">Mobile</p>
                                                    <p class="col-sm-9"><?php echo htmlspecialchars($row['5']); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <!-- Account Status -->
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title d-flex justify-content-between">
                                                    <span>Account Status</span>
                                                </h5>
                                                <button class="btn btn-success" type="button"><i class="fe fe-check-verified"></i> Active</button>
                                            </div>
                                        </div>
                                        <!-- /Account Status -->
                                    </div>
                                </div>
                                <!-- /Personal Details -->

                            </div>
                            <!-- /Personal Details Tab -->
                            
                            <!-- Change Password Tab -->
                            <div id="password_tab" class="tab-pane fade">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Change Password</h5>
                                        <div class="row">
                                            <div class="col-md-10 col-lg-6">
                                                <form method="post" action="">
                                                    <div class="form-group">
                                                        <label>Current Password</label>
                                                        <input type="password" name="old_password" class="form-control" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>New Password</label>
                                                        <input type="password" name="new_password" class="form-control" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Confirm Password</label>
                                                        <input type="password" name="confirm_password" class="form-control" required>
                                                    </div>
                                                    <button class="btn btn-primary" type="submit" name="update_pwd">Save Changes</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /Change Password Tab -->
                            
                        </div>
                    </div>
                    <?php 
                        }
                    } else {
                        echo '<div class="col-md-12"><div class="alert alert-info">No profile information found.</div></div>';
                    }
                    ?>
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
    
    <script>
        // Auto-close alerts after 5 seconds
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
            
            // Activate the password tab if there was a password update attempt
            <?php if(isset($_POST['update_pwd'])): ?>
            $('.nav-tabs a[href="#password_tab"]').tab('show');
            <?php endif; ?>
        });
    </script>
</body>
</html>
