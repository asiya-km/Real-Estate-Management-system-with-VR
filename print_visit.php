<?php
session_start();
require("config.php");

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login1.php");
    exit();
}

// Check if visit ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid visit ID";
    exit();
}

$visit_id = intval($_GET['id']);
$user_id = $_SESSION['uid'];

// Get visit details with property information (only for the logged-in user)
$query = "SELECT v.*, p.title as property_title, p.pimage, p.location, p.city, p.price, p.type, p.stype,
          u.uname, u.uemail, u.uphone
          FROM visits v 
          LEFT JOIN property p ON v.property_id = p.pid 
          LEFT JOIN user u ON v.user_id = u.uid 
          WHERE v.id = ? AND v.user_id = ?";

$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'ii', $visit_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$visit = mysqli_fetch_assoc($result)) {
    echo "Visit not found or unauthorized access";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Visit Details - Print</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .print-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .print-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #28a745;
        }
        .print-header h1 {
            color: #28a745;
            margin-bottom: 5px;
        }
        .property-details {
            display: flex;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .property-image {
            width: 200px;
            height: 150px;
            object-fit: cover;
            margin-right: 20px;
        }
        .detail-row {
            margin-bottom: 10px;
        }
        .detail-label {
            font-weight: bold;
            color: #28a745;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            color: white;
        }
        .status-pending { background-color: #ffc107; }
        .status-confirmed { background-color: #28a745; }
        .status-completed { background-color: #17a2b8; }
        .status-cancelled { background-color: #dc3545; }
        .section {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .section-title {
                        color: #28a745;
            margin-bottom: 15px;
        }
        .print-footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #6c757d;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .print-container {
                border: none;
                max-width: 100%;
                margin: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="print-header">
            <h1>Property Visit Confirmation</h1>
            <p>Visit #<?= str_pad($visit['id'], 6, '0', STR_PAD_LEFT) ?> | Requested on <?= date('F d, Y', strtotime($visit['request_date'])) ?></p>
        </div>
        
        <div class="property-details">
            <img src="admin/property/<?= $visit['pimage'] ?>" alt="Property" class="property-image">
            <div>
                <h3><?= htmlspecialchars($visit['property_title']) ?></h3>
                <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($visit['location']) ?>, <?= htmlspecialchars($visit['city']) ?></p>
                <p><strong>Price:</strong> ETB <?= number_format($visit['price']) ?></p>
                <p><strong>Type:</strong> <?= htmlspecialchars($visit['type']) ?> for <?= htmlspecialchars($visit['stype']) ?></p>
            </div>
        </div>
        
        <div class="section">
            <h3 class="section-title">Visit Status</h3>
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <?php 
                $statusClass = '';
                switch($visit['status']) {
                    case 'confirmed': $statusClass = 'status-confirmed'; break;
                    case 'pending': $statusClass = 'status-pending'; break;
                    case 'completed': $statusClass = 'status-completed'; break;
                    case 'cancelled': $statusClass = 'status-cancelled'; break;
                }
                ?>
                <span class="status-badge <?= $statusClass ?>">
                    <?= ucfirst(htmlspecialchars($visit['status'])) ?>
                </span>
            </div>
        </div>
        
        <div class="section">
            <h3 class="section-title">Your Information</h3>
            <div class="detail-row">
                <span class="detail-label">Name:</span>
                <span><?= htmlspecialchars($visit['name']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span><?= htmlspecialchars($visit['email']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Phone:</span>
                <span><?= htmlspecialchars($visit['phone']) ?></span>
            </div>
        </div>
        
        <div class="section">
            <h3 class="section-title">Visit Information</h3>
            <div class="detail-row">
                <span class="detail-label">Visit Date:</span>
                <span><?= date('F d, Y', strtotime($visit['visit_date'])) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Visit Time:</span>
                <span><?= date('h:i A', strtotime($visit['visit_time'])) ?></span>
            </div>
        </div>
        
        <?php if (!empty($visit['message'])): ?>
        <div class="section">
            <h3 class="section-title">Additional Message</h3>
            <p><?= nl2br(htmlspecialchars($visit['message'])) ?></p>
        </div>
        <?php endif; ?>
        
        <div class="section">
            <h3 class="section-title">Important Information</h3>
            <p>Please arrive on time for your scheduled visit. If you need to reschedule or cancel, please do so at least 24 hours in advance.</p>
            <p>Contact: <strong>support@remsko.com</strong> | Phone: <strong>+251 123 456 789</strong></p>
        </div>
        
        <div class="print-footer">
            <p>This document was generated on <?= date('F d, Y H:i:s') ?></p>
            <p>Remsko Real Estate Management System</p>
        </div>
        
        <div class="no-print text-center mt-4">
            <button onclick="window.print()" class="btn btn-success">
                <i class="fas fa-print"></i> Print
            </button>
            <button onclick="window.location.href='my_visits.php'" class="btn btn-secondary ml-2">
                <i class="fas fa-arrow-left"></i> Back
            </button>
        </div>
    </div>
</body>
</html>

