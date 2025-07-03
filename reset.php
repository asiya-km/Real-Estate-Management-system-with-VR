<?php
session_start();
include("config.php");

$error = "";
$success = "";

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Check if token is valid and not expired
    $stmt = $con->prepare("SELECT uid FROM user WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows !== 1) {
        $error = "Invalid or expired reset token";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $error = "Passwords don't match";
    } elseif (!validatePassword($password)) {
        $error = "Password must be 8+ chars with uppercase, lowercase, number, and special character";
    } else {
        // Verify token again
        $stmt = $con->prepare("SELECT uid FROM user WHERE reset_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $hashed_password = $password;
            
            // Update password and clear reset token
            $stmt = $con->prepare("UPDATE user SET upass = ?, reset_token = NULL  WHERE uid = ?");
            $stmt->bind_param("si", $hashed_password, $user['uid']);
            
            if ($stmt->execute()) {
                $success = "Password updated successfully. You can now <a href='login.php'>login</a>.";
            } else {
                $error = "Failed to update password";
            }
        } else {
            $error = "Invalid or expired reset token";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Same head content as register.php -->
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
</head>
<body>
    <?php include("include/header.php"); ?>

    <div class="page-wrappers login-body full-row bg-gray">
        <div class="login-wrapper">
            <div class="container">
                <div class="loginbox">
                    <div class="login-right">
                        <div class="login-right-wrap">
                            <h1>Reset Password</h1>
                            <?php if($error): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                            <?php endif; ?>
                            <?php if($success): ?>
                                <div class="alert alert-success"><?= $success ?></div>
                            <?php else: ?>
                                <form method="post">
                                    <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
                                    <div class="form-group">
                                        <input type="password" name="password" class="form-control" placeholder="New Password*" required>
                                    </div>
                                    <div class="form-group">
                                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password*" required>
                                    </div>
                                    <button class="btn btn-success" name="reset_password" type="submit">Reset Password</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include("include/footer.php"); ?>
</body>
</html>