<?php
include("config.php");
$error = "";
$msg = "";

function validatePhone($phone) {
    // Updated regex to support international phone numbers
    return preg_match('/^\+[1-9]\d{1,14}$/', $phone);
}

function validatePassword($pass) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $pass);
}

if (isset($_REQUEST['reg'])) {
    $name = $_REQUEST['name'];
    $email = $_REQUEST['email'];
    $phone = $_REQUEST['full_phone']; // Use full_phone from hidden input
    $pass = $_REQUEST['pass'];
    $utype = $_REQUEST['utype'];

    $uimage = $_FILES['uimage']['name'];
    $temp_name1 = $_FILES['uimage']['tmp_name'];

    $query = "SELECT * FROM user WHERE uemail='$email'";
    $res = mysqli_query($con, $query);
    $num = mysqli_num_rows($res);

    if ($num == 1) {
        $error = "<p class='alert alert-warning'>Email Id already exists</p>";
    } else {
        if (!empty($name) && !empty($email) && !empty($phone) && !empty($pass) && !empty($uimage)) {
            if (!validatePassword($pass)) {
                $error = "<p class='alert alert-warning'>Password must be 8+ chars with uppercase, lowercase, number, and special character</p>";
            } elseif (!validatePhone($phone)) {
                $error = "<p class='alert alert-warning'>Invalid phone number. Please include the country code.</p>";
            } else {
                $sql = "INSERT INTO user (uname, uemail, uphone, upass, utype, uimage) VALUES ('$name', '$email', '$phone', '$pass', '$utype', '$uimage')";
                $result = mysqli_query($con, $sql);
                move_uploaded_file($temp_name1, "admin/user/$uimage");

                if ($result) {
                    $msg = "<p class='alert alert-success'>Registered successfully!</p>";
                    header("location:login.php");
                } else {
                    $error = "<p class='alert alert-warning'>Registration failed</p>";
                }
            }
        } else {
            $error = "<p class='alert alert-warning'>Please fill all the fields</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    
<!-- Meta Tags -->
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
<link rel="stylesheet" type="text/css" href="css/color.css">
<link rel="stylesheet" type="text/css" href="css/owl.carousel.min.css">
<link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="fonts/flaticon/flaticon.css">
<link rel="stylesheet" type="text/css" href="css/style.css">
<link rel="stylesheet" type="text/css" href="css/login.css">

    <title>Register</title>
    <!-- Include your CSS files here -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
</head>
<body>
<?php include("include/header.php"); ?>

<div class="page-wrappers login-body full-row bg-gray">
    <div class="login-wrapper">
        <div class="container">
            <div class="loginbox">
                <div class="login-right">
                    <div class="login-right-wrap">
                        <h1>Register</h1>
                        <p class="account-subtitle">Access to our dashboard</p>
                        <?php echo $error; ?><?php echo $msg; ?>
                        <!-- Form -->
                        <form method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <input type="text" name="name" class="form-control" placeholder="Your Name*" required>
                            </div>
                            <div class="form-group">
                                <input type="email" name="email" class="form-control" placeholder="Your Email*" required>
                            </div>
                            <div class="form-group">
                                <input type="tel" id="phone" name="phone" class="form-control" placeholder="Your Phone*">
                                <input type="hidden" id="full_phone" name="full_phone">
                            </div>
                            <div class="form-group">
                                <input type="password" name="pass" class="form-control" placeholder="Your Password*" required>
                            </div>
                            <div class="form-check-inline">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="utype" value="user" checked>User
                                </label>
                            </div>
                            <div class="form-check-inline">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="utype" value="agent">Agent
                                </label>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label"><b>User Image</b></label>
                                <input class="form-control" name="uimage" type="file" required>
                            </div>
                            <button class="btn btn-success" name="reg" value="Register" type="submit">Register</button>
                        </form>
                        <div class="login-or">
                            <span class="or-line"></span>
                            <span class="span-or">or</span>
                        </div>
                        <div class="text-center dont-have">Already have an account? <a href="login.php">Login</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("include/footer.php"); ?>

<!-- Include intl-tel-input JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
<script>
    // Initialize intl-tel-input
    const phoneInput = document.querySelector("#phone");
    const fullPhoneInput = document.querySelector("#full_phone");

    const iti = window.intlTelInput(phoneInput, {
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
        separateDialCode: true,
        preferredCountries: ['us', 'gb', 'in'], // Default preferred countries
        initialCountry: "auto", // Auto-detect user's country
        geoIpLookup: function(callback) {
            fetch("https://ipapi.co/json")
                .then(response => response.json())
                .then(data => callback(data.country_code))
                .catch(() => callback("us")); // Fallback to US if IP lookup fails
        }
    });

    // Update hidden input with full phone number (including country code)
    phoneInput.addEventListener("input", function() {
        if (iti.isValidNumber()) {
            fullPhoneInput.value = iti.getNumber();
        } else {
            fullPhoneInput.value = "";
        }
    });
</script>
</body>
</html>