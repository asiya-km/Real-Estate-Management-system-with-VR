<?php
require_once 'includes/auth.php';
require_once 'includes/db_connect.php';

// Check if user has admin privileges
checkPermission('admin');

// Get all roles
$query = "SELECT * FROM roles";
$roles_result = mysqli_query($conn, $query);

// Get all users with their roles
$query = "SELECT u.id, u.username, u.email, r.name as role_name 
          FROM users u 
          JOIN roles r ON u.role_id = r.id 
          ORDER BY u.username";
$users_result = mysqli_query($conn, $query);

include 'includes/admin_header.php';
?>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">User Roles</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Role Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($role = mysqli_fetch_assoc($roles_result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($role['name']); ?></td>
                            <td><?php echo htmlspecialchars($role['description']); ?></td>
                            <td>
                                <a href="edit-role.php?id=<?php echo $role['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <a href="add-role.php" class="btn btn-success mt-2">Add New Role</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Assign User Roles</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Current Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['role_name']); ?></td>
                            <td>
                                <a href="change-role.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">Change Role</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
include 'includes/admin_footer.php';
?>