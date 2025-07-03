<?php
session_start();
include("config.php");

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";
$max_attempts = 3;
$lock_time = 300; // 5 minutes in seconds

if(isset($_POST['login'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid request";
    } else {
        $user = filter_input(INPUT_POST, 'user', FILTER_SANITIZE_STRING);
        $pass = filter_input(INPUT_POST, 'pass', FILTER_SANITIZE_STRING);
        
        // Check brute force attempts
        $attempts = $_SESSION['login_attempts'] ?? 0;
        $last_attempt = $_SESSION['last_attempt'] ?? 0;
        
        if(time() - $last_attempt < $lock_time && $attempts >= $max_attempts) {
            $error = "Too many failed attempts. Please try again later.";
        } else {
            if(!empty($user) && !empty($pass)) {
                $stmt = mysqli_prepare($con, "SELECT auser, apass FROM admin WHERE auser = $user");
                mysqli_stmt_bind_param($stmt, "s", $user);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                if(mysqli_num_rows($result) == 1) {
                    $row = mysqli_fetch_assoc($result);
                    
                    // Verify password (assuming apass stores bcrypt hash)
                    if(password_verify($pass, $row['apass'])) {
                        // Successful login
                        session_regenerate_id(true);
                        
                        // Reset security parameters
                        unset($_SESSION['login_attempts']);
                        unset($_SESSION['last_attempt']);
                        
                        $_SESSION['auser'] = $row['auser'];
                        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
                        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
                        
                        header("Location: dashboard.php");
                        exit();
                    } else {
                        $error = "Invalid credentials";
                        $_SESSION['login_attempts'] = ++$attempts;
                        $_SESSION['last_attempt'] = time();
                    }
                } else {
                    $error = "Invalid credentials";
                    $_SESSION['login_attempts'] = ++$attempts;
                    $_SESSION['last_attempt'] = time();
                }
            } else {
                $error = "Please fill all fields";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>RE Admin - Login</title>
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <!--[if lt IE 9]>
        <script src="assets/js/html5shiv.min.js"></script>
        <script src="assets/js/respond.min.js"></script>
    <![endif]-->
</head>
<body>
    <div class="page-wrappers login-body">
        <div class="login-wrapper">
            <div class="container">
                <div class="loginbox">
                    <div class="login-right">
                        <div class="login-right-wrap">
                            <h1>Admin Login Panel</h1>
                            <p class="account-subtitle">Access to our dashboard</p>
                            <?php if($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                            <?php endif; ?>
                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <div class="form-group">
                                    <input class="form-control" name="user" type="text" placeholder="User Name" required>
                                </div>
                                <div class="form-group">
                                    <input class="form-control" type="password" name="pass" placeholder="Password" required>
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-primary btn-block" name="login" type="submit">Login</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery-3.2.1.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>