<?php
session_start();
include("config.php");

if (!isset($_SESSION['uid'])) {
    header("location:login.php");
    exit;
}


$user_id = $_SESSION['uid'];
if(isset($_POST['add']))
{

}

// Verify booking belongs to user
$check_booking = mysqli_query($con, "SELECT * FROM bookings WHERE id='$booking_id' AND user_id='$user_id'");
if (mysqli_num_rows($check_booking) == 0) {
    die("Invalid booking");
}

$booking = mysqli_fetch_assoc($check_booking);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Include your header CSS/JS -->
    <title>Payment Gateway</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="shortcut icon" href="images/favicon.ico">

<!--	Fonts
	========================================================-->
<link href="https://fonts.googleapis.com/css?family=Muli:400,400i,500,600,700&amp;display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Comfortaa:400,700" rel="stylesheet">

<!--	Css Link
	========================================================-->
<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="css/bootstrap-slider.css">
<link rel="stylesheet" type="text/css" href="css/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="css/layerslider.css">
<link rel="stylesheet" type="text/css" href="css/color.css" id="color-change">
<link rel="stylesheet" type="text/css" href="css/owl.carousel.min.css">
<link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="fonts/flaticon/flaticon.css">
<link rel="stylesheet" type="text/css" href="css/style.css">

</head>
<body>
<?php include("include/header.php"); ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Complete Payment</h4>
                </div>
                <div class="card-body">
                   
                    <form method="post">
                        <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                        
                        <div class="form-group mt-4">
                            <label>Select Payment Method:</label>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="cbe" name="payment_method" value="CBEBirr" class="custom-control-input" required>
                                <label class="custom-control-label" for="cbe">CBEBirr</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="telebirr" name="payment_method" value="Telebirr" class="custom-control-input" required>
                                <label class="custom-control-label" for="telebirr">Telebirr</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="phone">Mobile Number</label>
                            <input type="tel" class="form-control" name="phone" required 
                                   placeholder="Enter your mobile number">
                        </div>

                        <div class="form-group">
                            <label for="pin">PIN Code</label>
                            <input type="password" class="form-control" name="pin" required 
                                   placeholder="Enter your mobile money PIN" maxlength="4">
                        </div>

                        <button type="submit" class="btn btn-success btn-block" name="pay" >Complete Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("include/footer.php"); ?>
</body>
</html>