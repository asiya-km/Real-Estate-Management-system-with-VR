<?php
session_start();
include("config.php");
$error = "";
$msg = "";

// Check if form is submitted
if(isset($_POST['reset'])) {
    $email = trim($_POST['email']);
    $security_answer = trim($_POST['security_answer']);
    
    // Basic validation
    if(empty($email)) {
        $error = "Please enter your email address";
    } elseif(empty($security_answer)) {
        $error = "Please enter your security answer";
    } else {
        // Check if email exists and verify security answer
        $sql = "SELECT * FROM user WHERE uemail = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if($row = mysqli_fetch_assoc($result)) {
            // Verify security answer (case-insensitive)
            if(strtolower(trim($security_answer)) === strtolower(trim($row['security_answer']))) {
                // Generate a simple token
                $token = md5(time() . $email);
                
                // Update user with token
                $update_sql = "UPDATE user SET reset_token = ? WHERE uemail = ?";
                $update_stmt = mysqli_prepare($con, $update_sql);
                mysqli_stmt_bind_param($update_stmt, "ss", $token, $email);
                
                if(mysqli_stmt_execute($update_stmt)) {
                    // Create reset link
                    $reset_link = "http://localhost/remsko/resetpassword1.php?token=$token&email=$email";
                    
                    // Show success message with link
                    $msg = "Password reset link: <a href='$reset_link'>$reset_link</a>";
                } else {
                    $error = "Database error. Please try again.";
                }
            } else {
                $error = "Incorrect answer to the security question";
            }
        } else {
            $error = "Email not found in our records";
        }
    }
}

// Get the security question for the provided email
$security_question = "";
if(isset($_POST['get_question']) && !empty($_POST['email'])) {
    $email = trim($_POST['email']);
    $query = "SELECT security_question FROM user WHERE uemail = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if($row = mysqli_fetch_assoc($result)) {
        $security_question = $row['security_question'];
    } else {
        $error = "Email not found in our records";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
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
        input[type="email"], input[type="text"] {
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
        <h2>Forgot Password</h2>
        
        <?php if(!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($msg)): ?>
            <div class="success"><?php echo $msg; ?></div>
        <?php else: ?>
            <form method="post">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                </div>
                
                <?php if(empty($security_question)): ?>
                    <button type="submit" name="get_question">Get Security Question</button>
                <?php else: ?>
                    <div class="form-group">
                        <label>Security Question: <?= htmlspecialchars($security_question) ?></label>
                        <!-- Remove any value attribute or pre-filling here -->
                        <input type="text" name="security_answer" placeholder="Your answer" required>
                    </div>
                    <button type="submit" name="reset">Reset Password</button>
                <?php endif; ?>
            </form>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="login1.php">Back to Login</a>
        </div>
    </div>
</body>
</html>
