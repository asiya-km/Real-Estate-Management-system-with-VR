<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require("config.php");

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login1.php");
    exit();
}

// Initialize variables
$user_id = $_SESSION['uid'];
$booking_id = isset($_REQUEST['booking_id']) ? intval($_REQUEST['booking_id']) : 0;
$error = "";
$success = "";

// Validate booking ID
if ($booking_id <= 0) {
    header("Location: user_dashboard.php");
    exit();
}

// Modify the query to be more flexible with payment status
$query = "SELECT b.*, p.title, p.price, p.location, p.city, p.pimage, p.type, p.stype 
          FROM bookings b 
          JOIN property p ON b.property_id = p.pid 
          WHERE b.id = ? AND b.user_id = ? AND (b.payment_status IS NULL OR b.payment_status != 'completed')";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'ii', $booking_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$booking = mysqli_fetch_assoc($result)) {
    header("Location: user_dashboard.php");
    exit();
}

// Get user information
$user_query = "SELECT * FROM user WHERE uid = ?";
$user_stmt = mysqli_prepare($con, $user_query);
mysqli_stmt_bind_param($user_stmt, 'i', $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user = mysqli_fetch_assoc($user_result);

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Display error message if set
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Display success message if set
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Calculate booking fee (5% of property price)
$booking_fee = $booking['price'] * 0.05;
$booking_fee_formatted = number_format($booking_fee);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Payment - Remsko Real Estate</title>
    
    <!-- Include CSS files -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        .payment-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .payment-option {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: all 0.3s;
            cursor: pointer;
        }
        .payment-option:hover, .payment-option.selected {
            border-color: #28a745;
            background-color: #f8f9fa;
        }
        .payment-logo {
            height: 40px;
            margin-right: 15px;
        }
        .property-summary {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
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
                    <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>Payment</b></h2>
                </div>
                <div class="col-md-6">
                    <nav aria-label="breadcrumb" class="float-left float-md-right">
                        <ol class="breadcrumb bg-transparent m-0 p-0">
                            <li class="breadcrumb-item text-white"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="user_dashboard.php#bookings">My Bookings</a></li>
                            <li class="breadcrumb-item active">Payment</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content -->
    <div class="full-row">
        <div class="container">
            <div class="payment-container">
                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <h3 class="mb-4">Complete Your Payment</h3>
                
                <div class="property-summary">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="admin/property/<?php echo htmlspecialchars($booking['pimage']); ?>" alt="Property" class="img-fluid rounded">
                        </div>
                        <div class="col-md-8">
                            <h4 class="text-secondary"><?php echo htmlspecialchars($booking['title']); ?></h4>
                            <p><i class="fas fa-map-marker-alt text-success"></i> <?php echo htmlspecialchars($booking['location']); ?>, <?php echo htmlspecialchars($booking['city']); ?></p>
                            
                            <div class="card bg-light p-3 mb-3">
                                <h5>Payment Details</h5>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td>Property Price:</td>
                                        <td class="text-right">ETB <?php echo number_format($booking['price']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Booking Charge (Refundable, 5%):</td>
                                        <td class="text-right">ETB <?php echo $booking_fee_formatted; ?></td>
                                    </tr>
                                    <tr class="border-top">
                                        <th>Total Amount:</th>
                                        <th class="text-right text-success">ETB <?php echo $booking_fee_formatted; ?></th>
                                    </tr>
                                </table>
                                <p class="text-muted small mt-2 mb-0">* The booking charge is fully refundable upon property visit or contract signing.</p>
                            </div>
                            
                            <p><strong>Booking Reference:</strong> #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Options -->
                <h5 class="mb-3">Select Payment Method</h5>
                
                <!-- Chapa Payment Option -->
                <div class="payment-option selected" data-method="chapa">
                    <div class="form-check">
                        <input class="form-check-input payment-method-radio" type="radio" name="payment_method_selector" id="chapa" value="chapa" checked>
                        <label class="form-check-label d-flex align-items-center" for="chapa">
                            <img src="images/chapa-logo.png" alt="Chapa" class="payment-logo">
                            <div>
                                <strong>Pay with Chapa</strong>
                                <p class="mb-0 text-muted">Secure payment with credit/debit card</p>
                            </div>
                        </label>
                    </div>
                    
                    <div class="chapa-fields mt-3">
                        <div class="alert alert-info">
                            <p>You will be redirected to Chapa's secure payment page to complete your payment of <strong>ETB <?php echo $booking_fee_formatted; ?></strong> (refundable booking charge).</p>
                            <p>After completing the payment, you will be redirected back to our website.</p>
                        </div>
                        
                        <form action="process_payment.php" method="post" target="_blank">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                            <input type="hidden" name="payment_method" value="chapa">
                            <input type="hidden" name="amount" value="<?php echo $booking_fee; ?>">
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg">Proceed to Payment</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Telebirr Payment Option -->
                <div class="payment-option" data-method="telebirr">
                    <div class="form-check">
                        <input class="form-check-input payment-method-radio" type="radio" name="payment_method_selector" id="telebirr" value="telebirr">
                        <label class="form-check-label d-flex align-items-center" for="telebirr">
                            <img src="images/telebirr-logo.png" alt="Telebirr" class="payment-logo">
                            <div>
                                <strong>Pay with Telebirr</strong>
                                <p class="mb-0 text-muted">Direct mobile money payment</p>
                            </div>
                        </label>
                    </div>
                    
                    <div class="telebirr-fields mt-3" style="display: none;">
                        <form action="process_telebirr.php" method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                            <input type="hidden" name="amount" value="<?php echo $booking_fee; ?>">
                            
                            <div class="form-group">
                                <label for="telebirr_phone">Your Telebirr Mobile Number</label>
                                <input type="tel" class="form-control" id="telebirr_phone" name="phone" placeholder="09xxxxxxxx" pattern="09[0-9]{8}" required>
                            </div>
                            <div class="form-group">
                                <label for="transaction_id">Telebirr Transaction ID/Reference</label>
                                <input type="text" class="form-control" id="transaction_id" name="transaction_id" required>
                                <small class="form-text text-muted">You can find this in your Telebirr app transaction history or SMS confirmation.</small>
                            </div>
                            
                            <p class="mt-3"><strong>Telebirr Direct Payment Instructions:</strong></p>
                            <div class="alert alert-info">
                                <ol>
                                    <li>Open your Telebirr app</li>
                                    <li>Select "Send Money" from the main menu</li>
                                    <li>Enter our Telebirr number: <strong>0912345678</strong></li>
                                    <li>Enter the exact amount: <strong>ETB <?php echo $booking_fee_formatted; ?></strong> (refundable booking charge)</li>
                                    <li>In the description/reason field, enter your booking reference: <strong>#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></strong></li>
                                    <li>Confirm the payment with your PIN</li>
                                    <li>After completing the payment, click "I've Completed Payment" below</li>
                                </ol>
                            </div>
                            
                            <div class="text-center my-4">
                                <img src="images/telebirr-qr.jpg" alt="Telebirr QR Code" style="max-width: 200px;">
                                <p class="mt-2"><strong>Telebirr Account:</strong> 0912345678</p>
                                <p class="text-danger">Please include your booking reference (#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?>) in the payment description!</p>
                            </div>
                            
                            <div class="card bg-light p-3 mb-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>Payment Details</h5>
                                        <p><strong>Telebirr Number:</strong> 0912345678</p>
                                        <p><strong>Account Name:</strong> Remsko Real Estate</p>
                                        <p><strong>Amount:</strong> ETB <?php echo $booking_fee_formatted; ?></p>
                                        <p><strong>Reference:</strong> #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></p>
                                    </div>
                                    <div class="col-md-6 text-center">
                                        <img src="images/telebirr-icon.png" alt="Telebirr" style="max-width: 100px;">
                                        <p class="mt-2 text-danger"><strong>Important:</strong> Include your booking reference in the payment description!</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning">
                                <strong>Note:</strong> This is a refundable booking charge of ETB <?php echo $booking_fee_formatted; ?> (5% of property price). The full property price will be discussed during your property visit.
                            </div>
                            
                            <div class="text-center mt-4">
                                <button type="submit" name="submit_payment" class="btn btn-success btn-lg">I've Completed Payment</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- CBE Payment Option -->
                <div class="payment-option" data-method="cbe">
                    <div class="form-check">
                        <input class="form-check-input payment-method-radio" type="radio" name="payment_method_selector" id="cbe" value="cbe">
                        <label class="form-check-label d-flex align-items-center" for="cbe">
                            <img src="images/cbe-logo.png" alt="CBE" class="payment-logo">
                            <div>
                                <strong>Pay with CBE</strong>
                                <p class="mb-0 text-muted">Commercial Bank of Ethiopia QR payment</p>
                            </div>
                        </label>
                    </div>
                    
                    <div class="cbe-fields mt-3" style="display: none;">
                        <form action="process_cbe.php" method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                            <input type="hidden" name="amount" value="<?php echo $booking_fee; ?>">
                            
                            <div class="form-group">
                                <label for="cbe_phone">Your CBE Mobile Number</label>
                                <input type="tel" class="form-control" id="cbe_phone" name="phone" placeholder="09xxxxxxxx" pattern="09[0-9]{8}" required>
                            </div>
                            <div class="form-group">
                                <label for="cbe_transaction_id">CBE Transaction ID/Reference</label>
                                <input type="text" class="form-control" id="cbe_transaction_id" name="transaction_id" required>
                                <small class="form-text text-muted">You can find this in your CBE app transaction history or SMS confirmation.</small>
                            </div>
                            
                            <p class="mt-3"><strong>CBE Direct Payment Instructions:</strong></p>
                            <div class="alert alert-info">
                                <ol>
                                    <li>Open your CBE mobile banking app</li>
                                    <li>Select "Scan QR" from the main menu</li>
                                    <li>Scan the QR code below</li>
                                    <li>Enter the exact amount: <strong>ETB <?php echo $booking_fee_formatted; ?></strong> (refundable booking charge)</li>
                                    <li>In the description/reason field, enter your booking reference: <strong>#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></strong></li>
                                    <li>Confirm the payment with your PIN</li>
                                    <li>After completing the payment, click "I've Completed Payment" below</li>
                                </ol>
                            </div>
                            
                            <div class="text-center my-4">
                                <img src="images/cbe-qr.jpg" alt="CBE QR Code" style="max-width: 200px;">
                                <p class="mt-2"><strong>CBE Account:</strong> 1000123456789</p>
                                <p class="text-danger">Please include your booking reference (#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?>) in the payment description!</p>
                            </div>
                            
                            <div class="card bg-light p-3 mb-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>Payment Details</h5>
                                        <p><strong>CBE Account Number:</strong> 1000123456789</p>
                                        <p><strong>Account Name:</strong> Remsko Real Estate</p>
                                        <p><strong>Amount:</strong> ETB <?php echo $booking_fee_formatted; ?></p>
                                        <p><strong>Reference:</strong> #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></p>
                                    </div>
                                    <div class="col-md-6 text-center">
                                        <img src="images/cbe-icon.png" alt="CBE" style="max-width: 100px;">
                                        <p class="mt-2 text-danger"><strong>Important:</strong> Include your booking reference in the payment description!</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning">
                                <strong>Note:</strong> This is a refundable booking charge of ETB <?php echo $booking_fee_formatted; ?> (5% of property price). The full property price will be discussed during your property visit.
                            </div>
                            
                            <div class="text-center mt-4">
                                <button type="submit" name="submit_payment" class="btn btn-success btn-lg">I've Completed Payment</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
     
    <?php include("include/footer.php"); ?>
    
    <!-- JavaScript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
        // Handle payment method selection
        $('.payment-method-radio').change(function() {
            // Hide all payment fields
            $('.chapa-fields, .telebirr-fields, .cbe-fields').hide();
            
            // Remove selected class from all options
            $('.payment-option').removeClass('selected');
            
            // Add selected class to current option
            $(this).closest('.payment-option').addClass('selected');
            
            // Show fields for selected payment method
            var method = $(this).val();
            if (method === 'chapa') {
                $('.chapa-fields').show();
            } else if (method === 'telebirr') {
                $('.telebirr-fields').show();
            } else if (method === 'cbe') {
                $('.cbe-fields').show();
            }
        });
        
        // Handle clicking on payment option div
        $('.payment-option').click(function() {
            $(this).find('.payment-method-radio').prop('checked', true).trigger('change');
        });
        
        // Pre-select Chapa payment method on page load and show its fields
        $('#chapa').prop('checked', true);
        $('.chapa-fields').show();
    });
    </script>
</body>
</html>
