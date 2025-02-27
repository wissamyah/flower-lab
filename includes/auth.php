<?php
// /flower-lab/includes/auth.php

require_once dirname(__DIR__) . '/includes/db.php';

// Check if user is logged in via Firebase
function isLoggedIn() {
    return isset($_SESSION['firebase_uid']) && !empty($_SESSION['firebase_uid']);
}

// Get current user ID from database
function getCurrentUserId() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDB();
    $firebase_uid = $_SESSION['firebase_uid'];
    
    $stmt = $db->prepare("SELECT id FROM users WHERE firebase_uid = ?");
    $stmt->bind_param("s", $firebase_uid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        return $user['id'];
    }
    
    return null;
}

// Get current user data
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDB();
    $firebase_uid = $_SESSION['firebase_uid'];
    
    $stmt = $db->prepare("SELECT * FROM users WHERE firebase_uid = ?");
    $stmt->bind_param("s", $firebase_uid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Fetch and return the user data
        $user = $result->fetch_assoc();
        return $user;
    }
    
    return null;
}

// Create or update user in database after Firebase authentication
function syncUserWithFirebase($firebase_uid, $email, $phone_number, $name = null) {
    $db = getDB();
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE firebase_uid = ?");
    $stmt->bind_param("s", $firebase_uid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing user
        $user = $result->fetch_assoc();
        $stmt = $db->prepare("UPDATE users SET email = ?, phone_number = ?, name = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("sssi", $email, $phone_number, $name, $user['id']);
        $stmt->execute();
        return $user['id'];
    } else {
        // Create new user
        $stmt = $db->prepare("INSERT INTO users (firebase_uid, email, phone_number, name, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("ssss", $firebase_uid, $email, $phone_number, $name);
        $stmt->execute();
        return $db->insert_id;
    }
}

// Require user to be logged in, redirect to login if not
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: /flower-lab/login.php');
        exit;
    }
}

// Check if user is admin
function isAdmin() {
    // Check if user has admin flag set
    $user = getCurrentUser();
    
    // Debug line - you can remove this after confirming it works
    error_log('Admin check for user: ' . ($user ? json_encode($user) : 'Not logged in'));
    
    // Explicit check for the admin flag set to 1 (as integer or string)
    return $user && isset($user['is_admin']) && ($user['is_admin'] == 1 || $user['is_admin'] === '1');
}
// Require user to be admin, redirect if not
function requireAdmin() {
    requireLogin();
    
    if (!isAdmin()) {
        header('Location: /flower-lab/index.php');
        exit;
    }
}