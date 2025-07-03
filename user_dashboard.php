<?php
session_start();
$booking_success_message = "";
if (isset($_GET['booking_success']) && $_GET['booking_success'] == 1 && isset($_SESSION['booking_message'])) {
    $booking_success_message = $_SESSION['booking_message'];
    unset($_SESSION['booking_message']);
}
include("config.php");

// Include authentication check
if (!isset($_SESSION['uid'])) {
    // Store the current URL as the intended destination
    $_SESSION['intended_redirect'] = $_SERVER['REQUEST_URI'];
    header("location:login1.php");
    exit();
}

$user_id = $_SESSION['uid'];

// Check for payment notification
$show_payment_notification = false;
$payment_success = false;
$booking_id = 0;
$property_title = '';

if (isset($_SESSION['payment_completed'])) {
    $show_payment_notification = true;
    $payment_success = $_SESSION['payment_success'] ?? false;
    $booking_id = $_SESSION['payment_booking_id'] ?? 0;
    $property_title = $_SESSION['payment_property_title'] ?? '';
    
    // Clear the session variables
    unset($_SESSION['payment_completed']);
    unset($_SESSION['payment_success']);
    unset($_SESSION['payment_booking_id']);
    unset($_SESSION['payment_property_title']);
}

// Fetch user information
$query = "SELECT * FROM user WHERE uid = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($user_result);

// Fetch recent bookings
$query = "SELECT b.*, p.title, p.pimage, p.location, p.city, p.price 
          FROM bookings b 
          JOIN property p ON b.property_id = p.pid 
          WHERE b.user_id = ? 
          ORDER BY b.booking_date DESC LIMIT 3";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$bookings_result = mysqli_stmt_get_result($stmt);

// Fetch recent visits
$query = "SELECT v.*, p.title, p.pimage, p.location, p.city 
          FROM visits v 
          JOIN property p ON v.property_id = p.pid 
          WHERE v.user_id = ? 
          ORDER BY v.visit_date DESC LIMIT 3";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$visits_result = mysqli_stmt_get_result($stmt);

