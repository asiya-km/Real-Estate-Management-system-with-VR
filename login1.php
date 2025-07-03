<?php
session_start();
include("config.php");
$error = "";
$msg = "";

$formData = [
    'firstName' => '',
    'phone' => '',
    'email' => '',
    'password' => '',
    'confirmPassword' => ''
];
$errors = [];
$loginErrors = [];
$isLoading = false;

// Google Client ID - replace with your actual client ID
$googleClientId = '1034370481764-n2ue8k1iebjenlo1us4n6dhk95k1abrd.apps.googleusercontent.com';

function validatePhone($phone) {
    return preg_match('/^\+251[97][0-9]{8}$/', $phone);
}

function validatePassword($password) {
    return strlen($password) >= 8 && 
           preg_match('/[A-Z]/', $password) && 
           preg_match('/[a-z]/', $password) && 
           preg_match('/[0-9]/', $password) && 
           preg_match('/[^A-Za-z0-9]/', $password);
}

// Handle Google Sign-In
if (isset($_POST['googleSignIn'])) {
    $token = $_POST['idtoken'];
    $email = $_POST['email'];
    $name = $_POST['name'];
    $picture = $_POST['picture'];
    $phone = $_POST['phone'];
    // Check if user exists in database
    $sql = "SELECT * FROM user WHERE uemail = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // User exists, check if account is active
        if (isset($row['status']) && $row['status'] == 0) {
            // Check if deactivation period has ended
            if (isset($row['status_end_date']) && $row['status_end_date'] && strtotime($row['status_end_date']) < time()) {
                // Reactivate user as deactivation period has ended
                $update_sql = "UPDATE user SET status = 1, status_end_date = NULL WHERE uid = ?";
                $update_stmt = mysqli_prepare($con, $update_sql);
                mysqli_stmt_bind_param($update_stmt, "i", $row['uid']);
                mysqli_stmt_execute($update_stmt);
            } else {
                // User is still deactivated
                $error = "Your account is currently deactivated";
                if (isset($row['status_end_date']) && $row['status_end_date']) {
                    $error .= " until " . date('M d, Y H:i', strtotime($row['status_end_date']));
                }
                $error .= ". Please contact the administrator for assistance.";
                echo json_encode(['success' => false, 'message' => $error]);
                exit;
            }
        }
          if (empty($row['uphone'])) {
        // Set a session variable to indicate this is a Google user who needs to complete profile
        $_SESSION['uid'] = $row['uid'];
        $_SESSION['uemail'] = $row['uemail'];
        $_SESSION['complete_profile'] = true;
        echo json_encode(['success' => true, 'redirect' => 'complete_profile.php']);
        exit;
    }
        // Log them in
        $_SESSION['uid'] = $row['uid'];
        $_SESSION['uemail'] = $row['uemail'];
        echo json_encode(['success' => true, 'redirect' => 'index.php']);
        exit;
    } else {
        // User doesn't exist, register them
        // Generate a random password
      //  $random_password = bin2hex(random_bytes(8));
        $hashedPassword = '';
        // Default values
        $phone = ''; // Empty phone
        $utype = 'user';
        $status = 1; // Active
       
        // Save profile image from Google
        $uimage = 'google_' . uniqid() . '.jpg';
        $upload_dir = "admin/user/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        
        // Try to download and save the profile image
        if (!empty($picture)) {
            file_put_contents($upload_dir . $uimage, file_get_contents($picture));
        } else {
            $uimage = 'default.jpg'; // Use default if no picture available
        }
        
        $security_question = 'Google Sign-In User';
        $security_answer = 'Google Sign-In User';
        
        // Insert new user
        $sql = "INSERT INTO user (uname, uemail, uphone, upass, utype, uimage, status, security_question, security_answer) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "sssssssss", 
            $name, 
            $email, 
            $phone, 
            $hashedPassword, 
            $utype, 
            $uimage, 
            $status, 
            $security_question, 
            $security_answer
        );
        
        if (mysqli_stmt_execute($stmt)) {
            // Get the new user ID
            $uid = mysqli_insert_id($con);
            
            // Log the user in
            $_SESSION['uid'] = $uid;
            $_SESSION['uemail'] = $email;
           $_SESSION['complete_profile'] = true;
            echo json_encode(['success' => true, 'redirect' => 'complete_profile.php']);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Registration failed: ' . mysqli_error($con)]);
            exit;
        }
    }
    exit;
}

