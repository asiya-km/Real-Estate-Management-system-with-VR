<?php
session_start();
include("config.php");

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";
$msg = "";

if (isset($_POST['reset'])) {
    // Validate CSRF token
    //if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
       // $error = "<p class='alert alert-danger'>Invalid request</p>";
   // } else {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "<p class='alert alert-warning'>Invalid email format</p>";
        } else {
            // Check if email exists
           // $stmt = mysqli_prepare($con, "SELECT uid FROM user WHERE uemail = ?");
           // mysqli_stmt_bind_param($stmt, "s", $email);
           // mysqli_stmt_execute($stmt);
           // mysqli_stmt_store_result($stmt);
            
            $query = "SELECT * FROM user WHERE uemail='$email'";
            $res = mysqli_query($con, $query);
            $num = mysqli_num_rows($res);
            if ($num === 1) {
                // Generate reset token
                //$token = bin2hex(random_bytes(32));
                //$expires = date("Y-m-d H:i:s", strtotime("+1 hour"));
                
                // Store token in database
                //$update_stmt = mysqli_prepare($con, "UPDATE user SET reset_token = ? WHERE uemail = ?");
                //mysqli_stmt_bind_param($update_stmt, "sss", $token, $email);
                //mysqli_stmt_execute($update_stmt);

                $token = bin2hex(random_bytes(10)); // Generate a unique token
                $update_query = "UPDATE user SET reset_token='$token' WHERE uemail='$email'";
                //$row = mysqli_fetch_assoc($res);
                 // $update_query = "UPDATE user SET reset_token='$token' WHERE uemail='$email'";
                $checked=mysqli_query($con, $update_query);
                $_SESSION['uid'] = $row['uid'];
                if($checked){
                // For local development - show the link directly
                header("location: resetpassword1.php");
                $reset_link = "resetpa.php?token=$token";
                $msg = "<div class='alert alert-info'>";
                $msg .= "<p>For local development, here's your password reset link:</p>";
                $msg .= "<p><a href='$reset_link'>$reset_link</a></p>";
                $msg .= "<p>This link will expire in 1 hour.</p>";
                $msg .= "</div>";
                }
            } else {
                // Don't reveal if email doesn't exist
                $msg = "<p class='alert alert-info'>If this email exists in our system, a reset link would be provided.</p>";
            }
           // mysqli_stmt_close($res);
        }
   // }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Same head content as before -->
</head>
<body>
    <!-- Same header and wrapper structure as before -->
    
    <div class="login-right-wrap">
        <h1>Forgot Password</h1>
        <p class="account-subtitle">Enter your email to get a reset link</p>
        <?php echo $error; ?><?php echo $msg; ?>
        
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="form-group">
                <input type="email" name="email" class="form-control" placeholder="Your Email*" required>
            </div>
            <button class="btn btn-success" name="reset" type="submit">Get Reset Link</button>
        </form>
        
        <div class="text-center dont-have">Remember your password? <a href="login.php">Login</a></div>
    </div>
    
    <!-- Same footer as before -->
</body>
</html>