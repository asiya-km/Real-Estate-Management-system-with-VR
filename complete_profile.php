<?php
session_start();
include("config.php");

// Redirect if not logged in or doesn't need to complete profile
if (!isset($_SESSION['uid']) || !isset($_SESSION['complete_profile'])) {
    header("Location: login1.php");
    exit;
}

$uid = $_SESSION['uid'];
$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phoneInput = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    // Password validation for Google users
    if (isset($_POST['is_google_user']) && $_POST['is_google_user'] == 1) {
        if (empty($password)) {
            $errors['password'] = 'Password is required';
        } elseif (!validatePassword($password)) {
            $errors['password'] = 'Password must contain uppercase, lowercase, number, and special character';
        }
        
        if ($password !== $confirmPassword) {
            $errors['confirmPassword'] = 'Passwords do not match';
        }
    }
    
    // Phone validation and formatting
    if (!preg_match('/^[97][0-9]{8}$/', $phoneInput)) {
        $errors['phone'] = 'Phone number must start with 9 or 7 and be 9 digits long';
    } else {
        $phoneWithCode = '+251' . $phoneInput;
        
        // Check if phone already exists
        $checkPhone = "SELECT uphone FROM user WHERE uphone = ? AND uid != ?";
        $stmt = mysqli_prepare($con, $checkPhone);
        mysqli_stmt_bind_param($stmt, "si", $phoneWithCode, $uid);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if(mysqli_stmt_num_rows($stmt) > 0) {
            $errors['phone'] = 'Phone number already exists. Please use a different phone number.';
        } else {
            // Update the user's phone number and password if Google user
            if (isset($_POST['is_google_user']) && $_POST['is_google_user'] == 1 && !empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateSql = "UPDATE user SET uphone = ?, upass = ? WHERE uid = ?";
                $updateStmt = mysqli_prepare($con, $updateSql);
                mysqli_stmt_bind_param($updateStmt, "ssi", $phoneWithCode, $hashedPassword, $uid);
            } else {
                // Just update phone for regular users
                $updateSql = "UPDATE user SET uphone = ? WHERE uid = ?";
                $updateStmt = mysqli_prepare($con, $updateSql);
                mysqli_stmt_bind_param($updateStmt, "si", $phoneWithCode, $uid);
            }
            
            if (mysqli_stmt_execute($updateStmt)) {
                // Profile completed successfully
                unset($_SESSION['complete_profile']);
                $success = true;
                
                // Redirect to index after 2 seconds
                header("refresh:2;url=index.php");
            } else {
                $errors['general'] = 'Failed to update profile: ' . mysqli_error($con);
            }
        }
    }
}

// Get user info
$sql = "SELECT uname, uemail, upass FROM user WHERE uid = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Check if this is a Google user (no password or default password)
$isGoogleUser = empty($user['upass']) || $user['upass'] == password_hash('google_default_password', PASSWORD_DEFAULT);

// Helper function to validate password
function validatePassword($password) {
    return strlen($password) >= 8 && 
           preg_match('/[A-Z]/', $password) && 
           preg_match('/[a-z]/', $password) && 
           preg_match('/[0-9]/', $password) && 
           preg_match('/[^A-Za-z0-9]/', $password);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Profile - Remsko Real Estate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-image: url('images/slu.jpg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 500px;
            background: #fff;
            border-radius: 30px;
            overflow: hidden;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.4);
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

        p {
            margin-bottom: 20px;
            text-align: center;
            color: #666;
        }

        .user-info {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .user-info p {
            margin: 5px 0;
            text-align: left;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        .phone-input {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .phone-input span {
            padding: 12px;
            background: #f1f1f1;
            border: 1px solid #aaa;
            border-right: none;
            border-radius: 5px 0 0 5px;
        }

        .phone-input input {
            padding: 12px;
            border: 1px solid #aaa;
            border-radius: 0 5px 5px 0;
            flex: 1;
        }

        .password-container {
            position: relative;
            margin-bottom: 20px;
        }

        .password-container input {
            width: 100%;
            padding: 12px;
            border: 1px solid #aaa;
            border-radius: 5px;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }

        button {
            padding: 12px;
            background: #3b82f6;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .error {
            color: #e74c3c;
            font-size: 14px;
            margin-top: -15px;
            margin-bottom: 15px;
        }

        .success {
            color: #2ecc71;
            font-size: 16px;
            text-align: center;
            margin-bottom: 15px;
        }
        
        .gmail-notice {
            background-color: #f8f9fa;
            border-left: 4px solid #3b82f6;
            padding: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Complete Your Profile</h2>
        
        <?php if (strpos($user['uemail'], '@gmail.com') === false): ?>
            <div class="gmail-notice">
                <p>Note: Only Gmail accounts are allowed for Google sign-in. Your current email is not a Gmail account.</p>
            </div>
        <?php endif; ?>
        
        <p>Please provide the following information to complete your registration</p>
        
        <div class="user-info">
            <p><strong>Name:</strong> <?= htmlspecialchars($user['uname']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['uemail']) ?></p>
        </div>
        
        <?php if ($success): ?>
            <div class="success">
                <p>Your profile has been updated successfully!</p>
                <p>Redirecting to homepage...</p>
            </div>
        <?php else: ?>
            <form method="POST" action="">
                <div class="phone-input">
                    <span>+251</span>
                    <input type="text" name="phone" placeholder="9XXXXXXXX" pattern="[97][0-9]{8}" maxlength="9" required>
                </div>
                <?php if(isset($errors['phone'])): ?>
                    <p class="error"><?= $errors['phone'] ?></p>
                <?php endif; ?>
                
                <?php if($isGoogleUser): ?>
                    <input type="hidden" name="is_google_user" value="1">
                    
                    <div class="password-container">
                        <input type="password" id="password" name="password" placeholder="Create Password" required>
                        <span class="password-toggle" onclick="togglePasswordVisibility('password', 'password-eye')">
                            <i id="password-eye" class="fa fa-eye"></i>
                        </span>
                    </div>
                    <?php if(isset($errors['password'])): ?>
                        <p class="error"><?= $errors['password'] ?></p>
                    <?php endif; ?>
                    
                    <div class="password-container">
                        <input type="password" id="confirm-password" name="confirmPassword" placeholder="Confirm Password" required>
                        <span class="password-toggle" onclick="togglePasswordVisibility('confirm-password', 'confirm-eye')">
                            <i id="confirm-eye" class="fa fa-eye"></i>
                        </span>
                    </div>
                    <?php if(isset($errors['confirmPassword'])): ?>
                        <p class="error"><?= $errors['confirmPassword'] ?></p>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php if(isset($errors['general'])): ?>
                    <p class="error"><?= $errors['general'] ?></p>
                <?php endif; ?>
                
                <button type="submit">Complete Profile</button>
            </form>
        <?php endif; ?>
    </div>
    
    <script>
        function togglePasswordVisibility(inputId, eyeId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(eyeId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
