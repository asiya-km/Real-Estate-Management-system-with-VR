<?php
session_start();
include("config.php");

if (!isset($_SESSION['uid'])) {
    header("location:login1.php");
    exit();
}

$user_id = $_SESSION['uid'];

// Fetch user's visits
$query = "SELECT v.*, p.title, p.pimage, p.location, p.city, p.price 
          FROM visits v 
          JOIN property p ON v.property_id = p.pid 
          WHERE v.user_id = ? 
          ORDER BY v.visit_date DESC";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Property Visits - Remsko Real Estate</title>
    <!-- Include CSS files -->
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
    <style>
        .visit-card {
            transition: all 0.3s;
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .visit-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        .visit-image {
            height: 200px;
            object-fit: cover;
        }
        .visit-status {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-confirmed {
            background-color: #28a745;
            color: white;
        }
        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }
        .status-cancelled {
            background-color: #dc3545;
            color: white;
        }
        .status-completed {
            background-color: #17a2b8;
            color: white;
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
                    <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>My Property Visits</b></h2>
                </div>
                <div class="col-md-6">
                    <nav aria-label="breadcrumb" class="float-left float-md-right">
                        <ol class="breadcrumb bg-transparent m-0 p-0">
                            <li class="breadcrumb-item text-white"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item text-white"><a href="user_dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">My Property Visits</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- Add this right after the opening of your content div -->
<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <?php echo htmlspecialchars($_GET['success']); ?>
    </div>
<?php endif; ?>
    <!-- Content -->
    <div class="full-row">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <div class="mb-4">
                        <a href="user_dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                        </a>
                    </div>
                    <?php include("include/user_sidebar.php"); ?>
                </div>
                
                <div class="col-lg-8">
                    <div class="row">
                        <div class="col-lg-12">
                            <h4 class="double-down-line-left text-secondary position-relative pb-4 mb-4">My Property Visits</h4>
                        </div>
                    </div>
                    
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($visit = mysqli_fetch_assoc($result)): ?>
                            <div class="visit-card">
                                <div class="card">
                                    <div class="row no-gutters">
                                        <div class="col-md-4 position-relative">
                                            <img src="admin/property/<?php echo htmlspecialchars($visit['pimage']); ?>" class="visit-image w-100" alt="Property">
                                            
                                            <!-- Status Badge -->
                                            <span class="visit-status status-<?php echo strtolower($visit['status']); ?>">
                                                <?php echo ucfirst($visit['status']); ?>
                                            </span>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="card-body">
                                                <h5 class="card-title">
                                                    <a href="propertydetail.php?pid=<?php echo $visit['property_id']; ?>" class="text-secondary">
                                                        <?php echo htmlspecialchars($visit['title']); ?>
                                                    </a>
                                                </h5>
                                                <p class="card-text text-muted">
                                                    <i class="fas fa-map-marker-alt text-success mr-2"></i>
                                                    <?php echo htmlspecialchars($visit['location']); ?>, <?php echo htmlspecialchars($visit['city']); ?>
                                                </p>
                                                <p class="card-text">
                                                    <strong>Visit Date:</strong> <?php echo date('M d, Y', strtotime($visit['visit_date'])); ?>
                                                </p>
                                                <p class="card-text">
                                                    <strong>Visit Time:</strong> <?php echo date('h:i A', strtotime($visit['visit_time'])); ?>
                                                </p>
                                                
                                                <div class="mt-3">
                                                    <a href="visit_details.php?id=<?php echo $visit['id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> View Details
                                                    </a>
                                                    
                                                    <?php if ($visit['status'] == 'pending'): ?>
                                                        <a href="reschedule_visit.php?id=<?php echo $visit['id']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-calendar-alt"></i> Reschedule
                                                        </a>
                                                        
                                                        <a href="cancel_visit.php?id=<?php echo $visit['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this visit?')">
                                                            <i class="fas fa-times"></i> Cancel
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($visit['status'] == 'completed'): ?>
                                                        <a href="book_property.php?id=<?php echo $visit['property_id']; ?>" class="btn btn-sm btn-success">
                                                            <i class="fas fa-bookmark"></i> Book Property
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i> You haven't scheduled any property visits yet.
                            <div class="mt-3">
                                <a href="property.php" class="btn btn-primary">Browse Properties</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    
    
    <!-- JavaScript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>
