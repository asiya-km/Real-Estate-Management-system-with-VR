<?php 
ini_set('session.cache_limiter','public');
session_cache_limiter(false);
session_start();
include("config.php");
								
?>
<!DOCTYPE html>
<html lang="en">

<head>
<!-- FOR MORE PROJECTS visit: codeastro.com -->
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
<link rel="stylesheet" type="text/css" href="css/color.css" id="color-change">
<link rel="stylesheet" type="text/css" href="css/owl.carousel.min.css">
<link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="fonts/flaticon/flaticon.css">
<link rel="stylesheet" type="text/css" href="css/style.css">
<!-- Add this to the head section -->


<!--	Title
	=========================================================-->
<title>Real Estate PHP</title>
</head>
<body>

<!--	Page Loader  -->
<!--<div class="page-loader position-fixed z-index-9999 w-100 bg-white vh-100">
	<div class="d-flex justify-content-center y-middle position-relative">
	  <div class="spinner-border" role="status">
		<span class="sr-only">Loading...</span>
	  </div>
	</div>
</div>  -->
<!--	Page Loader  -->

<div id="page-wrapper">
    <div class="row"> 
        <!--	Header start  -->
		<?php include("include/header.php");?>
        <!--	Header end  -->
		
        <!--	Banner Start   -->
<!-- Banner Start - Updated with modern design -->
<div class="overlay-black w-100 slider-banner1 position-relative" style="background-image: url('images/banner/rshmpg.jpg'); background-size: cover; background-position: center center; background-repeat: no-repeat; height: 80vh;">
    <div class="container h-100">
        <div class="row h-100 align-items-center">
            <div class="col-lg-12">
                <div class="text-white">
                    <h1 class="mb-4 display-4 font-weight-bold"><span class="text-success">Let us</span><br>
                    Guide you Home</h1>
                    <form method="post" action="propertygrid.php" class="search-form bg-white p-4 rounded shadow-lg">
                        <div class="row">
                            <div class="col-md-6 col-lg-3">
                                <div class="form-group">
                                    <label class="text-dark small font-weight-bold">PROPERTY TYPE</label>
                                    <select class="form-control custom-select" name="type">
                                        <option value="">Select Type</option>
                                        <option value="apartment">Apartment</option>
                                        <option value="flat">Flat</option>
                                        <option value="building">Building</option>
                                        <option value="house">House</option>
                                        <option value="villa">Villa</option>
                                        <option value="office">Office</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="form-group">
                                    <label class="text-dark small font-weight-bold">STATUS</label>
                                    <select class="form-control custom-select" name="stype">
                                        <option value="">Select Status</option>
                                        <option value="rent">Rent</option>
                                        <option value="sale">Sale</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="https://legacybuilderset.com/orm-group">
                                    <label class="text-dark small font-weight-bold">LOCATION</label>
                                    <input type="text" class="form-control" name="city" placeholder="Enter City">
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-2">
                                <div class="form-group">
                                    <label class="text-white small">&nbsp;</label>
                                    <button type="submit" name="filter" class="btn btn-success btn-lg w-100">SEARCH</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

        <!--	Banner End  -->
        
        <!--	Text Block One-->
		<!-- What We Do Section - Improved -->
        <div class="full-row bg-light py-5" style="background-image: url('images/main-bg.jpg'); background-size: cover; background-position: center; background-attachment: fixed;">
        <div class="container">
        <div class="container position-relative">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="text-secondary text-center mb-5">Our Services</h2>
                <div class="title-separator bg-success mx-auto mb-4"></div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="service-card bg-white hover-shadow rounded mb-4 transition-3s"> 
                    <div class="icon-box text-center pt-4">
                        <i class="flaticon-rent text-success flat-medium" aria-hidden="true"></i>
                    </div>
                    <div class="card-body text-center">
                        <h5 class="text-secondary hover-text-success py-3 m-0"><a href="#">Selling Service</a></h5>
                        <p class="px-2">We can sell for our customer like: home for family, for personal</p>
                        <a href="#" class="btn btn-outline-success btn-sm mt-2">Learn More</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="service-card bg-white hover-shadow rounded mb-4 transition-3s"> 
                    <div class="icon-box text-center pt-4">
                        <i class="flaticon-for-rent text-success flat-medium" aria-hidden="true"></i>
                    </div>
                    <div class="card-body text-center">
                        <h5 class="text-secondary hover-text-success py-3 m-0"><a href="#">Rental Service</a></h5>
                        <p class="px-2">We can offer for rental like: Office, Work, Store</p>
                        <a href="#" class="btn btn-outline-success btn-sm mt-2">Learn More</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="service-card bg-white hover-shadow rounded mb-4 transition-3s"> 
                    <div class="icon-box text-center pt-4">
                        <i class="flaticon-list text-success flat-medium" aria-hidden="true"></i>
                    </div>
                    <div class="card-body text-center">
                        <h5 class="text-secondary hover-text-success py-3 m-0"><a href="#">Property Listing</a></h5>
                        <p class="px-2">This is a dummy text for filling out spaces. Just some random words...</p>
                        <a href="#" class="btn btn-outline-success btn-sm mt-2">Learn More</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="service-card bg-white hover-shadow rounded mb-4 transition-3s"> 
                    <div class="icon-box text-center pt-4">
                        <i class="flaticon-diagram text-success flat-medium" aria-hidden="true"></i>
                    </div>
                    <div class="card-body text-center">
                        <h5 class="text-secondary hover-text-success py-3 m-0"><a href="#">Legal Investment</a></h5>
                        <p class="px-2">Also we can offer for Legal Investment</p>
                        <a href="#" class="btn btn-outline-success btn-sm mt-2">Learn More</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>

		<!-----  Our Services  ---->
		
        <!--	Recent Properties  -->
