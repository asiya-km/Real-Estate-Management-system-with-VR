<?php
session_start();
include("config.php");

// Redirect if user is not in password reset flow
//if (!isset($_SESSION['reset_email']) || !isset($_SESSION['uid'])) {
    //header("Location: forgetpassword1.php");
    //exit();
//}
$uid = $_GET['uid'];
// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";
$msg = "";

if (isset($_POST['reset'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "<p class='alert alert-danger'>Security token mismatch. Please try again.</p>";
    } else {
        if (empty($_POST['token'])) {
            $error = "<p class='alert alert-danger'>Please enter the reset token.</p>";
        } else {
            $token = $_POST['token'];
            //$uid = $_SESSION['uid'];
           // $uid = $_GET['uid'];
            // Validate token format (6-digit code example)
           // if (!preg_match('/^\d{6}$/', $token)) {
             //   $error = "<p class='alert alert-danger'>Invalid token format. Please enter a 6-digit code.</p>";
           // } else {
                // Check token expiration (assuming you have reset_token_expires column)
                $stmt = mysqli_prepare($con, "SELECT uid,reset_token FROM user WHERE uid =$uid");
                mysqli_stmt_bind_param($stmt, "i", $uid);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($result) === 1) {
                    $row = mysqli_fetch_assoc($result);
                    
                    // Verify token and check expiration
                   // if (password_verify($token, $row['reset_token'])) {
                      //  if (strtotime($row['reset_expires']) >= time()) {
                            // Token is valid and not expired
                          //  $_SESSION['reset_verified'] = true;
                            header("Location: index.php");
                           // exit();
                      //  } else {
                      //      $error = "<p class='alert alert-danger'>This token has expired. Please request a new one.</p>";
                      //  }
                    } else {
                        $error = "<p class='alert alert-danger'>Invalid reset token.</p>";
                    }
                //} else {
                    // Generic message for security
                   // $msg = "<p class='alert alert-info'>Password reset token could not be verified.</p>";
                }
           // }
        }
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Verification</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .wrapper { padding: 50px 0; }
        .login-right-wrap { 
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .account-subtitle { color: #6c757d; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="login-right-wrap">
                        <h1 class="text-center mb-4">Verify Reset Token</h1>
                        <p class="account-subtitle text-center">Enter the 6-digit verification code sent to your email</p>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if ($msg): ?>
                            <div class="alert alert-info"><?php echo $msg; ?></div>
                        <?php endif; ?>
                        
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            
                            <div class="mb-3">
                                <label for="token" class="form-label">Verification Code</label>
                                <input type="text" name="token" id="token" class="form-control form-control-lg" 
                                       placeholder="123456" >
                                <div class="form-text">Check your email for the 6-digit code</div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary btn-lg" name="reset" type="submit">Verify Code</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <p>Didn't receive code? <a href="forgetpassword1.php">Request again</a></p>
                            <p>Remember your password? <a href="login.php">Login here</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto focus on token input
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('token').focus();
            
          //   Auto advance between digits if you implement multiple inputs
            // document.querySelectorAll('.token-digit').forEach((input, index) => {
              //   input.addEventListener('input', function() {
                //     if (this.value.length === 1) {
                  //       const next = this.nextElementSibling;
                    //     if (next && next.classList.contains('token-digit')) {
                      //       next.focus();
                        // }
            //         }
              //   });
               //});
        });
    </script>
</body>
</html>