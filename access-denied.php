<?php
require_once 'includes/auth.php';

// Make sure user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

include 'includes/admin_header.php';
?>

<div class="alert alert-danger text-center p-5">
    <i class="fas fa-exclamation-triangle fa-4x mb-3"></i>
    <h2>Access Denied</h2>
    <p class="lead">You don't have permission to access this page.</p>
    <p>This area is restricted to users with higher privileges.</p>
    <a href="dashboard.php" class="btn btn-primary mt-3">Return to Dashboard</a>
</div>

<?php
include 'includes/admin_footer.php';
?>