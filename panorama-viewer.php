<?php
session_start();
include("config.php");

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    // Store the current URL for redirection after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: login1.php");
    exit();
}

// Validate the image parameter
$img = isset($_GET['img']) ? $_GET['img'] : '';
$imgPath = '';
$property_id = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

// Security check - only allow access to images in the property directory
if (!empty($img) && preg_match('/^[a-zA-Z0-9_\-\.]+$/', $img)) {
    // First check in the panoramas subdirectory
    $imgPath = "admin/property/panoramas/" . $img;
    
    // If not found, check in the main property directory
    if (!file_exists($imgPath)) {
        $imgPath = "admin/property/" . $img;
        
        // If still not found, set empty
        if (!file_exists($imgPath)) {
            $imgPath = '';
        }
    }
}

// Get property details if property ID is provided
$property = null;
if ($property_id > 0 && isset($con)) {
    $stmt = mysqli_prepare($con, "SELECT pid, title, price, status, mapimage, topmapimage, groundmapimage FROM property WHERE pid = ?");
    mysqli_stmt_bind_param($stmt, "i", $property_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $property = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>360° Virtual Tour - Remsko Real Estate</title>
    
    <!-- Include CSS files -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    
    <!-- Include A-Frame for VR -->
    <script src="https://aframe.io/releases/1.4.2/aframe.min.js"></script>
    
    <style>
        body { 
            margin: 0; 
            font-family: 'Arial', sans-serif;
        }
        
        .scene-container {
            position: relative;
            height: 80vh;
            width: 100%;
            overflow: hidden;
        }
        
        #scene-buttons {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 100;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .scene-button {
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .scene-button:hover {
            background-color: rgba(40, 167, 69, 0.8);
        }
        
        .scene-button.active {
            background-color: rgba(40, 167, 69, 0.8);
        }
        
        #control-buttons {
            position: absolute;
            bottom: 20px;
            right: 20px;
            z-index: 100;
        }
        
        .control-button {
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            margin-left: 10px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .control-button:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }
        
        .property-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 200;
            color: white;
            font-size: 24px;
        }
        
        .spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #28a745;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 2s linear infinite;
            margin-right: 15px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Include site header with navigation -->
    <?php include("include/header.php"); ?>
    
    <!-- Banner -->
    <div class="banner-full-row page-banner" style="background-image:url('images/breadcromb.jpg');">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h2 class="page-name float-left text-white text-uppercase mt-1 mb-0"><b>360° Virtual Tour</b></h2>
                </div>
                <div class="col-md-6">
                    <nav aria-label="breadcrumb" class="float-left float-md-right">
                        <ol class="breadcrumb bg-transparent m-0 p-0">
                            <li class="breadcrumb-item text-white"><a href="index.php">Home</a></li>
                            <?php if ($property): ?>
                            <li class="breadcrumb-item"><a href="propertydetail.php?pid=<?php echo $property_id; ?>">Property Details</a></li>
                            <?php endif; ?>
                            <li class="breadcrumb-item active">Virtual Tour</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <div class="full-row">
        <div class="container">
            <?php if (empty($imgPath)): ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-danger">
                        <h3>Error: Panorama image not found</h3>
                        <p>The requested panorama could not be loaded.</p>
                        <a href="javascript:history.back()" class="btn btn-primary mt-3">Go Back</a>
                    </div>
                </div>
            </div>
            <?php else: ?>
            
            <?php if ($property): ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="property-info">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4><?php echo htmlspecialchars($property['title']); ?></h4>
                                <p class="text-success h5 mb-0">ETB <?php echo number_format($property['price']); ?></p>
                            </div>
                            <div class="col-md-4 text-md-right">
                                <?php if ($property['status'] == 'available'): ?>
                                <a href="book_property.php?id=<?php echo $property_id; ?>" class="btn btn-success">
                                    <i class="fa fa-check-circle mr-2"></i> Book Now
                                </a>
                                <a href="schedule_visit.php?id=<?php echo $property_id; ?>" class="btn btn-primary ml-2">
                                    <i class="fa fa-calendar mr-2"></i> Schedule Visit
                                </a>
                                <?php else: ?>
                                <div class="alert alert-warning mb-0">
                                    <i class="fa fa-exclamation-triangle mr-2"></i> This property is not available for booking
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="scene-container">
                        <div id="scene-buttons">
                            <button class="scene-button active" onclick="changeScene('<?php echo $imgPath; ?>', this)">
                                <i class="fa fa-home mr-2"></i>Main View
                            </button>
                            
                            <?php if ($property && !empty($property['mapimage'])): ?>
                            <button class="scene-button" onclick="changeScene('admin/property/<?php echo htmlspecialchars($property['mapimage']); ?>', this)">
                                <i class="fa fa-map mr-2"></i>first class
                            </button>
                            <?php endif; ?>
                            
                            <?php if ($property && !empty($property['topmapimage'])): ?>
                            <button class="scene-button" onclick="changeScene('admin/property/<?php echo htmlspecialchars($property['topmapimage']); ?>', this)">
                                <i class="fa fa-building-o mr-2"></i>2nd class
                            </button>
                            <?php endif; ?>
                            
                            <?php if ($property && !empty($property['groundmapimage'])): ?>
                            <button class="scene-button" onclick="changeScene('admin/property/<?php echo htmlspecialchars($property['groundmapimage']); ?>', this)">
                                <i class="fa fa-square-o mr-2"></i>3rd class
                            </button>
                            <?php endif; ?>
                        </div>
                        
                        <div id="control-buttons">
                            <button class="control-button" id="rotation-toggle">
                                <i class="fa fa-refresh mr-2"></i>Toggle Rotation
                            </button>
                            <button class="control-button" id="fullscreen-toggle">
                                <i class="fa fa-expand mr-2"></i>Fullscreen
                            </button>
                        </div>
                        
                        <div id="loading-overlay" class="loading-overlay">
                            <div class="spinner"></div>
                            <span>Loading Virtual Tour...</span>
                        </div>
                        
                        <a-scene embedded>
                            <a-entity id="rig" rotation="0 0 0">
                                <a-camera wasd-controls-enabled="false" look-controls="pointerLockEnabled: true"></a-camera>
                            </a-entity>
                            <a-sky id="sky" src="<?php echo $imgPath; ?>" rotation="0 -130 0"></a-sky>
                        </a-scene>
                    </div>
                    
                    <div class="alert alert-info mt-4">
                        <i class="fa fa-info-circle mr-2"></i> 
                        <strong>Navigation Tips:</strong> Click and drag to look around. Use the buttons above to switch between different views.
                        Toggle rotation for an automatic tour experience.
                    </div>
                    
                    <div class="mb-4">
                        <a href="javascript:history.back()" class="btn btn-secondary">
                            <i class="fa fa-arrow-left mr-2"></i> Back to Property
                        </a>
                        <?php if ($property): ?>
                        <a href="propertydetail.php?pid=<?php echo $property_id; ?>" class="btn btn-primary ml-2">
                            <i class="fa fa-home mr-2"></i> View Property Details
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
        
    <!-- Include site footer -->
    <?php include("include/footer.php"); ?>
    
    <!-- JavaScript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/custom.js"></script>
    
    <script>
        // Scene switching
        function changeScene(imgSrc, buttonElement) {
            // Show loading overlay
            document.getElementById('loading-overlay').style.display = 'flex';
            
            // Update active button
            const buttons = document.querySelectorAll('.scene-button');
            buttons.forEach(btn => btn.classList.remove('active'));
            if (buttonElement) {
                buttonElement.classList.add('active');
            }
            
            // Change the sky image
            const sky = document.getElementById('sky');
            
            // Create a new image to preload
            const img = new Image();
            img.onload = function() {
                // Once image is loaded, update the sky and hide loading overlay
                sky.setAttribute('src', imgSrc);
                setTimeout(() => {
                    document.getElementById('loading-overlay').style.display = 'none';
                }, 500);
            };
            img.onerror = function() {
                // If image fails to load
                alert('Failed to load image: ' + imgSrc);
                document.getElementById('loading-overlay').style.display = 'none';
            };
            img.src = imgSrc;
        }
        
        // Auto rotation
        let isRotating = true;
        let angle = -130;
        let rotationInterval;
        
        function startRotation() {
            rotationInterval = setInterval(() => {
                angle += 0.2; // slow rotation
                document.getElementById('sky').setAttribute('rotation', `0 ${angle} 0`);
            }, 50);
        }
        
        function stopRotation() {
            clearInterval(rotationInterval);
        }
        
        // Toggle rotation
        document.getElementById('rotation-toggle').addEventListener('click', function() {
            isRotating = !isRotating;
            if (isRotating) {
                startRotation();
                this.innerHTML = '<i class="fa fa-pause mr-2"></i>Pause Rotation';
            } else {
                stopRotation();
                this.innerHTML = '<i class="fa fa-refresh mr-2"></i>Start Rotation';
            }
        });
        
        // Fullscreen toggle
        document.getElementById('fullscreen-toggle').addEventListener('click', function() {
            const sceneContainer = document.querySelector('.scene-container');
            
            if (!document.fullscreenElement) {
                if (sceneContainer.requestFullscreen) {
                    sceneContainer.requestFullscreen();
                } else if (sceneContainer.requestFullscreen) {
                    sceneContainer.requestFullscreen();
                } else if (sceneContainer.mozRequestFullScreen) { /* Firefox */
                    sceneContainer.mozRequestFullScreen();
                } else if (sceneContainer.webkitRequestFullscreen) { /* Chrome, Safari & Opera */
                    sceneContainer.webkitRequestFullscreen();
                } else if (sceneContainer.msRequestFullscreen) { /* IE/Edge */
                    sceneContainer.msRequestFullscreen();
                }
                this.innerHTML = '<i class="fa fa-compress mr-2"></i>Exit Fullscreen';
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.mozCancelFullScreen) { /* Firefox */
                    document.mozCancelFullScreen();
                } else if (document.webkitExitFullscreen) { /* Chrome, Safari & Opera */
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) { /* IE/Edge */
                    document.msExitFullscreen();
                }
                this.innerHTML = '<i class="fa fa-expand mr-2"></i>Fullscreen';
            }
        });
        
        // Initialize rotation on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Start rotation after a short delay to ensure everything is loaded
            setTimeout(() => {
                startRotation();
                // Hide loading overlay
                document.getElementById('loading-overlay').style.display = 'none';
            }, 1500);
            
            // Set initial button state
            document.getElementById('rotation-toggle').innerHTML = '<i class="fa fa-pause mr-2"></i>Pause Rotation';
        });
        
        // Handle fullscreen change event
        document.addEventListener('fullscreenchange', updateFullscreenButton);
        document.addEventListener('webkitfullscreenchange', updateFullscreenButton);
        document.addEventListener('mozfullscreenchange', updateFullscreenButton);
        document.addEventListener('MSFullscreenChange', updateFullscreenButton);
        
        function updateFullscreenButton() {
            const button = document.getElementById('fullscreen-toggle');
            if (document.fullscreenElement) {
                button.innerHTML = '<i class="fa fa-compress mr-2"></i>Exit Fullscreen';
            } else {
                button.innerHTML = '<i class="fa fa-expand mr-2"></i>Fullscreen';
            }
        }
        
        // Preload images for smoother transitions
        function preloadImages() {
            const buttons = document.querySelectorAll('.scene-button');
            buttons.forEach(button => {
                const onclick = button.getAttribute('onclick');
                if (onclick) {
                    const match = onclick.match(/'([^']+)'/);
                    if (match && match[1]) {
                        const imgSrc = match[1];
                        const img = new Image();
                        img.src = imgSrc;
                    }
                }
            });
        }
        
        // Call preload function after a short delay
        setTimeout(preloadImages, 2000);
    </script>
</body>
</html>
