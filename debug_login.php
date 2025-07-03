<?php
// Debug script - DELETE AFTER USE
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("config.php");

// Credentials to test (use the ones you created)
$username = "admin"; // Replace with your admin username
$password = "SecurePassword123!"; // Replace with your admin password

// Try to find the admin
$sql = "SELECT * FROM admin WHERE auser=? OR aemail=?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "ss", $username, $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

echo "<h2>Admin Login Debug</h2>";

if ($row = mysqli_fetch_assoc($result)) {
    echo "Admin found: " . htmlspecialchars($row['auser']) . "<br>";
    echo "Stored hash: " . substr($row['apass'], 0, 10) . "...<br>";
    
    // Verify password
    if (password_verify($password, $row['apass'])) {
        echo "<div style='color:green; font-weight:bold;'>Password verification SUCCESSFUL</div>";
    } else {
        echo "<div style='color:red; font-weight:bold;'>Password verification FAILED</div>";
        
        // For diagnostic purposes, check the length and format of the hash
        echo "Hash length: " . strlen($row['apass']) . "<br>";
        echo "Hash format check: " . (substr($row['apass'], 0, 4) === '$2y$' ? 'Correct bcrypt format' : 'Incorrect format') . "<br>";
    }
} else {
    echo "<div style='color:red; font-weight:bold;'>Admin not found in database</div>";
    
    // Show all admins for debugging
    $all_admins = mysqli_query($con, "SELECT aid, auser, aemail, LEFT(apass, 10) as hash_preview FROM admin");
    if (mysqli_num_rows($all_admins) > 0) {
        echo "<h3>All admins in database:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Hash (preview)</th></tr>";
        while ($admin = mysqli_fetch_assoc($all_admins)) {
            echo "<tr>";
            echo "<td>" . $admin['aid'] . "</td>";
            echo "<td>" . htmlspecialchars($admin['auser']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['aemail']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['hash_preview']) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No admins found in database!";
    }
}

// Display login form parameters
echo "<h3>Login Form Fields Check</h3>";
echo "In your login form, make sure:<br>";
echo "1. The username/email field name is 'emailOrUsername'<br>";
echo "2. The password field name is 'password'<br>";
echo "3. The user type field name is 'utype' with value 'admin'<br>";

// Show admin table structure
echo "<h3>Admin Table Structure</h3>";
$columns = mysqli_query($con, "SHOW COLUMNS FROM admin");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($col = mysqli_fetch_assoc($columns)) {
    echo "<tr>";
    foreach ($col as $key => $value) {
        echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
    }
    echo "</tr>";
}
echo "</table>";

mysqli_close($con);
?>