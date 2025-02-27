<?php
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAdmin();

// Process AJAX request
header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['products']) || !is_array($input['products'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data'
    ]);
    exit;
}

$products = $input['products'];
$db = getDB();

// Start transaction
$db->begin_transaction();

try {
    $importCount = 0;
    
    foreach ($products as $product) {
        $title = $product['title'] ?? '';
        $category = $product['category'] ?? '';
        $price = (float)($product['price'] ?? 0);
        $stock = (int)($product['stock'] ?? 0);
        $discount = isset($product['discount']) && $product['discount'] !== '' ? (float)$product['discount'] : null;
        $isFeatured = (int)($product['is_featured'] ?? 0);
        $description = $product['description'] ?? '';
        $imageUrl = $product['image_url'] ?? '';
        
        // Basic validation
        if (empty($title) || empty($category) || $price <= 0) {
            continue; // Skip invalid entries
        }
        
        // Create new product
        $query = "INSERT INTO items (title, category, price, stock, discount, is_featured, description, image_url, created_at, updated_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $db->prepare($query);
        $stmt->bind_param("ssdiiiss", $title, $category, $price, $stock, $discount, $isFeatured, $description, $imageUrl);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $importCount++;
        }
    }
    
    // Commit transaction
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => sprintf('%d products imported successfully', $importCount)
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $db->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}