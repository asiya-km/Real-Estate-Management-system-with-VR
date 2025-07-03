<?php
require_once 'includes/auth.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar bg-dark text-white" style="min-height: 100vh; width: 250px;">
    <div class="p-3">
        <h3 class="text-center py-3 border-bottom">
            <?php echo isAdmin() ? 'Admin' : 'Manager'; ?> Dashboard
        </h3>
        
        <div class="mt-4">
            <h5 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                <span>Overview</span>
            </h5>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $current_page == 'dashboard.php' ? 'active bg-primary' : ''; ?>" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $current_page == 'properties.php' ? 'active bg-primary' : ''; ?>" href="properties.php">
                        <i class="fas fa-home me-2"></i>
                        Properties
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $current_page == 'users.php' ? 'active bg-primary' : ''; ?>" href="users.php">
                        <i class="fas fa-users me-2"></i>
                        Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $current_page == 'revenue.php' ? 'active bg-primary' : ''; ?>" href="revenue.php">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        Revenue
                    </a>
                </li>
            </ul>
            
            <h5 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                <span>Quick Stats</span>
            </h5>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $current_page == 'active-listings.php' ? 'active bg-primary' : ''; ?>" href="active-listings.php">
                        <i class="fas fa-list-alt me-2"></i>
                        Active Listings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $current_page == 'pending-approvals.php' ? 'active bg-primary' : ''; ?>" href="pending-approvals.php">
                        <i class="fas fa-check-square me-2"></i>
                        Pending Approvals
                    </a>
                </li>
            </ul>
            
            <?php if (isAdmin()): ?>
            <h5 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                <span>Administration</span>
            </h5>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $current_page == 'system-settings.php' ? 'active bg-primary' : ''; ?>" href="system-settings.php">
                        <i class="fas fa-cogs me-2"></i>
                        System Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $current_page == 'user-roles.php' ? 'active bg-primary' : ''; ?>" href="user-roles.php">
                        <i class="fas fa-user-shield me-2"></i>
                        User Roles
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $current_page == 'activity-logs.php' ? 'active bg-primary' : ''; ?>" href="activity-logs.php">
                        <i class="fas fa-history me-2"></i>
                        Activity Logs
                    </a>
                </li>
            </ul>
            <?php endif; ?>
            
            <div class="mt-5 pt-3 border-top">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="profile.php">
                            <i class="fas fa-user-circle me-2"></i>
                            Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>