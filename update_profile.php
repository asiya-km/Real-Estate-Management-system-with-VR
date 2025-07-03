<?php
session_start();
include("config.php");

if (!isset($_SESSION['uid'])) {
    header("location:login.php");
    exit();
}

$user_id = $_SESSION['uid'];
$msg = "";
$error = "";

// Fetch user details
$query = "SELECT * FROM user WHERE uid = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($con, trim($_POST['name']));
    $email = mysqli_real_escape_string($con, trim($_POST['email']));
    $phone = mysqli_real_escape_string($con, trim($_POST['phone']));
    $address = mysqli_real_escape_string($con, trim($_POST['address']));
    
    // Validate input
    if (empty($name) || empty($email) || empty($phone)) {
        $error = "Name, email, and phone are required fields";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } else {
        // Check if email already exists (excluding current user)
        $check_query = "SELECT uid FROM user WHERE uemail = ? AND uid != ?";
        $stmt = mysqli_prepare($con, $check_query);
        mysqli_stmt_bind_param($stmt, 'si', $email, $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $error = "Email address already in use by another account";
        } else {
            // Handle profile image upload
            $image_update = "";
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['profile_image']['name'];
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                
                if (!in_array(strtolower($ext), $allowed)) {
                    $error = "Invalid file format. Only JPG, JPEG, PNG, and GIF are allowed.";
                } else {
                    $new_filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
                    $upload_dir = 'admin/user/';
                    
                    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_dir . $new_filename)) {
                        // Delete old image if exists
                        if (!empty($user['uimage']) && file_exists($upload_dir . $user['uimage'])) {
                            @unlink($upload_dir . $user['uimage']);
                        }
                        
                        $image_update = ", uimage = '$new_filename'";
                    } else {
                        $error = "Failed to upload profile image";
                    }
                }
            }
            
            if (empty($error)) {
                // Update user profile
                if (!empty($image_update)) {
                    // If there's an image update
                    $update_query = "UPDATE user SET uname = ?, uemail = ?, uphone = ?, uaddress = ? $image_update WHERE uid = ?";
                    $stmt = mysqli_prepare($con, $update_query);
                    mysqli_stmt_bind_param($stmt, 'ssssi', $name, $email, $phone, $address, $user_id);
                } else {
                    // If there's no image update
                    $update_query = "UPDATE user SET uname = ?, uemail = ?, uphone = ?, uaddress = ? WHERE uid = ?";
                    $stmt = mysqli_prepare($con, $update_query);
                    mysqli_stmt_bind_param($stmt, 'ssssi', $name, $email, $phone, $address, $user_id);
                }
                
                if (mysqli_stmt_execute($stmt)) {
                    $msg = "Profile updated successfully";
                    
                    // Refresh user data
                    $query = "SELECT * FROM user WHERE uid = ?";
                    $stmt = mysqli_prepare($con, $query);
                    mysqli_stmt_bind_param($stmt, 'i', $user_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $user = mysqli_fetch_assoc($result);
                } else {
                    $error = "Failed to update profile: " . mysqli_error($con);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile - Remsko Real Estate</title>
    <!-- Include CSS files -->
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
    <style>
        .profile-image-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
        }
        .profile-image {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #f8f9fa;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .profile-image-edit {
            position: absolute;
            bottom: 0;
            right: 0;
            background-color: #28a745;
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .profile-image-edit:hover {
            background-color: #218838;
        }
        .required-field::after {
            content: "*";
            color: red;
            margin-left: 4px;
        }
    </style>
</head>
<body>
    <?php include("include/header.php"); ?>
    
    <!-- Banner -->
    <div class="banner-full-row page-banner" style="background-image:url('images/breadcromb.jpg');">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>Update Profile</b></h2>
                </div>
                <div class="col-md-6">
                    <nav aria-label="breadcrumb" class="float-left float-md-right">
                        <ol class="breadcrumb bg-transparent m-0 p-0">
                            <li class="breadcrumb-item text-white"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item text-white"><a href="user_dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Update Profile</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content -->
    <div class="full-row">
        <div class="container">
            <div class="row">
                               <div class="col-lg-4">
                    <div class="mb-4">
                        <a href="user_dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                        </a>
                    </div>
                    <?php include("include/user_sidebar.php"); ?>
                </div>
                
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Update Profile</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($msg)): ?>
                                <div class="alert alert-success"><?php echo $msg; ?></div>
                            <?php endif; ?>
                            
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            
                            <form method="post" action="" enctype="multipart/form-data">
                                <div class="profile-image-container">
                                    <img src="admin/user/<?php echo !empty($user['uimage']) ? $user['uimage'] : 'default-user.jpg'; ?>" alt="Profile" class="profile-image" id="profile-preview">
                                    <label for="profile_image" class="profile-image-edit" title="Change profile picture">
                                        <i class="fas fa-camera"></i>
                                    </label>
                                    <input type="file" id="profile_image" name="profile_image" style="display: none;" accept="image/*">
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name" class="required-field">Full Name</label>
                                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['uname']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email" class="required-field">Email Address</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['uemail']); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phone" class="required-field">Phone Number</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['uphone']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="address">Address</label>
                                            <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($user['uaddress'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" name="update_profile" class="btn btn-success">Update Profile</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include("include/footer.php"); ?>
    
    <!-- JavaScript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        // Preview profile image before upload
        document.getElementById('profile_image').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    document.getElementById('profile-preview').setAttribute('src', e.target.result);
                }
                
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    </script>
</body>
</html>