<!-- Recent Properties - Enhanced -->
<!-- Recent Properties - Enhanced -->
<div class="full-row py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2 class="text-secondary text-center mb-4">Recent Properties</h2>
                <div class="title-separator bg-success mx-auto mb-4"></div>
                <p class="text-center mb-5">Discover our latest property listings that match your needs</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="tab-content mt-4" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home">
                        <div class="row">
                            <?php 
                          
								$query = "SELECT * FROM property ORDER BY date DESC LIMIT 3";
								$result = mysqli_query($con, $query); 
                            // Check if query executed successfully
                            if(!$result) {
                                echo "Query Error: " . mysqli_error($con);
                            }
                            
                            // Check if any rows were returned
                            if(mysqli_num_rows($result) == 0) {
                                echo '<div class="alert alert-info">No properties found. Please add some properties first.</div>';
                            }
                            
                            // Loop through results
                            while($row=mysqli_fetch_array($result)) {
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="property-card hover-zoomer mb-4 shadow-hover">
                                    <div class="overlay-black overflow-hidden position-relative rounded-top"> 
    <a href="propertydetail.php?pid=<?php echo $row['0']; ?>">
        <img src="admin/property/<?php echo $row['pimage'];?>" alt="pimage" class="img-fluid">
    </a>
    <div class="featured bg-success text-white">New</div>
    <div class="sale bg-success text-white text-capitalize">For <?php echo $row['4'];?></div>
    <div class="price text-white bg-dark-transparent py-2 px-3"><b>ETB <?php echo number_format($row['10']);?> </b></div>
</div>
                                    <div class="property-info bg-white rounded-bottom">
                                        <div class="p-3">
                                            <h5 class="text-secondary hover-text-success mb-2 text-capitalize"><a href="propertydetail.php?pid=<?php echo $row['0'];?>"><?php echo $row['1'];?></a></h5>
                                            <span class="location text-capitalize d-block mb-3"><i class="fas fa-map-marker-alt text-success mr-2"></i> <?php echo $row['11'];?></span>
                                            <p class="text-muted small"><?php echo substr($row['2'], 0, 100) . '...'; ?></p>
                                        </div>
                                        <div class="bg-light quantity px-4 py-3">
                                            <ul class="d-flex justify-content-between mb-0">
                                                <li><i class="fas fa-vector-square text-success mr-2"></i> <?php echo $row['9'];?> Sqft</li>
                                                <li><i class="fas fa-bed text-success mr-2"></i> <?php echo $row['5'];?> Beds</li>
                                                <li><i class="fas fa-bath text-success mr-2"></i> <?php echo $row['6'];?> Baths</li>
                                            </ul>
                                        </div>
                                        <div class="p-3 border-top d-flex justify-content-between">
                                            <div class="text-capitalize"><i class="fas fa-user text-success mr-1"></i> <?php echo $row['uname'];?></div>
                                            <div><i class="far fa-calendar-alt text-success mr-1"></i> <?php echo date('d-m-Y', strtotime($row['date']));?></div> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

        <!--	How It Work -->
        
        <!--	Achievement
        ============================================================-->
        <div class="full-row overlay-secondary" style="background-image: url('images/breadcromb.jpg'); background-size: cover; background-position: center center; background-repeat: no-repeat;">
            <div class="container">
                <div class="fact-counter">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="count wow text-center  mb-sm-50" data-wow-duration="300ms"> <i class="flaticon-house flat-large text-white" aria-hidden="true"></i>
								<?php
										$query=mysqli_query($con,"SELECT count(pid) FROM property");
											while($row=mysqli_fetch_array($query))
												{
										?>
                                <div class="count-num text-success my-4" data-speed="3000" data-stop="<?php 
												$total = $row[0];
												echo $total;?>">0</div>
								<?php } ?>
                                <div class="text-white h5">Property Available</div>
                            </div>
                        </div>
						<div class="col-md-3">
                            <div class="count wow text-center  mb-sm-50" data-wow-duration="300ms"> <i class="flaticon-house flat-large text-white" aria-hidden="true"></i>
								<?php
										$query=mysqli_query($con,"SELECT count(pid) FROM property where stype='sale'");
											while($row=mysqli_fetch_array($query))
												{
										?>
                                <div class="count-num text-success my-4" data-speed="3000" data-stop="<?php 
												$total = $row[0];
												echo $total;?>">0</div>
								<?php } ?>
                                <div class="text-white h5">Sale Property Available</div>
                            </div>
                        </div>
						<div class="col-md-3">
                            <div class="count wow text-center  mb-sm-50" data-wow-duration="300ms"> <i class="flaticon-house flat-large text-white" aria-hidden="true"></i>
								<?php
										$query=mysqli_query($con,"SELECT count(pid) FROM property where stype='rent'");
											while($row=mysqli_fetch_array($query))
												{
										?>
                                <div class="count-num text-success my-4" data-speed="3000" data-stop="<?php 
												$total = $row[0];
												echo $total;?>">0</div>
								<?php } ?>
                                <div class="text-white h5">Rent Property Available</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="count wow text-center  mb-sm-50" data-wow-duration="300ms"> <i class="flaticon-man flat-large text-white" aria-hidden="true"></i>
                                <?php
										$query=mysqli_query($con,"SELECT count(uid) FROM user");
											while($row=mysqli_fetch_array($query))
												{
										?>
                                <div class="count-num text-success my-4" data-speed="3000" data-stop="<?php 
												$total = $row[0];
												echo $total;?>">0</div>
								<?php } ?>
                                <div class="text-white h5">Registered Users</div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        
        <!--	Popular Place -->
        <div class="full-row bg-gray">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <h2 class="text-secondary double-down-line text-center mb-5">Popular Places</h2></div>
                </div>
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-md-6 col-lg-3 pb-1">
                            <div class="overflow-hidden position-relative overlay-secondary hover-zoomer mx-n13 z-index-9"> <img src="images/thumbnail4/1.jpg" alt="">
                                <div class="text-white xy-center z-index-9 position-absolute text-center w-100">
									<?php
										$query=mysqli_query($con,"SELECT count(state), property.* FROM property where city='Olisphis'");
											while($row=mysqli_fetch_array($query))
												{
										?>
                                    <h4 class="hover-text-success text-capitalize"><a href="stateproperty.php?id=<?php echo $row['17']?>"><?php echo $row['state'];?></a></h4>
                                    <span><?php 
												$total = $row[0];
												echo $total;?> Properties Listed</span> </div>
									<?php } ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 pb-1">
                            <div class="overflow-hidden position-relative overlay-secondary hover-zoomer mx-n13 z-index-9"> <img src="images/thumbnail4/2.jpg" alt="">
                                <div class="text-white xy-center z-index-9 position-absolute text-center w-100">
									<?php
										$query=mysqli_query($con,"SELECT count(state), property.* FROM property where city='Awrerton'");
											while($row=mysqli_fetch_array($query))
												{
										?>
                                    <h4 class="hover-text-success text-capitalize"><a href="stateproperty.php?id=<?php echo $row['17']?>"><?php echo $row['state'];?></a></h4>
                                    <span><?php 
												$total = $row[0];
												echo $total;?> Properties Listed</span> </div>
									<?php } ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 pb-1">
                            <div class="overflow-hidden position-relative overlay-secondary hover-zoomer mx-n13 z-index-9"> <img src="images/thumbnail4/3.jpg" alt="">
                                <div class="text-white xy-center z-index-9 position-absolute text-center w-100">
                                    <?php
										$query=mysqli_query($con,"SELECT count(state), property.* FROM property where city='Floson'");
											while($row=mysqli_fetch_array($query))
												{
										?>
                                    <h4 class="hover-text-success text-capitalize"><a href="stateproperty.php?id=<?php echo $row['17']?>"><?php echo $row['state'];?></a></h4>
                                    <span><?php 
												$total = $row[0];
												echo $total;?> Properties Listed</span> </div>
									<?php } ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 pb-1">
                            <div class="overflow-hidden position-relative overlay-secondary hover-zoomer mx-n13 z-index-9"> <img src="images/thumbnail4/4.jpg" alt="">
                                <div class="text-white xy-center z-index-9 position-absolute text-center w-100">
                                    <?php
										$query=mysqli_query($con,"SELECT count(state), property.* FROM property where city='Ulmore'");
											while($row=mysqli_fetch_array($query))
												{
										?>
                                    <h4 class="hover-text-success text-capitalize"><a href="stateproperty.php?id=<?php echo $row['17']?>"><?php echo $row['state'];?></a></h4>
                                    <span><?php 
												$total = $row[0];
												echo $total;?> Properties Listed</span> </div>
									<?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--	Popular Places -->
		
		<!--	Testonomial -->
        <div class="full-row" style="background-image: url('images/testimonial-bg.jpg'); background-size: cover; background-position: center; background-attachment: fixed; position: relative;">
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.7);"></div>
    <div class="container position-relative">
                <div class="row">
					<div class="col-lg-12">
						<div class="content-sidebar p-4">
							<div class="mb-3 col-lg-12">
								<h4 class="double-down-line-left text-white position-relative pb-4 mb-4">What Our Clients Say</h4>
									<div class="recent-review owl-carousel owl-dots-gray owl-dots-hover-success">
									
										<?php
													
												$query=mysqli_query($con,"select feedback.*, user.* from feedback,user where feedback.uid=user.uid and feedback.status='1'");
												while($row=mysqli_fetch_array($query))
													{
										?>
										<div class="item">
											<div class="p-4 bg-success down-angle-white position-relative">
												<p class="text-white"><i class="fas fa-quote-left mr-2 text-white"></i><?php echo $row['2']; ?>. <i class="fas fa-quote-right mr-2 text-white"></i></p>
											</div>
											<div class="p-2 mt-4">
												<span class="text-success d-table text-capitalize"><?php echo $row['uname']; ?></span> <span class="text-capitalize"><?php echo $row['utype']; ?></span>
											</div>
										</div>
										<?php }  ?>
										
									</div>
							</div>
						 </div>
					</div>
				</div>
			</div>
		</div>
		<!--	Testonomial -->
		
		
        <!--	Footer   start-->
		<?php include("include/footer.php");?>
		<!--	Footer   start-->
        
        
        <!-- Scroll to top --> 
        <a href="#" class="bg-success text-white hover-text-secondary" id="scroll"><i class="fas fa-angle-up"></i></a> 
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
<script src="js/YouTubePopUp.jquery.js"></script> 
<script src="js/validate.js"></script> 
<script src="js/jquery.cookie.js"></script> 
<script src="js/custom.js"></script>
</body>

</html>