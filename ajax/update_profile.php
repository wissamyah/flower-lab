<?php
// Turn off error reporting and use exception handling
error_reporting(0);
ini_set('display_errors', 0);

// Start a clean output buffer
ob_start();

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Include required files
    require_once dirname(__DIR__) . '/includes/db.php';
    require_once dirname(__DIR__) . '/includes/auth.php';

    // Check if the request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Check if user is logged in
    if (!isLoggedIn()) {
        throw new Exception('User not authenticated');
    }

    // Get user ID
    $userId = getCurrentUserId();
    if (!$userId) {
        throw new Exception('Unable to determine user ID');
    }

    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST; // Try regular POST data if JSON fails
    }

    // Validate required fields
    if (empty($data['name'])) {
        throw new Exception('Name is required');
    }
    
    if (empty($data['phone'])) {
        throw new Exception('Phone number is required');
    }

    // Extract data
    $name = trim($data['name']);
    $phone = trim($data['phone']);
    $address = trim($data['address'] ?? '');

    // Log the data we're about to use
    error_log("Update profile with name: $name, phone: $phone, userId: $userId");

    // Get DB connection
    $db = getDB();

    // Prepare update statement
    $query = "UPDATE users SET name = ?, phone_number = ?, address = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $db->error);
    }

    // Bind parameters and execute
    $stmt->bind_param("sssi", $name, $phone, $address, $userId);
    $success = $stmt->execute();
    
    if (!$success) {
        throw new Exception('Database update failed: ' . $stmt->error);
    }

    // Check if any rows were affected
    if ($stmt->affected_rows === 0) {
        // Try to determine if the record exists
        $checkQuery = "SELECT id FROM users WHERE id = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bind_param("i", $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows === 0) {
            throw new Exception('User record not found');
        } else {
            // Record exists but no changes were made
            echo json_encode([
                'success' => true,
                'message' => 'No changes were made to the profile'
            ]);
        }
    } else {
        // Success
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'name' => $name,
                'phone_number' => $phone,
                'address' => $address
            ]
        ]);
    }
} catch (Exception $e) {
    // Handle all exceptions
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
    // Log error for debugging
    error_log('Profile update error: ' . $e->getMessage());
}

// End output buffer and flush
ob_end_flush();
exit;