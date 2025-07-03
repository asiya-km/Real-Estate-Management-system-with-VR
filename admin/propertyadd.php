<?php
// Add these lines at the top of the file
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require("config.php");

if (!isset($_SESSION['auser'])) {
    header("location:../login1.php");
    exit();
}

$error = "";
$msg = "";

// Initialize variables to store form data
$title = $ptype = $bed = $bath = $kitc = $floor = $price = $city = $asize = $loc = $state = $status = $uid = $isFeatured = $stype = "";
$tour_config = ""; // Initialize tour_config variable

if (isset($_POST['add'])) {
    // Validate and sanitize inputs
    $title = mysqli_real_escape_string($con, $_POST['title']);
    $content = mysqli_real_escape_string($con, $_POST['content']);
    $ptype = mysqli_real_escape_string($con, $_POST['ptype']);
    
    $bed = mysqli_real_escape_string($con, $_POST['bed']);
    $stype = mysqli_real_escape_string($con, $_POST['stype']);
    $bath = mysqli_real_escape_string($con, $_POST['bath']);
    $kitc = mysqli_real_escape_string($con, $_POST['kitc']);
    $floor = mysqli_real_escape_string($con, $_POST['floor']);
    $price = mysqli_real_escape_string($con, $_POST['price']);
    $asize = mysqli_real_escape_string($con, $_POST['asize']);
    $loc = mysqli_real_escape_string($con, $_POST['loc']);
    $city = mysqli_real_escape_string($con, $_POST['city']);
    $state = mysqli_real_escape_string($con, $_POST['state']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    $uid = mysqli_real_escape_string($con, $_POST['uid']);
    $feature = mysqli_real_escape_string($con, $_POST['feature'] ?? '');
    $isFeatured = mysqli_real_escape_string($con, $_POST['isFeatured']);
    $latitude = mysqli_real_escape_string($con, $_POST['latitude'] ?? '');
    $longitude = mysqli_real_escape_string($con, $_POST['longitude'] ?? '');
    $map_zoom = mysqli_real_escape_string($con, $_POST['map_zoom'] ?? '14');
    $tour_config = mysqli_real_escape_string($con, $_POST['tour_config'] ?? ''); // Get tour_config from form

    // Validate minimum price based on selling type
    $priceError = false;
    if ($stype == 'rent' && floatval($price) < 50000) {
        $error = "<p class='alert alert-warning'>Rental properties must have a minimum price of 50,000 birr</p>";
        $priceError = true;
    } elseif ($stype == 'sale' && floatval($price) < 500000) {
        $error = "<p class='alert alert-warning'>Properties for sale must have a minimum price of 500,000 birr</p>";
        $priceError = true;
    }
    
    // Only proceed if there's no price error
    if (!$priceError) {
        // Handle file uploads
        $uploadErrors = [];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif','webp'];
        $uploadDir = "admin/property/";

        // Make sure upload directories exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        if (!is_dir($uploadDir . "panoramas/")) {
            mkdir($uploadDir . "panoramas/", 0755, true);
        }

        // Function to handle file uploads
        function handleUpload($fileKey, $uploadDir, $allowedExtensions, &$uploadErrors, $isPanorama = false) {
            // If file is not uploaded, return empty string instead of null
            if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) {
                return '';
            }
            
            if ($_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
                $uploadErrors[] = "File $fileKey upload failed with error code: " . $_FILES[$fileKey]['error'];
                return '';
            }
            
            $fileExt = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
            if (!in_array($fileExt, $allowedExtensions)) {
                $uploadErrors[] = "Invalid file type for $fileKey.";
                return '';
            }
            
            $fileName = uniqid('', true) . '.' . $fileExt;
            
            // Use different directory for panorama images
            $targetDir = $isPanorama ? $uploadDir . "panoramas/" : $uploadDir;
            
            if (!move_uploaded_file($_FILES[$fileKey]['tmp_name'], $targetDir . $fileName)) {
                $uploadErrors[] = "Failed to move uploaded file $fileKey.";
                return '';
            }
            return $fileName;
        }

        // Process each file upload - make them optional
        $aimage = handleUpload('aimage', $uploadDir, $allowedExtensions, $uploadErrors);
        $aimage1 = handleUpload('aimage1', $uploadDir, $allowedExtensions, $uploadErrors);
        $aimage2 = handleUpload('aimage2', $uploadDir, $allowedExtensions, $uploadErrors);
        $panorama_image = handleUpload('panorama_image', $uploadDir, $allowedExtensions, $uploadErrors, true);
        $fimage = handleUpload('fimage', $uploadDir, $allowedExtensions, $uploadErrors);
        $fimage1 = handleUpload('fimage1', $uploadDir, $allowedExtensions, $uploadErrors);
        $fimage2 = handleUpload('fimage2', $uploadDir, $allowedExtensions, $uploadErrors);

        if (!empty($uploadErrors)) {
            $error = "<p class='alert alert-warning'>" . implode("<br>", $uploadErrors) . "</p>";
        } else {
            // Update the SQL statement - removed aimage2, aimage3, aimage4 fields
            $sql = "INSERT INTO property (title, pcontent, type, stype, bedroom, bathroom, kitchen, floor, size, price, location, city, state, feature, pimage, pimage1,pimage2, panorama_image, tour_config, uid, status, mapimage, topmapimage, groundmapimage, date, isFeatured, latitude, longitude, map_zoom) 
                    VALUES (?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($con, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'ssssssssssssssssssssssssssss', 
                    $title, $content, $ptype, $stype, $bed, $bath, $kitc, $floor, $asize, $price, $loc, $city, $state, $feature, 
                    $aimage, $aimage1,$aimage2, $panorama_image, $tour_config, $uid, $status, $fimage, $fimage1, $fimage2, $isFeatured, $latitude, $longitude, $map_zoom);
                
                if (mysqli_stmt_execute($stmt)) {
                    $msg = "<p class='alert alert-success'>Property Inserted Successfully</p>";
                    // Reset form fields after successful submission
                    $title = $ptype = $bed = $bath = $kitc = $floor = $price = $city = $asize = $loc = $state = $status = $uid = $isFeatured = $stype = "";
                } else {
                    $error = "<p class='alert alert-warning'>Error: " . mysqli_stmt_error($stmt) . "</p>";
                }
                mysqli_stmt_close($stmt);
            } else {
                $error = "<p class='alert alert-warning'>Error preparing statement: " . mysqli_error($con) . "</p>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
        <title>LM HOMES | Property</title>
		
		<!-- Favicon -->
        <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.png">
		
		<!-- Bootstrap CSS -->
        <link rel="stylesheet" href="assets/css/bootstrap.min.css">
		
		<!-- Fontawesome CSS -->
        <link rel="stylesheet" href="assets/css/font-awesome.min.css">
		
		<!-- Feathericon CSS -->
        <link rel="stylesheet" href="assets/css/feathericon.min.css">
		
		<!-- Main CSS -->
        <link rel="stylesheet" href="assets/css/style.css">

        <!-- Add Leaflet CSS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
              crossorigin=""/>
        <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
		
		<!--[if lt IE 9]>
			<script src="assets/js/html5shiv.min.js"></script>
			<script src="assets/js/respond.min.js"></script>
		<![endif]-->
</head>
<body>
    <!-- Main Wrapper -->
    <div class="main-wrapper">
        
        <!-- Header -->
        <?php include("header.php"); ?>
        <!-- /Header -->
        
        <!-- Page Wrapper -->
        <div class="page-wrapper">
            <div class="content container-fluid">
            
                <!-- Page Header -->
                <div class="page-header">
                    <div class="row">
                        <div class="col"><p>.</p>
                            <h3 class="page-title">Property</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Property</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /Page Header -->
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Add Property Details</h4>
                            </div>
                            <form method="post" enctype="multipart/form-data">
                                <div class="card-body">
                                    <h5 class="card-title">Property Detail</h5>
                                    <?php echo $error; ?>
                                    <?php echo $msg; ?>
                                    
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="form-group row">
                                                <label class="col-lg-2 col-form-label">Title</label>
                                                <div class="col-lg-9">
                                                    <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($title); ?>" required placeholder="Enter Title">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-lg-2 col-form-label">Content</label>
                                                <div class="col-lg-9">
                                                    <textarea class="tinymce form-control" name="content" rows="10" cols="30"></textarea>
                                                </div>
                                            </div>
                                            
                                        </div>
                                        <div class="col-xl-6">
                                            <div class="form-group row">
                                                <label class="col-lg-3 col-form-label">Property Type</label>
                                                <div class="col-lg-9">
                                                    <select class="form-control" required name="ptype">
                                                        <option value="">Select Type</option>
                                                        <option value="apartment" <?php if($ptype == 'apartment') echo 'selected'; ?>>Apartment</option>
                                                        <option value="flat" <?php if($ptype == 'flat') echo 'selected'; ?>>Flat</option>
                                                        <option value="building" <?php if($ptype == 'building') echo 'selected'; ?>>Building</option>
                                                        <option value="house" <?php if($ptype == 'house') echo 'selected'; ?>>House</option>
                                                        <option value="villa" <?php if($ptype == 'villa') echo 'selected'; ?>>Villa</option>
                                                        <option value="office" <?php if($ptype == 'office') echo 'selected'; ?>>Office</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-lg-3 col-form-label">Selling Type</label>
                                                <div class="col-lg-9">
                                                    <select class="form-control" required name="stype">
                                                        <option value="">Select Status</option>
                                                        <option value="rent" <?php if($stype == 'rent') echo 'selected'; ?>>Rent</option>
                                                        <option value="sale" <?php if($stype == 'sale') echo 'selected'; ?>>Sale</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-lg-3 col-form-label">Bathroom</label>
                                                <div class="col-lg-9">
                                                    <input type="text" class="form-control" name="bath" value="<?php echo htmlspecialchars($bath); ?>" required placeholder="Enter Bathroom (only no 1 to 10)">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-lg-3 col-form-label">Kitchen</label>
                                                <div class="col-lg-9">
                                                         <input type="text" class="form-control" name="kitc" value="<?php echo htmlspecialchars($kitc); ?>" required placeholder="Enter Kitchen (only no 1 to 10)">
                                                </div>
                                            </div>
                                            
                                        </div>   
                                        <div class="col-xl-6">
                                            <div class="form-group row">
                                                <label class="col-lg-3 col-form-label">Bedroom</label>
                                                <div class="col-lg-9">
                                                    <input type="text" class="form-control" name="bed" value="<?php echo htmlspecialchars($bed); ?>" required placeholder="Enter Bedroom  (only no 1 to 10)">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <h4 class="card-title">Price & Location</h4>
                                    <div class="row">
                                        <div class="col-xl-6">
                                            <div class="form-group row">
                                                <label class="col-lg-3 col-form-label">Floor</label>
                                                <div class="col-lg-9">
                                                    <select class="form-control" required name="floor">
                                                        <option value="">Select Floor</option>
                                                        <option value="1st Floor" <?php if($floor == '1st Floor') echo 'selected'; ?>>1st Floor</option>
                                                        <option value="2nd Floor" <?php if($floor == '2nd Floor') echo 'selected'; ?>>2nd Floor</option>
                                                        <option value="3rd Floor" <?php if($floor == '3rd Floor') echo 'selected'; ?>>3rd Floor</option>
                                                        <option value="4th Floor" <?php if($floor == '4th Floor') echo 'selected'; ?>>4th Floor</option>
                                                        <option value="5th Floor" <?php if($floor == '5th Floor') echo 'selected'; ?>>5th Floor</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-lg-3 col-form-label">Price</label>
                                                <div class="col-lg-9">
                                                    <input type="text" class="form-control" name="price" value="<?php echo htmlspecialchars($price); ?>" required placeholder="Enter Price">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-lg-3 col-form-label">City</label>
                                                <div class="col-lg-9">
                                                    <input type="text" class="form-control" name="city" value="<?php echo htmlspecialchars($city); ?>" required placeholder="Enter City">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-lg-3 col-form-label">State</label>
                                                <div class="col-lg-9">
                                                    <input type="text" class="form-control" name="state" value="<?php echo htmlspecialchars($state); ?>" required placeholder="Enter State">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-6">
                                            <div class="form-group row">
                                                <label class="col-lg-3 col-form-label">Area Size</label>
                                                <div class="col-lg-9">
                                                    <input type="text" class="form-control" name="asize" value="<?php echo htmlspecialchars($asize); ?>" required placeholder="Enter Area Size (in sqrt)">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-lg-3 col-form-label">Address</label>
                                                <div class="col-lg-9">
                                                    <input type="text" class="form-control" name="loc" value="<?php echo htmlspecialchars($loc); ?>" required placeholder="Enter Address">
                                                </div>
                                            </div>

                                            <!-- Add Map Picker -->
                                            <div class="form-group row">
                                                <label class="col-lg-3 col-form-label">Property Location</label>
                                                <div class="col-lg-9">
                                                    <div class="map-picker-container">
                                                        <div id="map-picker" style="height: 400px; width: 100%; margin-bottom: 15px;"></div>
                                                        <input type="text" id="search-location" class="form-control" placeholder="Search for a location" style="margin-bottom: 15px;">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" id="latitude" name="latitude" placeholder="Latitude">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" id="longitude" name="longitude" placeholder="Longitude">
                                                            </div>
                                                        </div>
                                                        <small class="form-text text-muted">Click on the map to set the property location or search for an address above.</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-lg-3 col-form-label">Map Zoom Level</label>
                                                <div class="col-lg-9">
                                                    <select class="form-control" name="map_zoom" id="map-zoom">
                                                        <option value="14">Default (14)</option>
                                                        <option value="16">Close (16)</option>
                                                        <option value="18">Very Close (18)</option>
                                                        <option value="12">Far (12)</option>
                                                        <option value="10">Very Far (10)</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Feature section - simplified -->
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Feature</label>
                                        <div class="col-lg-9">
                                            <textarea class="form-control" name="feature" rows="5" placeholder="Enter property features"></textarea>
                                            <small class="form-text text-muted">Enter features like Swimming Pool, Parking, GYM, etc.</small>
                                        </div>
                                    </div>
                                          
                                    <h4 class="card-title">Image & Status</h4>
                                    <div class="row">
                                        <div class="col-xl-6">
                                            
                                            <div class="form-group row">
                                                <label class="col-lg-3 col-form-label">Main Image</label>
                                                <div class="col-lg-9">
                                                    <input class="form-control" name="aimage" type="file">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-lg-3 col-form-label">Image 1</label>
                                                <div class="col-lg-9">
                                                    <input class="form-control" name="aimage1" type="file">
                                                </div>
                                            </div>
                                                                                        <div class="form-group row">
                                                <label class="col-lg-3 col-form-label">fooler plan</label>
                                                <div class="col-lg-9">
                                                    <input class="form-control" name="aimage2" type="file">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-lg-3 col-form-label">Status</label>
                                                <div class="col-lg-9">
                                                    <select class="form-control" required name="status">
                                                        <option value="">Select Status</option>
                                                        <option value="available" <?php if($status == 'available') echo 'selected'; ?>>Available</option>
                                                        <option value="sold out" <?php if($status == 'sold out') echo 'selected'; ?>>Sold Out</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-6">
                                            <div class="form-group row">
                                                <label class="col-lg-3 col-form-label">Panorama Image</label>
                                                <div class="col-lg-9">
                                                    <input class="form-control" name="panorama_image" type="file">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-lg-3 col-form-label">Uid</label>
                                                <div class="col-lg-9">
                                                    <input type="text" class="form-control" name="uid" value="<?php echo htmlspecialchars($uid); ?>" required placeholder="Enter User Id (only number)">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-xl-6">
                                            <div class="form-group row">
                                                <label class="col-lg-3 col-form-label">Panorama Image2</label>
                                                <div class="col-lg-9">
                                                    <input class="form-control" name="fimage" type="file">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-6">
                                            <div class="form-group row">
                                                <label class="col-lg-3 col-form-label">Panorama Image3 </label>
                                                <div class="col-lg-9">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <input class="form-control mb-2" name="fimage1" type="file" placeholder="Floor Plan 1">
                                                        </div><br>
                                                        <div class="col-md-6">
                                                            <input class="form-control" name="fimage2" type="file" placeholder="Floor Plan 2">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-lg-3 col-form-label"><b>Is Featured?</b></label>
                                                <div class="col-lg-9">
                                                    <select class="form-control" required name="isFeatured">
                                                        <option value="">Select...</option>
                                                        <option value="0" <?php if($isFeatured == '0') echo 'selected'; ?>>No</option>
                                                        <option value="1" <?php if($isFeatured == '1') echo 'selected'; ?>>Yes</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-center mt-4">
                                        <input type="submit" value="Submit" class="btn btn-primary" name="add">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>			
        </div>
        <!-- /Page Wrapper -->
    </div>
    <!-- /Main Wrapper -->

    <!-- jQuery -->
    <script src="assets/js/jquery-3.2.1.min.js"></script>
    <script src="assets/plugins/tinymce/tinymce.min.js"></script>
    <script src="assets/plugins/tinymce/init-tinymce.min.js"></script>
    
    <!-- Bootstrap Core JS -->
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    
    <!-- Slimscroll JS -->
    <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>

    <!-- Add Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
            crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

    <script>
        // Initialize the map picker when the page loads
        let map, marker, currentZoom = 14;
        
        // Default coordinates (Ethiopia)
        const defaultLat = 9.0222;
        const defaultLng = 38.7468;
        
        document.addEventListener('DOMContentLoaded', function() {
            initMapPicker();
            
            // Initialize JSON validation
            document.getElementById('tour_config').addEventListener('change', validateJson);
        });
        
        function initMapPicker() {
            try {
                // Initialize the map
                map = L.map('map-picker').setView([defaultLat, defaultLng], currentZoom);
                
                // Add OpenStreetMap tile layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);
                
                // Create a marker that will be placed on the map
                marker = L.marker([defaultLat, defaultLng], {
                    draggable: true
                }).addTo(map);
                
                // Update coordinates when marker is dragged
                marker.on('dragend', function() {
                    const position = marker.getLatLng();
                    updateCoordinates(position);
                });
                
                // Update coordinates when map is clicked
                map.on('click', function(event) {
                    marker.setLatLng(event.latlng);
                    updateCoordinates(event.latlng);
                });
                
                // Add geocoder control for search functionality
                const geocoder = L.Control.geocoder({
                    defaultMarkGeocode: false
                }).addTo(map);
                
                geocoder.on('markgeocode', function(event) {
                    const result = event.geocode;
                    const latlng = result.center;
                    
                    marker.setLatLng(latlng);
                    map.setView(latlng, 16);
                    updateCoordinates(latlng);
                });
                
                // Listen for zoom changes
                map.on('zoomend', function() {
                    currentZoom = map.getZoom();
                    document.getElementById('map-zoom').value = currentZoom;
                });
                
                // Set initial coordinates
                updateCoordinates(marker.getLatLng());
                
                // Handle manual coordinate input
                document.getElementById('latitude').addEventListener('change', updateMarkerFromInput);
                 document.getElementById('longitude').addEventListener('change', updateMarkerFromInput);
                
                // Handle zoom level changes
                document.getElementById('map-zoom').addEventListener('change', function() {
                    currentZoom = parseInt(this.value);
                    map.setZoom(currentZoom);
                });
            } catch (e) {
                console.error("Error initializing map:", e);
            }
        }
        
        // Update the coordinate input fields
        function updateCoordinates(position) {
            document.getElementById('latitude').value = position.lat.toFixed(6);
            document.getElementById('longitude').value = position.lng.toFixed(6);
        }
        
        // Update marker position when coordinates are manually entered
        function updateMarkerFromInput() {
            const lat = parseFloat(document.getElementById('latitude').value);
            const lng = parseFloat(document.getElementById('longitude').value);
            
            if (!isNaN(lat) && !isNaN(lng)) {
                const newLatLng = L.latLng(lat, lng);
                marker.setLatLng(newLatLng);
                map.setView(newLatLng, currentZoom);
            }
        }
        
        // Validate JSON format
        function validateJson() {
            const jsonInput = document.getElementById('tour_config');
            const validationMessage = document.getElementById('json_validation_message');
            
            try {
                JSON.parse(jsonInput.value);
                validationMessage.innerHTML = '<div class="alert alert-success">Valid JSON format</div>';
            } catch (e) {
                validationMessage.innerHTML = '<div class="alert alert-danger">Invalid JSON format: ' + e.message + '</div>';
            }
        }
    </script>
</body>
</html>
              