<?php
require_once 'includes/db.php';

$db = getDB();

// Check if is_admin column already exists
$checkQuery = "SHOW COLUMNS FROM users LIKE 'is_admin'";
$result = $db->query($checkQuery);

if ($result->num_rows == 0) {
    // Column doesn't exist, add it
    $alterQuery = "ALTER TABLE users ADD COLUMN is_admin TINYINT(1) NOT NULL DEFAULT 0";
    if ($db->query($alterQuery)) {
        echo "Added is_admin column to users table successfully!<br>";
    } else {
        echo "Error adding is_admin column: " . $db->error . "<br>";
    }
} else {
    echo "is_admin column already exists in users table.<br>";
}

// Check existing admin users
$adminQuery = "SELECT * FROM users WHERE is_admin = 1";
$adminResult = $db->query($adminQuery);

if ($adminResult->num_rows == 0) {
    echo "No admin users found. You can create one at <a href='setup.php'>setup.php</a><br>";
} else {
    echo "Found " . $adminResult->num_rows . " admin user(s).<br>";
    while ($admin = $adminResult->fetch_assoc()) {
        echo "- " . htmlspecialchars($admin['email']) . "<br>";
    }
}
?>