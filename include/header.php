<header id="header" class="transparent-header-modern fixed-header-bg-white w-100">
            <div class="top-header bg-secondary">
                <div class="container">
                    <div class="row">
                        <div class="col-md-8">
                            <ul class="top-contact list-text-white  d-table">
                                <li><a href="#"><i class="fas fa-phone-alt text-success mr-1"></i>+251904340273</a></li>
                                <li><a href="#"><i class="fas fa-envelope text-success mr-1"></i>legacyrealestate@gmail.com</a></li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <div class="top-contact float-right">
                                <ul class="list-text-white d-table">
								<li><i class="fas fa-user text-success mr-1"></i>
								<?php  if(isset($_SESSION['uemail']))
								{ ?>
								<a href="logout.php">Logout</a>&nbsp;&nbsp;<?php } else { ?>
								<a href="login1.php">Login</a>&nbsp;&nbsp;
								
								| </li>
								<li><i class="fas fa-user-plus text-success mr-1"></i><a href="login1.php"> Register</li><?php } ?>
								</ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="main-nav secondary-nav hover-success-nav py-2">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <nav class="navbar navbar-expand-lg navbar-light p-0"> <a class="navbar-brand position-relative" href="index.php"><img class="nav-logo" src="images/logo/legacy-logo.png" alt=""></a>
                                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"> <span class="navbar-toggler-icon"></span> </button>
                                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                                    <ul class="navbar-nav mr-auto">
                                        <li class="nav-item dropdown"> <a class="nav-link" href="index.php" role="button" aria-haspopup="true" aria-expanded="false">Home</a></li>
										
										<li class="nav-item"> <a class="nav-link" href="about.php">About</a> </li>
										
                                        <li class="nav-item"> <a class="nav-link" href="contact.php">Contact</a> </li>										
										
                                        <li class="nav-item"> <a class="nav-link" href="property.php">Properties</a> </li>
                                        
                                       <!-- <li class="nav-item"> <a class="nav-link" href="agent.php">Agent</a> </li>-->

										
										<?php  if(isset($_SESSION['uemail']))
										{ ?>
										<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">My Account</a>
    <ul class="dropdown-menu">
        <li class="nav-item"> 
            <a class="nav-link" href="user_dashboard.php">
                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
            </a> 
        </li>
        <!-- <li class="nav-item"> 
            <a class="nav-link" href="update_profile.php">
                <i class="fas fa-user-edit mr-2"></i> Edit Profile
            </a> 
        </li> -->
        <li class="nav-item"> 
            <a class="nav-link" href="my_bookings.php">
                <i class="fas fa-bookmark mr-2"></i> My Bookings
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="my_visits.php">
                <i class="fas fa-calendar-alt mr-2"></i> Upcoming Visits
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="my_payments.php">
                <i class="fas fa-credit-card mr-2"></i> Payment History
            </a>
        </li>
        <!-- <li class="nav-item">
            <a class="nav-link" href="favorites.php">
                <i class="fas fa-heart mr-2"></i> Favorite Properties
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="change_password.php">
                <i class="fas fa-key mr-2"></i> Change Password
            </a>
        </li> -->
        <li class="nav-item"> 
            <a class="nav-link" href="logout.php">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a> 
        </li>
    </ul>
</li>
<?php } else { ?>
<li class="nav-item"> <a class="nav-link" href="login1.php">Login/Register</a> </li>
<?php } ?>

                                    </ul>
                                    
									
									<!--<a class="btn btn-success d-none d-xl-block" style="border-radius:30px;" href="submitproperty.php">Submit Property</a> -->
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </header>