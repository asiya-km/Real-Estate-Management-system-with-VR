<?php
session_start();
include("config.php");

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_request'])) {
    // Validate CSRF token
    $email = $_REQUEST['email'];
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid request";
    } else {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format";
        } else {
            // Check if email exists
            //$stmt = $con->prepare("SELECT uid FROM user WHERE uemail = ?");
            //$stmt->bind_param("s", $email);
            //$stmt->execute();
           // $result = $stmt->get_result();
            $query = "SELECT * FROM user WHERE uemail='$email'";
            $res = mysqli_query($con, $query);
            $num = mysqli_num_rows($res);
            
            if ($num == 1) {
                // Generate reset token
               // $token = bin2hex(random_bytes(12));
                //$expires = date("Y-m-d H:i:s", strtotime("+1 hour"));
                
                // Store token in database
                $row = mysqli_fetch_assoc($res);
                $token = bin2hex(random_bytes(10)); // Generate a unique token
                $update_query = "UPDATE user SET reset_token='$token' WHERE uemail='$email'";
                mysqli_query($con, $update_query);
                
            
            // Display the reset link on the screen (for local testing)
            $reset_link = "http://localhost/remsko/reset.php?token=$token";
            $msg = "<p class='alert alert-success'>Password reset link: <a href='$reset_link'>$reset_link</a></p>";
            } else {
                $error = "If this email exists, a reset link has been sent";
                // Don't reveal if email doesn't exist for security
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Same head content as register.php -->
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
                            <h1>Forgot Password</h1>
                            <?php if($error): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                            <?php endif; ?>
                            <?php if($msg): ?>
                                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                            <?php endif; ?>
                            
                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <div class="form-group">
                                    <input type="email" name="email" class="form-control" placeholder="Your Email*" required>
                                </div>
                                <button class="btn btn-success" name="reset_request" type="submit">Request Reset Link</button>
                            </form>
                            
                            <div class="text-center dont-have">
                                <a href="login.php">Back to Login</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include("include/footer.php"); ?>
</body>
</html>