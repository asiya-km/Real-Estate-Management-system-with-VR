<?php
// Get current page filename
$current_page = basename($_SERVER['PHP_SELF']);

// Fetch user details if not already available
if (!isset($user) && isset($_SESSION['uid'])) {
    $user_id = $_SESSION['uid'];
    $query = "SELECT * FROM user WHERE uid = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
}
?>

<div class="profile-card mb-4">
    <div class="profile-header">
        <a href="update_profile.php" style="text-decoration: none; color: inherit;">
            <img src="admin/user/<?php echo !empty($user['uimage']) ? $user['uimage'] : 'default-user.jpg'; ?>" alt="Profile" class="profile-img">
            <h4><?php echo htmlspecialchars($user['uname']); ?></h4>
            <p class="mb-0"><?php echo htmlspecialchars($user['utype']); ?></p>
        </a>
    </div>
    <div class="profile-body">
        <div class="profile-info">
            <div class="profile-info-label">Email</div>
            <div><?php echo htmlspecialchars($user['uemail']); ?></div>
        </div>
        <div class="profile-info">
            <div class="profile-info-label">Phone</div>
            <div><?php echo htmlspecialchars($user['uphone']); ?></div>
        </div>
        <div class="profile-info">
            <div class="profile-info-label">Member Since</div>
            <div><?php echo date('F Y', strtotime($user['udate'])); ?></div>
        </div>
    </div>
</div>

<div class="list-group mb-4">
    <a href="user_dashboard.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'user_dashboard.php') ? 'active' : ''; ?>">
        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
    </a>
    <a href="my_bookings.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'my_bookings.php') ? 'active' : ''; ?>">
        <i class="fas fa-bookmark mr-2"></i> My Bookings
    </a>
    <a href="my_visits.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'my_visits.php') ? 'active' : ''; ?>">
        <i class="fas fa-calendar-alt mr-2"></i> My Property Visits
    </a>
    <a href="favorites.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'favorites.php') ? 'active' : ''; ?>">
        <i class="fas fa-heart mr-2"></i> Favorite Properties
    </a>
    <a href="update_profile.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'update_profile.php') ? 'active' : ''; ?>">
        <i class="fas fa-user-edit mr-2"></i> Edit Profile
    </a>
    <a href="change_password.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'change_password.php') ? 'active' : ''; ?>">
        <i class="fas fa-key mr-2"></i> Change Password
    </a>
    <a href="logout.php" class="list-group-item list-group-item-action text-danger">
        <i class="fas fa-sign-out-alt mr-2"></i> Logout
    </a>
</div>
