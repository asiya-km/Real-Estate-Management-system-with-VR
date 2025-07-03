<?php
include("config.php");

// Check if admin is logged in
session_start();
if(!isset($_SESSION['auser'])) {
    echo "Access denied. Please log in as administrator.";
    header("location:../login1.php");
    exit;
}

// Add panorama_image column if it doesn't exist
$checkColumn = mysqli_query($con, "SHOW COLUMNS FROM property LIKE 'panorama_image'");
if(mysqli_num_rows($checkColumn) == 0) {
    $addColumn = mysqli_query($con, "ALTER TABLE property ADD COLUMN panorama_image VARCHAR(255) DEFAULT NULL");
    if($addColumn) {
        echo "Added panorama_image column to property table.<br>";
    } else {
        echo "Error adding panorama_image column: " . mysqli_error($con) . "<br>";
    }
}

// Add tour_config column if it doesn't exist
$checkColumn = mysqli_query($con, "SHOW COLUMNS FROM property LIKE 'tour_config'");
if(mysqli_num_rows($checkColumn) == 0) {
    $addColumn = mysqli_query($con, "ALTER TABLE property ADD COLUMN tour_config TEXT DEFAULT NULL");
    if($addColumn) {
        echo "Added tour_config column to property table.<br>";
    } else {
        echo "Error adding tour_config column: " . mysqli_error($con) . "<br>";
    }
}

// Create directory for panorama images if it doesn't exist
$panoramaDir = "../admin/property/panoramas";
if(!file_exists($panoramaDir)) {
    if(mkdir($panoramaDir, 0755, true)) {
        echo "Created directory for panorama images at: $panoramaDir<br>";
    } else {
        echo "Error creating panorama directory. Please create it manually: $panoramaDir<br>";
    }
}

echo "Database update completed.";
?>
