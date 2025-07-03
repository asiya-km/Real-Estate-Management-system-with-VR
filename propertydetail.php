<?php 
ini_set('session.cache_limiter','public');
session_cache_limiter(false);
session_start();
include("config.php");
?>
<?php
// At the top of propertydetail.php, after session_start()
// Check if user just logged in and needs to be redirected
if (isset($_SESSION['uid']) && isset($_SESSION['redirect_after_login'])) {
    $redirectUrl = $_SESSION['redirect_after_login'];
    unset($_SESSION['redirect_after_login']); // Clear the session variable
    
    // Redirect to the stored URL
    header("Location: $redirectUrl");
    exit();
}
?>
<?php

// Allow all users to view basic property details
// No login redirect here

// Define a variable to track login status for feature access control
$isLoggedIn = isset($_SESSION['uid']);
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
<meta name="description" content="Real Estate PHP">
<meta name="keywords" content="">
<meta name="author" content="Unicoder">
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css"/>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>


<!--	Title
	=========================================================-->
<title>Real Estate PHP</title>
<style>
    .booking-form {
      max-width: 400px;
      margin: 30px auto;
      padding: 20px;
      border-radius: 10px;
      background-color: #f5f5f5;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      font-family: Arial, sans-serif;
    }

    .booking-form h2 {
      text-align: center;
      margin-bottom: 20px;
    }

    .booking-form label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }

    .booking-form input {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .booking-form button {
      width: 100%;
      padding: 12px;
      background-color: #00b3b3;
      color: white;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      cursor: pointer;
    }

    .booking-form button:hover {
      background-color: #008080;
    }
    
    /* Login required overlay */
    .login-required-overlay {
      position: relative;
    }
    
    .login-required-overlay .content-blur {
      filter: blur(5px);
      pointer-events: none;
    }
    
    .login-required-overlay .login-message {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: rgba(255,255,255,0.9);
      padding: 20px;
      border-radius: 8px;
      text-align: center;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
      z-index: 100;
      width: 80%;
      max-width: 400px;
    }
    
    .login-message h4 {
      margin-bottom: 15px;
      color: #333;
    }
    
    .login-message .btn {
      margin-top: 10px;
    }
  </style>
</head>
<body>

<div id="page-wrapper">
    <div class="row"> 
        <!--	Header start  -->
        <?php include("include/header.php");?>
        <!--	Header end  -->
        
        <!--	Banner   --->
        <div class="banner-full-row page-banner" style="background-image:url('images/breadcromb.jpg');">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>Property Detail</b></h2>
                    </div>
                    <div class="col-md-6">
                        <nav aria-label="breadcrumb" class="float-left float-md-right">
                            <ol class="breadcrumb bg-transparent m-0 p-0">
                                <li class="breadcrumb-item text-white"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Property Detail</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
         <!--	Banner   --->

		
        <div class="full-row">
            <div class="container">
                <div class="row">
				
					<?php
						$id = isset($_REQUEST['pid']) ? intval($_REQUEST['pid']) : 0;
                        
                        if ($id <= 0) {
                            echo '<div class="col-md-12"><div class="alert alert-danger">Invalid property ID</div></div>';
                            include("include/footer.php");
                            echo '</div></div></body></html>';
                            exit;
                        }
                        
                        // Use prepared statement for security
                       $stmt = mysqli_prepare($con, "SELECT property.*, user.uname, user.uemail, user.uphone, user.uimage FROM property LEFT JOIN user ON property.uid=user.uid WHERE property.pid=?");
                        mysqli_stmt_bind_param($stmt, "i", $id);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        
                        if (!$result || mysqli_num_rows($result) == 0) {
                            echo '<div class="col-md-12"><div class="alert alert-danger">Property not found</div></div>';
                            include("include/footer.php");
                            echo '</div></div></body></html>';
                            exit;
                        }
                        
                        $row = mysqli_fetch_assoc($result);
// Add fallback for user information if not available
if ($row) {
    // Set default values for user data if not available
    if (!isset($row['uname']) || empty($row['uname'])) {
        $row['uname'] = 'Admin';
    }
    if (!isset($row['uemail']) || empty($row['uemail'])) {
        $row['uemail'] = 'admin@example.com';
    }
    if (!isset($row['uphone']) || empty($row['uphone'])) {
        $row['uphone'] = 'N/A';
    }
    if (!isset($row['uimage']) || empty($row['uimage'])) {
        $row['uimage'] = 'default-user.jpg'; // Make sure this default image exists
    }
}

					?>
				  
                    <div class="col-lg-8">
                        <div class="row">
                            <div class="col-md-12">
                                <div id="single-property" style="width:1200px; height:700px; margin:30px auto 50px;"> 
                                    <!-- Slide 1-->
                                    <div class="ls-slide" data-ls="duration:7500; transition2d:5; kenburnszoom:in; kenburnsscale:1.2;"> 
                                        <img width="1920" height="1080" src="admin/property/<?php echo htmlspecialchars($row['pimage']); ?>" class="ls-bg" alt="Property Image" /> 
                                    </div>
                                    
                                    <!-- Slide 2-->
                                    <div class="ls-slide" data-ls="duration:7500; transition2d:5; kenburnszoom:in; kenburnsscale:1.2;"> 
                                        <img width="1920" height="1080" src="admin/property/<?php echo htmlspecialchars($row['pimage1']); ?>" class="ls-bg" alt="Property Image" /> 
                                    </div>
                                    
                                    <!-- Slide 3-->
                                    <div class="ls-slide" data-ls="duration:7500; transition2d:5; kenburnszoom:in; kenburnsscale:1.2;"> 
                                        <img width="1920" height="1080" src="admin/property/<?php echo htmlspecialchars($row['pimage2']); ?>" class="ls-bg" alt="Property Image" /> 
                                    </div>
									
								
                                </div>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="bg-success d-table px-3 py-2 rounded text-white text-capitalize">For <?php echo htmlspecialchars($row['stype']); ?></div>
                                <h5 class="mt-2 text-secondary text-capitalize"><?php echo htmlspecialchars($row['title']); ?></h5>
                                <span class="mb-sm-20 d-block text-capitalize"><i class="fas fa-map-marker-alt text-success font-12"></i> &nbsp;<?php echo htmlspecialchars($row['city']); ?></span>
                            </div>
                            <div class="col-md-6">
                                <div class="text-success text-left h5 my-2 text-md-right">ETB <?php echo number_format($row['price']); ?></div>
                                <div class="text-left text-md-right">Price</div>
        
         <div class="text-left text-md-right mt-3">
    <?php if ($row['status'] == 'available'): ?>
        <div class="booking-options mt-4">
            <div class="row">
                <div class="col-md-6">
                    <?php if ($isLoggedIn): ?>
                        <!-- Direct link for logged in users -->
                        <a href="book_property.php?id=<?php echo intval($row['pid']); ?>" class="btn btn-success btn-lg btn-block">
                            <i class="fas fa-check-circle mr-2"></i> Book Now
                        </a>
                    <?php else: ?>
                        <!-- For non-logged in users, set session and redirect to login -->
                        <a href="set_redirect.php?redirect=<?php echo urlencode('book_property.php?id=' . intval($row['pid'])); ?>" class="btn btn-success btn-lg btn-block">
                            <i class="fas fa-check-circle mr-2"></i> Book Now
                        </a>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <?php if ($isLoggedIn): ?>
                        <!-- Direct link for logged in users -->
                        <a href="schedule_visit.php?id=<?php echo intval($row['pid']); ?>" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-calendar-alt mr-2"></i> Schedule Visit
                        </a>
                    <?php else: ?>
                        <!-- For non-logged in users, set session and redirect to login -->
                        <a href="set_redirect.php?redirect=<?php echo urlencode('schedule_visit.php?id=' . intval($row['pid'])); ?>" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-calendar-alt mr-2"></i> Schedule Visit
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning mt-4">
            <i class="fas fa-exclamation-triangle mr-2"></i> This property is currently not available for booking.
        </div>
    <?php endif; ?>
</div>

       
                            </div>
                        </div>
                        <div class="property-details">
                            <div class="bg-gray property-quantity px-4 pt-4 w-100">
                                <ul>
                                    <li><span class="text-secondary"><?php echo htmlspecialchars($row['size']); ?></span> Sqft</li>
                                    <li><span class="text-secondary"><?php echo htmlspecialchars($row['bedroom']); ?></span> Bedroom</li>
                                    <li><span class="text-secondary"><?php echo htmlspecialchars($row['bathroom']); ?></span> Bathroom</li>
                                    <li><span class="text-secondary"><?php echo htmlspecialchars($row['location']  ); ?></span> Location</li>
                                    <li><span class="text-secondary"><?php echo htmlspecialchars($row['floor'] ); ?></span> Floor</li>
                                    <li><span class="text-secondary"><?php echo htmlspecialchars($row['kitchen']); ?></span> Kitchen</li>
                                </ul>
                            </div>
                            <h4 class="text-secondary my-4">Description</h4>
                            <p><?php echo htmlspecialchars($row['pcontent']); ?></p>
                            
                            <h5 class="mt-5 mb-4 text-secondary">Property Summary</h5>
                            <div class="table-striped font-14 pb-2">
                                <table class="w-100">
                                    <tbody>
                                        <tr>
                                            <td>For :</td>
                                            <td class="text-capitalize"><b><?php echo htmlspecialchars($row['stype']); ?></b></td>
                                            <td>Property Type :</td>
                                            <td class="text-capitalize"><b><?php echo htmlspecialchars($row['type']); ?></b></td>
                                        </tr>
                                        <tr>
                                            <td>Floor :</td>
                                            <td class="text-capitalize"><b><?php echo htmlspecialchars($row['floor']); ?></b></td>
                                            <td>Status :</td>
                                            <td class="text-capitalize"><b><?php echo htmlspecialchars($row['status']); ?></b></td>
                                        </tr>
                                        <tr>
                                            <td>City :</td>
                                            <td class="text-capitalize"><b><?php echo htmlspecialchars($row['city']); ?></b></td>
                                            <td>State :</td>
                                            <td class="text-capitalize"><b><?php echo htmlspecialchars($row['state']); ?></b></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <h5 class="mt-5 mb-4 text-secondary">Features</h5>
                            <div class="row">
								<?php echo $row['feature']; ?>
                            </div>   
							
                            <h5 class="mt-5 mb-4 text-secondary">Floor Plans</h5>
                            <div class="accordion" id="accordionExample">
                                <button class="bg-gray hover-bg-success hover-text-white text-ordinary py-3 px-4 mb-1 w-100 text-left rounded position-relative" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne"> Floor Plans </button>
                                <div id="collapseOne" class="collapse show p-4" aria-labelledby="headingOne" data-parent="#accordionExample">
                                    <img src="admin/property/<?php echo htmlspecialchars($row['mapimage'] ?? ''); ?>" alt="Not Available"> 
                                </div>
                                <button class="bg-gray hover-bg-success hover-text-white text-ordinary py-3 px-4 mb-1 w-100 text-left rounded position-relative collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">Basement Floor</button>
                                <div id="collapseTwo" class="collapse p-4" aria-labelledby="headingTwo" data-parent="#accordionExample">
                                    <img src="admin/property/<?php echo htmlspecialchars($row['topmapimage'] ?? ''); ?>" alt="Not Available"> 
                                </div>
                                <button class="bg-gray hover-bg-success hover-text-white text-ordinary py-3 px-4 mb-1 w-100 text-left rounded position-relative collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">Ground Floor</button>
                                <div id="collapseThree" class="collapse p-4" aria-labelledby="headingThree" data-parent="#accordionExample">
                                    <img src="admin/property/<?php echo htmlspecialchars($row['groundmapimage'] ?? ''); ?>" alt="Not Available"> 
                                </div>
                            </div>
<?php if(isset($row['panorama_image']) && !empty($row['panorama_image'])): ?>
<h5 class="mt-5 mb-4 text-secondary">360° Virtual Tour</h5>
<div class="mb-4">
    <!-- Preview image for all users -->
    <div class="position-relative">
        <!-- Preview image (not blurred for logged-in users) -->
        <div style="height: 400px; width: 100%; background-image: url('admin/property/panoramas/<?php echo htmlspecialchars($row['panorama_image']); ?>'); background-size: cover; background-position: center; <?php if(!$isLoggedIn): ?>filter: blur(5px);<?php endif; ?>"></div>
        
        <!-- Overlay with View VR button -->
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; background-color: rgba(0,0,0,0.4);">
            <h4 class="text-white mb-3">Experience this property in 360°</h4>
            <?php if($isLoggedIn): ?>
                <!-- Direct link for logged in users -->
                <a href="panorama-viewer.php?img=<?php echo urlencode($row['panorama_image']); ?>&pid=<?php echo intval($row['pid']); ?>" class="btn btn-success btn-lg">
                    <i class="fas fa-vr-cardboard mr-2"></i> View VR Tour
                </a>
            <?php else: ?>
                <!-- Login redirect for non-logged in users -->
                <a href="set_redirect.php?redirect=<?php echo urlencode('panorama-viewer.php?img=' . $row['panorama_image'] . '&pid=' . intval($row['pid'])); ?>" class="btn btn-success btn-lg">
                    <i class="fas fa-vr-cardboard mr-2"></i> View VR Tour
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
                            <h5 class="mt-5 mb-4 text-secondary double-down-line-left position-relative">Contact Agent</h5>
                            <div class="agent-contact pt-60">
                                <div class="row">
                                    <div class="col-sm-4 col-lg-3"> 
                                        <img src="admin/user/<?php echo htmlspecialchars($row['uimage']); ?>" alt="Agent" height="200" width="170">
                                        </div>
                                    <div class="col-sm-8 col-lg-9">
                                        <div class="agent-data text-ordinary mt-sm-20">
                                            <h6 class="text-success text-capitalize"><?php echo htmlspecialchars($row['uname']); ?></h6>
                                            <ul class="mb-3">
                                                <li><?php echo htmlspecialchars($row['uphone']); ?></li>
                                                <li><?php echo htmlspecialchars($row['uemail']); ?></li>
                                            </ul>
                                            
                                            <div class="mt-3 text-secondary hover-text-success">
                                                <ul>
                                                    <li class="float-left mr-3"><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                                    <li class="float-left mr-3"><a href="#"><i class="fab fa-twitter"></i></a></li>
                                                    <li class="float-left mr-3"><a href="#"><i class="fab fa-google-plus-g"></i></a></li>
                                                    <li class="float-left mr-3"><a href="#"><i class="fab fa-linkedin-in"></i></a></li>
                                                    <li class="float-left mr-3"><a href="#"><i class="fas fa-rss"></i></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <h4 class="double-down-line-left text-secondary position-relative pb-4 mb-4 mt-5">Featured Property</h4>
                        <ul class="property_list_widget">
                            
                            <?php 
                            // Make sure $con is available
                            if (isset($con) && $con) {
                                $query = mysqli_query($con, "SELECT * FROM `property` WHERE isFeatured = 1 ORDER BY date DESC LIMIT 3");
                                if ($query) {
                                    while($fprop = mysqli_fetch_assoc($query)) {
                            ?>
                            <li> 
    <a href="propertydetail.php?pid=<?php echo intval($fprop['pid']); ?>">
        <img src="admin/property/<?php echo htmlspecialchars($fprop['pimage']); ?>" alt="Property Image">
    </a>
    <h6 class="text-secondary hover-text-success text-capitalize">
        <a href="propertydetail.php?pid=<?php echo intval($fprop['pid']); ?>"><?php echo htmlspecialchars($fprop['title']); ?></a>
    </h6>
    <span class="font-14">
        <i class="fas fa-map-marker-alt icon-success icon-small"></i> 
        <?php echo htmlspecialchars($fprop['city']); ?>
    </span>
</li>

                            <?php 
                                    }
                                } else {
                                    echo '<li>Error fetching featured properties</li>';
                                }
                            } else {
                                echo '<li>Database connection error</li>';
                            }
                            ?>
                        </ul>

                        <div class="sidebar-widget mt-5">
                            <h4 class="double-down-line-left text-secondary position-relative pb-4 mb-4">Recently Added Property</h4>
                            <ul class="property_list_widget">
                            
                                <?php 
                                // Make sure $con is available
                                if (isset($con) && $con) {
                                    $query = mysqli_query($con, "SELECT * FROM `property` ORDER BY date DESC LIMIT 7");
                                    if ($query) {
                                        while($rprop = mysqli_fetch_assoc($query)) {
                                ?>
                                <li> 
    <a href="propertydetail.php?pid=<?php echo intval($rprop['pid']); ?>">
        <img src="admin/property/<?php echo htmlspecialchars($rprop['pimage']); ?>" alt="Property Image">
    </a>
    <h6 class="text-secondary hover-text-success text-capitalize">
        <a href="propertydetail.php?pid=<?php echo intval($rprop['pid']); ?>"><?php echo htmlspecialchars($rprop['title']); ?></a>
    </h6>
    <span class="font-14">
        <i class="fas fa-map-marker-alt icon-success icon-small"></i> 
        <?php echo htmlspecialchars($rprop['city']); ?>
    </span>
</li>
                                <?php 
                                        }
                                    } else {
                                        echo '<li>Error fetching recent properties</li>';
                                    }
                                } else {
                                    echo '<li>Database connection error</li>';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--	Footer   start-->
        <?php include("include/footer.php");?>
        <!--	Footer   start-->
        
        <!-- Scroll to top --> 
        <a href="#" class="bg-secondary text-white hover-text-secondary" id="scroll"><i class="fas fa-angle-up"></i></a> 
        <!-- End Scroll To top --> 
    </div>
</div>
<!-- Wrapper End --> 

<!--	Js Link
============================================================--> 
<script src="js/jquery.min.js"></script> 
<!--jQuery Layer Slider --> 
<script src="js/greensock.js"></script> 
<script src="js/layerslider.transitions.js"></script> 
<script src="js/layerslider.kreaturamedia.jquery.js"></script> 
<!--jQuery Layer Slider --> 
<script src="js/popper.min.js"></script> 
<script src="js/bootstrap.min.js"></script> 
<script src="js/owl.carousel.min.js"></script> 
<script src="js/tmpl.js"></script> 
<script src="js/jquery.dependClass-0.1.js"></script> 
<script src="js/draggable-0.1.js"></script> 
<script src="js/jquery.slider.js"></script> 
<script src="js/wow.js"></script> 
<script src="js/custom.js"></script>

<!-- Add this script to handle login redirects with return URL -->
<script>
    // Update all login links to include the current page as redirect
    document.addEventListener('DOMContentLoaded', function() {
        const loginLinks = document.querySelectorAll('a[href^="login1.php"]');
        const currentUrl = encodeURIComponent(window.location.href);
        
        loginLinks.forEach(link => {
            // Only update links that don't already have a redirect parameter
            if (!link.href.includes('redirect=')) {
                if (link.href.includes('?')) {
                    link.href += '&redirect=' + currentUrl;
                } else {
                    link.href += '?redirect=' + currentUrl;
                }
            }
        });
    });
</script>
</body>
</html>
