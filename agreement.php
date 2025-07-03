<?php
session_start();
require("config.php");

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login1.php");
    exit();
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize variables
$msg = "";
$error = "";
$user_id = $_SESSION['uid'];

// Validate booking ID
if (isset($_REQUEST['booking_id']) && is_numeric($_REQUEST['booking_id'])) {
    $booking_id = intval($_REQUEST['booking_id']);
    
    // Get booking details with property information
    $query = "SELECT b.*, p.title, p.price, p.location, p.city, p.pimage, p.type, p.stype 
              FROM bookings b 
              JOIN property p ON b.property_id = p.pid 
              WHERE b.id = ? AND b.user_id = ?";
    
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $booking_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$booking = mysqli_fetch_assoc($result)) {
        $msg = "<p class='alert alert-danger'>Booking not found or unauthorized access</p>";
        header("Location: user_dashboard.php?msg=" . urlencode($msg));
        exit();
    }
} else {
    $msg = "<p class='alert alert-danger'>Invalid booking ID</p>";
    header("Location: user_dashboard.php?msg=" . urlencode($msg));
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_agreement'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid form submission";
    } else {
        // Update booking status to agreement signed
        $update_query = "UPDATE bookings SET agreement_signed = 1, agreement_date = NOW() WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt, 'ii', $booking_id, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Store booking amount in session for payment (5% of property price)
            $_SESSION['booking_amount'] = $booking['price'] * 0.05;
            $_SESSION['booking_id'] = $booking_id;
            
            // Redirect to payment page
            header("Location: payment.php?booking_id=" . $booking_id);
            exit();
        } else {
            $error = "Failed to update agreement status: " . mysqli_error($con);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Booking Agreement - Real Estate PHP</title>
    
    <!-- Include CSS files -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        .agreement-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .property-summary {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .agreement-text {
            height: 400px;
            overflow-y: auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <?php include("include/header.php"); ?>
    
    <!-- Banner -->
    <div class="banner-full-row page-banner" style="background-image:url('images/breadcromb.jpg');">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>Booking Agreement</b></h2>
                </div>
                <div class="col-md-6">
                    <nav aria-label="breadcrumb" class="float-left float-md-right">
                        <ol class="breadcrumb bg-transparent m-0 p-0">
                            <li class="breadcrumb-item text-white"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Booking Agreement</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content -->
    <div class="full-row">
        <div class="container">
            <div class="agreement-container">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <h3 class="mb-4 text-secondary">Property Booking Agreement</h3>
                
                <div class="property-summary">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="admin/property/<?php echo htmlspecialchars($booking['pimage']); ?>" alt="Property" class="img-fluid rounded">
                        </div>
                        <div class="col-md-8">
                            <h4 class="text-secondary"><?php echo htmlspecialchars($booking['title']); ?></h4>
                            <p><i class="fas fa-map-marker-alt text-success"></i> <?php echo htmlspecialchars($booking['location']); ?>, <?php echo htmlspecialchars($booking['city']); ?></p>
                            <p class="text-success h5">ETB <?php echo number_format($booking['price']); ?></p>
                            <p><strong>Type:</strong> <?php echo htmlspecialchars($booking['type']); ?> | <strong>Status:</strong> For <?php echo htmlspecialchars($booking['stype']); ?></p>
                            <p><strong>Booking Date:</strong> <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></p>
                            <p><strong>Booking Fee (5%):</strong> ETB <?php echo number_format($booking['price'] * 0.05); ?> <span class="text-danger">(Non-refundable)</span></p>
                        </div>
                    </div>
                </div>
                
                <h4 class="mb-3 text-secondary">Terms and Conditions</h4>
                
                <div class="agreement-text">
                    <h5>PROPERTY BOOKING AGREEMENT</h5>
                    <p>This booking agreement is entered into on <?php echo date('F j, Y'); ?> between:</p>
                    
                    <p><strong>Property Provider:</strong> Remsko Real Estate Company, a legally registered real estate company in Ethiopia.</p>
                    
                    <p><strong>Client:</strong> <?php echo htmlspecialchars($booking['name']); ?></p>
                    
                    <p><strong>Property:</strong> <?php echo htmlspecialchars($booking['title']); ?> located at <?php echo htmlspecialchars($booking['location']); ?>, <?php echo htmlspecialchars($booking['city']); ?>, Ethiopia.</p>
                    
                    <h6>1. BOOKING FEE AND PAYMENT</h6>
                    <p>1.1 The Client agrees to pay a non-refundable booking fee of 5% of the property price, amounting to ETB <?php echo number_format($booking['price'] * 0.05); ?>.</p>
                    <p>1.2 This booking fee reserves the Client's interest in the property for a period of 30 days from the date of payment.</p>
                    <p>1.3 The booking fee is NON-REFUNDABLE under any circumstances.</p>
                    
                    <h6>2. PURPOSE OF BOOKING</h6>
                    <p>2.1 This booking reserves the Client's interest in the property and prevents the property from being sold or rented to another party during the booking period.</p>
                    <p>2.2 This booking does not constitute a final sale or rental agreement.</p>
                    <p>2.3 A separate sale or rental agreement must be executed within the booking period to complete the transaction.</p>
                    
                    <h6>3. BOOKING PERIOD</h6>
                    <p>3.1 The booking period is 30 days from the date of payment of the booking fee.</p>
                    <p>3.2 If the Client does not proceed with the transaction within the booking period, the booking will expire, and the property will be made available to other interested parties.</p>
                    <p>3.3 The booking fee will not be refunded if the Client decides not to proceed with the transaction.</p>
                    
                    <h6>4. PROPERTY VIEWING</h6>
                    <p>4.1 The Client is entitled to view the property during the booking period by appointment.</p>
                    <p>4.2 The Client must provide at least 24 hours' notice for property viewing appointments.</p>
                    
                    <h6>5. FINAL TRANSACTION</h6>
                    <p>5.1 If the Client decides to proceed with the transaction, a separate sale or rental agreement will be prepared.</p>
                    <p>5.2 The booking fee will be credited towards the total purchase price or rental deposit.</p>
                    
                    <h6>6. GOVERNING LAW</h6>
                    <p>6.1 This Agreement shall be governed by the laws of Ethiopia.</p>
                    
                    <h6>7. ENTIRE AGREEMENT</h6>
                    <p>7.1 This Agreement contains the entire understanding between the parties regarding the booking of the property.</p>
                    
                    <p>By accepting this Agreement, the Client acknowledges having read, understood, and agreed to all terms and conditions stated herein, particularly the non-refundable nature of the booking fee.</p>
                </div>
                
                <form method="post" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="agree_terms" required>
                            <label class="form-check-label" for="agree_terms">
                                I have read, understood, and agree to the terms and conditions of this booking agreement, including the 5% non-refundable booking fee.
                            </label>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" name="accept_agreement" class="btn btn-success btn-lg" id="submit_btn" disabled>Accept & Pay 5% Booking Fee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include("include/footer.php"); ?>
    
    <!-- JavaScript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        // Enable submit button only when checkbox is checked
        document.getElementById('agree_terms').addEventListener('change', function() {
            document.getElementById('submit_btn').disabled = !this.checked;
        });
    </script>
</body>
</html>
