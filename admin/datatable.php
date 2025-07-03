<?php
session_start();
require("config.php");
////code
 
if(!isset($_SESSION['auser']))
{
	header("location:../login1.php");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
        <title>Ventura - Data Tables</title>
		
		<!-- Favicon -->
        <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png">
		
		<!-- Bootstrap CSS -->
        <link rel="stylesheet" href="assets/css/bootstrap.min.css">
		
		<!-- Fontawesome CSS -->
        <link rel="stylesheet" href="assets/css/font-awesome.min.css">
		
		<!-- Feathericon CSS -->
        <link rel="stylesheet" href="assets/css/feathericon.min.css">
		
		<!-- Datatables CSS -->
		<link rel="stylesheet" href="assets/plugins/datatables/dataTables.bootstrap4.min.css">
		<link rel="stylesheet" href="assets/plugins/datatables/responsive.bootstrap4.min.css">
		<link rel="stylesheet" href="assets/plugins/datatables/select.bootstrap4.min.css">
		<link rel="stylesheet" href="assets/plugins/datatables/buttons.bootstrap4.min.css">
		
		<!-- Main CSS -->
        <link rel="stylesheet" href="assets/css/style.css">
		
		<!--[if lt IE 9]>
			<script src="assets/js/html5shiv.min.js"></script>
			<script src="assets/js/respond.min.js"></script>
		<![endif]-->
    </head>
    <body>
	
		<!-- Main Wrapper -->
		
		
			<!-- Header -->
				<?php include("header.php"); ?>
			<!-- /Sidebar -->
			
			<!-- Page Wrapper -->
            <div class="page-wrapper">
                <div class="content container-fluid">

					<!-- Page Header -->
					<div class="page-header">
						<div class="row">
							<div class="col">
								<p>..</p>
								<h3 class="page-title">User</h3>
								<ul class="breadcrumb">
									<li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
									<li class="breadcrumb-item active">User</li>
								</ul>
							</div>
						</div>
					</div>
					<!-- /Page Header -->
					
					<div class="row">
						<div class="col-sm-12">
							<div class="card">
								<div class="card-header">
									<h4 class="card-title">Default Datatable</h4>
									<p class="card-text">
										This is the most basic example of the datatables with zero configuration. Use the <code>.datatable</code> class to initialize datatables.
									</p>
								</div>
								<div class="card-body">

									<table id="basic-datatable" class="table dt-responsive nowrap">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Position</th>
                                                    <th>Office</th>
                                                    <th>Age</th>
                                                    <th>Start date</th>
                                                    <th>Salary</th>
                                                </tr>
                                            </thead>
                                        
                                        
                                            <tbody>
                                                <tr>
                                                    <td>Lemi Bekele</td>
                                                    <td>System Architect</td>
                                                    <td>Finfine</td>
                                                    <td>21</td>
                                                    <td>2025/03/25</td>
                                                    <td>ETB 320,800</td>
                                                </tr>
                                                
                                                <tr>
                                                    <td>Marara Fikadu</td>
                                                    <td>Javascript Developer</td>
                                                    <td>Finfine</td>
                                                    <td>23</td>
                                                    <td>2025/03/11</td>
                                                    <td>ETB 205,500</td>
                                                </tr>
                                                <tr>
                                                    <td>Asia Khalifa</td>
                                                    <td>Software Engineer</td>
                                                    <td>Finfine</td>
                                                    <td>23</td>
                                                    <td>2025/03/13</td>
                                                    <td>ETB 103,600</td>
                                                </tr>
                                                <tr>
                                                    <td>Olifan Fituma</td>
                                                    <td>Office Manager</td>
                                                    <td>Ambo</td>
                                                    <td>22</td>
                                                    <td>2025/03/19</td>
                                                    <td>ETB 90,560</td>
                                                </tr>
                                                <tr>
                                                    <td>Gadaa Beko</td>
                                                    <td>Support Lead</td>
                                                    <td>Tulu Diimtuu</td>
                                                    <td>22</td>
                                                    <td>2025/03/03</td>
                                                    <td>ETB 42,000</td>
                                                </tr>
                                                <tr>
                                                    <td>Asnake Bekele</td>
                                                    <td>Regional Director</td>
                                                    <td>Fitche</td>
                                                    <td>36</td>
                                                    <td>2025/03/16</td>
                                                    <td>ETB 70,600</td>
                                                </tr>
                                                <tr>
                                                    <td>Shelema</td>
                                                    <td>Senior Marketing Designer</td>
                                                    <td>Fitche</td>
                                                    <td>43</td>
                                                    <td>2025/03/18</td>
                                                    <td>ETB 63,500</td>
                                                </tr>
                                                <tr>
                                                    <td>Tatyana Fitzpatrick</td>
                                                    <td>Regional Director</td>
                                                    <td>London</td>
                                                    <td>19</td>
                                                    <td>2010/03/17</td>
                                                    <td>$385,750</td>
                                                </tr>
                                                <tr>
                                                    <td>Michael Silva</td>
                                                    <td>Marketing Designer</td>
                                                    <td>London</td>
                                                    <td>66</td>
                                                    <td>2012/11/27</td>
                                                    <td>$198,500</td>
                                                </tr>
                                                <tr>
                                                    <td>Bekinan Anisa</td>
                                                    <td>Chief Financial Officer (CFO)</td>
                                                    <td>Asko</td>
                                                    <td>24</td>
                                                    <td>2025/06/09</td>
                                                    <td>ETB 75,000</td>
                                                </tr>
                                                <tr>
                                                    <td>Lemi Beko</td>
                                                    <td>Systems Administrator</td>
                                                    <td>Bole</td>
                                                    <td>21</td>
                                                    <td>2025/04/10</td>
                                                    <td>ETB 77,500</td>
                                                </tr>
                                                <tr>
                                                    <td>Masfin Gutu</td>
                                                    <td>Software Engineer</td>
                                                    <td>Adama</td>
                                                    <td>41</td>
                                                    <td>2025/03/13</td>
                                                    <td>ETB 62,000</td>
                                                </tr>
                                                <tr>
                                                    <td>Kasahun Goshime</td>
                                                    <td>Personnel Lead</td>
                                                    <td>Shakkiso</td>
                                                    <td>35</td>
                                                    <td>2025/04/06</td>
                                                    <td>ETB 67,500</td>
                                                </tr>
                                                
                                                <tr>
                                                    <td>Fasika Yemane</td>
                                                    <td>Javascript Developer</td>
                                                    <td>Yirga Chafe</td>
                                                    <td>25</td>
                                                    <td>2025/06/27</td>
                                                    <td>ETB 83,000</td>
                                                </tr>
                                                <tr>
                                                    <td>Efirata</td>
                                                    <td>Customer Support</td>
                                                    <td>Finfine</td>
                                                    <td>22</td>
                                                    <td>2025/03/25</td>
                                                    <td>ETB 52,000</td>
                                                </tr>
                                            </tbody>
                                        </table>
								</div>
							</div>
						</div>
					</div>
					
					
					
					<div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                        <h4 class="header-title mt-0 mb-1">Buttons example</h4>
                                        <p class="sub-header">
                                            The Buttons extension for DataTables provides a common set of options, API methods and styling to display buttons on a page
                                            that will interact with a DataTable. The core library provides the based framework upon which plug-ins can built.
                                        </p>


                                        <table id="datatable-buttons" class="table table-striped dt-responsive nowrap">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Position</th>
                                                    <th>Office</th>
                                                    <th>Age</th>
                                                    <th>Start date</th>
                                                    <th>Salary</th>
                                                </tr>
                                            </thead>
                                        
                                        
                                            <tbody>
                                                <tr>
                                                    <td>Lemi Bekele</td>
                                                    <td>System Architect</td>
                                                    <td>Finfine</td>
                                                    <td>21</td>
                                                    <td>2025/03/25</td>
                                                    <td>ETB 320,800</td>
                                                </tr>
                                                <tr>
                                                    <td>Asia Khalifa</td>
                                                    <td>Accountant</td>
                                                    <td>Finfine</td>
                                                    <td>23</td>
                                                    <td>2025/04/25</td>
                                                    <td>ETB 70,750</td>
                                                </tr>
                                                
                                                <tr>
                                                    <td>Teka Efa</td>
                                                    <td>Regional Director</td>
                                                    <td>Finfine</td>
                                                    <td>19</td>
                                                    <td>2025/03/17</td>
                                                    <td>ETB 85,750</td>
                                                </tr>
                                                <tr>
                                                    <td>Michael Bogale</td>
                                                    <td>Marketing Designer</td>
                                                    <td>Finfine</td>
                                                    <td>36</td>
                                                    <td>2025/03/27</td>
                                                    <td>ETB 98,500</td>
                                                </tr>
                                                <tr>
                                                    <td>Yerosan Defaru</td>
                                                    <td>Chief Financial Officer (CFO)</td>
                                                    <td>Finfine</td>
                                                    <td>25</td>
                                                    <td>2025/06/09</td>
                                                    <td>ETB 75,000</td>
                                                </tr>
                                                <tr>
                                                    <td>Bayisa Beka</td>
                                                    <td>Systems Administrator</td>
                                                    <td>Finfine</td>
                                                    <td>25</td>
                                                    <td>2025/06/09</td>
                                                    <td>ETB 75,000</td>
                                                </tr>
                                                
                                                <tr>
                                                    <td>Dabala Fayisa</td>
                                                    <td>Customer Support</td>
                                                    <td>Finfine</td>
                                                    <td>25</td>
                                                    <td>2025/06/09</td>
                                                    <td>ETB 75,000</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        
                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->
					
					
					<div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                        <h4 class="header-title mt-0 mb-1">Multi item selection</h4>
                                        <p class="sub-header">
                                            This example shows the multi option. Note how a click on a row will toggle its selected state without effecting other rows,
                                            unlike the os and single options shown in other examples.
                                        </p>

                                        <table id="selection-datatable" class="table dt-responsive nowrap">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Position</th>
                                                    <th>Office</th>
                                                    <th>Age</th>
                                                    <th>Start date</th>
                                                    <th>Salary</th>
                                                </tr>
                                            </thead>
                                        
                                        
                                            <tbody>
                                                <tr>
                                                <td>Lemi Bekele</td>
                                                    <td>System Architect</td>
                                                    <td>Finfine</td>
                                                    <td>21</td>
                                                    <td>2025/03/25</td>
                                                    <td>ETB 320,800</td>
                                                </tr>
                                                <tr>
                                                    <td>Asia Khalifa</td>
                                                    <td>Accountant</td>
                                                    <td>Finfine</td>
                                                    <td>23</td>
                                                    <td>2025/04/25</td>
                                                    <td>ETB 70,750</td>
                                                </tr>
                                                <tr>
                                                    <td>Muktar Jamal</td>
                                                    <td>Junior Technical Author</td>
                                                    <td>Shashamanne</td>
                                                    <td>26</td>
                                                    <td>2025/01/12</td>
                                                    <td>ETB 86,000</td>
                                                </tr>
                                                <tr>
                                                    <td>Marara Fikadu</td>
                                                    <td>Senior Javascript Developer</td>
                                                    <td>Finfine</td>
                                                    <td>22</td>
                                                    <td>2025/03/29</td>
                                                    <td>ETB 43,060</td>
                                                </tr>
                                                <tr>
                                                    <td>Kasalem Ewunetu</td>
                                                    <td>Accountant</td>
                                                    <td>Bardar</td>
                                                    <td>23</td>
                                                    <td>2025/03/28</td>
                                                    <td>ETB 62,700</td>
                                                </tr>
                                                <tr>
                                                <td>Ermiyas kafe</td>
                                                    <td>Accountant</td>
                                                    <td>Bardar</td>
                                                    <td>23</td>
                                                    <td>2025/03/28</td>
                                                    <td>ETB 62,700</td>
                                                </tr>
                                                <tr>
                                                <td>Jiregna Ayana</td>
                                                    <td>Accountant</td>
                                                    <td>Finfine</td>
                                                    <td>23</td>
                                                    <td>2025/03/28</td>
                                                    <td>ETB 62,700</td>
                                                </tr>
                                                
                                                <tr>
                                                <td>Esiyak Bella</td>
                                                    <td>Assistant</td>
                                                    <td>Finfine</td>
                                                    <td>23</td>
                                                    <td>2025/03/28</td>
                                                    <td>ETB 62,700</td>
                                                </tr>
                                                <tr>
                                                    <td>Selam </td>
                                                    <td>Customer Support</td>
                                                    <td>Finfine</td>
                                                    <td>27</td>
                                                    <td>2025/01/25</td>
                                                    <td>ETB 12,000</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    
                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                    </div>
                    <!-- end row-->
					
					
					
				
				</div>			
			</div>
			<!-- /Main Wrapper -->

		
		<!-- jQuery -->
        <script src="assets/js/jquery-3.2.1.min.js"></script>
		
		<!-- Bootstrap Core JS -->
        <script src="assets/js/popper.min.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>
		
		<!-- Slimscroll JS -->
        <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
		
		<!-- Datatables JS -->
		<script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
		<script src="assets/plugins/datatables/dataTables.bootstrap4.min.js"></script>
		<script src="assets/plugins/datatables/dataTables.responsive.min.js"></script>
		<script src="assets/plugins/datatables/responsive.bootstrap4.min.js"></script>
		
		<script src="assets/plugins/datatables/dataTables.select.min.js"></script>
		
		<script src="assets/plugins/datatables/dataTables.buttons.min.js"></script>
		<script src="assets/plugins/datatables/buttons.bootstrap4.min.js"></script>
		<script src="assets/plugins/datatables/buttons.html5.min.js"></script>
		<script src="assets/plugins/datatables/buttons.flash.min.js"></script>
		<script src="assets/plugins/datatables/buttons.print.min.js"></script>
		
		<!-- Custom JS -->
		<script  src="assets/js/script.js"></script>
		
    </body>
</html>
