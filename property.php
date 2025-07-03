<?php 
ini_set('session.cache_limiter','public');
session_cache_limiter(false);
session_start();
include("config.php");
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

<!--	Title
	=========================================================-->
<title>Remsko - Real Estate</title>
</head>
<body>

<div id="page-wrapper">
    <div class="row"> 
        <!--	Header start  -->
		<?php include("include/header.php");?>
        <!--	Header end  -->
        
        <!--	Property Grid
		===============================================================-->
        <div class="full-row">
            <div class="container">
                <div class="row">
				
					<div class="col-lg-8">
                        <div class="row">
						
							<?php 
							// Improved query with proper JOIN syntax
							$query = "SELECT p.*, u.uname, u.utype, u.uimage 
         FROM property p 
         LEFT JOIN user u ON p.uid = u.uid 
         ORDER BY p.date DESC";

							$result = mysqli_query($con, $query);
							
							if (!$result) {
                                echo '<div class="col-md-12"><div class="alert alert-danger">Error: ' . mysqli_error($con) . '</div></div>';
                            } else if (mysqli_num_rows($result) == 0) {
                                echo '<div class="col-md-12"><div class="alert alert-info">No properties found</div></div>';
                            } else {
                                while($row = mysqli_fetch_assoc($result)) {
                                    // Add fallback for user information if not available
    if (!isset($row['uname']) || empty($row['uname'])) {
        $row['uname'] = 'Admin';
    }
							?>
                            <div class="col-md-6">
                                <div class="featured-thumb hover-zoomer mb-4">
                                    <div class="overlay-black overflow-hidden position-relative"> 
    <a href="propertydetail.php?pid=<?php echo $row['pid']; ?>">
        <img src="admin/property/<?php echo $row['pimage']; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
    </a>
    
    <div class="sale bg-success text-white">For <?php echo ucfirst($row['stype']); ?></div>
    <div class="price text-primary text-capitalize">ETB <?php echo number_format($row['price']); ?> 
        <span class="text-white"><?php echo $row['size']; ?> Sqft</span>
    </div>
</div>
                                    <div class="featured-thumb-data shadow-one">
                                        <div class="p-4">
                                            <h5 class="text-secondary hover-text-success mb-2 text-capitalize">
                                                <a href="propertydetail.php?pid=<?php echo $row['pid']; ?>">
                                                    <?php echo htmlspecialchars($row['title']); ?>
                                                </a>
                                            </h5>
                                            <span class="location text-capitalize">
                                                <i class="fas fa-map-marker-alt text-success"></i> 
                                                <?php echo htmlspecialchars($row['location']); ?>
                                            </span> 
                                        </div>
                                        <div class="px-4 pb-4 d-inline-block w-100">
                                            <div class="float-left text-capitalize">
                                                <i class="fas fa-user text-success mr-1"></i>By : <?php echo htmlspecialchars($row['uname']); ?>
                                            </div>
                                            <div class="float-right">
                                                <i class="far fa-calendar-alt text-success mr-1"></i> 
                                                <?php echo date('d-m-Y', strtotime($row['date'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php 
                                }
                            }
                            ?>
                        </div>
                    </div>
					
                    <div class="col-lg-4">
                        <!--<div class="sidebar-widget">
                            <h4 class="double-down-line-left text-secondary position-relative pb-4 my-4">Instalment Calculator</h4>
						<form class="d-inline-block w-100" action="calc.php" method="post">
                            <label class="sr-only">Property Amount</label>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">ETB</div>
                                </div>
                                <input type="text" class="form-control" name="amount" placeholder="Property Price" required>
                            </div>
                            <label class="sr-only">Month</label>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text"><i class="far fa-calendar-alt"></i></div>
                                </div>
                                <input type="text" class="form-control" name="month" placeholder="Duration Year" required>
                            </div>
                            <label class="sr-only">Interest Rate</label>
                            <div class="input-group mb-2 mr-sm-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">%</div>
                                </div>
                                <input type="text" class="form-control" name="interest" placeholder="Interest Rate" required>
                            </div>
                            <button type="submit" value="submit" name="calc" class="btn btn-danger mt-4">Calculate Instalment</button>
                        </form>
                        </div>-->

                        <h4 class="double-down-line-left text-secondary position-relative pb-4 mb-4 mt-5">Featured Property</h4>
                        <ul class="property_list_widget">
							
                            <?php 
                            $query = "SELECT * FROM property WHERE isFeatured = 1 ORDER BY date DESC LIMIT 3";
                            $result = mysqli_query($con, $query);
                            
                            if ($result && mysqli_num_rows($result) > 0) {
                                while($row = mysqli_fetch_assoc($result)) {
                            ?>
                            <li> 
                                <img src="admin/property/<?php echo $row['pimage']; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                                <h6 class="text-secondary hover-text-success text-capitalize">
                                    <a href="propertydetail.php?pid=<?php echo $row['pid']; ?>">
                                        <?php echo htmlspecialchars($row['title']); ?>
                                    </a>
                                </h6>
                                <span class="font-14">
                                    <i class="fas fa-map-marker-alt icon-success icon-small"></i> 
                                    <?php echo htmlspecialchars($row['location']); ?>
                                </span>
                            </li>
                            <?php 
                                }
                            } else {
                                echo '<li>No featured properties found</li>';
                            }
                            ?>
                        </ul>
                        
                        <div class="sidebar-widget mt-5">
                            <h4 class="double-down-line-left text-secondary position-relative pb-4 mb-4">Recently Added Property</h4>
                            <ul class="property_list_widget">
							
								<?php 
								$query = "SELECT * FROM property ORDER BY date DESC LIMIT 6";
								$result = mysqli_query($con, $query);
								
								if ($result && mysqli_num_rows($result) > 0) {
                                    while($row = mysqli_fetch_assoc($result)) {
								?>
                                <li> 
    <a href="propertydetail.php?pid=<?php echo $row['pid']; ?>">
        <img src="admin/property/<?php echo $row['pimage']; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
    </a>
    <h6 class="text-secondary hover-text-success text-capitalize">
        <a href="propertydetail.php?pid=<?php echo $row['pid']; ?>">
            <?php echo htmlspecialchars($row['title']); ?>
        </a>
    </h6>
    <span class="font-14">
        <i class="fas fa-map-marker-alt icon-success icon-small"></i> 
        <?php echo htmlspecialchars($row['location']); ?>
    </span>
</li>
                                <?php 
                                    }
                                } else {
                                    echo '<li>No properties found</li>';
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
</body>
</html>
