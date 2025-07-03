<?php
session_start();
include("config.php");

if (!isset($_SESSION['uid'])) {
    header("location:login1.php");
    exit();
}

$user_id = $_SESSION['uid'];

// Check if adding or removing a favorite
if (isset($_GET['action']) && isset($_GET['property_id'])) {
    $property_id = intval($_GET['property_id']);
    
    if ($_GET['action'] === 'add') {
        // Check if already favorited
        $check_query = "SELECT * FROM favorites WHERE user_id = ? AND property_id = ?";
        $stmt = mysqli_prepare($con, $check_query);
        mysqli_stmt_bind_param($stmt, 'ii', $user_id, $property_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) == 0) {
            // Add to favorites
            $insert_query = "INSERT INTO favorites (user_id, property_id, date_added) VALUES (?, ?, NOW())";
            $stmt = mysqli_prepare($con, $insert_query);
            mysqli_stmt_bind_param($stmt, 'ii', $user_id, $property_id);
            mysqli_stmt_execute($stmt);
        }
    } elseif ($_GET['action'] === 'remove') {
        // Remove from favorites
        $delete_query = "DELETE FROM favorites WHERE user_id = ? AND property_id = ?";
        $stmt = mysqli_prepare($con, $delete_query);
        mysqli_stmt_bind_param($stmt, 'ii', $user_id, $property_id);
        mysqli_stmt_execute($stmt);
    }
    
    // Redirect back to the referring page or favorites page
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'favorites.php';
    header("Location: $redirect");
    exit();
}

// Fetch favorite properties
$query = "SELECT f.*, p.title, p.pcontent, p.price, p.location, p.city, p.pimage, p.type, p.stype, p.status 
          FROM favorites f 
          JOIN property p ON f.property_id = p.pid 
          WHERE f.user_id = ? 
          ORDER BY f.date_added DESC";
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
    <title>Favorite Properties - Remsko Real Estate</title>
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
        .property-card {
            transition: transform 0.3s;
            margin-bottom: 30px;
            height: 100%;
        }
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .property-image {
            height: 200px;
            object-fit: cover;
        }
        .favorite-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .favorite-btn:hover {
            background-color: #fff;
            transform: scale(1.1);
        }
        .favorite-btn i {
            color: #dc3545;
            font-size: 20px;
        }
        .property-status {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-available {
            background-color: #28a745;
            color: white;
        }
        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }
        .status-booked {
            background-color: #dc3545;
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
                    <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>Favorite Properties</b></h2>
                </div>
                <div class="col-md-6">
                    <nav aria-label="breadcrumb" class="float-left float-md-right">
                        <ol class="breadcrumb bg-transparent m-0 p-0">
                            <li class="breadcrumb-item text-white"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item text-white"><a href="user_dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Favorite Properties</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
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
                            <h4 class="double-down-line-left text-secondary position-relative pb-4 mb-4">My Favorite Properties</h4>
                        </div>
                    </div>
                    
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <div class="row">
                            <?php while ($property = mysqli_fetch_assoc($result)): ?>
                                <div class="col-md-6">
                                    <div class="card property-card">
                                        <div class="position-relative">
                                            <img src="admin/property/<?php echo htmlspecialchars($property['pimage']); ?>" class="card-img-top property-image" alt="<?php echo htmlspecialchars($property['title']); ?>">
                                            
                                            <!-- Status Badge -->
                                            <span class="property-status status-<?php echo strtolower($property['status']); ?>">
                                                <?php echo ucfirst($property['status']); ?>
                                            </span>
                                            
                                            <!-- Favorite Button -->
                                            <a href="favorites.php?action=remove&property_id=<?php echo $property['property_id']; ?>" class="favorite-btn" title="Remove from favorites">
                                                <i class="fas fa-heart"></i>
                                            </a>
                                        </div>
                                        
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <a href="propertydetail.php?pid=<?php echo $property['property_id']; ?>" class="text-secondary">
                                                    <?php echo htmlspecialchars($property['title']); ?>
                                                </a>
                                            </h5>
                                            <p class="card-text text-muted">
                                                <i class="fas fa-map-marker-alt text-success mr-2"></i>
                                                <?php echo htmlspecialchars($property['location']); ?>, <?php echo htmlspecialchars($property['city']); ?>
                                            </p>
                                            <p class="card-text">
                                                <strong>Price:</strong> ETB <?php echo number_format($property['price']); ?>
                                            </p>
                                            <p class="card-text">
                                                <strong>Type:</strong> <?php echo htmlspecialchars($property['type']); ?> for <?php echo htmlspecialchars($property['stype']); ?>
                                            </p>
                                            
                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                <a href="propertydetail.php?pid=<?php echo $property['property_id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View Details
                                                </a>
                                                
                                                <?php if ($property['status'] === 'available'): ?>
                                                    <a href="book_property.php?id=<?php echo $property['property_id']; ?>" class="btn btn-sm btn-success">
                                                        <i class="fas fa-bookmark"></i> Book Now
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i> You haven't added any properties to your favorites yet.
                            <div class="mt-3">
                                <a href="property.php" class="btn btn-primary">Browse Properties</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php include("include/footer.php"); ?>
    
    <!-- JavaScript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>
