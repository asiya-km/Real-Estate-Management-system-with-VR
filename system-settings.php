<?php
require_once 'includes/auth.php';
require_once 'includes/db_connect.php';

// Check if user has admin privileges
checkPermission('admin');

include 'includes/admin_header.php';
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">System Settings</h5>
    </div>
    <div class="card-body">
        <form method="post" action="update-settings.php">
            <div class="mb-3">
                <label for="siteName" class="form-label">Site Name</label>
                <input type="text" class="form-control" id="siteName" name="siteName" value="Property Management System">
            </div>
            
            <div class="mb-3">
                <label for="contactEmail" class="form-label">Contact Email</label>
                <input type="email" class="form-control" id="contactEmail" name="contactEmail" value="contact@example.com">
            </div>
            
            <div class="mb-3">
                <label for="maintenanceMode" class="form-label">Maintenance Mode</label>
                <select class="form-select" id="maintenanceMode" name="maintenanceMode">
                    <option value="0">Off</option>
                    <option value="1">On</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="listingsPerPage" class="form-label">Listings Per Page</label>
                <input type="number" class="form-control" id="listingsPerPage" name="listingsPerPage" value="10">
            </div>
            
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</div>

<?php
include 'includes/admin_footer.php';
?>