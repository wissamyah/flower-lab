<?php
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAdmin();

// Process AJAX request
header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['product_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data'
    ]);
    exit;
}

$productId = (int)$input['product_id'];
$db = getDB();

// Check if product exists
$checkQuery = "SELECT id FROM items WHERE id = ?";
$checkStmt = $db->prepare($checkQuery);
$checkStmt->bind_param("i", $productId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Product not found'
    ]);
    exit;
}

// Check if product is in any orders (if so, we should not delete it)
$orderQuery = "SELECT COUNT(*) as order_count FROM order_items WHERE item_id = ?";
$orderStmt = $db->prepare($orderQuery);
$orderStmt->bind_param("i", $productId);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();
$orderCount = $orderResult->fetch_assoc()['order_count'];

if ($orderCount > 0) {
    echo json_encode([
        'success' => false,
        'message' => "This product is used in $orderCount order(s) and cannot be deleted. Consider setting stock to 0 instead."
    ]);
    exit;
}

// Start transaction
$db->begin_transaction();

try {
    // Remove from wishlist
    $wishlistQuery = "DELETE FROM wishlist WHERE item_id = ?";
    $wishlistStmt = $db->prepare($wishlistQuery);
    $wishlistStmt->bind_param("i", $productId);
    $wishlistStmt->execute();
    
    // Remove from basket
    $basketQuery = "DELETE FROM basket WHERE item_id = ?";
    $basketStmt = $db->prepare($basketQuery);
    $basketStmt->bind_param("i", $productId);
    $basketStmt->execute();
    
    // Delete product
    $deleteQuery = "DELETE FROM items WHERE id = ?";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->bind_param("i", $productId);
    $deleteStmt->execute();
    
    if ($deleteStmt->affected_rows === 0) {
        throw new Exception('Failed to delete product');
    }
    
    // Commit transaction
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Product deleted successfully'
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $db->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}