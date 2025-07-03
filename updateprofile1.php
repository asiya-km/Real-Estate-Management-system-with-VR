<?php
session_start();
include("config.php");

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

if (!isset($_SESSION['uid'])) {
    header("location:login.php");
    exit();
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";
$msg = "";
$uid = $_SESSION['uid'];

// Fetch user data using prepared statement
$query = "SELECT uname, uphone, uimage, upass FROM user WHERE uid = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    die("User not found");
}

// Validation functions
function validatePhone($phone) {
    return empty($phone) || preg_match('/^\+[1-9]\d{1,14}$/', $phone);
}




function validatePassword($pass) {
    return empty($pass) || preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $pass);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid request";
    } else {
        $name = htmlspecialchars(trim($_POST['name']));
        $phone = trim($_POST['full_phone']);
        $pass = trim($_POST['pass']);
        
        // Initialize with existing values
        $uimage = $row['uimage'];
        $currentPhone = $row['uphone'];
        $imageUpdated = false;

        // Validate inputs
        if (empty($name)) {
            $error = "Name is required";
        } elseif (!validatePhone($phone)) {
            $error = "Invalid phone number format";
        } elseif (!validatePassword($pass)) {
            $error = "Password must be 8+ chars with uppercase, lowercase, number, and special character";
        } else {
            // Handle phone number update
            $phone = empty($phone) ? $currentPhone : $phone;

            // Handle file upload
            if (!empty($_FILES['uimage']['name'])) {
                // ... [keep existing file upload code] ...
                $uploadDir = 'admin/user/';
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $maxSize = 5 * 1024 * 1024; // 2MB

                $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
                $detectedType = finfo_file($fileInfo, $_FILES['uimage']['tmp_name']);
                
                if (!in_array($detectedType, $allowedTypes)) {
                    $error = "Invalid file type. Only JPG, PNG, and GIF allowed.";
                } elseif ($_FILES['uimage']['size'] > $maxSize) {
                    $error = "File size exceeds 2MB limit";
                } else {
                    $extension = pathinfo($_FILES['uimage']['name'], PATHINFO_EXTENSION);
                    $uimage = uniqid('profile_', true) . '.' . $extension;
                    $uploadPath = $uploadDir . $uimage;
                    
                    if (move_uploaded_file($_FILES['uimage']['tmp_name'], $uploadPath)) {
                        $imageUpdated = true;
                    } else {
                        $error = "Error uploading file";
                    }
                }
                finfo_close($fileInfo);
            }
            }

            // Update database if no errors
            if (empty($error)) {
                $query = "UPDATE user SET 
                          uname = ?, 
                          uphone = ?, 
                          upass = COALESCE(?, upass), 
                          uimage = ? 
                          WHERE uid = ?";
                
                $stmt = mysqli_prepare($con, $query);
                $hashedPass = !empty($pass) ? $pass : null;
                
                mysqli_stmt_bind_param($stmt, "ssssi", 
                    $name,
                    $phone,
                    $hashedPass,
                    $uimage,
                    $uid
                );

                if (mysqli_stmt_execute($stmt)) {
                    // ... [keep existing success code] ...
                    $_SESSION['uimage'] = $uimage;
                    $msg = "Profile updated successfully";
                   // header("location: profile.php?msg=$msg")
                    // Remove old image if updated
                    if ($imageUpdated && !empty($row['uimage'])) {
                        @unlink($uploadDir . $row['uimage']);
                    }
                } else {
                    $error = "Error updating profile: " . mysqli_error($con);
                }
                }
            }
        }
    

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Keep head content the same -->
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
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
    <title>Profile Update</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">

</head>
<body>
    <?php include("include/header.php"); ?>

    <div class="page-wrappers login-body full-row bg-gray">
        <div class="login-wrapper">
            <div class="container">
                <div class="loginbox">
                    <div class="login-right">
                        <div class="login-right-wrap">
                            <h1>Update Profile</h1>
                            <!-- Display messages -->
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                            <?php endif; ?>
                            <?php if ($msg): ?>
                                <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
                            <?php endif; ?>
                            <form method="post" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                
                                <div class="form-group">
                                    <label>Name *</label>
                                    <input type="text" name="name" class="form-control" 
                                        value="<?= htmlspecialchars($row['uname']) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input type="tel" id="phone" class="form-control">
                                    <input type="hidden" id="full_phone" name="full_phone" value="<?php echo htmlspecialchars($row['uphone']); ?>">
                                </div>

                                <div class="form-group">
                                    <label>New Password (leave blank to keep current)</label>
                                    <input type="password" name="pass" class="form-control" value="<?php echo htmlspecialchars($row['upass']); ?>">
                                </div>

                                <div class="form-group">
                                    <label>Profile Image</label>
                                    <input type="file" name="uimage" class="form-control-file">
                                    <?php if(!empty($row['uimage'])): ?>
                                        <img src="admin/user/<?= $row['uimage'] ?>" 
                                            alt="Current Image" style="max-width: 150px;">
                                    <?php endif; ?>
                                </div>

                                <button type="submit" name="update" class="btn btn-primary btn-block">
                                    Update Profile
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include("include/footer.php"); ?>

    <script>
        
        <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
<script>
    const phoneInput = document.querySelector("#phone");
    const fullPhoneInput = document.querySelector("#full_phone");

    const iti = window.intlTelInput(phoneInput, {
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
        separateDialCode: true,
        preferredCountries: ['us', 'gb', 'in'],
        initialCountry: "auto",
        geoIpLookup: function(callback) {
            fetch("https://ipapi.co/json")
                .then(response => response.json())
                .then(data => callback(data.country_code))
                .catch(() => callback("us"));
        }
    });

    phoneInput.addEventListener("input", function() {
        if (iti.isValidNumber()) {
            fullPhoneInput.value = iti.getNumber();
        } else {
            fullPhoneInput.value = "";
        }
    });
</body>
</html>