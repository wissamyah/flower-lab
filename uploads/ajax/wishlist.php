<?php
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';

// Process AJAX request
header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data'
    ]);
    exit;
}

$action = $input['action'];
$userId = getCurrentUserId();

// Require login for wishlist operations
if (!$userId) {
    echo json_encode([
        'success' => false,
        'message' => 'User not authenticated',
        'redirect' => '/flower-lab/login.php'
    ]);
    exit;
}

$db = getDB();

switch ($action) {
    case 'add':
        if (!isset($input['itemId'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Missing item ID'
            ]);
            exit;
        }
        
        $itemId = (int)$input['itemId'];
        
        // Check if item exists
        $checkStmt = $db->prepare("SELECT id FROM items WHERE id = ?");
        $checkStmt->bind_param("i", $itemId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Item not found'
            ]);
            exit;
        }
        
        // Check if item already in wishlist
        $stmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND item_id = ?");
        $stmt->bind_param("ii", $userId, $itemId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Item already in wishlist'
            ]);
            exit;
        }
        
        // Add to wishlist
        $insertStmt = $db->prepare("INSERT INTO wishlist (user_id, item_id) VALUES (?, ?)");
        $insertStmt->bind_param("ii", $userId, $itemId);
        $success = $insertStmt->execute();
        
        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Item added to wishlist'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to add item to wishlist'
            ]);
        }
        break;
        
    case 'remove':
        if (!isset($input['itemId'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Missing item ID'
            ]);
            exit;
        }
        
        $itemId = (int)$input['itemId'];
        
        $stmt = $db->prepare("DELETE FROM wishlist WHERE user_id = ? AND item_id = ?");
        $stmt->bind_param("ii", $userId, $itemId);
        $success = $stmt->execute();
        
        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Item removed from wishlist'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to remove item from wishlist'
            ]);
        }
        break;
        
    case 'check':
        if (!isset($input['itemId'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Missing item ID'
            ]);
            exit;
        }
        
        $itemId = (int)$input['itemId'];
        
        $stmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND item_id = ?");
        $stmt->bind_param("ii", $userId, $itemId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        echo json_encode([
            'success' => true,
            'inWishlist' => $result->num_rows > 0
        ]);
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
}