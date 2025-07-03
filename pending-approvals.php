<?php
require_once 'includes/db_connect.php';
include 'includes/admin_header.php';
?>

<h1 class="mb-4">Pending Approvals</h1>

<!-- Pending approvals content goes here -->
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Properties Awaiting Approval</h5>
        <p class="card-text">This page will display all properties that need admin approval before being listed.</p>
    </div>
</div>

<?php
include 'includes/admin_footer.php';
?>