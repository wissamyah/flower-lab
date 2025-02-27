<?php
// Turn off error display for AJAX requests
error_reporting(0);
ini_set('display_errors', 0);

// Start with a clean output buffer
ob_start();

require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

// Process AJAX request
header('Content-Type: application/json');

try {
    // Check if user is logged in
    $userId = getCurrentUserId();
    if (!$userId) {
        throw new Exception('User not authenticated');
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['notification_id'])) {
        throw new Exception('Invalid request data');
    }
    
    $notificationId = (int)$input['notification_id'];
    
    // Get database connection
    $db = getDB();
    
    // Mark notification as read
    $query = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("ii", $notificationId, $userId);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('Notification not found or not owned by user');
    }
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'message' => 'Notification marked as read'
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