<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("config.php");

// Check user passwords
echo "<h2>User Passwords</h2>";
$query = "SELECT uid, uname, uemail, upass FROM user LIMIT 10";
$result = mysqli_query($con, $query);

echo "<table border='1'>";
echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Password Hash Type</th><th>Hash Length</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    $hashType = "Unknown";
    if (strpos($row['upass'], '$2y$') === 0) {
        $hashType = "Modern (bcrypt)";
    } else if (strlen($row['upass']) === 40 && ctype_xdigit($row['upass'])) {
        $hashType = "SHA-1";
    }
    
    echo "<tr>";
    echo "<td>" . $row['uid'] . "</td>";
    echo "<td>" . htmlspecialchars($row['uname']) . "</td>";
    echo "<td>" . htmlspecialchars($row['uemail']) . "</td>";
    echo "<td>" . $hashType . "</td>";
    echo "<td>" . strlen($row['upass']) . "</td>";
    echo "</tr>";
}
echo "</table>";

// Check admin passwords
echo "<h2>Admin Passwords</h2>";
$query = "SELECT aid, auser, aemail, apass FROM admin LIMIT 10";
$result = mysqli_query($con, $query);

echo "<table border='1'>";
echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Password Hash Type</th><th>Hash Length</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    $hashType = "Unknown";
    if (strpos($row['apass'], '$2y$') === 0) {
        $hashType = "Modern (bcrypt)";
    } else if (strlen($row['apass']) === 40 && ctype_xdigit($row['apass'])) {
        $hashType = "SHA-1";
    }
    
    echo "<tr>";
    echo "<td>" . $row['aid'] . "</td>";
    echo "<td>" . htmlspecialchars($row['auser']) . "</td>";
    echo "<td>" . htmlspecialchars($row['aemail']) . "</td>";
    echo "<td>" . $hashType . "</td>";
    echo "<td>" . strlen($row['apass']) . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
