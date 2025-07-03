<?php
require_once 'includes/auth.php';
require_once 'includes/db_connect.php';

// Check if user has admin privileges
checkPermission('admin');

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 15;
$offset = ($page - 1) * $records_per_page;

// Get total number of logs
$count_query = "SELECT COUNT(*) as total FROM activity_logs";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get logs with pagination
$query = "SELECT l.*, u.username 
          FROM activity_logs l
          LEFT JOIN users u ON l.user_id = u.id
          ORDER BY l.created_at DESC
          LIMIT $offset, $records_per_page";
$result = mysqli_query($conn, $query);

include 'includes/admin_header.php';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">System Activity Logs</h5>
        <div>
            <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print
            </button>
            <a href="export-logs.php" class="btn btn-sm btn-outline-primary ms-2">
                <i class="fas fa-download me-1"></i> Export
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>IP Address</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($log = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></td>
                                <td><?php echo $log['username'] ? htmlspecialchars($log['username']) : 'System'; ?></td>
                                <td><?php echo htmlspecialchars($log['action']); ?></td>
                                <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                <td>
                                    <?php if ($log['details']): ?>
                                        <button class="btn btn-sm btn-link" data-bs-toggle="modal" data-bs-target="#logModal<?php echo $log['id']; ?>">
                                            View Details
                                        </button>
                                        
                                        <!-- Modal for log details -->
                                        <div class="modal fade" id="logModal<?php echo $log['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Log Details</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <pre class="bg-light p-3 rounded"><?php echo htmlspecialchars($log['details']); ?></pre>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No details</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No activity logs found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Activity log pagination">
                <ul class="pagination justify-content-center mt-4">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php
include 'includes/admin_footer.php';
?>