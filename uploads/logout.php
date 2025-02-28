<?php
// /flower-lab/logout.php

// Start the session if it hasn't already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Store some data for flash message after logout
$wasLoggedIn = isset($_SESSION['firebase_uid']);
$userName = $_SESSION['user_name'] ?? '';

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Check if this is an AJAX request
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    // Respond with JSON for AJAX requests
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'You have been logged out successfully'
    ]);
    exit;
}

// Store flash message in cookie for non-AJAX requests
if ($wasLoggedIn) {
    setcookie('flash_message', json_encode([
        'type' => 'success',
        'message' => 'You have been signed out successfully.',
        'title' => $userName ? "Goodbye, $userName!" : 'Signed Out'
    ]), time() + 5, '/');
}

// Redirect to the index page
header('Location: /flower-lab/index.php');
exit;