// Handle regular form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['googleSignIn'])) {
    if (isset($_POST['action'])) {
        // Registration handling
        if ($_POST['action'] === 'register') {
            // Your existing registration code
            $phoneInput = trim($_POST['phone'] ?? '');
            
            $formData = [
                'firstName' => trim($_POST['firstName'] ?? ''),
                'phone' => '',
                'email' => trim($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'confirmPassword' => $_POST['confirmPassword'] ?? '',
                'utype' => 'user'
            ];
            
            // Phone validation and formatting
            if (!preg_match('/^[97][0-9]{8}$/', $phoneInput)) {
                $errors['phone'] = 'Phone number must start with 9 or 7 and be 9 digits long';
            } else {
                $phoneWithCode = '+251' . $phoneInput;
                $formData['phone'] = $phoneWithCode;
            }
            
            // Validation
            if (empty($formData['firstName'])) $errors['firstName'] = 'First name is required';
            if (empty($formData['phone'])) $errors['phone'] = 'Phone number is required';
            elseif (!validatePhone($formData['phone'])) $errors['phone'] = 'Invalid phone number format. Must start with +251 followed by 9 or 7 and 8 more digits';
            if (empty($formData['email'])) $errors['email'] = 'Email is required';
            elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email format';
            if (empty($formData['password'])) $errors['password'] = 'Password is required';
            elseif (!validatePassword($formData['password'])) $errors['password'] = 'Password must contain uppercase, lowercase, number, and special character';
            if ($formData['password'] !== $formData['confirmPassword']) $errors['confirmPassword'] = 'Passwords do not match';

            // Check if email already exists
            $checkEmail = "SELECT uemail FROM user WHERE uemail = ?";
            $stmt = mysqli_prepare($con, $checkEmail);
            mysqli_stmt_bind_param($stmt, "s", $formData['email']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if(mysqli_stmt_num_rows($stmt) > 0) {
                $errors['email'] = 'Email already exists. Please use a different email.';
            }
           $uphone = "SELECT uphone FROM user WHERE uphone = ?";
$stmt = mysqli_prepare($con, $uphone);
mysqli_stmt_bind_param($stmt, "s", $formData['phone']);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if(mysqli_stmt_num_rows($stmt) > 0) {
    $errors['phone'] = 'phone number is already exists. Please use a different phone number.';
}


            // File upload handling
            $uimage = '';
            if ($_FILES['uimage']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
                $fileType = $_FILES['uimage']['type'];
                if (in_array($fileType, $allowedTypes)) {
                    $uimage = uniqid() . '_' . basename($_FILES['uimage']['name']);
                    $upload_dir = "admin/user/";
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                    move_uploaded_file($_FILES['uimage']['tmp_name'], $upload_dir . $uimage);
                } else {
                    $errors['uimage'] = 'Only JPG, PNG, and GIF files are allowed';
                }
            } else {
                $errors['uimage'] = 'Profile image is required';
            }

            // Database operations
            if (empty($errors)) {
                $hashedPassword = password_hash($formData['password'], PASSWORD_DEFAULT);
                $status = 1; // Active by default
                $security_question = trim($_POST['security_question'] ?? '');
                $security_answer = trim($_POST['security_answer'] ?? '');

                // Store the answer in a standardized format (lowercase)
                $standardized_answer = strtolower($security_answer);

                // Add validation
                if (empty($security_question)) $errors['security_question'] = 'Security question is required';
                if (empty($security_answer)) $errors['security_answer'] = 'Security answer is required';

                $sql = "INSERT INTO user (uname, uemail, uphone, upass, utype, uimage, status, security_question, security_answer) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($con, $sql);
                mysqli_stmt_bind_param($stmt, "sssssssss", 
                    $formData['firstName'],
                    $formData['email'],
                    $formData['phone'],
                    $hashedPassword,
                    $formData['utype'],
                    $uimage,
                    $status,
                    $security_question,
                    $security_answer
                );
                if (mysqli_stmt_execute($stmt)) {
                    $msg = "Registration successful! Please login.";
                    header("Location: login1.php?msg=" . urlencode($msg));
                    exit;
                } else {
                    $errors['signup'] = 'Registration failed: ' . mysqli_error($con);
                }
            }
        }
        // Login handling
        elseif ($_POST['action'] === 'login') {
            // Your existing login code
            $emailOrUsername = trim($_POST['emailOrUsername'] ?? '');
            $password = $_POST['password'] ?? '';
            $utype = $_POST['utype'] ?? 'user';
            
            // Validation
            if (empty($emailOrUsername)) $loginErrors['emailOrUsername'] = 'Email/Username required';
            if (empty($password)) $loginErrors['password'] = 'Password required';

            if (empty($loginErrors)) {
                if ($utype === 'admin') {
                    $sql = "SELECT * FROM admin WHERE (aemail=? OR auser=?)";
                    $stmt = mysqli_prepare($con, $sql);
                    mysqli_stmt_bind_param($stmt, "ss", $emailOrUsername, $emailOrUsername);
                } else {
                    $sql = "SELECT * FROM user WHERE (uemail=? OR uname=?) AND utype=?";
                    $stmt = mysqli_prepare($con, $sql);
                    mysqli_stmt_bind_param($stmt, "sss", $emailOrUsername, $emailOrUsername, $utype);
                }
                
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                if ($row = mysqli_fetch_assoc($result)) {
                    // Get the stored hash based on user type
                    $stored_hash = ($utype === 'admin') ? $row['apass'] : $row['upass'];
                    $password_verified = false;

                    // Detect if this is an old SHA-1 hash (they're exactly 40 chars)
                    $is_sha1 = (strlen($stored_hash) == 40 && ctype_xdigit($stored_hash));

                    // For old SHA-1 hashes, verify differently
                    if ($is_sha1 && $stored_hash === sha1($password)) {
                        $password_verified = true;
                        
                        // Upgrade to new format
                        $new_hash = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Update the password in the database
                        if ($utype === 'admin') {
                            $update_sql = "UPDATE admin SET apass = ? WHERE " . ($row['aid'] ? "aid = ?" : "auser = ?");
                            $update_stmt = mysqli_prepare($con, $update_sql);
                            $id_or_user = $row['aid'] ?? $row['auser'];
                            mysqli_stmt_bind_param($update_stmt, "ss", $new_hash, $id_or_user);
                        } else {
                            $update_sql = "UPDATE user SET upass = ? WHERE uid = ?";
                            $update_stmt = mysqli_prepare($con, $update_sql);
                            mysqli_stmt_bind_param($update_stmt, "si", $new_hash, $row['uid']);
                        }
                        mysqli_stmt_execute($update_stmt);
                    } else {
                        // Normal verification for new-style passwords
                        $password_verified = password_verify($password, $stored_hash);
                    }

                    // For regular users, check if account is active
                    $canLogin = true; // Flag to determine if login should proceed
                    
                    if ($password_verified) {
                        if ($utype !== 'admin') {
                            // Check if user is active
                            if (isset($row['status']) && $row['status'] == 0) {
                                // Check if deactivation period has ended
                                if (isset($row['status_end_date']) && $row['status_end_date'] && strtotime($row['status_end_date']) < time()) {
                                    // Reactivate user as deactivation period has ended
                                    $update_sql = "UPDATE user SET status = 1, status_end_date = NULL WHERE uid = ?";
                                    $update_stmt = mysqli_prepare($con, $update_sql);
                                    mysqli_stmt_bind_param($update_stmt, "i", $row['uid']);
                                    mysqli_stmt_execute($update_stmt);
                                                                // Continue with login process (keep canLogin as true)
                                } else {
                                    // User is still deactivated
                                    $deactivation_message = "Your account is currently deactivated";
                                    if (isset($row['status_end_date']) && $row['status_end_date']) {
                                        $deactivation_message .= " until " . date('M d, Y H:i', strtotime($row['status_end_date']));
                                    }
                                    $deactivation_message .= ". Please contact the administrator for assistance.";
                                    
                                    // Show error message
                                    $loginErrors['login'] = $deactivation_message;
                                    // Prevent login by setting flag to false
                                    $canLogin = false;
                                }
                            }
                        }

                        // Process login based on user type if allowed
                        if ($canLogin) {
                            switch($utype) {
                                case 'admin':
                                    $_SESSION['auser'] = $emailOrUsername;
                                    $_SESSION['aemail'] = $row['aemail'];
                                    header("Location: admin/dashboard.php");
                                    break;
                            
                                case 'manager':
                                    $_SESSION['uemail'] = $row['uemail'];
                                    $_SESSION['auser'] = $emailOrUsername;
                                    header("Location: manager/dashboard.php");
                                    break;
                                
                                case 'user':
                                    $_SESSION['uid'] = $row['uid'];
                                    $_SESSION['uemail'] = $row['uemail'];
                                    header("location:index.php");                       
                                    break;
                                
                                default:
                                    header("Location: login1.php?error=invalid_role");
                                    break;
                            }
                            exit;
                        }
                    } else {
                        $loginErrors['login'] = 'Invalid credentials or user not found';
                    }
                } else {
                    $loginErrors['login'] = 'Invalid credentials or user not found';
                }
            }
        }
    }
}

// Get message from URL if redirected
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login/Register - Remsko Real Estate</title>
  <!-- Add Font Awesome for the eye icon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <!-- Add Google Sign-In API -->
  <script src="https://accounts.google.com/gsi/client" async defer></script>
 <style>
    /* General Styles */
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', sans-serif; background: url('images/slu.jpg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2') center/cover no-repeat fixed; display: flex; justify-content: center; align-items: center; height: 100vh; }

    /* Container */
    .container { width: 880px; background: #fff; border-radius: 30px; overflow: hidden; display: flex; box-shadow: 0 0 20px rgba(0,0,0,0.4); transition: all 0.6s ease-in-out; }

    /* Form Boxes */
    .form-box { width: 50%; padding: 40px; display: flex; flex-direction: column; justify-content: center; transition: all 0.6s ease-in-out; }
    .form-box h2 { margin-bottom: 20px; color: #333; }
    .form-box input, .form-box select { margin-bottom: 15px; padding: 12px; border: 1px solid #aaa; border-radius: 5px; }
    .form-box button { padding: 10px; background: #3b82f6; border: none; color: white; border-radius: 5px; cursor: pointer; font-weight: bold; }

    /* Panel */
    .panel { width: 28%; background: #3b82f6; color: white; padding: 50px; display: flex; flex-direction: column; justify-content: center; align-items: center; transition: all 0.6s ease-in-out; }
    .panel h2 { font-size: 20px; margin-bottom: 40px; }
    .panel p { margin-bottom: 30px; text-align: center; }
    .panel button { background: white; color: rgb(2, 10, 6); font-weight: bold; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }

    /* Sliding Effect */
    .container.right-panel-active { transform: translateX(0); }
    .container.right-panel-active .login-form { transform: translateX(-100%); opacity: 0; }
    .container.right-panel-active .register-form { transform: translateX(-400); opacity: 1; }
    .container.right-panel-active .panel { transform: translateX(-317%); }
    .login-form { left: 0; opacity: 1; z-index: 2; }
    .register-form { left: 0; opacity: 0; z-index: 1; }
    .panel-content { transition: transform 0.6s ease-in-out; }
    .container.right-panel-active .panel-content { transform: translateX(30%); }

    /* Error/Success Messages */
    .error { color: #e74c3c; font-size: 12px; margin: -10px 0 10px; }
    .success { color: #2ecc71; margin-bottom: 15px; }

    /* Phone Input */
    .phone-input { display: flex; align-items: center; }
    .phone-input span { padding: 12px; background: #f1f1f1; border: 1px solid #aaa; border-right: none; border-radius: 5px 0 0 5px; }
    .phone-input input { margin-bottom: 0; border-radius: 0 5px 5px 0; flex: 1; }

    /* Password Toggle */
    .password-container { position: relative; }
    .password-toggle { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666; }

    /* Google Sign-In Button */
    .google-btn { width: 100%; background: white; border: 1px solid #ccc; color: #757575; border-radius: 5px; padding: 15px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; cursor: pointer; text-decoration: none; }
    .google-btn:hover { background-color: #f5f5f5; }
    .google-btn img { width: 20px; margin-right: 10px; }

    /* OR Divider */
    .or-divider { display: flex; align-items: center; margin: 15px 0; color: #757575; text-align: center; }
    .or-divider:before, .or-divider:after { content: ""; flex: 1; border-bottom: 1px solid #ccc; }
    .or-divider:before { margin-right: 10px; }
    .or-divider:after { margin-left: 10px; }
  </style>

</head>
<body>

<div class="container <?php echo !empty($errors) ? 'right-panel-active' : ''; ?>" id="container">
  <!-- Login Form -->
  <div class="form-box login-form">
    <marquee><h2>Login</h2></marquee>
    <?php if(!empty($error)): ?>
      <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if(!empty($msg)): ?>
      <p class="success"><?php echo $msg; ?></p>
    <?php endif; ?>
    <?php if(isset($_GET['error'])): ?>
      <p style="color:red;"><?php echo $_GET['error']; ?></p>
    <?php endif; ?>
    
    <!-- Google Sign-In Button -->
    <div id="g_id_onload"
    
         data-client_id="<?php echo $googleClientId; ?>"
         data-context="signin"
         data-callback="handleCredentialResponse"
         data-auto_prompt="false">
         <a href="#"><i class="fab fa-google-plus-g"></i></a>  
    </div>
    
    <div class="g_id_signin google-btn"
         data-type="standard"
         data-size="large"
         data-theme="outline"
         data-text="sign_in_with"
         data-shape="rectangular"
         data-logo_alignment="left">
    </div>
    
 
    
    <form method="POST" action="">
      <input type="hidden" name="action" value="login">
      <input type="text" name="emailOrUsername" placeholder="Email/Username" required>
      <?php if(isset($loginErrors['emailOrUsername'])): ?>
        <p class="error"><?= $loginErrors['emailOrUsername'] ?></p>
      <?php endif; ?>
      <div class="password-container">
        <input type="password" id="login-password" name="password" placeholder="Password" required>
        <span class="password-toggle" onclick="togglePasswordVisibility('login-password', 'login-eye')">
          <i id="login-eye" class="fa fa-eye"></i>
        </span>
      </div>
      <?php if(isset($loginErrors['password'])): ?>
        <p class="error"><?= $loginErrors['password'] ?></p>
      <?php endif; ?>
      
      <select name="utype" required>
        <option value="user">Customer</option>
        <option value="manager">Manager</option>
        <option value="admin">Admin</option>
      </select><br><br>
      
      <?php if(isset($loginErrors['login'])): ?>
        <p class="error"><?= $loginErrors['login'] ?></p>
      <?php endif; ?>
      
      <button type="submit"><b>Login</b></button>
    </form>
    <p><a href="forgetpassword.php">Forgot Password?</a></p>
    <p>© 2025 Legacy RealEstate. All rights reserved.</p>
  </div>

  <!-- Register Form -->
  <div class="form-box register-form">
    <marquee><h2>Register</h2></marquee>
    
    <!-- Google Sign-Up Button -->
    <div id="g_id_onload_register"
         data-client_id="<?php echo $googleClientId; ?>"
         data-context="signup"
         data-callback="handleCredentialResponse"
         data-auto_prompt="false">
    </div>
    
    <div class="g_id_signin google-btn"
         data-type="standard"
         data-size="large"
         data-theme="outline"
         data-text="signup_with"
         data-shape="rectangular"
         data-logo_alignment="left">
    </div>
    
    
    
    <form method="POST" action="" enctype="multipart/form-data">
      <input type="hidden" name="action" value="register">
      
      <input type="text" name="firstName" placeholder="First Name" value="<?= htmlspecialchars($formData['firstName']) ?>" required>
      <?php if(isset($errors['firstName'])): ?>
        <p class="error"><?= $errors['firstName'] ?></p>
      <?php endif; ?>

      <div class="phone-input">
        <span>+251</span>
        <input type="text" name="phone" placeholder="9XXXXXXXX" pattern="[97][0-9]{8}" maxlength="9" required>
      </div>
      <?php if(isset($errors['phone'])): ?>
        <p class="error"><?= $errors['phone'] ?></p>
      <?php endif; ?>

      <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($formData['email']) ?>" required>
      <?php if(isset($errors['email'])): ?>
        <p class="error"><?= $errors['email'] ?></p>
      <?php endif; ?>

      <div class="password-container">
        <input type="password" id="register-password" name="password" placeholder="Password" required>
        <span class="password-toggle" onclick="togglePasswordVisibility('register-password', 'register-eye')">
          <i id="register-eye" class="fa fa-eye"></i>
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

      <select name="security_question" required>
        <option value="">Select Security Question</option>
        <option value="What was your childhood nickname?">What was your childhood nickname?</option>
        <option value="What is the name of your first pet?">What is the name of your first pet?</option>
        <option value="In what city were you born?">In what city were you born?</option>
        <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
               <option value="What was your first car?">What was your first car?</option>
      </select>
      <?php if(isset($errors['security_question'])): ?>
        <p class="error"><?= $errors['security_question'] ?></p>
      <?php endif; ?>
      
      <input type="text" name="security_answer" placeholder="Security Answer" required>
      <?php if(isset($errors['security_answer'])): ?>
        <p class="error"><?= $errors['security_answer'] ?></p>
      <?php endif; ?>

      <input type="file" name="uimage" accept="image/*" required>
      <?php if(isset($errors['uimage'])): ?>
        <p class="error"><?= $errors['uimage'] ?></p>
      <?php endif; ?>

      <?php if(isset($errors['signup'])): ?>
        <p class="error"><?= $errors['signup'] ?></p>
      <?php endif; ?>

      <button type="submit">Register</button>
    </form>
  </div>

  <!-- Panel -->
  <div class="panel">
    <a href="#"><img class="logo-bottom" src="images/logo/legacy-logo.png" alt="image" style="max-height: 40px;"></a> 
    <div class="panel-content">
      
      <h2><?= empty($errors) ? 'Welcome!' : 'Fix Errors' ?></h2>
      <p id="panelText"><?= empty($errors) ? 'Don\'t have an account?' : 'Please correct the errors' ?></p>
      <button id="toggleBtn" onclick="toggleForm()">
        <?= empty($errors) ? 'Register' : 'Try Again' ?>
      </button>
    </div>
  </div>
</div>

<!-- Hidden form for Google Sign-In data submission -->
<form id="google-signin-form" method="POST" style="display:none;">
  <input type="hidden" name="googleSignIn" value="1">
  <input type="hidden" name="idtoken" id="idtoken">
  <input type="hidden" name="email" id="email">
  <input type="hidden" name="name" id="name">
  <input type="hidden" name="picture" id="picture">
</form>

<script>
  function toggleForm() {
    const container = document.getElementById('container');
    container.classList.toggle('right-panel-active');
    
    const toggleBtn = document.getElementById('toggleBtn');
    const panelTitle = document.querySelector('.panel h2');
    const panelText = document.getElementById('panelText');
    
    if (container.classList.contains('right-panel-active')) {
      toggleBtn.textContent = 'Login';
      panelTitle.textContent = 'Welcome Back!';
      panelText.textContent = 'Already have an account?';
    } else {
      toggleBtn.textContent = 'Register';
      panelTitle.textContent = 'New Here?';
      panelText.textContent = 'Start your journey with us';
    }
  }

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

  // Google Sign-In callback
  function handleCredentialResponse(response) {
    // Decode the JWT token to get user info
    const responsePayload = parseJwt(response.credential);
    
    // Set form values
    document.getElementById('idtoken').value = response.credential;
    document.getElementById('email').value = responsePayload.email;
    document.getElementById('name').value = responsePayload.name;
    document.getElementById('picture').value = responsePayload.picture;
    
    // Submit the form
    const form = document.getElementById('google-signin-form');
    
    // Use fetch API to submit the form asynchronously
    fetch('login1.php', {
      method: 'POST',
      body: new FormData(form)
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        window.location.href = data.redirect;
      } else {
        alert(data.message || 'An error occurred during sign-in');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('An error occurred during sign-in. Please try again.');
    });
  }
  
  // Helper function to parse JWT token
  function parseJwt(token) {
    const base64Url = token.split('.')[1];
    const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
      return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join(''));
    return JSON.parse(jsonPayload);
  }

  // Auto-toggle to registration form if there are errors
  <?php if(!empty($errors)): ?>
    document.addEventListener('DOMContentLoaded', () => {
      document.getElementById('container').classList.add('right-panel-active');
    });
  <?php endif; ?>
</script>

</body>
</html>

                                   
