<?php
// Completely disable error output
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering
ob_start();

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['email'])) {
        throw new Exception('Invalid request data');
    }
    
    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Generate a dummy firebase UID
    $firebase_uid = 'firebase_' . uniqid();
    
    // Store in session
    $_SESSION['firebase_uid'] = $firebase_uid;
    
    // Extract data
    $email = $input['email'];
    $phone = $input['phoneNumber'] ?? '';
    $name = $input['displayName'] ?? '';
    
    // Connect to database
    $dbHost = 'localhost';
    $dbUser = 'root';
    $dbPass = '';
    $dbName = 'flower-lab';
    
    $db = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    
    if ($db->connect_error) {
        throw new Exception('Database connection failed: ' . $db->connect_error);
    }
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing user
        $user = $result->fetch_assoc();
        $userId = $user['id'];
        
        // Only update firebase_uid and keep other fields as is
        $stmt = $db->prepare("UPDATE users SET firebase_uid = ? WHERE id = ?");
        $stmt->bind_param("si", $firebase_uid, $userId);
        $stmt->execute();
    } else {
        // Create new user
        $stmt = $db->prepare("INSERT INTO users (firebase_uid, email, phone_number, name) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $firebase_uid, $email, $phone, $name);
        $stmt->execute();
        $userId = $db->insert_id;
    }
    
    // Check for redirect
    $redirect = null;
    if (isset($_SESSION['redirect_after_login'])) {
        $redirect = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'User synced successfully',
        'redirect' => $redirect
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    
    // Log the error
    error_log('Firebase sync error: ' . $e->getMessage());
}

// Flush output buffer and end script
ob_end_flush();
exit;