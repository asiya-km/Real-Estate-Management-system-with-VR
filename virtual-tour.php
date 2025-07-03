<?php
include("config.php");

// Get property ID
$id = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

if ($id <= 0) {
    echo "Invalid property ID";
    exit;
}

// Get property data
$stmt = mysqli_prepare($con, "SELECT title, tour_config FROM property WHERE pid = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "Property not found";
    exit;
}

$property = mysqli_fetch_assoc($result);

// Parse tour configuration
$tourConfig = json_decode($property['tour_config'] ?? '{}', true);
if (empty($tourConfig) || empty($tourConfig['scenes'])) {
    echo "No virtual tour available for this property";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Tour - <?php echo htmlspecialchars($property['title']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css"/>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Arial', sans-serif;
        }
        #panorama {
            width: 100%;
            height: 100%;
        }
        .tour-controls {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 100;
            background: rgba(0,0,0,0.5);
            border-radius: 30px;
            padding: 10px 20px;
            display: flex;
            align-items: center;
        }
        .scene-button {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            margin: 0 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .scene-button:hover, .scene-button.active {
            background: rgba(255,255,255,0.8);
            color: #333;
        }
        .scene-title {
            color: white;
            margin: 0 15px;
            font-size: 14px;
            min-width: 120px;
            text-align: center;
        }
        .info-hotspot {
            background-color: rgba(0, 0, 0, 0.7);
            border-radius: 5px;
            padding: 15px;
            color: white;
            max-width: 250px;
        }
        .info-hotspot h3 {
            margin-top: 0;
            margin-bottom: 10px;
        }
        .info-hotspot p {
            margin-bottom: 0;
        }
        .back-to-property {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 100;
            background: rgba(0,0,0,0.5);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        .back-to-property i {
            margin-right: 8px;
        }
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            color: white;
        }
        .spinner {
            border: 5px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top: 5px solid white;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin-right: 15px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div id="panorama"></div>
    
    <a href="propertydetail.php?pid=<?php echo $id; ?>" class="back-to-property">
        <i class="fas fa-arrow-left"></i> Back to Property
    </a>
    
    <div class="tour-controls">
        <button id="prev-scene" class="scene-button"><i class="fas fa-chevron-left"></i></button>
        <div id="scene-title" class="scene-title">Loading...</div>
        <button id="next-scene" class="scene-button"><i class="fas fa-chevron-right"></i></button>
    </div>
    
    <div id="loading" class="loading-overlay">
        <div class="spinner"></div>
        <div>Loading Virtual Tour...</div>
    </div>
    
    <script>
        // Tour configuration from PHP
        const tourConfig = <?php echo json_encode($tourConfig); ?>;
        const scenes = tourConfig.scenes || [];
        let currentSceneIndex = 0;
        let viewer = null;
        
        // Initialize the panorama viewer
        function initViewer() {
            if (scenes.length === 0) {
                document.getElementById('loading').innerHTML = '<div>No panorama scenes available</div>';
                return;
            }
            
            const firstScene = scenes[0];
            const config = {
                default: {
                    firstScene: firstScene.id,
                    sceneFadeDuration: 1000,
                    autoLoad: true,
                    compass: true,
                    hotSpotDebug: false
                },
                scenes: {}
            };
            
            // Build scenes configuration
            scenes.forEach(scene => {
                config.scenes[scene.id] = {
                    title: scene.title,
                    hfov: scene.hfov || 100,
                    pitch: scene.pitch || 0,
                    yaw: scene.yaw || 0,
                    type: "equirectangular",
                    panorama: "admin/property/panoramas/" + scene.panorama,
                    hotSpots: []
                };
                
                // Add navigation hotspots
                if (scene.hotspots) {
                    scene.hotspots.forEach(hotspot => {
                        config.scenes[scene.id].hotSpots.push({
                            pitch: hotspot.pitch,
                            yaw: hotspot.yaw,
                            type: hotspot.type,
                            text: hotspot.text,
                            sceneId: hotspot.sceneId,
                            cssClass: hotspot.type === 'info' ? 'custom-info-hotspot' : 'custom-scene-hotspot',
                            createTooltipFunc: hotspot.type === 'info' ? infoHotspot : null,
                            createTooltipArgs: hotspot.type === 'info' ? {
                                title: hotspot.text,
                                description: hotspot.description || ''
                            } : null
                        });
                    });
                }
            });
            
            // Initialize the viewer
            viewer = pannellum.viewer('panorama', config);
            
            // Update scene title
            document.getElementById('scene-title').textContent = firstScene.title;
            
            // Hide loading overlay
            document.getElementById('loading').style.display = 'none';
            
            // Set up scene change event
            viewer.on('scenechange', function(sceneId) {
                // Find the index of the current scene
                currentSceneIndex = scenes.findIndex(scene => scene.id === sceneId);
                document.getElementById('scene-title').textContent = scenes[currentSceneIndex].title;
                
                // Update navigation buttons
                updateNavButtons();
            });
        }
        
        // Function to create info hotspots
        function infoHotspot(hotSpotDiv, args) {
            // Create tooltip element
            const tooltip = document.createElement('div');
            tooltip.classList.add('info-hotspot');
            
            // Add title
            const title = document.createElement('h3');
            title.textContent = args.title;
            tooltip.appendChild(title);
            
            // Add description if available
            if (args.description) {
                const description = document.createElement('p');
                description.textContent = args.description;
                tooltip.appendChild(description);
            }
            
            // Set up click behavior
            hotSpotDiv.addEventListener('click', function() {
                if (hotSpotDiv.classList.contains('expanded')) {
                    hotSpotDiv.classList.remove('expanded');
                    hotSpotDiv.removeChild(tooltip);
                } else {
                    hotSpotDiv.classList.add('expanded');
                    hotSpotDiv.appendChild(tooltip);
                }
            });
        }
        
        // Function to navigate to previous scene
        function prevScene() {
            if (currentSceneIndex > 0) {
                currentSceneIndex--;
                viewer.loadScene(scenes[currentSceneIndex].id);
            }
        }
        
        // Function to navigate to next scene
        function nextScene() {
            if (currentSceneIndex < scenes.length - 1) {
                currentSceneIndex++;
                viewer.loadScene(scenes[currentSceneIndex].id);
            }
        }
        
        // Update navigation buttons state
        function updateNavButtons() {
            document.getElementById('prev-scene').disabled = currentSceneIndex === 0;
            document.getElementById('next-scene').disabled = currentSceneIndex === scenes.length - 1;
            
            // Visual feedback
            document.getElementById('prev-scene').style.opacity = currentSceneIndex === 0 ? '0.5' : '1';
            document.getElementById('next-scene').style.opacity = currentSceneIndex === scenes.length - 1 ? '0.5' : '1';
        }
        
        // Set up event listeners
        document.getElementById('prev-scene').addEventListener('click', prevScene);
        document.getElementById('next-scene').addEventListener('click', nextScene);
        
        // Initialize the viewer when the page loads
        window.addEventListener('load', initViewer);
    </script>
</body>
</html>

