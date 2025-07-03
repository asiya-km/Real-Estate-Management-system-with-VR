<?php
session_start();
require("config.php");

if (!isset($_SESSION['uemail'])) {
    header("Location: ../login1.php");
    exit();
}

$error = "";
$msg = "";

if (isset($_POST['add'])) {
    $pid = intval($_GET['id']);
    
    // Retrieve existing data
    $existingQuery = mysqli_prepare($con, "SELECT * FROM property WHERE pid = ?");
    mysqli_stmt_bind_param($existingQuery, 'i', $pid);
    mysqli_stmt_execute($existingQuery);
    $existingData = mysqli_stmt_get_result($existingQuery)->fetch_assoc();
    mysqli_stmt_close($existingQuery);

    // Sanitize inputs
    $fields = [
        'title', 'content', 'ptype', 'bed', 'stype', 
        'bath', 'kitc', 'floor', 'price', 'city', 'asize', 'loc', 
        'state', 'status', 'feature', 'isFeatured',
        'latitude', 'longitude', 'map_zoom', 'tour_config'
    ];
    
    $data = [];
    foreach ($fields as $field) {
        $data[$field] = mysqli_real_escape_string($con, $_POST[$field] ?? '');
    }

    // Validate numeric fields
    if (!is_numeric($data['bed']) || intval($data['bed']) < 0) {
        $error .= "<p class='alert alert-warning'>Bedroom must be a positive number</p>";
    }
    if (!is_numeric($data['bath']) || intval($data['bath']) < 0) {
        $error .= "<p class='alert alert-warning'>Bathroom must be a positive number</p>";
    }
    if (!is_numeric($data['kitc']) || intval($data['kitc']) < 0) {
        $error .= "<p class='alert alert-warning'>Kitchen must be a positive number</p>";
    }
    if (!is_numeric($data['price']) || floatval($data['price']) <= 0) {
        $error .= "<p class='alert alert-warning'>Price must be a positive number</p>";
    }

    // Price validation based on selling type
    $priceError = false;
    if ($data['stype'] == 'rent' && floatval($data['price']) < 50000) {
        $error .= "<p class='alert alert-warning'>Rental properties must have a minimum price of 50,000 birr</p>";
        $priceError = true;
    } elseif ($data['stype'] == 'sale' && floatval($data['price']) < 500000) {
        $error .= "<p class='alert alert-warning'>Properties for sale must have a minimum price of 500,000 birr</p>";
        $priceError = true;
    }
    
    // Only proceed if there's no error
    if (empty($error)) {
        // File upload handling
        $uploadDir = "../admin/property/";
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
$fileFields = [
    'aimage', 'aimage1', 'aimage2', 'fimage', 'fimage1', 'fimage2', 'panorama_image'
];

        // Map file field names to database column names
$fileFieldMapping = [
    'aimage' => 'pimage',
    'aimage1' => 'pimage1',
    'aimage2' => 'pimage2',  // Add this line
    'fimage' => 'mapimage',
    'fimage1' => 'topmapimage',
    'fimage2' => 'groundmapimage',
    'panorama_image' => 'panorama_image'
];

        foreach ($fileFields as $fileField) {
            $dbField = $fileFieldMapping[$fileField];
            
            if (!empty($_FILES[$fileField]['name'])) {
                $file = $_FILES[$fileField];
                
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $error .= "Error uploading $fileField. ";
                    $data[$dbField] = $existingData[$dbField];
                    continue;
                }
                
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExtensions)) {
                    $error .= "Invalid file type for $fileField. ";
                    $data[$dbField] = $existingData[$dbField];
                    continue;
                }
                
                $newFilename = uniqid() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $newFilename)) {
                    $data[$dbField] = $newFilename;
                    // Delete old file if exists
                    if (!empty($existingData[$dbField])) {
                        @unlink($uploadDir . $existingData[$dbField]);
                    }
                } else {
                    $error .= "Failed to move $fileField. ";
                    $data[$dbField] = $existingData[$dbField];
                }
            } else {
                // Keep existing image if no new one uploaded
                $data[$dbField] = $existingData[$dbField];
            }
        }

        // Use the manager's UID
        $data['uid'] = $_SESSION['uemail'];

        // Prepare SQL statement based on your table structure
