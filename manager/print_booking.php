<?php
session_start();
require("../config.php");

// Check if admin is logged in
if (!isset($_SESSION['auser'])) {
    header("location:index.php");
}

// Check if booking ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid booking ID";
    exit();
}

$booking_id = intval($_GET['id']);

// Get booking details with property and user information
$query = "SELECT b.*, p.title as property_title, p.pimage, p.location, p.city, p.price, p.type, p.stype,
          u.uname as user_name, u.uemail as user_email, u.uphone as user_phone
          FROM bookings b 
          LEFT JOIN property p ON b.property_id = p.pid 
          LEFT JOIN user u ON b.user_id = u.uid 
          WHERE b.id = ?";

$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'i', $booking_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$booking = mysqli_fetch_assoc($result)) {
    echo "Booking not found";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Booking Details - Print</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
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
            border-bottom: 2px solid #4e73df;
        }
        .print-header h1 {
            color: #4e73df;
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
            color: #4e73df;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            color: white;
        }
        .status-pending { background-color: #f6c23e; }
        .status-confirmed { background-color: #1cc88a; }
        .status-active { background-color: #4e73df; }
        .status-expired { background-color: #858796; }
        .status-cancelled { background-color: #e74a3b; }
        .payment-pending { background-color: #f6c23e; }
        .payment-completed { background-color: #1cc88a; }
        .payment-failed { background-color: #e74a3b; }
        .section {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .section-title {
            color: #4e73df;
            margin-bottom: 15px;
        }
        .print-footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #858796;
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
            <h1>Booking Details</h1>
            <p>Booking #<?= $booking['id'] ?> | Created on <?= date('F d, Y', strtotime($booking['booking_date'])) ?></p>
        </div>
        
        <div class="property-details">
            <img src="../admin/property/<?= $booking['pimage'] ?>" alt="Property" class="property-image">
            <div>
                <h3><?= htmlspecialchars($booking['property_title']) ?></h3>
                <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($booking['location']) ?>, <?= htmlspecialchars($booking['city']) ?></p>
                <p><strong>Price:</strong> ETB <?= number_format($booking['price']) ?></p>
                <p><strong>Type:</strong> <?= htmlspecialchars($booking['type']) ?> for <?= htmlspecialchars($booking['stype']) ?></p>
            </div>
        </div>
        
        <div class="section">
            <h3 class="section-title">Booking Status</h3>
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <?php 
                $statusClass = '';
                switch($booking['status']) {
                    case 'confirmed': $statusClass = 'status-confirmed'; break;
                    case 'pending': $statusClass = 'status-pending'; break;
                    case 'active': $statusClass = 'status-active'; break;
                    case 'expired': $statusClass = 'status-expired'; break;
                    case 'cancelled': $statusClass = 'status-cancelled'; break;
                }
                ?>
                <span class="status-badge <?= $statusClass ?>">
                    <?= ucfirst(htmlspecialchars($booking['status'])) ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Status:</span>
                <?php 
                $paymentStatusClass = '';
                switch($booking['payment_status'] ?? 'pending') {
                    case 'completed': $paymentStatusClass = 'payment-completed'; break;
                    case 'pending': $paymentStatusClass = 'payment-pending'; break;
                    case 'failed': $paymentStatusClass = 'payment-failed'; break;
                }
                ?>
                <span class="status-badge <?= $paymentStatusClass ?>">
                    <?= ucfirst(htmlspecialchars($booking['payment_status'] ?? 'pending')) ?>
                </span>
            </div>
        </div>
        
        <div class="section">
            <h3 class="section-title">Tenant Information</h3>
            <div class="detail-row">
                <span class="detail-label">Name:</span>
                <span><?= htmlspecialchars($booking['name']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span><?= htmlspecialchars($booking['email']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Phone:</span>
                <span><?= htmlspecialchars($booking['phone']) ?></span>
            </div>
        </div>
        
        <div class="section">
            <h3 class="section-title">Lease Information</h3>
            <div class="detail-row">
                <span class="detail-label">Move-in Date:</span>
                <span><?= date('F d, Y', strtotime($booking['move_in_date'])) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Lease Term:</span>
                <span><?= $booking['lease_term'] ?> months</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">End Date:</span>
                <span><?= date('F d, Y', strtotime($booking['end_date'])) ?></span>
            </div>
        </div>
        
        <?php if (!empty($booking['message'])): ?>
        <div class="section">
            <h3 class="section-title">Additional Message</h3>
            <p><?= nl2br(htmlspecialchars($booking['message'])) ?></p>
        </div>
        <?php endif; ?>
        
        <div class="section">
            <h3 class="section-title">Payment Information</h3>
            <div class="detail-row">
                <span class="detail-label">Payment Status:</span>
                <span><?= ucfirst(htmlspecialchars($booking['payment_status'] ?? 'pending')) ?></span>
            </div>
            <?php if (!empty($booking['payment_method'])): ?>
            <div class="detail-row">
                <span class="detail-label">Payment Method:</span>
                <span><?= htmlspecialchars($booking['payment_method']) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($booking['payment_date'])): ?>
            <div class="detail-row">
                <span class="detail-label">Payment Date:</span>
                <span><?= date('F d, Y H:i:s', strtotime($booking['payment_date'])) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($booking['payment_transaction_id'])): ?>
            <div class="detail-row">
                <span class="detail-label">Transaction ID:</span>
                <span><?= htmlspecialchars($booking['payment_transaction_id']) ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="print-footer">
            <p>This document was generated on <?= date('F d, Y H:i:s') ?></p>
            <p>Remsko Real Estate Management System</p>
        </div>
        
        <div class="no-print text-center mt-4">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Print
            </button>
            <button onclick="window.close()" class="btn btn-secondary ml-2">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>
</body>
</html>
