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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - Remsko Real Estate</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .receipt {
            max-width: 800px;
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
        .receipt-header p {
            margin: 5px 0;
        }
        .receipt-body {
            margin-bottom: 20px;
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
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .receipt-label {
            font-weight: bold;
            width: 40%;
        }
        .receipt-value {
            width: 60%;
        }
        .receipt-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 14px;
        }
        .receipt-total {
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        .print-button {
            text-align: center;
            margin: 20px 0;
        }
        .print-button button {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        @media print {
            .print-button {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="print-button">
        <button onclick="window.print()">Print Receipt</button>
    </div>
    
    <div class="receipt">
        <div class="receipt-header">
            <h1>Remsko Real Estate</h1>
            <p>Payment Receipt</p>
            <p>Receipt #: <?php echo str_pad($payment_id, 6, '0', STR_PAD_LEFT); ?></p>
            <p>Date: <?php echo date('F d, Y', strtotime($payment['payment_date'])); ?></p>
        </div>
        
        <div class="receipt-body">
            <div class="receipt-section">
                <h3>Customer Information</h3>
                <div class="receipt-row">
                    <div class="receipt-label">Name:</div>
                    <div class="receipt-value"><?php echo htmlspecialchars($payment['uname']); ?></div>
                </div>
                <div class="receipt-row">
                    <div class="receipt-label">Email:</div>
                    <div class="receipt-value"><?php echo htmlspecialchars($payment['uemail']); ?></div>
                </div>
                <div class="receipt-row">
                    <div class="receipt-label">Phone:</div>
                    <div class="receipt-value"><?php echo htmlspecialchars($payment['uphone']); ?></div>
                </div>
            </div>
            
            <div class="receipt-section">
                <h3>Property Information</h3>
                <div class="receipt-row">
                    <div class="receipt-label">Property:</div>
                    <div class="receipt-value"><?php echo htmlspecialchars($payment['property_title']); ?></div>
                </div>
                <div class="receipt-row">
                    <div class="receipt-label">Location:</div>
                    <div class="receipt-value"><?php echo htmlspecialchars($payment['location']); ?>, <?php echo htmlspecialchars($payment['city']); ?></div>
                </div>
                <div class="receipt-row">
                    <div class="receipt-label">Booking Date:</div>
                    <div class="receipt-value"><?php echo date('F d, Y', strtotime($payment['date'])); ?></div>
                </div>
            </div>
            
            <div class="receipt-section">
                <h3>Payment Information</h3>
                <div class="receipt-row">
                    <div class="receipt-label">Payment Method:</div>
                    <div class="receipt-value"><?php echo htmlspecialchars($payment['payment_method']); ?></div>
                </div>
                <div class="receipt-row">
                    <div class="receipt-label">Transaction ID:</div>
                    <div class="receipt-value"><?php echo htmlspecialchars($payment['payment_transaction_id']); ?></div>
                </div>
                <div class="receipt-row">
                    <div class="receipt-label">Payment Status:</div>
                    <div class="receipt-value"><?php echo ucfirst($payment['payment_status']); ?></div>
                </div>
                
                <div class="receipt-total">
                    <div class="receipt-row">
                        <div class="receipt-label">Total Amount:</div>
                        <div class="receipt-value">ETB <?php echo number_format($payment['payment_amount'], 2); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="receipt-footer">
            <p>Thank you for choosing Remsko Real Estate!</p>
            <p>For any inquiries, please contact us at support@remsko.com</p>
        </div>
    </div>
</body>
</html>
