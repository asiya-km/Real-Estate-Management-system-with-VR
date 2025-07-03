<?php
require("config.php");

// Add new columns to the property table
$alterTableSQL = "
ALTER TABLE property 
ADD COLUMN latitude VARCHAR(20) DEFAULT NULL,
ADD COLUMN longitude VARCHAR(20) DEFAULT NULL,
ADD COLUMN map_zoom INT DEFAULT 14;
";

if (mysqli_query($con, $alterTableSQL)) {
    echo "Database updated successfully with new GPS location fields.";
} else {
    echo "Error updating database: " . mysqli_error($con);
}

mysqli_close($con);
?>