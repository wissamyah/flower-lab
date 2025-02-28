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
    
    // Mark all notifications as read
    $query = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'message' => 'All notifications marked as read',
        'count' => $stmt->affected_rows
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