// Fetch recent payments
$query = "SELECT b.*, p.title, p.pimage, p.location, p.city 
          FROM bookings b 
          JOIN property p ON b.property_id = p.pid 
          WHERE b.user_id = ? AND b.payment_status = 'completed' 
          ORDER BY b.payment_date DESC LIMIT 3";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$payments_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Meta Tags -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" href="images/favicon.ico">

    <!--	Fonts
    ========================================================-->
    <link href="https://fonts.googleapis.com/css?family=Muli:400,400i,500,600,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Comfortaa:400,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <title>User Dashboard</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        
        .dashboard-card {
            transition: all 0.3s;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: none;
            background-color: #fff;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }
        
        .dashboard-card .card-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            font-weight: 600;
            border: none;
            padding: 15px 20px;
        }
        
        .dashboard-stats {
            background-color: #fff;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            border-right: 1px solid #e9ecef;
            transition: all 0.3s;
        }
        
        .stat-item:hover {
            transform: translateY(-5px);
        }
        
        .stat-item:last-child {
            border-right: none;
        }
        
        .stat-number {
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(135deg, #28a745, #20c997);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 14px;
            font-weight: 500;
        }
        
        .profile-card {
            background-color: #fff;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        
        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }
        
        .profile-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        
        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid rgba(255,255,255,0.3);
            margin: 0 auto 20px;
            display: block;
            object-fit: cover;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .profile-img:hover {
            transform: scale(1.05);
            border-color: rgba(255,255,255,0.5);
        }
        
        .profile-body {
            padding: 25px;
        }
        
        .profile-info {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f1f1f1;
        }
        
        .profile-info:last-child {
            border-bottom: none;
        }
        
        .profile-info-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #28a745, #20c997);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.2);
        }
        
        .nav-pills .nav-link {
            color: #495057;
            font-weight: 500;
            border-radius: 10px;
            padding: 12px 20px;
            transition: all 0.3s;
        }
        
        .nav-pills .nav-link:hover:not(.active) {
            background-color: #f8f9fa;
            transform: translateY(-2px);
        }
        
        .list-group-item {
            border: none;
            padding: 15px 20px;
            margin-bottom: 5px;
            border-radius: 10px !important;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .list-group-item:hover:not(.active) {
            background-color: #f8f9fa;
            transform: translateY(-2px);
        }
        
        .list-group-item.active {
            background: linear-gradient(135deg, #28a745, #20c997);
            border-color: transparent;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.2);
        }
        
        .list-group-item i {
            margin-right: 10px;
            font-size: 18px;
            vertical-align: middle;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.2);
            transition: all 0.3s;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
        }
        
        .btn-outline-success {
            color: #28a745;
            border-color: #28a745;
            transition: all 0.3s;
        }
        
        .btn-outline-success:hover {
            background: linear-gradient(135deg, #28a745, #20c997);
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.2);
        }
        
        .quick-link-btn {
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .quick-link-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }
        
        .quick-link-btn i {
            font-size: 32px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        
        .quick-link-btn:hover i {
            transform: scale(1.2);
        }
        
        .badge {
            padding: 8px 12px;
            font-weight: 500;
            border-radius: 8px;
        }
        
        .badge-success {
            background: linear-gradient(135deg, #28a745, #20c997);
        }
        
        .badge-warning {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: #fff;
        }
        
        .badge-danger {
            background: linear-gradient(135deg, #dc3545, #f86565);
        }
        
        .property-img {
            height: 200px;
            object-fit: cover;
            width: 100%;
            transition: all 0.5s;
        }
        
        .dashboard-card:hover .property-img {
            transform: scale(1.05);
        }
        
        .card-content {
            padding: 20px;
        }
        
        .page-banner {
            position: relative;
        }
        
        /* .page-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.8), rgba(32, 201, 151, 0.8));
            z-index: 1;
        }
         */
        .page-banner .container {
            position: relative;
            z-index: 2;
        }
        
        .tab-content {
            background: #fff;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .alert {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .alert-success {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(32, 201, 151, 0.1));
            color: #28a745;
        }
        
               .alert-info {
            background: linear-gradient(135deg, rgba(13, 202, 240, 0.1), rgba(23, 162, 184, 0.1));
            color: #0dcaf0;
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
            overflow: hidden;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
        }
        
        .success-icon {
            color: #28a745;
            font-size: 80px;
            margin-bottom: 20px;
            animation: pulse 1.5s infinite;
        }
        
        .error-icon {
            color: #dc3545;
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }
        
        .property-details {
            padding: 0 15px;
        }
        
        .property-location {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 10px;
        }
        
        .property-price {
            font-weight: 600;
            color: #28a745;
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .booking-date {
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            border-radius: 8px;
            padding: 8px 15px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
        }
        
        .action-btn i {
            margin-right: 5px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 991px) {
            .stat-item {
                border-right: none;
                border-bottom: 1px solid #e9ecef;
                padding-bottom: 20px;
                margin-bottom: 20px;
            }
            
            .stat-item:last-child {
                border-bottom: none;
                margin-bottom: 0;
            }
        }
        
        @media (max-width: 767px) {
            .profile-card {
                margin-bottom: 30px;
            }
            
            .quick-link-btn {
                margin-bottom: 15px;
            }
            .footer {
  display: none;
}
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
                    <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>User Dashboard</b></h2>
                </div>
                <div class="col-md-6">
                    <nav aria-label="breadcrumb" class="float-left float-md-right">
                        <ol class="breadcrumb bg-transparent m-0 p-0">
                            <li class="breadcrumb-item text-white"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="full-row py-5">
        <div class="container">
            <?php if (!empty($booking_success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong><i class="fas fa-check-circle mr-2"></i> Success!</strong> <?php echo $booking_success_message; ?>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Sidebar -->
<div class="col-lg-4">
    <!-- Modern simplified profile section -->
<div class="bg-white rounded-lg shadow-sm p-4 mb-4 text-center">
    <div class="position-relative mb-3 mx-auto" style="width: 110px; height: 110px;">
        <a href="update_profile.php" class="d-block position-relative" style="width: 100%; height: 100%;" data-toggle="tooltip" title="Edit Profile">
            <img src="admin/user/<?php echo !empty($user['uimage']) ? $user['uimage'] : 'default-user.jpg'; ?>" alt="Profile" 
                style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; border: 4px solid #f8f9fa; box-shadow: 0 5px 15px rgba(0,0,0,0.08); transition: all 0.3s ease;">
            <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-center" 
                style="top: 0; left: 0; border-radius: 50%; background-color: rgba(84, 110, 122, 0.7); opacity: 0; transition: opacity 0.3s ease;">
                <i class="fas fa-user-edit text-white" style="font-size: 24px;"></i>
            </div>
        </a>
        <style>
            .position-relative a:hover img {
                transform: scale(0.95);
            }
            .position-relative a:hover .position-absolute {
                opacity: 1;
            }
        </style>
    </div>
    <h4 class="font-weight-bold mb-1"><?php echo htmlspecialchars($user['uname']); ?></h4>
    <div class="d-flex align-items-center justify-content-center mb-3">
        <span class="badge badge-light px-3 py-1 rounded-pill">
            <?php echo htmlspecialchars($user['utype']); ?>
        </span>
    </div>
    <div class="d-flex justify-content-around text-center">
        <div>
            <div class="font-weight-bold" style="color: #546e7a;"><?php echo $bookings_count; ?></div>
            <div class="small text-muted">Bookings</div>
        </div>
        <div style="border-left: 1px solid #eee; border-right: 1px solid #eee;" class="px-3">
            <div class="font-weight-bold" style="color: #546e7a;"><?php echo $visits_count; ?></div>
            <div class="small text-muted">Visits</div>
        </div>
        <div>
            <div class="font-weight-bold" style="color: #546e7a;"><?php echo $payments_count; ?></div>
            <div class="small text-muted">Payments</div>
        </div>
    </div>
</div>

    
    <!-- Modern navigation menu -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-4">
        <div class="list-group list-group-flush">
            <a href="user_dashboard.php" class="list-group-item list-group-item-action active d-flex align-items-center py-3">
                <i class="fas fa-tachometer-alt mr-3" style="width: 20px;"></i>
                <span>Dashboard</span>
            </a>
              <a href="profile.php" class="list-group-item list-group-item-action d-flex align-items-center py-3">
                <i class="fas fa-bookmark mr-3" style="width: 20px;"></i>
                <span>My Profile</span>
            </a>
            <a href="my_bookings.php" class="list-group-item list-group-item-action d-flex align-items-center py-3">
                <i class="fas fa-bookmark mr-3" style="width: 20px;"></i>
                <span>My Bookings</span>
            </a>
            <a href="my_visits.php" class="list-group-item list-group-item-action d-flex align-items-center py-3">
                <i class="fas fa-calendar-alt mr-3" style="width: 20px;"></i>
                <span>My Property Visits</span>
            </a>
            <a href="my_payments.php" class="list-group-item list-group-item-action d-flex align-items-center py-3">
                <i class="fas fa-credit-card mr-3" style="width: 20px;"></i>
                <span>My Payments</span>
            </a>
           
        </div>
    </div>
    
    <!-- Logout button -->
    <a href="logout.php" class="btn btn-light btn-block d-flex align-items-center justify-content-center py-3 mb-4 shadow-sm">
        <i class="fas fa-sign-out-alt mr-2 text-danger"></i>
        <span>Logout</span>
    </a>
</div>  
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Stats -->
                    <div class="dashboard-stats mb-4">
                        <div class="row">
                            <div class="col-md-4 stat-item">
                                <?php
                                $query = "SELECT COUNT(*) as count FROM bookings WHERE user_id = ?";
                                $stmt = mysqli_prepare($con, $query);
                                mysqli_stmt_bind_param($stmt, 'i', $user_id);
                                mysqli_stmt_execute($stmt);
                                $result = mysqli_stmt_get_result($stmt);
                                $bookings_count = mysqli_fetch_assoc($result)['count'];
                                ?>
                                <div class="stat-number"><?php echo $bookings_count; ?></div>
                                <div class="stat-label">Total Bookings</div>
                            </div>
                            <div class="col-md-4 stat-item">
                                <?php
                                $query = "SELECT COUNT(*) as count FROM visits WHERE user_id = ?";
                                $stmt = mysqli_prepare($con, $query);
                                mysqli_stmt_bind_param($stmt, 'i', $user_id);
                                mysqli_stmt_execute($stmt);
                                $result = mysqli_stmt_get_result($stmt);
                                $visits_count = mysqli_fetch_assoc($result)['count'];
                                ?>
                                <div class="stat-number"><?php echo $visits_count; ?></div>
                                <div class="stat-label">Property Visits</div>
                            </div>
                            <div class="col-md-4 stat-item">
                                <?php
                                $query = "SELECT COUNT(*) as count FROM bookings WHERE user_id = ? AND payment_status = 'completed'";
                                $stmt = mysqli_prepare($con, $query);
                                mysqli_stmt_bind_param($stmt, 'i', $user_id);
                                mysqli_stmt_execute($stmt);
                                $result = mysqli_stmt_get_result($stmt);
                                $payments_count = mysqli_fetch_assoc($result)['count'];
                                ?>
                                <div class="stat-number"><?php echo $payments_count; ?></div>
                                <div class="stat-label">Completed Payments</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <a href="my_bookings.php" class="quick-link-btn btn btn-light d-block h-100">
                                <i class="fas fa-bookmark text-success"></i>
                                <span>My Bookings</span>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <a href="my_visits.php" class="quick-link-btn btn btn-light d-block h-100">
                                <i class="fas fa-calendar-alt text-info"></i>
                                <span>My Visits</span>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="my_payments.php" class="quick-link-btn btn btn-light d-block h-100">
                                <i class="fas fa-credit-card text-primary"></i>
                                <span>My Payments</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Tabs -->
                    <ul class="nav nav-pills mb-4" id="dashboardTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="bookings-tab" data-toggle="pill" href="#bookings" role="tab">Recent Bookings</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="visits-tab" data-toggle="pill" href="#visits" role="tab">Recent Visits</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="payments-tab" data-toggle="pill" href="#payments" role="tab">Recent Payments</a>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="dashboardTabsContent">
                        <!-- Recent Bookings Tab -->
                        <div class="tab-pane fade show active" id="bookings" role="tabpanel" aria-labelledby="bookings-tab">
                            <?php if (mysqli_num_rows($bookings_result) > 0): ?>
                                <div class="row">
                                    <?php while ($booking = mysqli_fetch_assoc($bookings_result)): ?>
                                        <div class="col-md-12">
                                            <div class="dashboard-card mb-4">
                                                <div class="row no-gutters">
                                                    <div class="col-md-4">
                                                        <div style="overflow: hidden;">
                                                            <img src="admin/property/<?php echo htmlspecialchars($booking['pimage']); ?>" class="property-img" alt="Property">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="card-content">
                                                            <h5 class="mb-2"><?php echo htmlspecialchars($booking['title']); ?></h5>
                                                            <p class="property-location mb-2">
                                                                <i class="fas fa-map-marker-alt text-success mr-2"></i>
                                                                <?php echo htmlspecialchars($booking['location']); ?>, <?php echo htmlspecialchars($booking['city']); ?>
                                                            </p>
                                                            <p class="property-price mb-2">
                                                                ETB <?php echo number_format($booking['price']); ?>
                                                            </p>
                                                            <p class="booking-date mb-3">
                                                                <i class="far fa-calendar-alt mr-2"></i>
                                                                Booked on <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?>
                                                            </p>
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <span class="badge <?php echo ($booking['status'] == 'confirmed') ? 'badge-success' : (($booking['status'] == 'pending') ? 'badge-warning' : 'badge-danger'); ?>">
                                                                    <?php echo ucfirst($booking['status']); ?>
                                                                </span>
                                                                
                                                                <div class="action-buttons">
                                                                                                                                       <a href="booking_confirmation.php?booking_id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-info action-btn">
                                                                        <i class="fas fa-eye"></i> View
                                                                    </a>
                                                                    
                                                                    <?php if ($booking['payment_status'] != 'completed' && $booking['status'] != 'cancelled'): ?>
                                                                        <a href="agreement.php?booking_id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-success action-btn">
                                                                            <i class="fas fa-credit-card"></i> Pay
                                                                        </a>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="my_bookings.php" class="btn btn-outline-success">View All Bookings</a>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i> You haven't made any property bookings yet.
                                    <div class="mt-3">
                                        <a href="property.php" class="btn btn-success">Browse Properties</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Recent Visits Tab -->
                        <div class="tab-pane fade" id="visits" role="tabpanel" aria-labelledby="visits-tab">
                            <?php if (mysqli_num_rows($visits_result) > 0): ?>
                                <div class="row">
                                    <?php while ($visit = mysqli_fetch_assoc($visits_result)): ?>
                                        <div class="col-md-12">
                                            <div class="dashboard-card mb-4">
                                                <div class="row no-gutters">
                                                    <div class="col-md-4">
                                                        <div style="overflow: hidden;">
                                                            <img src="admin/property/<?php echo htmlspecialchars($visit['pimage']); ?>" class="property-img" alt="Property">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="card-content">
                                                            <h5 class="mb-2"><?php echo htmlspecialchars($visit['title']); ?></h5>
                                                            <p class="property-location mb-2">
                                                                <i class="fas fa-map-marker-alt text-success mr-2"></i>
                                                                <?php echo htmlspecialchars($visit['location']); ?>, <?php echo htmlspecialchars($visit['city']); ?>
                                                            </p>
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                    <p class="mb-1">
                                                                        <i class="far fa-calendar-alt mr-2 text-info"></i>
                                                                        <?php echo date('M d, Y', strtotime($visit['visit_date'])); ?>
                                                                    </p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p class="mb-1">
                                                                        <i class="far fa-clock mr-2 text-info"></i>
                                                                        <?php echo date('h:i A', strtotime($visit['visit_time'])); ?>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <span class="badge <?php echo ($visit['status'] == 'confirmed') ? 'badge-success' : (($visit['status'] == 'pending') ? 'badge-warning' : 'badge-danger'); ?>">
                                                                    <?php echo ucfirst($visit['status']); ?>
                                                                </span>
                                                                
                                                                <div class="action-buttons">
                                                                    <a href="visit_details.php?id=<?php echo $visit['id']; ?>" class="btn btn-sm btn-info action-btn">
                                                                        <i class="fas fa-eye"></i> View
                                                                    </a>
                                                                    
                                                                    <?php if ($visit['status'] == 'pending'): ?>
                                                                        <a href="reschedule_visit.php?id=<?php echo $visit['id']; ?>" class="btn btn-sm btn-primary action-btn">
                                                                            <i class="fas fa-calendar-alt"></i> Reschedule
                                                                        </a>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="my_visits.php" class="btn btn-outline-success">View All Visits</a>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i> You haven't scheduled any property visits yet.
                                    <div class="mt-3">
                                        <a href="property.php" class="btn btn-success">Browse Properties</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Recent Payments Tab -->
                        <div class="tab-pane fade" id="payments" role="tabpanel" aria-labelledby="payments-tab">
                            <?php if (mysqli_num_rows($payments_result) > 0): ?>
                                <div class="row">
                                    <?php while ($payment = mysqli_fetch_assoc($payments_result)): ?>
                                        <div class="col-md-12">
                                            <div class="dashboard-card mb-4">
                                                <div class="row no-gutters">
                                                    <div class="col-md-4">
                                                        <div style="overflow: hidden;">
                                                            <img src="admin/property/<?php echo htmlspecialchars($payment['pimage']); ?>" class="property-img" alt="Property">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="card-content">
                                                            <h5 class="mb-2"><?php echo htmlspecialchars($payment['title']); ?></h5>
                                                            <p class="property-location mb-2">
                                                                <i class="fas fa-map-marker-alt text-success mr-2"></i>
                                                                <?php echo htmlspecialchars($payment['location']); ?>, <?php echo htmlspecialchars($payment['city']); ?>
                                                            </p>
                                                            <p class="property-price mb-2">
                                                                <strong>Amount Paid:</strong> ETB <?php echo number_format($payment['payment_amount']); ?>
                                                            </p>
                                                            <p class="booking-date mb-3">
                                                                <i class="far fa-calendar-check mr-2 text-success"></i>
                                                                Paid on <?php echo date('M d, Y', strtotime($payment['payment_date'])); ?>
                                                            </p>
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <span class="badge badge-success">
                                                                    Payment Completed
                                                                </span>
                                                                
                                                                <div class="action-buttons">
                                                                    <a href="payment_receipt.php?booking_id=<?php echo $payment['id']; ?>" class="btn btn-sm btn-info action-btn" target="_blank">
                                                                        <i class="fas fa-file-invoice"></i> Receipt
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="my_payments.php" class="btn btn-outline-success">View All Payments</a>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i> You don't have any completed payments yet.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    
    
    <!-- Payment Confirmation Modal -->
    <div class="modal fade payment-modal" id="paymentConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="paymentConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white">Payment Status</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body text-center py-4">
                    <?php if (isset($_SESSION['payment_success']) && $_SESSION['payment_success']): ?>
                        <i class="fas fa-check-circle success-icon"></i>
                        <h3 class="mb-3">Payment Successful!</h3>
                        <p>Your booking #<?php echo isset($_SESSION['payment_booking_id']) ? str_pad($_SESSION['payment_booking_id'], 6, '0', STR_PAD_LEFT) : ''; ?> has been confirmed.</p>
                        <?php if (isset($_SESSION['payment_transaction_id'])): ?>
                            <p class="text-muted">Transaction ID: <?php echo htmlspecialchars($_SESSION['payment_transaction_id']); ?></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <i class="fas fa-exclamation-circle error-icon"></i>
                        <h3 class="mb-3">Payment Processing</h3>
                        <p>We're still processing your payment. Please check your booking status in your dashboard.</p>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <?php if (isset($_SESSION['payment_booking_id'])): ?>
                            <a href="booking_confirmation.php?booking_id=<?php echo $_SESSION['payment_booking_id']; ?>" class="btn btn-success">
                                View Booking Details
                            </a>
                        <?php endif; ?>
                        <button type="button" class="btn btn-outline-secondary ml-2" data-dismiss="modal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script>
        $(document).ready(function() {
            <?php if (isset($_SESSION['show_payment_modal']) && $_SESSION['show_payment_modal']): ?>
                // Show the payment confirmation modal
                $('#paymentConfirmationModal').modal('show');
                
                // Auto-close the modal after 8 seconds if payment was successful
                <?php if (isset($_SESSION['payment_success']) && $_SESSION['payment_success']): ?>
                setTimeout(function() {
                    $('#paymentConfirmationModal').modal('hide');
                }, 8000);
                <?php endif; ?>
                
                // Clear the session variables
                <?php 
                    unset($_SESSION['show_payment_modal']);
                    unset($_SESSION['payment_success']);
                ?>
            <?php endif; ?>
            
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Handle tab persistence
            var activeTab = localStorage.getItem('activeTab');
            if (activeTab) {
                $('#dashboardTabs a[href="' + activeTab + '"]').tab('show');
            }
            
            $('#dashboardTabs a').on('shown.bs.tab', function(e) {
                localStorage.setItem('activeTab', $(e.target).attr('href'));
            });
        });
    </script>
</body>
</html>
