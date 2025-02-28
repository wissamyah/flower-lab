<?php
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/config.php';

// Process AJAX request
header('Content-Type: application/json');

// Handle GET requests for order details
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'details' && isset($_GET['orderNumber'])) {
    $orderNumber = $_GET['orderNumber'];
    $db = getDB();
    
    // Get order details
    $orderQuery = "SELECT o.*, u.name, u.email FROM orders o 
                  JOIN users u ON o.user_id = u.id 
                  WHERE o.order_number = ?";
    $orderStmt = $db->prepare($orderQuery);
    $orderStmt->bind_param("s", $orderNumber);
    $orderStmt->execute();
    $orderResult = $orderStmt->get_result();
    
    if ($orderResult->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Order not found'
        ]);
        exit;
    }
    
    $order = $orderResult->fetch_assoc();
    
    // Get order items
    $itemsQuery = "SELECT oi.*, i.title, i.category FROM order_items oi 
                  JOIN items i ON oi.item_id = i.id 
                  WHERE oi.order_id = ?";
    $itemsStmt = $db->prepare($itemsQuery);
    $itemsStmt->bind_param("i", $order['id']);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();
    
    $items = [];
    $subtotal = 0;
    
    while ($item = $itemsResult->fetch_assoc()) {
        $items[] = $item;
        $itemTotal = ($item['price'] - ($item['discount'] ?? 0)) * $item['quantity'];
        $subtotal += $itemTotal;
    }
    
    $order['items'] = $items;
    
    // Include delivery charge in total if present
    $deliveryCharge = isset($order['delivery_charge']) ? (float)$order['delivery_charge'] : 0;
    $order['total'] = $subtotal + $deliveryCharge;
    
    echo json_encode([
        'success' => true,
        'order' => $order
    ]);
    exit;
}

// Handle POST requests
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

// Require login for order operations
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
    case 'create':
        // Required fields
        if (!isset($input['name']) || !isset($input['phone']) || !isset($input['address'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Missing required fields'
            ]);
            exit;
        }
        
        $name = $input['name'];
        $phone = $input['phone'];
        $address = $input['address'];
        $giftMessage = $input['gift_message'] ?? null;
        
        // Start transaction
        $db->begin_transaction();
        
        try {
            // Get basket items
            $basketQuery = "SELECT b.item_id, b.quantity, i.price, i.discount, i.stock FROM basket b 
                          JOIN items i ON b.item_id = i.id 
                          WHERE b.user_id = ?";
            $basketStmt = $db->prepare($basketQuery);
            $basketStmt->bind_param("i", $userId);
            $basketStmt->execute();
            $basketResult = $basketStmt->get_result();
            
            if ($basketResult->num_rows === 0) {
                throw new Exception('Basket is empty');
            }
            
            $basketItems = [];
            while ($item = $basketResult->fetch_assoc()) {
                // Check stock
                if ($item['stock'] < $item['quantity']) {
                    throw new Exception('Not enough stock for one or more items');
                }
                
                $basketItems[] = $item;
            }
            
            // Get delivery settings
            $deliveryRate = 0;
            $freeDeliveryThreshold = 0;

            $settingsQuery = "SELECT * FROM settings WHERE setting_key IN ('delivery_rate', 'free_delivery_threshold')";
            $settingsResult = $db->query($settingsQuery);

            if ($settingsResult && $settingsResult->num_rows > 0) {
                while ($setting = $settingsResult->fetch_assoc()) {
                    if ($setting['setting_key'] === 'delivery_rate') {
                        $deliveryRate = floatval($setting['setting_value']);
                    } elseif ($setting['setting_key'] === 'free_delivery_threshold') {
                        $freeDeliveryThreshold = floatval($setting['setting_value']);
                    }
                }
            }

            // Calculate subtotal from basket items
            $subtotal = 0;
            foreach ($basketItems as $item) {
                $itemSubtotal = $item['price'] * $item['quantity'];
                $itemDiscount = $item['discount'] ? $item['discount'] * $item['quantity'] : 0;
                $subtotal += ($itemSubtotal - $itemDiscount);
            }

            // Calculate if eligible for free delivery
            $freeDelivery = ($freeDeliveryThreshold > 0 && $subtotal >= $freeDeliveryThreshold) || $deliveryRate <= 0;

            // Calculate delivery charge
            $deliveryCharge = $freeDelivery ? 0 : $deliveryRate;
            
            // Generate order number
            $orderNumber = ORDER_PREFIX . date('Ymd') . sprintf('%04d', rand(1, 9999));
            
            // Create order with delivery_charge
            $orderQuery = "INSERT INTO orders (order_number, user_id, address, phone, gift_message, status, delivery_charge) 
                          VALUES (?, ?, ?, ?, ?, 'Pending', ?)";
            $orderStmt = $db->prepare($orderQuery);
            $orderStmt->bind_param("sisssd", $orderNumber, $userId, $address, $phone, $giftMessage, $deliveryCharge);
            $orderStmt->execute();
            $orderId = $db->insert_id;
            
            // Add order items
            $orderItemQuery = "INSERT INTO order_items (order_id, item_id, quantity, price, discount) 
                              VALUES (?, ?, ?, ?, ?)";
            $orderItemStmt = $db->prepare($orderItemQuery);
            
            foreach ($basketItems as $item) {
                $itemId = $item['item_id'];
                $quantity = $item['quantity'];
                $price = $item['price'];
                $discount = $item['discount'];
                
                $orderItemStmt->bind_param("iiidi", $orderId, $itemId, $quantity, $price, $discount);
                $orderItemStmt->execute();
                
                // Update stock
                $newStock = $item['stock'] - $quantity;
                $updateStockStmt = $db->prepare("UPDATE items SET stock = ? WHERE id = ?");
                $updateStockStmt->bind_param("ii", $newStock, $itemId);
                $updateStockStmt->execute();
            }
            
            // Clear basket
            $clearBasketStmt = $db->prepare("DELETE FROM basket WHERE user_id = ?");
            $clearBasketStmt->bind_param("i", $userId);
            $clearBasketStmt->execute();
            
            // Update user info if needed
            $updateUserStmt = $db->prepare("UPDATE users SET name = ?, phone_number = ?, address = ? WHERE id = ?");
            $updateUserStmt->bind_param("sssi", $name, $phone, $address, $userId);
            $updateUserStmt->execute();
            
            // Commit transaction
            $db->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Order created successfully',
                'order_number' => $orderNumber
            ]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $db->rollback();
            
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
}