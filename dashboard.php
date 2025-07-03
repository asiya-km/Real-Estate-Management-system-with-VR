<?php
// Include authentication and database connection
require_once 'includes/auth.php';
require_once 'includes/db_connect.php';

// Check if user is logged in (can be either admin or manager)
checkPermission('manager');

// Fetch statistics from database
$stats = [
    'total_properties' => 0,
    'total_users' => 0,
    'total_revenue' => 0,
    'active_listings' => 0,
    'pending_approvals' => 0
];

// Get total properties
$query = "SELECT COUNT(*) as count FROM properties";
$result = mysqli_query($conn, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['total_properties'] = $row['count'];
}

// Get total users (only for admin)
if (isAdmin()) {
    $query = "SELECT COUNT(*) as count FROM users";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $stats['total_users'] = $row['count'];
    }
}

// Get total revenue
$query = "SELECT SUM(amount) as total FROM payments WHERE status = 'completed'";
$result = mysqli_query($conn, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['total_revenue'] = $row['total'] ? $row['total'] : 0;
}

// Get active listings
$query = "SELECT COUNT(*) as count FROM properties WHERE status = 'active'";
$result = mysqli_query($conn, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['active_listings'] = $row['count'];
}

// Get pending approvals
$query = "SELECT COUNT(*) as count FROM properties WHERE status = 'pending'";
$result = mysqli_query($conn, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['pending_approvals'] = $row['count'];
}

// Include header
include 'includes/admin_header.php';
?>

<!-- Role-specific welcome message -->
<div class="alert <?php echo isAdmin() ? 'alert-primary' : 'alert-success'; ?> mb-4">
    <h4 class="alert-heading">Welcome, <?php echo $_SESSION['username']; ?>!</h4>
    <p>You are logged in as a <?php echo isAdmin() ? 'System Administrator' : 'Property Manager'; ?>.</p>
    <?php if (isAdmin()): ?>
    <p class="mb-0">You have full access to all system features and settings.</p>
    <?php else: ?>
    <p class="mb-0">You can manage properties, view reports, and handle approvals.</p>
    <?php endif; ?>
</div>

<section class="mb-5">
    <h2 class="mb-3">Overview</h2>
    <div class="row">
        <div class="col-md-4">
            <div class="stat-card bg-blue">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="title">Total Properties</div>
                        <div class="value"><?php echo $stats['total_properties']; ?></div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-home"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (isAdmin()): ?>
        <div class="col-md-4">
            <div class="stat-card bg-green">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="title">Total Users</div>
                        <div class="value"><?php echo $stats['total_users']; ?></div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="col-md-<?php echo isAdmin() ? '4' : '8'; ?>">
            <div class="stat-card bg-purple">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="title">Total Revenue</div>
                        <div class="value">$<?php echo number_format($stats['total_revenue'], 2); ?></div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section>
    <h2 class="mb-3">Quick Stats</h2>
    <div class="row">
        <div class="col-md-6">
            <div class="stat-card bg-orange">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="title">Active Listings</div>
                        <div class="value"><?php echo $stats['active_listings']; ?></div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-list-alt"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card bg-red">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="title">Pending Approvals</div>
                        <div class="value"><?php echo $stats['pending_approvals']; ?></div>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-square"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (isAdmin()): ?>
<section class="mt-5">
    <h2 class="mb-3">Admin Quick Actions</h2>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-user-shield fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Manage User Roles</h5>
                    <p class="card-text">Assign or modify user roles and permissions</p>
                    <a href="user-roles.php" class="btn btn-primary">Go to User Roles</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-cogs fa-3x text-secondary mb-3"></i>
                    <h5 class="card-title">System Settings</h5>
                    <p class="card-text">Configure system-wide settings and preferences</p>
                    <a href="system-settings.php" class="btn btn-secondary">Go to Settings</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-history fa-3x text-info mb-3"></i>
                    <h5 class="card-title">Activity Logs</h5>
                    <p class="card-text">View system activity and user action logs</p>
                    <a href="activity-logs.php" class="btn btn-info">View Logs</a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
// Include footer
include 'includes/admin_footer.php';
?>