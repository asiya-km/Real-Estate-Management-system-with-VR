<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>System Diagnostic</h1>";

// PHP Version
echo "<h2>PHP Environment</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "password_hash() available: " . (function_exists('password_hash') ? "Yes" : "No") . "<br>";

// Database Connection
echo "<h2>Database Connection</h2>";
include("config.php");
if ($con) {
    echo "Connection successful<br>";
    
    // Check user table
    $result = mysqli_query($con, "SHOW TABLES LIKE 'user'");
    if (mysqli_num_rows($result) > 0) {
        echo "User table exists<br>";
        
        // Check table structure
        $result = mysqli_query($con, "DESCRIBE user");
        echo "<table border='1'><tr><th>Field</th><th>Type</th></tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "User table does not exist<br>";
    }
} else {
    echo "Connection failed: " . mysqli_connect_error() . "<br>";
}

// File System
echo "<h2>File System</h2>";
$upload_dir = "admin/user/";
echo "Upload directory: " . realpath($upload_dir) . "<br>";
echo "Directory exists: " . (is_dir($upload_dir) ? "Yes" : "No") . "<br>";
if (!is_dir($upload_dir)) {
    echo "Creating directory: ";
    if (mkdir($upload_dir, 0777, true)) {
        echo "Success<br>";
    } else {
        echo "Failed - " . error_get_last()['message'] . "<br>";
    }
}
echo "Directory writable: " . (is_writable($upload_dir) ? "Yes" : "No") . "<br>";

// Test file creation
$test_file = $upload_dir . "test_file.txt";
echo "Creating test file: ";
$file = @fopen($test_file, "w");
if ($file) {
    fwrite($file, "Test");
    fclose($file);
    echo "Success<br>";
    echo "Test file exists: " . (file_exists($test_file) ? "Yes" : "No") . "<br>";
    echo "Test file readable: " . (is_readable($test_file) ? "Yes" : "No") . "<br>";
    unlink($test_file);
} else {
    echo "Failed - " . error_get_last()['message'] . "<br>";
}

// Server Information
echo "<h2>Server Information</h2>";
echo "Server software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script filename: " . $_SERVER['SCRIPT_FILENAME'] . "<br>";

// PHP Configuration
echo "<h2>PHP Configuration</h2>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "memory_limit: " . ini_get('memory_limit') . "<br>";
echo "max_execution_time: " . ini_get('max_execution_time') . "<br>";
?>