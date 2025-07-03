<?php
// Enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
	$con = mysqli_connect("localhost","root","","rems");
	if (mysqli_connect_errno())
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}
	
?>
