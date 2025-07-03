<?php
session_start();
require("../config.php");

// Check if admin is logged in
if (!isset($_SESSION['auser'])) {
    header("location:index.php");
    exit();
}

// Check if payment ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("location:payment_history.php");
    exit();
}

$payment_id = intval($_GET['id']);

// Get payment details
$query = "SELECT b.*, u.uname, u.uemail, u.uphone, p.title as property_title, p.price as property_price, 
          p.location, p.city 
          FROM bookings b 
          JOIN user u ON b.user_id = u.uid 
          JOIN property p ON b.property_id = p.pid 
          WHERE b.id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'i', $payment_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$payment = mysqli_fetch_assoc($result)) {
    header("location:payment_history.php");
    exit();
}

// Create email content
$to = $payment['uemail'];
$subject = "Payment Receipt - Remsko Real Estate";

$message = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .receipt {
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        .receipt-header h1 {
            color: #28a745;
            margin: 0;
        }
        .receipt-section {
            margin-bottom: 20px;
        }
        .receipt-section h3 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .receipt-row {
            margin-bottom: 10px;
        }
        .receipt-label {
            font-weight: bold;
        }
        .receipt-total {
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        .receipt-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="receipt-header">
            <h1>Remsko Real Estate</h1>
            <p>Payment Receipt</p>
            <p>Receipt #: ' . str_pad($payment_id, 6, '0', STR_PAD_LEFT) . '</p>
            <p>Date: ' . date('F d, Y', strtotime($payment['payment_date'])) . '</p>
        </div>
        
        <div class="receipt-section">
            <h3>Customer Information</h3>
            <div class="receipt-row">
                <span class="receipt-label">Name:</span> ' . htmlspecialchars($payment['uname']) . '
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Email:</span> ' . htmlspecialchars($payment['uemail']) . '
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Phone:</span> ' . htmlspecialchars($payment['uphone']) . '
            </div>
        </div>
        
        <div class="receipt-section">
            <h3>Property Information</h3>
            <div class="receipt-row">
                <span class="receipt-label">Property:</span> ' . htmlspecialchars($payment['property_title']) . '
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Location:</span> ' . htmlspecialchars($payment['location']) . ', ' . htmlspecialchars($payment['city']) . '
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Booking Date:</span> ' . date('F d, Y', strtotime($payment['date'])) . '
            </div>
        </div>
        
        <div class="receipt-section">
            <h3>Payment Information</h3>
            <div class="receipt-row">
                <span class="receipt-label">Payment Method:</span> ' . htmlspecialchars($payment['payment_method']) . '
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Transaction ID:</span> ' . htmlspecialchars($payment['payment_transaction_id']) . '
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Payment Status:</span> ' . ucfirst($payment['payment_status']) . '
            </div>
            
            <div class="receipt-total">
                <div class="receipt-row">
                    <span class="receipt-label">Total Amount:</span> ETB ' . number_format($payment['payment_amount'], 2) . '
                </div>
            </div>
        </div>
        
        <div class="receipt-footer">
            <p>Thank you for choosing Remsko Real Estate!</p>
            <p>For any inquiries, please contact us at support@remsko.com</p>
        </div>
    </div>
</body>
</html>';

// Set content-type header for sending HTML email
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: Remsko Real Estate <noreply@remsko.com>" . "\r\n";

// Send email
$mail_sent = mail($to, $subject, $message, $headers);

// Update database to record that receipt was sent
if ($mail_sent) {
    $update_query = "UPDATE bookings SET 
                    admin_notes = CONCAT(IFNULL(admin_notes, ''), '\nReceipt emailed to customer on ".date('Y-m-d H:i:s')."') 
                    WHERE id = ?";
    $stmt = mysqli_prepare($con, $update_query);
    mysqli_stmt_bind_param($stmt, 'i', $payment_id);
    mysqli_stmt_execute($stmt);
}

// Redirect back to payment details page with success/error message
if ($mail_sent) {
    header("location:view_payment.php?id=" . $payment_id . "&email_sent=1");
} else {
    header("location:view_payment.php?id=" . $payment_id . "&email_error=1");
}
exit();
?>
