<?php
include("config.php");
$error = "";
$msg = "";

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $query = "SELECT * FROM user WHERE reset_token='$token'";
    $res = mysqli_query($con, $query);
    $num = mysqli_num_rows($res);

    if ($num == 1) {
        if (isset($_POST['reset'])) {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if ($new_password == $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_query = "UPDATE user SET upass='$hashed_password', reset_token=NULL WHERE reset_token='$token'";
                mysqli_query($con, $update_query);

                $msg = "<p class='alert alert-success'>Password reset successfully. <a href='login.php'>Login</a></p>";
            } else {
                $error = "<p class='alert alert-warning'>Passwords do not match.</p>";
            }
        }
    } else {
        $error = "<p class='alert alert-warning'>Invalid or expired token.</p>";
    }
} else {
    $error = "<p class='alert alert-warning'>No token provided.</p>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Meta Tags -->
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="shortcut icon" href="images/favicon.ico">

<!--	Fonts
	========================================================-->
<link href="https://fonts.googleapis.com/css?family=Muli:400,400i,500,600,700&amp;display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Comfortaa:400,700" rel="stylesheet">

<!--	Css Link
	========================================================-->
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
    <title>Reset Password</title>
    <!-- Include your CSS files here -->
</head>
<body>
<div id="page-wrapper">
    <div class="row">
        <!-- Header start -->
        <?php include("include/header.php"); ?>
        <!-- Header end -->

        <div class="page-wrappers login-body full-row bg-gray">
            <div class="login-wrapper">
                <div class="container">
                    <div class="loginbox">
                        <div class="login-right">
                            <div class="login-right-wrap">
                                <h1>Reset Password</h1>
                                <p class="account-subtitle">Enter your new password</p>
                                <?php echo $error; ?><?php echo $msg; ?>
                                <!-- Form -->
                                <form method="post">
                                    <div class="form-group">
                                        <input type="password" name="new_password" class="form-control" placeholder="New Password*">
                                    </div>
                                    <div class="form-group">
                                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password*">
                                    </div>
                                    <button class="btn btn-success" name="reset" value="Reset Password" type="submit">Reset Password</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer start -->
        <?php include("include/footer.php"); ?>
        <!-- Footer end -->
    </div>
</div>
<!-- Include your JS files here -->
</body>
</html>