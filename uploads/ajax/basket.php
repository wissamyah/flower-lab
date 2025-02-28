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

// Require login for basket operations
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
        if (!isset($input['itemId']) || !isset($input['quantity'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Missing item ID or quantity'
            ]);
            exit;
        }
        
        $itemId = (int)$input['itemId'];
        $quantity = (int)$input['quantity'];
        
        if ($quantity <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Quantity must be greater than zero'
            ]);
            exit;
        }
        
        // Check if item exists and has stock
        $checkStmt = $db->prepare("SELECT stock FROM items WHERE id = ?");
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
        
        $item = $checkResult->fetch_assoc();
        if ($item['stock'] < $quantity) {
            echo json_encode([
                'success' => false,
                'message' => 'Not enough stock available'
            ]);
            exit;
        }
        
        // Check if item already in basket
        $stmt = $db->prepare("SELECT id, quantity FROM basket WHERE user_id = ? AND item_id = ?");
        $stmt->bind_param("ii", $userId, $itemId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update quantity
            $basketItem = $result->fetch_assoc();
            $newQuantity = $basketItem['quantity'] + $quantity;
            
            if ($newQuantity > $item['stock']) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Cannot add more of this item (stock limit reached)'
                ]);
                exit;
            }
            
            $updateStmt = $db->prepare("UPDATE basket SET quantity = ?, updated_at = NOW() WHERE id = ?");
            $updateStmt->bind_param("ii", $newQuantity, $basketItem['id']);
            $success = $updateStmt->execute();
        } else {
            // Add new item to basket
            $insertStmt = $db->prepare("INSERT INTO basket (user_id, item_id, quantity) VALUES (?, ?, ?)");
            $insertStmt->bind_param("iii", $userId, $itemId, $quantity);
            $success = $insertStmt->execute();
        }
        
        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Item added to basket'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to add item to basket'
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
        
        $stmt = $db->prepare("DELETE FROM basket WHERE user_id = ? AND item_id = ?");
        $stmt->bind_param("ii", $userId, $itemId);
        $success = $stmt->execute();
        
        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Item removed from basket'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to remove item from basket'
            ]);
        }
        break;
        
    case 'update':
        if (!isset($input['itemId']) || !isset($input['quantity'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Missing item ID or quantity'
            ]);
            exit;
        }
        
        $itemId = (int)$input['itemId'];
        $quantity = (int)$input['quantity'];
        
        if ($quantity <= 0) {
            // Remove item from basket if quantity is 0 or negative
            $stmt = $db->prepare("DELETE FROM basket WHERE user_id = ? AND item_id = ?");
            $stmt->bind_param("ii", $userId, $itemId);
            $success = $stmt->execute();
        } else {
            // Check if item has enough stock
            $checkStmt = $db->prepare("SELECT stock FROM items WHERE id = ?");
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
            
            $item = $checkResult->fetch_assoc();
            if ($item['stock'] < $quantity) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Not enough stock available'
                ]);
                exit;
            }
            
            // Update quantity
            $stmt = $db->prepare("UPDATE basket SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND item_id = ?");
            $stmt->bind_param("iii", $quantity, $userId, $itemId);
            $success = $stmt->execute();
        }
        
        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Basket updated'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update basket'
            ]);
        }
        break;
        
    case 'count':
        // Get total items in basket
        $stmt = $db->prepare("SELECT SUM(quantity) as total FROM basket WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'count' => (int)($data['total'] ?? 0)
        ]);
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
}