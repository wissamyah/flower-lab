<?php
// Turn off all error reporting for AJAX requests
error_reporting(0);
ini_set('display_errors', 0);

// Start with a clean output buffer
ob_start();

// Include the database connection file
require_once dirname(__DIR__) . '/includes/db.php';

// Explicitly set content type
header('Content-Type: application/json');

try {
    // Get JSON input
    $jsonInput = file_get_contents('php://input');
    $input = json_decode($jsonInput, true);

    if (!$input || !isset($input['idToken']) || !isset($input['email'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request data'
        ]);
        ob_end_flush();
        exit;
    }

    // In a real application, you would verify the Firebase ID token
    // Here we're simplifying and just accepting the data
    $firebase_uid = uniqid('firebase_'); // Simulate Firebase UID for testing
    $email = $input['email'];
    $phone_number = $input['phoneNumber'] ?? '';
    $name = $input['displayName'] ?? '';

    // Store in session
    session_start();
    $_SESSION['firebase_uid'] = $firebase_uid;

    // Get database connection
    $db = getDB();

    // Check if user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing user
        $user = $result->fetch_assoc();
        $userId = $user['id'];
        
        $stmt = $db->prepare("UPDATE users SET firebase_uid = ?, phone_number = ?, name = ? WHERE id = ?");
        $stmt->bind_param("sssi", $firebase_uid, $phone_number, $name, $userId);
        $stmt->execute();
    } else {
        // Create new user
        $stmt = $db->prepare("INSERT INTO users (firebase_uid, email, phone_number, name) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $firebase_uid, $email, $phone_number, $name);
        $stmt->execute();
        $userId = $db->insert_id;
    }

    // Check for redirect
    $redirect = null;
    if (isset($_SESSION['redirect_after_login'])) {
        $redirect = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
    }

    // Output success response
    echo json_encode([
        'success' => true,
        'message' => 'User synced successfully',
        'redirect' => $redirect
    ]);
    
} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

// Clean and end the output buffer to ensure only JSON is sent
ob_end_flush();
exit;