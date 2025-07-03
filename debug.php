<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>PHP Debugging Page</h1>";

include("config.php");

// Check database connection
echo "<h2>Database Connection</h2>";
if ($con) {
    echo "<p style='color:green'>Database connection successful</p>";
    
    // Check user table
    echo "<h2>User Table Structure</h2>";
    $result = mysqli_query($con, "SHOW COLUMNS FROM user");
    if (!$result) {
        echo "<p style='color:red'>Error querying table structure: " . mysqli_error($con) . "</p>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check specific columns
    echo "<h2>Security Question Columns</h2>";
    $securityResult = mysqli_query($con, "SHOW COLUMNS FROM user LIKE 'security%'");
    if (mysqli_num_rows($securityResult) > 0) {
        echo "<p style='color:green'>Security question columns exist</p>";
    } else {
        echo "<p style='color:orange'>Security question columns do not exist yet</p>";
        
        // Try to create them
        echo "<h3>Adding Security Columns</h3>";
        $alterQuery = "ALTER TABLE user 
                       ADD COLUMN security_question VARCHAR(255) DEFAULT NULL,
                       ADD COLUMN security_answer VARCHAR(255) DEFAULT NULL";
        if (mysqli_query($con, $alterQuery)) {
            echo "<p style='color:green'>Successfully added security columns</p>";
        } else {
            echo "<p style='color:red'>Failed to add security columns: " . mysqli_error($con) . "</p>";
        }
    }
} else {
    echo "<p style='color:red'>Database connection failed: " . mysqli_connect_error() . "</p>";
}

// Directory permissions
echo "<h2>Upload Directory</h2>";
$upload_dir = "admin/user/";
if (!is_dir($upload_dir)) {
    echo "<p>Directory doesn't exist, attempting to create...</p>";
    if (mkdir($upload_dir, 0755, true)) {
        echo "<p style='color:green'>Successfully created upload directory</p>";
    } else {
        echo "<p style='color:red'>Failed to create upload directory</p>";
    }
} else {
    echo "<p style='color:green'>Upload directory exists</p>";
    if (is_writable($upload_dir)) {
        echo "<p style='color:green'>Directory is writable</p>";
    } else {
        echo "<p style='color:red'>Directory is not writable</p>";
    }
}

echo "<h2>PHP Information</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Post Max Size: " . ini_get('post_max_size') . "</p>";
echo "<p>Upload Max Filesize: " . ini_get('upload_max_filesize') . "</p>";
?>