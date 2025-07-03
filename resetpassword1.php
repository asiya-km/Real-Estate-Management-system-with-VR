<?php
session_start();
include("config.php");
$error = "";
$msg = "";

// Check if token and email are provided
if(isset($_GET['token']) && isset($_GET['email'])) {
    $token = $_GET['token'];
    $email = $_GET['email'];
    
    // Verify token
    $sql = "SELECT * FROM user WHERE uemail = ? AND reset_token = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $email, $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($result) == 0) {
        $error = "Invalid or expired reset link";
    }
} else {
    $error = "Invalid request";
}

// Handle form submission
if(isset($_POST['update'])) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $token = $_GET['token'];
    $email = $_GET['email'];
    
    // Validate passwords
    if($password != $confirm_password) {
        $error = "Passwords do not match";
    } elseif(strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } else {
        // Hash password using secure method
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password and clear token
        $update_sql = "UPDATE user SET upass = ?, reset_token = NULL WHERE uemail = ? AND reset_token = ?";
        $update_stmt = mysqli_prepare($con, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "sss", $hashed_password, $email, $token);
        
        if(mysqli_stmt_execute($update_stmt)) {
            $msg = "Password updated successfully";
        } else {
            $error = "Failed to update password";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            margin-bottom: 10px;
        }
        .back-link {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        
        <?php if(!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($msg)): ?>
            <div class="success">
                <?php echo $msg; ?>
                <p>You can now <a href="login1.php">login</a> with your new password.</p>
            </div>
        <?php elseif(empty($error)): ?>
            <form method="post">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" name="update">Update Password</button>
            </form>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="login1.php">Back to Login</a>
        </div>
    </div>
</body>
</html>
