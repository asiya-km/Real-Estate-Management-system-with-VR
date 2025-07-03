<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("config.php");

echo "<h1>Registration Debug Tool</h1>";

// Test database connection
echo "<h2>Database Connection Test</h2>";
if ($con) {
    echo "<p style='color:green'>Database connection successful!</p>";
    
    // Check user table
    $result = mysqli_query($con, "SHOW TABLES LIKE 'user'");
    if (mysqli_num_rows($result) > 0) {
        echo "<p style='color:green'>User table exists!</p>";
        
        // Check table structure
        $columns = mysqli_query($con, "SHOW COLUMNS FROM user");
        echo "<p>User table structure:</p>";
        echo "<ul>";
        while ($column = mysqli_fetch_assoc($columns)) {
            echo "<li>" . $column['Field'] . " - " . $column['Type'] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:red'>User table does not exist!</p>";
    }
} else {
    echo "<p style='color:red'>Database connection failed: " . mysqli_connect_error() . "</p>";
}

// Test directory permissions
echo "<h2>Directory Permissions Test</h2>";
$upload_dir = "admin/user/";
if (!is_dir($upload_dir)) {
    echo "<p>Upload directory doesn't exist. Attempting to create it...</p>";
    if (mkdir($upload_dir, 0755, true)) {
        echo "<p style='color:green'>Successfully created directory: $upload_dir</p>";
    } else {
        echo "<p style='color:red'>Failed to create directory: $upload_dir</p>";
        echo "<p>PHP process is running as: " . exec('whoami') . "</p>";
    }
} else {
    echo "<p style='color:green'>Upload directory exists: $upload_dir</p>";
    if (is_writable($upload_dir)) {
        echo "<p style='color:green'>Directory is writable!</p>";
    } else {
        echo "<p style='color:red'>Directory is not writable!</p>";
        echo "<p>Current permissions: " . substr(sprintf('%o', fileperms($upload_dir)), -4) . "</p>";
    }
}

// Test sample insert
echo "<h2>Sample INSERT Test</h2>";
echo "<p>Attempting a sample insert with minimal data...</p>";

try {
    // Generate test data
    $testName = "Test_" . time();
    $testEmail = "test_" . time() . "@example.com";
    $testPhone = "+251912345678";
    $testPass = password_hash("Test123!", PASSWORD_DEFAULT);
    $testType = "user";
    $testImage = "";
    $testStatus = 1;
    
    // First check if we have all required columns
    $columns = [];
    $result = mysqli_query($con, "SHOW COLUMNS FROM user");
    while ($column = mysqli_fetch_assoc($result)) {
        $columns[] = $column['Field'];
    }
    
    echo "<p>Building query based on available columns...</p>";
    
    // Basic columns that should exist
    $fields = ["uname", "uemail", "uphone", "upass", "utype"];
    $values = [$testName, $testEmail, $testPhone, $testPass, $testType];
    $types = "sssss";
    
    // Add optional columns if they exist
    if (in_array("uimage", $columns)) {
        $fields[] = "uimage";
        $values[] = $testImage;
        $types .= "s";
    }
    
    if (in_array("status", $columns)) {
        $fields[] = "status";
        $values[] = $testStatus;
        $types .= "i";
    }
    
    // Build the SQL query
    $sql = "INSERT INTO user (" . implode(", ", $fields) . ") VALUES (" . str_repeat("?,", count($fields) - 1) . "?)";
    echo "<p>SQL Query: " . htmlspecialchars($sql) . "</p>";
    
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . mysqli_error($con));
    }
    
    // Dynamically bind parameters
    mysqli_stmt_bind_param($stmt, $types, ...$values);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color:green'>Test INSERT successful!</p>";
        $insert_id = mysqli_stmt_insert_id($stmt);
        echo "<p>Inserted ID: $insert_id</p>";
        
        // Clean up test data
        mysqli_query($con, "DELETE FROM user WHERE uid = $insert_id");
        echo "<p>Test data cleaned up.</p>";
    } else {
        throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Test INSERT failed: " . $e->getMessage() . "</p>";
}
?>