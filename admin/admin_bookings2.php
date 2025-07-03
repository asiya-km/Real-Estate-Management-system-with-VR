<?php
session_start();
include("config.php");

if (!isset($_SESSION['auser'])) {
    header("location:../login1.php");
    exit();
}

// Fetch all bookings
$query = "SELECT b.id, p.title, u.uname, b.booking_date, b.status 
          FROM bookings b 
          JOIN property p ON b.property_id = p.pid 
          JOIN user u ON b.user_id = u.uid";
$result = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings</title>
    <!-- Include your CSS files here -->
         <!-- ... keep head section the same ... -->
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
</head>
<body>
<?php include("header.php"); ?>
<div class="page-wrapper">
     
        
     <div class="content container-fluid">
         <!-- Page Header -->
         <div class="page-header">
             <!-- ... keep header the same ... -->
             <?php include("header.php"); ?>
         </div>

         <!-- Messages -->
         <?php if(isset($_SESSION['success'])): ?>
             <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
         <?php endif; ?>
         <?php if(isset($_SESSION['error'])): ?>
             <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
         <?php endif; ?>

         <div class="row">
             <div class="col-12">
                 <div class="card">
                     <div class="card-body">

    <h1>Manage Bookings</h1>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Property</th>
                <th>User</th>
                <th>Booking Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['title']; ?></td>
                    <td><?php echo $row['uname']; ?></td>
                    <td><?php echo $row['booking_date']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td>
                        <a href="update_booking.php?id=<?php echo $row['id']; ?>&status=confirmed" class="btn btn-success">Confirm</a>
                        <a href="update_booking.php?id=<?php echo $row['id']; ?>&status=canceled" class="btn btn-danger">Cancel</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript files -->
    <script src="assets/js/jquery-3.2.1.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <!-- Add DataTables JS if needed -->
</body>
</html>