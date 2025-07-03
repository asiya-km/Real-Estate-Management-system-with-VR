<?php
session_start();
require("../config.php");

// Check if admin is logged in
if (!isset($_SESSION['auser'])) {
    header("location:index.php");
    exit();
}

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="payment_history_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = " AND (u.uname LIKE '%".mysqli_real_escape_string($con, $search)."%' OR 
                              p.title LIKE '%".mysqli_real_escape_string($con, $search)."%' OR 
                              b.payment_transaction_id LIKE '%".mysqli_real_escape_string($con, $search)."%')";
}

// Filter by payment status
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$status_condition = '';
if (!empty($status_filter)) {
    $status_condition = " AND b.payment_status = '".mysqli_real_escape_string($con, $status_filter)."'";
}

// Get payment records
$query = "SELECT b.id, b.payment_date, b.payment_amount, b.payment_method, b.payment_transaction_id, 
          b.payment_status, u.uname, u.uemail, p.title as property_title, p.location, p.city 
          FROM bookings b 
          JOIN user u ON b.user_id = u.uid 
          JOIN property p ON b.property_id = p.pid 
          WHERE b.payment_status IS NOT NULL".$search_condition.$status_condition." 
          ORDER BY b.payment_date DESC";
$result = mysqli_query($con, $query);

// Start the Excel file content
echo '
<table border="1">
    <tr>
        <th colspan="10" style="background-color: #28a745; color: white; font-size: 16px; text-align: center;">
            Remsko Real Estate - Payment History
        </th>
    </tr>
    <tr>
        <th colspan="10" style="background-color: #f2f2f2; text-align: center;">
            Generated on: ' . date('F d, Y H:i:s') . '
        </th>
    </tr>
    <tr>
        <th style="background-color: #f2f2f2; font-weight: bold;">ID</th>
        <th style="background-color: #f2f2f2; font-weight: bold;">Date</th>
        <th style="background-color: #f2f2f2; font-weight: bold;">Customer Name</th>
        <th style="background-color: #f2f2f2; font-weight: bold;">Customer Email</th>
        <th style="background-color: #f2f2f2; font-weight: bold;">Property</th>
        <th style="background-color: #f2f2f2; font-weight: bold;">Location</th>
        <th style="background-color: #f2f2f2; font-weight: bold;">Amount</th>
        <th style="background-color: #f2f2f2; font-weight: bold;">Payment Method</th>
        <th style="background-color: #f2f2f2; font-weight: bold;">Transaction ID</th>
        <th style="background-color: #f2f2f2; font-weight: bold;">Status</th>
    </tr>';

// Add data rows
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<tr>
            <td>' . $row['id'] . '</td>
            <td>' . date('Y-m-d H:i', strtotime($row['payment_date'])) . '</td>
            <td>' . htmlspecialchars($row['uname']) . '</td>
            <td>' . htmlspecialchars($row['uemail']) . '</td>
            <td>' . htmlspecialchars($row['property_title']) . '</td>
            <td>' . htmlspecialchars($row['location']) . ', ' . htmlspecialchars($row['city']) . '</td>
            <td>ETB ' . number_format($row['payment_amount'], 2) . '</td>
            <td>' . htmlspecialchars($row['payment_method']) . '</td>
            <td>' . htmlspecialchars($row['payment_transaction_id']) . '</td>
            <td>' . ucfirst($row['payment_status']) . '</td>
        </tr>';
    }
} else {
    echo '<tr><td colspan="10" style="text-align: center;">No payment records found</td></tr>';
}

// Close the table
echo '</table>';
?>
