<?php
// Save this as notification-debug.php in your root directory
// This tool will help diagnose issues with notification elements

require_once 'includes/db.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo '<div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 20px;">
            <h2>Error: Not Logged In</h2>
            <p>You must be logged in to use this diagnostic tool.</p>
            <p><a href="/flower-lab/login.php" style="color: #721c24; font-weight: bold;">Go to Login Page</a></p>
          </div>';
    exit;
}

// Get user ID
$userId = getCurrentUserId();

// HTML header
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification System Debug</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
        .card { border: 1px solid #ddd; border-radius: 4px; padding: 15px; margin-bottom: 20px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .warning { background-color: #fff3cd; color: #856404; }
        .info { background-color: #d1ecf1; color: #0c5460; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        code { background-color: #f8f9fa; padding: 2px 4px; border-radius: 4px; font-family: monospace; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .btn { display: inline-block; padding: 8px 16px; background-color: #821633; color: white; text-decoration: none; border-radius: 4px; }
        .btn:hover { background-color: #375645; }
    </style>
</head>
<body>
    <h1>Notification System Debug Tool</h1>';

// Check for unread notifications in the database
$db = getDB();

// Check if notifications table exists
$tableCheck = $db->query("SHOW TABLES LIKE 'notifications'");
if ($tableCheck->num_rows === 0) {
    echo '<div class="card error">
            <h2>Notifications Table Missing</h2>
            <p>The notifications table does not exist in your database. This could be why notifications aren\'t working.</p>
            <p>Create the table with the following SQL:</p>
            <pre>
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            </pre>
          </div>';
} else {
    // Table exists, check for notifications
    $countQuery = "SELECT 
                      COUNT(*) as total,
                      SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread
                  FROM notifications 
                  WHERE user_id = ?";
    
    $stmt = $db->prepare($countQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $counts = $result->fetch_assoc();
    
    echo '<div class="card info">
            <h2>Notification Database Status</h2>
            <p>Total notifications: <strong>' . $counts['total'] . '</strong></p>
            <p>Unread notifications: <strong>' . $counts['unread'] . '</strong></p>
          </div>';
    
    // Recent notifications
    $recentQuery = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
    $stmt = $db->prepare($recentQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo '<div class="card info">
            <h2>Recent Notifications</h2>';
    
    if ($result->num_rows > 0) {
        echo '<table>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th>Is Read</th>
                    <th>Created At</th>
                </tr>';
        
        while ($notification = $result->fetch_assoc()) {
            echo '<tr>
                    <td>' . $notification['id'] . '</td>
                    <td>' . htmlspecialchars($notification['type']) . '</td>
                    <td>' . htmlspecialchars($notification['title']) . '</td>
                    <td>' . ($notification['is_read'] ? 'Yes' : 'No') . '</td>
                    <td>' . $notification['created_at'] . '</td>
                  </tr>';
        }
        
        echo '</table>';
    } else {
        echo '<p>No recent notifications found.</p>';
    }
    
    echo '</div>';
}

// Notification Elements Check
echo '<div class="card info">
        <h2>Element Check Script</h2>
        <p>Add this code to your site temporarily to check if notification elements exist:</p>
        <pre>
&lt;script&gt;
document.addEventListener("DOMContentLoaded", function() {
    console.log("Checking notification elements");
    
    // Profile icon notification indicator
    const profileIndicator = document.getElementById("profile-notification-indicator");
    console.log("Profile notification indicator:", profileIndicator ? "Found" : "Missing");
    
    // Dropdown notification badge
    const dropdownBadge = document.getElementById("dropdown-notification-badge");
    console.log("Dropdown notification badge:", dropdownBadge ? "Found" : "Missing");
    
    // Notification count element
    const notificationCount = document.getElementById("notification-count");
    console.log("Notification count element:", notificationCount ? "Found" : "Missing");
    
    // Notification list container
    const notificationList = document.getElementById("notification-list");
    console.log("Notification list element:", notificationList ? "Found" : "Missing");
    
    // Check visibility
    if (profileIndicator) console.log("Profile indicator visible:", !profileIndicator.classList.contains("hidden"));
    if (dropdownBadge) console.log("Dropdown badge visible:", !dropdownBadge.classList.contains("hidden"));
});
&lt;/script&gt;
        </pre>
      </div>';

// Create Test Notification Option
echo '<div class="card info">
        <h2>Create Test Notification</h2>
        <p>You can create a test notification to check if the notification system is working:</p>
        <form method="post" action="">
            <button type="submit" name="create_test" class="btn">Create Test Notification</button>
        </form>
      </div>';

// Handle test notification creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_test'])) {
    // Create a test notification in the database
    $query = "INSERT INTO notifications (user_id, type, title, message, is_read) 
              VALUES (?, 'test', 'Test Notification', 'This is a test notification created at " . date('Y-m-d H:i:s') . "', 0)";
    
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $userId);
    
    if ($stmt->execute()) {
        echo '<div class="card success">
                <h2>Success!</h2>
                <p>Test notification created successfully. Refresh your main page to see if it appears.</p>
              </div>';
    } else {
        echo '<div class="card error">
                <h2>Error</h2>
                <p>Failed to create test notification: ' . $db->error . '</p>
              </div>';
    }
}

// Mark All As Read Option
echo '<div class="card info">
        <h2>Reset Notification Status</h2>
        <p>You can mark all notifications as read to reset the notification state:</p>
        <form method="post" action="">
            <button type="submit" name="mark_all_read" class="btn">Mark All As Read</button>
        </form>
      </div>';

// Handle mark all as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
    $query = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $userId);
    
    if ($stmt->execute()) {
        echo '<div class="card success">
                <h2>Success!</h2>
                <p>All notifications have been marked as read.</p>
              </div>';
    } else {
        echo '<div class="card error">
                <h2>Error</h2>
                <p>Failed to mark notifications as read: ' . $db->error . '</p>
              </div>';
    }
}

// Footer
echo '<p><a href="/flower-lab/index.php" class="btn">Return to Home Page</a></p>
    </body>
    </html>';