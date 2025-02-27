<?php
// /flower-lab/ajax/firebase_sync.php

// Completely disable error output
error_reporting(0);
ini_set('display_errors', 0);

// Make sure we're only sending JSON
header('Content-Type: application/json');

// Start output buffering to ensure no stray output
ob_start();

try {
    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Log received data for debugging (optional)
    error_log('Firebase sync received: ' . json_encode($input));
    
    if (!$input || !isset($input['email'])) {
        throw new Exception('Invalid request data');
    }
    
    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Get Firebase UID from input or generate one if not available
    $firebase_uid = $input['uid'] ?? ('firebase_' . uniqid());
    
    // Store in session
    $_SESSION['firebase_uid'] = $firebase_uid;
    $_SESSION['user_email'] = $input['email'];
    
    // Extract data
    $email = $input['email'];
    $phone = $input['phoneNumber'] ?? '';
    $name = $input['displayName'] ?? '';
    
    // Include database connection
    require_once dirname(__DIR__) . '/includes/db.php';
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
        
        // Update the firebase UID and other fields if provided
        $updateFields = array();
        $updateParams = array();
        $updateTypes = "";
        
        // Always update firebase_uid and timestamp
        $updateFields[] = "firebase_uid = ?";
        $updateParams[] = $firebase_uid;
        $updateTypes .= "s";
        
        $updateFields[] = "updated_at = NOW()";
        
        // Update name if provided and not empty
        if (!empty($name)) {
            $updateFields[] = "name = ?";
            $updateParams[] = $name;
            $updateTypes .= "s";
        }
        
        // Update phone if provided and not empty
        if (!empty($phone)) {
            $updateFields[] = "phone_number = ?";
            $updateParams[] = $phone;
            $updateTypes .= "s";
        }
        
        // Build the SQL query
        $query = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
        $updateParams[] = $userId;
        $updateTypes .= "i";
        
        $stmt = $db->prepare($query);
        $stmt->bind_param($updateTypes, ...$updateParams);
        $stmt->execute();
        
        error_log("Updated existing user: $email with firebase_uid: $firebase_uid");
    } else {
        // Create new user
        $stmt = $db->prepare("INSERT INTO users (firebase_uid, email, phone_number, name, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("ssss", $firebase_uid, $email, $phone, $name);
        $stmt->execute();
        $userId = $db->insert_id;
        
        error_log("Created new user: $email with firebase_uid: $firebase_uid, ID: $userId");
    }
    
    // Check for redirect
    $redirect = null;
    if (isset($_SESSION['redirect_after_login'])) {
        $redirect = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
    } else {
        // Default redirect to home page
        $redirect = '/flower-lab/';
    }
    
    // Clean the buffer to ensure no stray output
    ob_clean();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'User synced successfully',
        'redirect' => $redirect
    ]);
    
} catch (Exception $e) {
    // Clean the buffer
    ob_clean();
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    
    // Log the error
    error_log('Firebase sync error: ' . $e->getMessage());
}

// End output buffer
ob_end_flush();
exit;