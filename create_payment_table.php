<?php
require("config.php");

$sql = "CREATE TABLE IF NOT EXISTS payment_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100) NOT NULL,
    payment_date DATETIME NOT NULL,
    status VARCHAR(20) NOT NULL,
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    FOREIGN KEY (user_id) REFERENCES user(uid),
    FOREIGN KEY (property_id) REFERENCES property(pid)
)";

if (mysqli_query($con, $sql)) {
    echo "Payment history table created successfully";
} else {
    echo "Error creating table: " . mysqli_error($con);
}
?>