$sql = "UPDATE property SET 
    title = ?, pcontent = ?, type = ?, stype = ?,
    bedroom = ?, bathroom = ?, kitchen = ?, 
    floor = ?, size = ?, price = ?, location = ?, city = ?, state = ?, 
    feature = ?, pimage = ?, pimage1 = ?, pimage2 = ?, panorama_image = ?, uid = ?, status = ?, 
    mapimage = ?, topmapimage = ?, groundmapimage = ?, isFeatured = ?, 
    tour_config = ?, latitude = ?, longitude = ?, map_zoom = ? 
    WHERE pid = ?";
        
        $stmt = mysqli_prepare($con, $sql);
$params = [
    $data['title'], $data['content'], $data['ptype'], $data['stype'],
    $data['bed'], $data['bath'], $data['kitc'], 
    $data['floor'], $data['asize'], $data['price'], $data['loc'], $data['city'],
    $data['state'], $data['feature'], 
    $data['pimage'], $data['pimage1'], $data['pimage2'], $data['panorama_image'], $data['uid'], $data['status'], 
    $data['mapimage'], $data['topmapimage'], $data['groundmapimage'],
    $data['isFeatured'], $data['tour_config'], $data['latitude'], $data['longitude'], $data['map_zoom'], 
    $pid
];



        mysqli_stmt_bind_param($stmt, str_repeat('s', count($params) - 1) . 'i', ...$params);
        
        if (mysqli_stmt_execute($stmt)) {
            $msg = "<p class='alert alert-success'>Property Updated</p>";
            $_SESSION['message'] = $msg;
        } else {
            $error = "<p class='alert alert-warning'>Error: " . mysqli_error($con) . "</p>";
            $_SESSION['message'] = $error;
        }
        
        mysqli_stmt_close($stmt);
        header("Location: propertyview.php?msg=$msg");
        exit();
    }
}

// Fetch existing data for editing
$pid = intval($_GET['id']);
$query = mysqli_prepare($con, "SELECT * FROM property WHERE pid = ?");
mysqli_stmt_bind_param($query, 'i', $pid);
mysqli_stmt_execute($query);
$row = mysqli_stmt_get_result($query)->fetch_assoc();
mysqli_stmt_close($query);

if (!$row) {
    die("Invalid property ID");
}
?>

