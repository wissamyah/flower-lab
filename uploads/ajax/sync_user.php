<?php
// /flower-lab/ajax/sync_user.php

// Turn off all error reporting for AJAX requests
error_reporting(0);
ini_set('display_errors', 0);

// Start with a clean output buffer
ob_start();

// Explicitly set content type
header('Content-Type: application/json');

try {
    // Include the database connection file
    require_once dirname(__DIR__) . '/includes/db.php';

    // Get JSON input
    $jsonInput = file_get_contents('php://input');
    $input = json_decode($jsonInput, true);

    if (!$input || !isset($input['email'])) {
        // Clean buffer before sending JSON response
        ob_clean();
        
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request data'
        ]);
        ob_end_flush();
        exit;
    }

    // Use Firebase UID from the input
    $firebase_uid = $input['uid'] ?? ('firebase_' . uniqid());
    $email = $input['email'];
    $phone_number = $input['phoneNumber'] ?? '';
    $name = $input['displayName'] ?? '';
    $skipRedirect = $input['skipRedirect'] ?? false;

    // Store in session
    session_start();
    $_SESSION['firebase_uid'] = $firebase_uid;
    $_SESSION['user_email'] = $email;

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
        
        // Build update query dynamically
        $query = "UPDATE users SET firebase_uid = ?, updated_at = NOW()";
        $params = array($firebase_uid);
        $types = "s";
        
        if (!empty($name)) {
            $query .= ", name = ?";
            $params[] = $name;
            $types .= "s";
        }
        
        if (!empty($phone_number)) {
            $query .= ", phone_number = ?";
            $params[] = $phone_number;
            $types .= "s";
        }
        
        $query .= " WHERE id = ?";
        $params[] = $userId;
        $types .= "i";
        
        $stmt = $db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    } else {
        // Create new user
        $stmt = $db->prepare("INSERT INTO users (firebase_uid, email, phone_number, name, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("ssss", $firebase_uid, $email, $phone_number, $name);
        $stmt->execute();
        $userId = $db->insert_id;
    }

    // Check for redirect
    $redirect = null;
    if (isset($_SESSION['redirect_after_login']) && !$skipRedirect) {
        $redirect = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
    }

    // Clean buffer before JSON output
    ob_clean();
    
    // Output success response
    echo json_encode([
        'success' => true,
        'message' => 'User synced successfully',
        'redirect' => $redirect
    ]);
    
} catch (Exception $e) {
    // Clean buffer before error response
    ob_clean();
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    
    // Log the error
    error_log('User sync error: ' . $e->getMessage());
}

// Clean and end the output buffer to ensure only JSON is sent
ob_end_flush();
exit;