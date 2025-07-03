<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database configuration
include("config.php"); // Adjust path if needed

// Admin credentials to create - CHANGE THESE VALUES
$admin_username = "admin";
$admin_email = "admin@gmail.com";
$admin_password = "password"; // Use a strong password

// Verify database connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if admin table exists
$table_check = mysqli_query($con, "SHOW TABLES LIKE 'admin'");
if (mysqli_num_rows($table_check) == 0) {
    // Create admin table if it doesn't exist
    $create_table = "CREATE TABLE admin (
        aid INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        auser VARCHAR(60) NOT NULL,
        aemail VARCHAR(100) NOT NULL,
        apass VARCHAR(255) NOT NULL
    )";
    
    if (!mysqli_query($con, $create_table)) {
        die("Error creating admin table: " . mysqli_error($con));
    }
    echo "Admin table created successfully.<br>";
}

// Hash password with PHP's password_hash function
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

// Check if this admin username or email already exists
$check_query = "SELECT * FROM admin WHERE auser = ? OR aemail = ?";
$check_stmt = mysqli_prepare($con, $check_query);
mysqli_stmt_bind_param($check_stmt, "ss", $admin_username, $admin_email);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);

if (mysqli_stmt_num_rows($check_stmt) > 0) {
    echo "An admin with this username or email already exists.<br>";
    
    // Option to update existing admin password
    echo "Do you want to update the password for this admin? <br>";
    echo "<form method='post'>";
    echo "<input type='hidden' name='update_admin' value='yes'>";
    echo "<button type='submit'>Yes, update password</button>";
    echo "</form>";
    
    if (isset($_POST['update_admin']) && $_POST['update_admin'] == 'yes') {
        $update_query = "UPDATE admin SET apass = ? WHERE auser = ? OR aemail = ?";
        $update_stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($update_stmt, "sss", $hashed_password, $admin_username, $admin_email);
        
        if (mysqli_stmt_execute($update_stmt)) {
            echo "Admin password updated successfully!<br>";
            echo "Username: " . htmlspecialchars($admin_username) . "<br>";
            echo "Email: " . htmlspecialchars($admin_email) . "<br>";
            echo "Password: [HIDDEN]<br>";
            echo "<strong>Please delete this file immediately for security reasons.</strong>";
        } else {
            echo "Error updating admin: " . mysqli_error($con) . "<br>";
        }
    }
} else {
    // Insert new admin
    $insert_query = "INSERT INTO admin (auser, aemail, apass) VALUES (?, ?, ?)";
    $insert_stmt = mysqli_prepare($con, $insert_query);
    mysqli_stmt_bind_param($insert_stmt, "sss", $admin_username, $admin_email, $hashed_password);
    
    if (mysqli_stmt_execute($insert_stmt)) {
        echo "Admin created successfully!<br>";
        echo "Username: " . htmlspecialchars($admin_username) . "<br>";
        echo "Email: " . htmlspecialchars($admin_email) . "<br>";
        echo "Password: [HIDDEN]<br>";
        echo "<strong>Please delete this file immediately for security reasons.</strong>";
    } else {
        echo "Error creating admin: " . mysqli_error($con) . "<br>";
    }
}

// Close connection
mysqli_close($con);
?>

<style>
    body {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        margin: 20px;
        padding: 20px;
        background-color: #f5f5f5;
    }
    
    button {
        background-color: #4CAF50;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        margin-top: 10px;
        margin-bottom: 20px;
    }
    
    button:hover {
        background-color: #45a049;
    }
    
    strong {
        color: #ff0000;
    }
</style>