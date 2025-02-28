<?php
// Turn off error display for AJAX requests
error_reporting(0);
ini_set('display_errors', 0);

// Start with a clean output buffer
ob_start();

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Include required files
    require_once dirname(__DIR__) . '/includes/db.php';
    require_once dirname(__DIR__) . '/includes/auth.php';

    // Check if user is logged in
    $userId = getCurrentUserId();
    if (!$userId) {
        throw new Exception('User not authenticated');
    }
    
    // Get database connection
    $db = getDB();
    
    // Check if notifications table exists
    $checkTable = $db->query("SHOW TABLES LIKE 'notifications'");
    if ($checkTable->num_rows === 0) {
        // Create the notifications table if it doesn't exist
        $createTableSQL = "CREATE TABLE IF NOT EXISTS `notifications` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `type` varchar(50) NOT NULL,
            `title` varchar(255) NOT NULL,
            `message` text NOT NULL,
            `is_read` tinyint(1) NOT NULL DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $db->query($createTableSQL);
    }
    
    // Get user notifications
    $query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'notifications' => $notifications
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Clean and end the output buffer
ob_end_flush();
exit;