<!-- HTML remains mostly the same with proper selected attributes added to dropdowns -->
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
									<h4 class="card-title">Update Property Details</h4>
									<?php echo $error; ?>
									<?php echo $msg; ?>
								</div>
								<form method="post" enctype="multipart/form-data">
								
								<?php
									
									$pid=$_REQUEST['id'];
									$query=mysqli_query($con,"select * from property where pid='$pid'");
									while($row=mysqli_fetch_row($query))
									{
								?>
												
								<div class="card-body">
									<h5 class="card-title">Property Detail</h5>
										<div class="row">
											<div class="col-xl-12">
												<div class="form-group row">
													<label class="col-lg-2 col-form-label">Title</label>
													<div class="col-lg-9">
														<input type="text" class="form-control" name="title" required value="<?php echo $row['1']; ?>">
													</div>
												</div>
												<div class="form-group row">
													<label class="col-lg-2 col-form-label">Content</label>
													<div class="col-lg-9">
														<textarea class="tinymce form-control" name="content" rows="10" cols="30"><?php echo $row['2']; ?></textarea>
													</div>
												</div>
												
											</div>
											<div class="col-xl-6">
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Property Type</label>
													<div class="col-lg-9">
													<select class="form-control" name="ptype">
    <option value="apartment" <?php echo ($row['3'] == 'apartment') ? 'selected' : ''; ?>>Apartment</option>
    <option value="flat" <?php echo ($row['3'] == 'flat') ? 'selected' : ''; ?>>Flat</option>
    <option value="building" <?php echo ($row['3'] == 'building') ? 'selected' : ''; ?>>Building</option>
    <option value="house" <?php echo ($row['3'] == 'house') ? 'selected' : ''; ?>>House</option>
    <option value="villa" <?php echo ($row['3'] == 'villa') ? 'selected' : ''; ?>>Villa</option>
    <option value="office" <?php echo ($row['3'] == 'office') ? 'selected' : ''; ?>>Office</option>
</select>
													</div>
												</div>
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Selling Type</label>
													<div class="col-lg-9">
													<select class="form-control" name="stype">
    <option value="rent" <?php echo ($row['4'] == 'rent') ? 'selected' : ''; ?>>Rent</option>
    <option value="sale" <?php echo ($row['4'] == 'sale') ? 'selected' : ''; ?>>Sale</option>
</select>
													</div>
												</div>
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Bathroom</label>
													<div class="col-lg-9">
														<input type="number" class="form-control" name="bath" required min="0" value="<?php echo $row['6']; ?>">
													</div>
												</div>
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Kitchen</label>
													<div class="col-lg-9">
														<input type="number" class="form-control" name="kitc" required min="0" value="<?php echo $row['7']; ?>">
													</div>
												</div>
												
											</div>   
											<div class="col-xl-6">
												
												
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Bedroom</label>
													<div class="col-lg-9">
														<input type="number" class="form-control" name="bed" required min="0" value="<?php echo $row['5']; ?>">
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
													<select class="form-control" name="floor">
    
    <option value="1st Floor" <?php echo ($row['8'] == '1st Floor') ? 'selected' : ''; ?>>1st Floor</option>
    <option value="2nd Floor" <?php echo ($row['8'] == '2nd Floor') ? 'selected' : ''; ?>>2nd Floor</option>
    <option value="3rd Floor" <?php echo ($row['8'] == '3rd Floor') ? 'selected' : ''; ?>>3rd Floor</option>
    <option value="4th Floor" <?php echo ($row['8'] == '4th Floor') ? 'selected' : ''; ?>>4th Floor</option>
    <option value="5th Floor" <?php echo ($row['8'] == '5th Floor') ? 'selected' : ''; ?>>5th Floor</option>
</select>
													</div>
												</div>
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Price</label>
													<div class="col-lg-9">
														<input type="number" class="form-control" name="price" required min="0" value="<?php echo $row['10']; ?>">
													</div>
												</div>
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">City</label>
													<div class="col-lg-9">
														<input type="text" class="form-control" name="city" required value="<?php echo $row['12']; ?>">
													</div>
												</div>
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">State</label>
													<div class="col-lg-9">
														<input type="text" class="form-control" name="state" required value="<?php echo $row['13']; ?>">
													</div>
												</div>
											</div>
											<div class="col-xl-6">
												
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Area Size</label>
													<div class="col-lg-9">
														<input type="text" class="form-control" name="asize" required value="<?php echo $row['9']; ?>">
													</div>
												</div>
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Address</label>
													<div class="col-lg-9">
														<input type="text" class="form-control" name="loc" required value="<?php echo $row['11']; ?>">
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
                    <input type="text" class="form-control" id="latitude" name="latitude" placeholder="Latitude" required value="<?php echo $row['latitude'] ?? ''; ?>">
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control" id="longitude" name="longitude" placeholder="Longitude" required value="<?php echo $row['longitude'] ?? ''; ?>">
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
            <option value="14" <?php echo ($row['map_zoom'] == 14 || empty($row['map_zoom'])) ? 'selected' : ''; ?>>Default (14)</option>
            <option value="16" <?php echo ($row['map_zoom'] == 16) ? 'selected' : ''; ?>>Close (16)</option>
            <option value="18" <?php echo ($row['map_zoom'] == 18) ? 'selected' : ''; ?>>Very Close (18)</option>
            <option value="12" <?php echo ($row['map_zoom'] == 12) ? 'selected' : ''; ?>>Far (12)</option>
            <option value="10" <?php echo ($row['map_zoom'] == 10) ? 'selected' : ''; ?>>Very Far (10)</option>
        </select>
    </div>
</div>

											</div>
										</div>
										
										<div class="form-group row">
											<label class="col-lg-2 col-form-label">Feature</label>
											<div class="col-lg-9">
											<p class="alert alert-danger">* Important Please Do Not Remove Below Content Only Change <b>Yes</b> Or <b>No</b> or Details and Do Not Add More Details</p>
											
											<textarea class="tinymce form-control" name="feature" rows="10" cols="30">
												
													<?php echo $row['14']; ?>
												
											</textarea>
											</div>
										</div>
												
										<h4 class="card-title">Image & Status</h4>
										<div class="row">
											<div class="col-xl-6">
												
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Main Image</label>
													<div class="col-lg-9">
														<input class="form-control" name="aimage" type="file" value="<?php echo $row['15'];?>">
														<img src="../admin/property/<?php echo $row['15'];?>" alt="pimage" height="150" width="180">
													</div>
												</div>
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Image 1</label>
													<div class="col-lg-9">
														<input class="form-control" name="aimage1" type="file" value="<?php echo $row['16'];?>" >
														<img src="../admin/property/<?php echo $row['16'];?>" alt="pimage" height="150" width="180">
													</div>
												</div>
                                                												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Floor image</label>
													<div class="col-lg-9">
														<input class="form-control" name="aimage2" type="file" value="<?php echo $row['29'];?>" >
														<img src="../admin/property/<?php echo $row['29'];?>" alt="pimage" height="150" width="180">
													</div>
												</div>
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Status</label>
													<div class="col-lg-9">
													<select class="form-control" name="status">
                                                      <option value="available" <?php echo ($row['19'] == 'available') ? 'selected' : ''; ?>>Available</option>
                                                      <option value="sold out" <?php echo ($row['19'] == 'sold out') ? 'selected' : ''; ?>>Sold Out</option>
                                                    </select>
													</div>
												</div>
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Basement Floor Plan Image</label>
													<div class="col-lg-9">
														<input class="form-control" name="fimage1" type="file" value="<?php echo $row['21'];?>">
														<img src="../admin/property/<?php echo $row['21'];?>" alt="pimage" height="150" width="180">
													</div>
												</div>
											</div>
											<div class="col-xl-6">
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Floor Plan Image</label>
													<div class="col-lg-9">
														<input class="form-control" name="fimage" type="file" value="<?php echo $row['20'];?>">
														<img src="../admin/property/<?php echo $row['20'];?>" alt="pimage" height="150" width="180">
													</div>
												</div>
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Ground Floor Plan Image</label>
													<div class="col-lg-9">
														<input class="form-control" name="fimage2" type="file" value="<?php echo $row['22'];?>">
														<img src="../admin/property/<?php echo $row['22'];?>" alt="pimage" height="150" width="180">
													</div>
												</div>
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Panorama Image</label>
													<div class="col-lg-9">
														<input class="form-control" name="panorama_image" type="file" value="<?php echo $row['17'];?>">
														<?php if (!empty($row['17'])): ?>
															<img src="../admin/property/<?php echo $row['17']; ?>" alt="panorama" height="150" width="180">
														<?php endif; ?>
													</div>
												</div>
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Tour Configuration</label>
													<div class="col-lg-9">
														<textarea class="form-control" name="tour_config" id="tour_config" rows="3" placeholder="Enter JSON configuration for virtual tour (optional)"><?php echo $row['25']; ?></textarea>
														<div id="json_validation_message"></div>
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
													<select class="form-control" name="isFeatured">
  
    <option value="0" <?php echo ($row['24'] == 0) ? 'selected' : ''; ?>>No</option>
    <option value="1" <?php echo ($row['24'] == 1) ? 'selected' : ''; ?>>Yes</option>
</select>
													</div>
												</div>
											</div>
										</div>

										
											<input type="submit" value="Submit" class="btn btn-primary" name="add" style="margin-left:200px;">
										
									</div>
								</form>
								
								<?php
									} 
								?>
												
							</div>
						</div>
					</div>
				
				</div>			
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
		<script  src="assets/js/script.js"></script>

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
    
    // Get saved coordinates if available
    const savedLat = parseFloat(document.getElementById('latitude').value) || defaultLat;
    const savedLng = parseFloat(document.getElementById('longitude').value) || defaultLng;
    const savedZoom = parseInt(document.getElementById('map-zoom').value) || currentZoom;
    
    document.addEventListener('DOMContentLoaded', function() {
        initMapPicker();
        
        // Initialize JSON validation
        document.getElementById('tour_config').addEventListener('change', validateJson);
    });
    
    function initMapPicker() {
        // Initialize the map with saved coordinates if available
        map = L.map('map-picker').setView([savedLat, savedLng], savedZoom);
        currentZoom = savedZoom;
        
        // Add OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Create a marker that will be placed on the map
        marker = L.marker([savedLat, savedLng], {
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
    
    // Handle zoom level changes
    document.getElementById('map-zoom').addEventListener('change', function() {
        currentZoom = parseInt(this.value);
        map.setZoom(currentZoom);
    });
    
    // Validate JSON format
    function validateJson() {
        const jsonInput = document.getElementById('tour_config');
        const validationMessage = document.getElementById('json_validation_message');
        
        if (!jsonInput.value.trim()) {
            validationMessage.innerHTML = '';
            return;
        }
        
        try {
            JSON.parse(jsonInput.value);
            validationMessage.innerHTML = '<div class="alert alert-success mt-2">Valid JSON format</div>';
        } catch (e) {
            validationMessage.innerHTML = '<div class="alert alert-danger mt-2">Invalid JSON format: ' + e.message + '</div>';
        }
    }
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get form elements
    const form = document.querySelector('form');
    const priceInput = document.querySelector('input[name="price"]');
    const stypeSelect = document.querySelector('select[name="stype"]');
    
    // Add form submission handler
    form.addEventListener('submit', function(event) {
        const price = parseFloat(priceInput.value);
        const stype = stypeSelect.value;
        
        // Validate price based on selling type
        if (stype === 'rent' && price < 50000) {
            event.preventDefault();
            alert('Rental properties must have a minimum price of 50,000 birr');
            priceInput.focus();
        } else if (stype === 'sale' && price < 500000) {
            event.preventDefault();
            alert('Properties for sale must have a minimum price of 500,000 birr');
            priceInput.focus();
        }
    });
    
    // Add real-time validation when selling type changes
    stypeSelect.addEventListener('change', function() {
        validatePrice();
    });
    
    // Add real-time validation when price changes
    priceInput.addEventListener('input', function() {
        validatePrice();
    });
    
    function validatePrice() {
        const price = parseFloat(priceInput.value);
        const stype = stypeSelect.value;
        
        if (stype === 'rent' && price < 50000) {
            priceInput.setCustomValidity('Rental properties must have a minimum price of 50,000 birr');
        } else if (stype === 'sale' && price < 500000) {
            priceInput.setCustomValidity('Properties for sale must have a minimum price of 500,000 birr');
        } else {
            priceInput.setCustomValidity('');
        }
    }
    
    // Initial validation
    validatePrice();
});
</script>

    </body>

</html>
  
      