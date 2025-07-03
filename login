// Make sure your login code is updated to use password_verify() like this:
if ($utype === 'admin') {
    // This query should NOT include password in the WHERE clause
    $sql = "SELECT * FROM admin WHERE (aemail=? OR auser=?)";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $emailOrUsername, $emailOrUsername);
} else {
    // Other user types...
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // Get the stored hash based on user type
    $stored_hash = ($utype === 'admin') ? $row['apass'] : $row['upass'];
    
    // Use password_verify to check the password
    if (password_verify($password, $stored_hash)) {
        // Password is correct, proceed with login
        // ...
    } else {
        $loginErrors['login'] = 'Invalid credentials';
    }
}