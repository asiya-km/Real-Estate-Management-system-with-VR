<?php
session_start();
require("config.php");
 
if(!isset($_SESSION['uemail'])) {
    header("location:../login1.php");
    exit;
}
?>  
<div class="header">
    <!-- Logo -->
    <div class="header-left">
        <a href="dashboard.php" class="logo">
            <img src="assets/img/rsadmin.png" alt="Remsko Logo" height="45" class="img-fluid">
        </a>
        <a href="dashboard.php" class="logo logo-small">
            <img src="assets/img/logo-small.png" alt="Remsko Logo" width="30" height="30">
        </a>
    </div>
    <!-- /Logo -->
    
    <a href="javascript:void(0);" id="toggle_btn" class="toggle-btn">
        <i class="fe fe-text-align-left"></i>
    </a>
    
    <!-- Mobile Menu Toggle -->
    <a class="mobile_btn" id="mobile_btn">
        <i class="fa fa-bars"></i>
    </a>

    <ul class="nav user-menu">
        <!-- Notifications -->
        <li class="nav-item dropdown notifications">
            <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                <i class="fe fe-bell"></i> <span class="badge badge-pill badge-primary">3</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right notifications-dropdown">
                <div class="topnav-dropdown-header">
                    <span class="notification-title">Notifications</span>
                    <a href="javascript:void(0)" class="clear-noti"> Clear All </a>
                </div>
                <div class="noti-content">
                    <ul class="notification-list">
                        <li class="notification-message">
                            <a href="#">
                                <div class="media">
                                    <span class="avatar avatar-sm">
                                        <img class="avatar-img rounded-circle" alt="User Image" src="assets/img/profiles/avatar-02.jpg">
                                    </span>
                                    <div class="media-body">
                                        <p class="noti-details">New property listing added</p>
                                        <p class="noti-time"><span class="notification-time">4 mins ago</span></p>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="notification-message">
                            <a href="#">
                                <div class="media">
                                    <span class="avatar avatar-sm">
                                        <img class="avatar-img rounded-circle" alt="User Image" src="assets/img/profiles/avatar-03.jpg">
                                    </span>
                                    <div class="media-body">
                                        <p class="noti-details">New booking request received</p>
                                        <p class="noti-time"><span class="notification-time">1 hour ago</span></p>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="notification-message">
                            <a href="#">
                                <div class="media">
                                    <span class="avatar avatar-sm">
                                        <img class="avatar-img rounded-circle" alt="User Image" src="assets/img/profiles/avatar-04.jpg">
                                    </span>
                                    <div class="media-body">
                                        <p class="noti-details">New contact message</p>
                                        <p class="noti-time"><span class="notification-time">2 days ago</span></p>
                                    </div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="topnav-dropdown-footer">
                    <a href="#">View all Notifications</a>
                </div>
            </div>
        </li>
        <!-- /Notifications -->
        
        <!-- User Menu -->
        <li class="nav-item dropdown app-dropdown">
            <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                <span class="user-img">
                    <img class="rounded-circle" src="assets/img/profiles/avatar-01.png" width="31" alt="Manager Profile">
                    <span class="status online"></span>
                </span>
                <span class="d-none d-lg-inline-block ml-2"><?php echo $_SESSION['uemail']; ?></span>
            </a>
            
            <div class="dropdown-menu dropdown-menu-right">
                <div class="user-header">
                    <div class="avatar avatar-sm">
                        <img src="assets/img/profiles/avatar-01.png" alt="User Image" class="avatar-img rounded-circle">
                    </div>
                    <div class="user-text">
                        <h6><?php echo $_SESSION['uemail']; ?></h6>
                        <p class="text-muted mb-0">Property Manager</p>
                    </div>
                </div>
                <a class="dropdown-item" href="profile.php"><i class="fe fe-user mr-2"></i> My Profile</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="logout.php"><i class="fe fe-power mr-2"></i> Logout</a>
            </div>
        </li>
        <!-- /User Menu -->
    </ul>
    <!-- /Header Right Menu -->
</div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                               <li class="menu-title">
                    <span>Main</span>
                </li>
                <li class="active"> 
                    <a href="dashboard.php"><i class="fe fe-home"></i> <span>Dashboard</span></a>
                </li>
                
                <li class="menu-title"> 
                    <span>Property Management</span>
                </li>
                <li class="submenu">
                    <a href="#"><i class="fe fe-map"></i> <span>Properties</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a href="propertyadd.php"><i class="fe fe-plus-circle"></i> Add Property</a></li>
                        <li><a href="propertyview.php"><i class="fe fe-list"></i> View Properties</a></li>
                    </ul>
                </li>
                
                <li class="submenu">
                    <a href="#"><i class="fe fe-calendar"></i> <span>Manage Booking</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a href="update_booking.php"><i class="fe fe-edit"></i> Update Book </a></li>
                    </ul>
                </li>
                
                <li class="submenu">
                    <a href="#"><i class="fe fe-credit-card"></i> <span>Manage Payments</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a href="payment_history.php"><i class="fe fe-list"></i> Payment history </a></li>
                    </ul>
                </li>
                
                <li class="submenu">
                    <a href="#"><i class="fe fe-map-pin"></i> <span>Manage Visits</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a href="manage_visits.php"><i class="fe fe-eye"></i> Manage visit </a></li>
                    </ul>
                </li>
                
             
                
                <li class="menu-title"> 
                    <span>Query</span>
                </li>
                <li class="submenu">
                    <a href="#"><i class="fe fe-message-square"></i> <span>Messages</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a href="contactview.php"><i class="fe fe-mail"></i> Contact Messages</a></li>
                        <li><a href="feedbackview.php"><i class="fe fe-message-circle"></i> Feedback</a></li>
                    </ul>
                </li>
                
                <li class="menu-title"> 
                    <span>Content Management</span>
                </li>
                <li class="submenu">
                    <a href="#"><i class="fe fe-file-text"></i> <span>Website Content</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a href="aboutview.php"><i class="fe fe-file"></i> View About Content</a></li>
                    </ul>
                </li>
                
                <li class="menu-title"> 
                    <span>Account</span>
                </li>
                <li>
                    <a href="profile.php"><i class="fe fe-user"></i> <span>My Profile</span></a>
                </li>
                <li>
                    <a href="logout.php"><i class="fe fe-power"></i> <span>Logout</span></a>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- /Sidebar -->

