<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'property_management';

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>