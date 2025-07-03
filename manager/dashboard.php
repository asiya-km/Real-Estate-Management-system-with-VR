<?php
session_start();
require("config.php");
 
if(!isset($_SESSION['auser'])) {
    header("location:../login1.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
    
<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
        <title>Remsko - Manager Dashboard</title>
		
		<!-- Favicon -->
        <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png">
		
		<!-- Bootstrap CSS -->
        <link rel="stylesheet" href="assets/css/bootstrap.min.css">
		
		<!-- Fontawesome CSS -->
        <link rel="stylesheet" href="assets/css/font-awesome.min.css">
		
		<!-- Feathericon CSS -->
        <link rel="stylesheet" href="assets/css/feathericon.min.css">
		
		<link rel="stylesheet" href="assets/plugins/morris/morris.css">
		
		<!-- Main CSS -->
        <link rel="stylesheet" href="assets/css/style.css">
        
        <!-- Custom Dashboard CSS -->
        <style>
            .dash-card {
                border-radius: 10px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.05);
                transition: all 0.3s ease;
                border: none;
                margin-bottom: 24px;
            }
            
            .dash-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            }
            
            .dash-widget-icon {
                width: 60px;
                height: 60px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                font-size: 24px;
                margin-right: 15px;
            }
            
            .dash-widget-info h3 {
                font-size: 28px;
                font-weight: 600;
                margin-bottom: 5px;
            }
            
            .dash-widget-info h6 {
                font-size: 14px;
                margin-bottom: 10px;
            }
                      .progress {
                height: 6px;
                margin-bottom: 0;
            }
            
            .card-header {
                border-bottom: 1px solid rgba(0,0,0,0.05);
                padding: 15px 20px;
            }
            
            .dashboard-header {
                background-color: #f8f9fa;
                border-radius: 10px;
                padding: 20px;
                margin-bottom: 25px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            }
            
            .stat-category {
                margin-bottom: 30px;
            }
            
            .stat-category h4 {
                margin-bottom: 20px;
                font-weight: 600;
                color: #333;
                border-left: 4px solid #20c0f3;
                padding-left: 10px;
            }
            
            .quick-action-card {
                text-align: center;
                padding: 25px 15px;
                transition: all 0.3s;
                border-radius: 10px;
                background: #fff;
                box-shadow: 0 5px 15px rgba(0,0,0,0.05);
                margin-bottom: 24px;
                height: 100%;
                display: block;
                text-decoration: none;
                color: #333;
            }
            
            .quick-action-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 20px rgba(0,0,0,0.1);
                text-decoration: none;
                color: #333;
            }
            
            .quick-action-card i {
                font-size: 48px;
                margin-bottom: 15px;
                display: block;
            }
            
            .quick-action-card h5 {
                font-weight: 500;
            }
        </style>
    </head>
    <body>
		<!-- Main Wrapper -->
		
		<!-- Header -->
		<?php include("header.php"); ?>
		<!-- /Header -->
			
		<!-- Page Wrapper -->
        <div class="page-wrapper">
			<div class="content container-fluid">
				
				<!-- Page Header -->
				<div class="dashboard-header">
					<div class="row align-items-center">
						<div class="col-sm-7">
							<h3 class="page-title">Welcome to Manager Dashboard!</h3>
							<ul class="breadcrumb">
								<li class="breadcrumb-item active">Dashboard</li>
								<li class="breadcrumb-item"><a href="admin_bookings.php" class="btn btn-sm btn-primary">Manage Bookings</a></li>
							</ul>
						</div>
						<div class="col-sm-5 text-right">
							<div class="btn-group">
								<a href="propertyadd.php" class="btn btn-outline-primary mr-2"><i class="fe fe-plus"></i> Add Property</a>
								<!-- <a href="userlist.php" class="btn btn-outline-secondary"><i class="fe fe-users"></i> Manage Users</a> -->
							</div>
						</div>
					</div>
				</div>
				<!-- /Page Header -->

				<!-- User Statistics -->
				<div class="stat-category">
					<h4>User Statistics</h4>
					<div class="row">
						<div class="col-xl-3 col-sm-6 col-12">
							<div class="card dash-card">
								<div class="card-body">
									<div class="dash-widget-header">
										<span class="dash-widget-icon bg-primary">
											<i class="fe fe-users"></i>
										</span>
										<div class="dash-widget-info">
											<h3><?php $sql = "SELECT * FROM user WHERE utype = 'user'";
											$query = $con->query($sql);
											echo "$query->num_rows";?></h3>
											<h6 class="text-muted">Registered Users</h6>
											<div class="progress progress-sm">
												<div class="progress-bar bg-primary w-50"></div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						
					
						<div class="col-xl-3 col-sm-6 col-12">
							<div class="card dash-card">
								<div class="card-body">
									<div class="dash-widget-header">
										<span class="dash-widget-icon bg-info">
											<i class="fe fe-home"></i>
										</span>
										<div class="dash-widget-info">
											<h3><?php $sql = "SELECT * FROM property";
											$query = $con->query($sql);
											echo "$query->num_rows";?></h3>
											<h6 class="text-muted">Total Properties</h6>
											<div class="progress progress-sm">
												<div class="progress-bar bg-info w-50"></div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						
						<div class="col-xl-3 col-sm-6 col-12">
							<div class="card dash-card">
								<div class="card-body">
									<div class="dash-widget-header">
										<span class="dash-widget-icon bg-success">
											<i class="fe fe-calendar"></i>
										</span>
										<div class="dash-widget-info">
											<h3><?php 
											$sql = "SELECT COUNT(*) as count FROM bookings";
											$result = $con->query($sql);
											$row = $result->fetch_assoc();
											echo $row['count'];
											?></h3>
											<h6 class="text-muted">Total Bookings</h6>
											<div class="progress progress-sm">
												<div class="progress-bar bg-success w-50"></div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						
						<div class="col-xl-3 col-sm-6 col-12">
							<div class="card dash-card">
								<div class="card-body">
									<div class="dash-widget-header">
										<span class="dash-widget-icon bg-warning">
											<i class="fe fe-message-square"></i>
										</span>
										<div class="dash-widget-info">
											<h3><?php 
											$sql = "SELECT COUNT(*) as count FROM contact";
											$result = $con->query($sql);
											$row = $result->fetch_assoc();
											echo $row['count'];
											?></h3>
											<h6 class="text-muted">Contact Messages</h6>
											<div class="progress progress-sm">
												<div class="progress-bar bg-warning w-50"></div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Property Types -->
				<div class="stat-category">
					<h4>Property Types</h4>
					<div class="row">
						<div class="col-xl-3 col-sm-6 col-12">
							<div class="card dash-card">
								<div class="card-body">
									<div class="dash-widget-header">
										<span class="dash-widget-icon bg-warning">
											<i class="fe fe-table"></i>
										</span>
										<div class="dash-widget-info">
											<h3><?php $sql = "SELECT * FROM property where type = 'apartment'";
											$query = $con->query($sql);
											echo "$query->num_rows";?></h3>
											<h6 class="text-muted">Apartments</h6>
											<div class="progress progress-sm">
												<div class="progress-bar bg-warning w-50"></div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-xl-3 col-sm-6 col-12">
							<div class="card dash-card">
								<div class="card-body">
									<div class="dash-widget-header">
										<span class="dash-widget-icon bg-info">
											<i class="fe fe-home"></i>
										</span>
										<div class="dash-widget-info">
											<h3><?php $sql = "SELECT * FROM property where type = 'house'";
											$query = $con->query($sql);
											echo "$query->num_rows";?></h3>
											<h6 class="text-muted">Houses</h6>
											<div class="progress progress-sm">
												<div class="progress-bar bg-info w-50"></div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-xl-3 col-sm-6 col-12">
							<div class="card dash-card">
								<div class="card-body">
									<div class="dash-widget-header">
										<span class="dash-widget-icon bg-secondary">
											<i class="fe fe-building"></i>
										</span>
										<div class="dash-widget-info">
											<h3><?php $sql = "SELECT * FROM property where type = 'building'";
											$query = $con->query($sql);
											echo "$query->num_rows";?></h3>
											<h6 class="text-muted">Buildings</h6>
											<div class="progress progress-sm">
												<div class="progress-bar bg-secondary w-50"></div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-xl-3 col-sm-6 col-12">
							<div class="card dash-card">
								<div class="card-body">
									<div class="dash-widget-header">
										<span class="dash-widget-icon bg-primary">
											<i class="fe fe-tablet"></i>
										</span>
										<div class="dash-widget-info">
											<h3><?php $sql = "SELECT * FROM property where type = 'flat'";
											$query = $con->query($sql);
											echo "$query->num_rows";?></h3>
											<h6 class="text-muted">Flats</h6>
											<div class="progress progress-sm">
												<div class="progress-bar bg-primary w-50"></div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Property Status -->
				<div class="stat-category">
					<h4>Property Status</h4>
					<div class="row">
						<div class="col-xl-6 col-sm-6 col-12">
							<div class="card dash-card">
								<div class="card-body">
									<div class="dash-widget-header">
										<span class="dash-widget-icon bg-success">
											<i class="fe fe-tag"></i>
										</span>
										<div class="dash-widget-info">
											<h3><?php $sql = "SELECT * FROM property where stype = 'sale'";
											$query = $con->query($sql);
											echo "$query->num_rows";?></h3>
											<h6 class="text-muted">Properties For Sale</h6>
											<div class="progress progress-sm">
												<div class="progress-bar bg-success w-50"></div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-xl-6 col-sm-6 col-12">
							<div class="card dash-card">
								<div class="card-body">
									<div class="dash-widget-header">
										<span class="dash-widget-icon bg-info">
											<i class="fe fe-key"></i>
										</span>
										<div class="dash-widget-info">
											<h3><?php $sql = "SELECT * FROM property where stype = 'rent'";
											$query = $con->query($sql);
											echo "$query->num_rows";?></h3>
											<h6 class="text-muted">Properties For Rent</h6>
											<div class="progress progress-sm">
												<div class="progress-bar bg-info w-50"></div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Quick Actions -->
				<div class="stat-category">
					<h4>Quick Actions</h4>
					<div class="row">
						<div class="col-md-3 col-sm-6">
							<a href="propertyview.php" class="quick-action-card">
								<i class="fe fe-list text-primary"></i>
								<h5>View Properties</h5>
							</a>
						</div>
						<div class="col-md-3 col-sm-6">
							<a href="contactview.php" class="quick-action-card">
								<i class="fe fe-mail text-warning"></i>
								<h5>Contact Messages</h5>
							</a>
						</div>
						<div class="col-md-3 col-sm-6">
							<a href="admin_bookings.php" class="quick-action-card">
								<i class="fe fe-calendar text-danger"></i>
								<h5>Manage Bookings</h5>
							</a>
						</div>
						<div class="col-md-3 col-sm-6">
							<a href="payment_history.php" class="quick-action-card">
								<i class="fe fe-credit-card text-success"></i>
								<h5>Payment History</h5>
							</a>
						</div>
					</div>
				</div>

				<!-- Recent Activity -->
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title">System Overview</h4>
							</div>
							<div class="card-body">
								<div class="row">
									<div class="col-md-6">
										<div class="card card-chart">
											<div class="card-header">
												<h5 class="card-title">Property Distribution</h5>
											</div>
											<div class="card-body">
												<canvas id="propertyChart" height="250"></canvas>
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="card card-chart">
											<div class="card-header">
												<h5 class="card-title">Sales vs Rentals</h5>
											</div>
											<div class="card-body">
												<canvas id="salesRentChart" height="250"></canvas>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>			
		</div>
				</div>
		<!-- /Page Wrapper -->
		
		<!-- jQuery -->
        <script src="assets/js/jquery-3.2.1.min.js"></script>
		
		<!-- Bootstrap Core JS -->
        <script src="assets/js/popper.min.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>
		
		<!-- Slimscroll JS -->
        <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
		
		<!-- Chart JS -->
		<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
		
		<!-- Custom JS -->
		<script src="assets/js/script.js"></script>
		
		<!-- Dashboard Charts -->
		<script>
			$(document).ready(function() {
				// Property Distribution Chart
				var propertyCtx = document.getElementById('propertyChart').getContext('2d');
				var propertyChart = new Chart(propertyCtx, {
					type: 'doughnut',
					data: {
						labels: ['Apartments', 'Houses', 'Buildings', 'Flats'],
						datasets: [{
							data: [
								<?php 
									$sql = "SELECT COUNT(*) as count FROM property WHERE type='apartment'";
									$result = $con->query($sql);
									$row = $result->fetch_assoc();
									echo $row['count'];
								?>,
								<?php 
									$sql = "SELECT COUNT(*) as count FROM property WHERE type='house'";
									$result = $con->query($sql);
									$row = $result->fetch_assoc();
									echo $row['count'];
								?>,
								<?php 
									$sql = "SELECT COUNT(*) as count FROM property WHERE type='building'";
									$result = $con->query($sql);
									$row = $result->fetch_assoc();
									echo $row['count'];
								?>,
								<?php 
									$sql = "SELECT COUNT(*) as count FROM property WHERE type='flat'";
									$result = $con->query($sql);
									$row = $result->fetch_assoc();
									echo $row['count'];
								?>
							],
							backgroundColor: [
								'#ffc107',
								'#20c0f3',
								'#6c757d',
								'#007bff'
							],
							borderWidth: 1
						}]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						plugins: {
							legend: {
								position: 'right',
							}
						}
					}
				});
				
				// Sales vs Rentals Chart
				var salesRentCtx = document.getElementById('salesRentChart').getContext('2d');
				var salesRentChart = new Chart(salesRentCtx, {
					type: 'pie',
					data: {
						labels: ['For Sale', 'For Rent'],
						datasets: [{
							data: [
								<?php 
									$sql = "SELECT COUNT(*) as count FROM property WHERE stype='sale'";
									$result = $con->query($sql);
									$row = $result->fetch_assoc();
									echo $row['count'];
								?>,
								<?php 
									$sql = "SELECT COUNT(*) as count FROM property WHERE stype='rent'";
									$result = $con->query($sql);
									$row = $result->fetch_assoc();
									echo $row['count'];
								?>
							],
							backgroundColor: [
								'#28a745',
								'#20c0f3'
							],
							borderWidth: 1
						}]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						plugins: {
							legend: {
								position: 'right',
							}
						}
					}
				});
				
				// Add active class to current menu item
				$("#sidebar-menu a").each(function() {
					var pageUrl = window.location.href.split(/[?#]/)[0];
					if (this.href == pageUrl) {
						$(this).addClass("active");
						$(this).parent().addClass("active");
						$(this).parent().parent().addClass("active");
						$(this).parent().parent().prev().addClass("active");
						$(this).parent().parent().parent().addClass("active");
						$(this).parent().parent().prev().click();
					}
				});
			});
		</script>
    </body>
</html>

