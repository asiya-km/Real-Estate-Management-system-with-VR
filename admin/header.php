<?php
session_start();
require("config.php");
 
if(!isset($_SESSION['auser']))
{
	header("location:../login1.php");
}
?>  
<div class="header">
    <!-- Logo -->
    <div class="header-left">
        <a href="dashboard.php" class="logo">
            <img src="assets/img/legacy-logo.png" alt="Logo" class="img-fluid" style="max-height: 45px;">
        </a>
        <a href="dashboard.php" class="logo logo-small">
            <img src="assets/img/logo-small.png" alt="Logo" width="30" height="30">
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
    <!-- /Mobile Menu Toggle -->
    
    <!-- Header Right Menu -->
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
                    <img class="rounded-circle" src="assets/img/profiles/avatar-01.png" width="31" alt="Admin">
                    <span class="status online"></span>
                </span>
                <span class="d-none d-lg-inline-block ml-2"><?php echo $_SESSION['auser']; ?></span>
            </a>
            
            <div class="dropdown-menu dropdown-menu-right">
                <div class="user-header">
                    <div class="avatar avatar-sm">
                        <img src="assets/img/profiles/avatar-01.png" alt="User Image" class="avatar-img rounded-circle">
                    </div>
                    <div class="user-text">
                        <h6><?php echo $_SESSION['auser']; ?></h6>
                        <p class="text-muted mb-0">Administrator</p>
                    </div>
                </div>
                <a class="dropdown-item" href="profile.php"><i class="fe fe-user mr-2"></i> Profile</a>
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
                <li> 
                    <a href="dashboard.php" class="active"><i class="fe fe-home"></i> <span>Dashboard</span></a>
                </li>
                
                <li class="menu-title"> 
                    <span>All Users</span>
                </li>
            
                <li class="submenu">
                    <a href="#"><i class="fe fe-users"></i> <span>Manage Users </span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a href="adminlist.php"><i class="fe fe-user-check"></i> Admin </a></li>
                        <li><a href="managerlist.php"><i class="fe fe-user-plus"></i> Manager </a></li>
                        <li><a href="userlist.php"><i class="fe fe-user"></i> Users </a></li>
                    </ul>
                </li>
            
                <li class="menu-title"> 
                    <span>Property Management</span>
                </li>
                <li class="submenu">
                    <a href="#"><i class="fe fe-map"></i> <span> Manage Property</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a href="propertyadd.php"><i class="fe fe-plus-circle"></i> Add Property</a></li>
                        <li><a href="propertyview.php"><i class="fe fe-list"></i> View Property </a></li>
                    </ul>
                </li>
                
                <li class="submenu">
                    <a href="#"><i class="fe fe-calendar"></i> <span> Manage Booking</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a href="update_booking.php"><i class="fe fe-edit"></i> Update Book </a></li>
                    </ul>
                </li>
                
                <li class="submenu">
                    <a href="#"><i class="fe fe-credit-card"></i> <span> Manage Payments</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a href="payment_history.php"><i class="fe fe-list"></i> Payment history </a></li>
                    </ul>
                </li>
                
                <li class="submenu">
                    <a href="#"><i class="fe fe-map-pin"></i> <span> Manage Visits</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a href="manage_visits.php"><i class="fe fe-eye"></i> Manage visit </a></li>
                    </ul>
                </li>
                
                <li class="menu-title"> 
                    <span>Query</span>
                </li>
                <li class="submenu">
                    <a href="#"><i class="fe fe-message-square"></i> <span> Contact,Feedback </span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a href="contactview.php"><i class="fe fe-mail"></i> Contact </a></li>
                        <li><a href="feedbackview.php"><i class="fe fe-message-circle"></i> Feedback </a></li>
                    </ul>
                </li>
                
                <li class="menu-title"> 
                    <span>About</span>
                </li>
                <li class="submenu">
                    <a href="#"><i class="fe fe-file-text"></i> <span> About Page </span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a href="aboutadd.php"><i class="fe fe-file-plus"></i> Add About Content </a></li>
                        <li><a href="aboutview.php"><i class="fe fe-file"></i> View About </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- /Sidebar -->
<!-- Add this right after the existing style.css link -->

<!-- Add this right after the existing style.css link -->
<style>
    /* Header color */
    .header {
        background-color:rgb(97, 230, 128) !important;
    }
    
    .header .header-left {
        background-color:rgb(96, 240, 130) !important;
    }
    
    /* Button styles */
    .btn-primary {
        background: linear-gradient(135deg, #28a745, #20c997) !important;
        border: none !important;
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.2) !important;
        transition: all 0.3s !important;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3) !important;
    }
    
    /* Sidebar active item */
    .sidebar .sidebar-menu > ul > li > a.active {
        background-color: #28a745 !important;
        color: #fff !important;
    }
    
    /* Hover effects */
    .sidebar .sidebar-menu ul li a:hover {
        background-color: rgba(40, 167, 69, 0.1) !important;
        color: #28a745 !important;
    }
    
    /* Dashboard cards hover */
    .dash-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(40, 167, 69, 0.1) !important;
    }
    
    /* Progress bars */
    .progress-bar.bg-primary {
        background: linear-gradient(135deg,rgb(94, 238, 128), #20c997) !important;
    }
    
    /* Widget icons */
    .dash-widget-icon.bg-primary {
        background: linear-gradient(135deg, #28a745, #20c997) !important;
    }
    
    /* Text colors */
    .text-primary {
        color: #28a745 !important;
    }
    
    /* Badge colors */
    .badge-primary {
        background: linear-gradient(135deg, #28a745, #20c997) !important;
    }
    
    /* Nav pills */
    .nav-pills .nav-link.active {
        background: linear-gradient(135deg, #28a745, #20c997) !important;
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.2) !important;
    }
    
    .nav-pills .nav-link:hover:not(.active) {
        background-color: rgba(40, 167, 69, 0.1) !important;
        transform: translateY(-2px) !important;
    }
    
    /* Quick action cards */
    .quick-action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(40, 167, 69, 0.12) !important;
    }
    
    /* Dropdown menu */
    .dropdown-item:hover, .dropdown-item:focus {
        background-color: rgba(40, 167, 69, 0.1) !important;
        color: #28a745 !important;
    }
    
    /* Table hover */
    .table-hover tbody tr:hover {
        background-color: rgba(40, 167, 69, 0.05) !important;
    }
</style>

