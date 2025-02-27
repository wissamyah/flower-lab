<?php
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$pageTitle = 'Order Management';
include dirname(__DIR__) . '/includes/header.php';

$db = getDB();

// Add this function to the PHP section at the top
function createOrderStatusNotification($orderId, $status) {
    $db = getDB();
    
    // Get order details
    $orderQuery = "SELECT o.*, u.id as user_id FROM orders o 
                  JOIN users u ON o.user_id = u.id 
                  WHERE o.id = ?";
    $orderStmt = $db->prepare($orderQuery);
    $orderStmt->bind_param("i", $orderId);
    $orderStmt->execute();
    $orderResult = $orderStmt->get_result();
    
    if ($orderResult->num_rows === 0) {
        return false;
    }
    
    $order = $orderResult->fetch_assoc();
    $userId = $order['user_id'];
    $orderNumber = $order['order_number'];
    
    // Create notification based on status
    $title = '';
    $message = '';
    $type = '';
    
    if ($status === 'Confirmed') {
        $title = 'Order Confirmed';
        $message = "Your order #$orderNumber has been confirmed and is being processed.";
        $type = 'order_confirmed';
    } else if ($status === 'Completed') {
        $title = 'Order Completed';
        $message = "Your order #$orderNumber has been completed and is ready for delivery.";
        $type = 'order_completed';
    } else {
        // Don't create notifications for other statuses
        return false;
    }
    
    // Insert notification
    $query = "INSERT INTO notifications (user_id, type, title, message) VALUES (?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->bind_param("isss", $userId, $type, $title, $message);
    return $stmt->execute();
}

// Then update the status update handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $orderId = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $oldStatus = '';
    
    // Get current status first
    $currentStatusQuery = "SELECT status FROM orders WHERE id = ?";
    $currentStatusStmt = $db->prepare($currentStatusQuery);
    $currentStatusStmt->bind_param("i", $orderId);
    $currentStatusStmt->execute();
    $currentStatusResult = $currentStatusStmt->get_result();
    
    if ($currentStatusResult->num_rows > 0) {
        $currentStatusRow = $currentStatusResult->fetch_assoc();
        $oldStatus = $currentStatusRow['status'];
    }
    
    // Update status
    $query = "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("si", $status, $orderId);
    
    if ($stmt->execute()) {
        // Create notification if status changed to Confirmed or Completed
        if (($status === 'Confirmed' || $status === 'Completed') && $oldStatus !== $status) {
            createOrderStatusNotification($orderId, $status);
        }
        
        $message = 'Order status updated successfully!';
    } else {
        $message = 'Error updating order status: ' . $db->error;
    }
}

// Handle status update
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $orderId = (int)$_POST['order_id'];
    $status = $_POST['status'];
    
    $query = "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("si", $status, $orderId);
    
    if ($stmt->execute()) {
        $message = 'Order status updated successfully!';
    } else {
        $message = 'Error updating order status: ' . $db->error;
    }
}

// Get order details if ID is provided
$order = null;
$orderItems = [];

if (isset($_GET['id'])) {
    $orderId = (int)$_GET['id'];
    
    // Get order details
    $orderQuery = "SELECT o.*, u.name, u.email, u.phone_number FROM orders o 
                  JOIN users u ON o.user_id = u.id 
                  WHERE o.id = ?";
    $orderStmt = $db->prepare($orderQuery);
    $orderStmt->bind_param("i", $orderId);
    $orderStmt->execute();
    $orderResult = $orderStmt->get_result();
    
    if ($orderResult->num_rows > 0) {
        $order = $orderResult->fetch_assoc();
        
        // Get order items
        $itemsQuery = "SELECT oi.*, i.title, i.category, i.image_url FROM order_items oi 
                      JOIN items i ON oi.item_id = i.id 
                      WHERE oi.order_id = ?";
        $itemsStmt = $db->prepare($itemsQuery);
        $itemsStmt->bind_param("i", $orderId);
        $itemsStmt->execute();
        $itemsResult = $itemsStmt->get_result();
        
        while ($item = $itemsResult->fetch_assoc()) {
            $orderItems[] = $item;
        }
    }
}

// Get all orders for the list
$allOrdersQuery = "SELECT o.*, u.name as customer_name FROM orders o 
                  JOIN users u ON o.user_id = u.id 
                  ORDER BY o.created_at DESC";
$allOrdersResult = $db->query($allOrdersQuery);
$allOrders = [];

while ($row = $allOrdersResult->fetch_assoc()) {
    $allOrders[] = $row;
}
?>

<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">
            <?= $order ? 'Order Details: ' . htmlspecialchars($order['order_number']) : 'Order Management' ?>
        </h1>
        <a href="/flower-lab/admin/" class="px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded hover:bg-gray-200">
            <i data-lucide="arrow-left" class="inline-block h-4 w-4 mr-1"></i> Back to Dashboard
        </a>
    </div>
    
    <?php if ($message): ?>
        <div class="mb-6 p-3 rounded <?= strpos($message, 'Error') === false ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Details (2/3 width on large screens) -->
        <div class="lg:col-span-2">
            <?php if ($order): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
                    <div class="p-4 bg-gray-50 border-b border-gray-100">
                        <h2 class="font-medium text-gray-800">Order Information</h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-1">Order Details</h3>
                                <p class="text-gray-800 mb-1"><span class="font-medium">Order Number:</span> <?= htmlspecialchars($order['order_number']) ?></p>
                                <p class="text-gray-800 mb-1"><span class="font-medium">Date:</span> <?= date('F j, Y g:i A', strtotime($order['created_at'])) ?></p>
                                
                                <?php
                                $statusClass = '';
                                switch ($order['status']) {
                                    case 'Pending':
                                        $statusClass = 'bg-yellow-50 text-yellow-800';
                                        break;
                                    case 'Confirmed':
                                        $statusClass = 'bg-blue-50 text-blue-800';
                                        break;
                                    case 'Completed':
                                        $statusClass = 'bg-green-50 text-green-800';
                                        break;
                                    case 'Cancelled':
                                        $statusClass = 'bg-red-50 text-red-800';
                                        break;
                                }
                                ?>
                                <p class="text-gray-800 mb-1">
                                    <span class="font-medium">Status:</span> 
                                    <span class="px-2 py-1 text-xs rounded-full <?= $statusClass ?>">
                                        <?= htmlspecialchars($order['status']) ?>
                                    </span>
                                </p>
                            </div>
                            
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-1">Customer Information</h3>
                                <p class="text-gray-800 mb-1"><span class="font-medium">Name:</span> <?= htmlspecialchars($order['name']) ?></p>
                                <p class="text-gray-800 mb-1"><span class="font-medium">Email:</span> <?= htmlspecialchars($order['email']) ?></p>
                                <p class="text-gray-800 mb-1"><span class="font-medium">Phone:</span> <?= htmlspecialchars($order['phone']) ?></p>
                                <p class="text-gray-800 mb-1"><span class="font-medium">Address:</span> <?= htmlspecialchars($order['address']) ?></p>
                            </div>
                        </div>
                        
                        <?php if ($order['gift_message']): ?>
                            <div class="mb-6">
                                <h3 class="text-sm font-medium text-gray-500 mb-1">Gift Message</h3>
                                <div class="p-3 bg-gray-50 rounded border border-gray-200 text-gray-800 italic">
                                    <?= nl2br(htmlspecialchars($order['gift_message'])) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Update Status</h3>
                            <form method="post" action="" class="flex items-center">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <select name="status" class="p-2 border border-gray-300 rounded mr-2">
                                    <option value="Pending" <?= $order['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Confirmed" <?= $order['status'] === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                    <option value="Completed" <?= $order['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="Cancelled" <?= $order['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                                <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark transition">
                                    Update Status
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-4 bg-gray-50 border-b border-gray-100">
                        <h2 class="font-medium text-gray-800">Order Items</h2>
                    </div>
                    
                    <?php if (empty($orderItems)): ?>
                        <div class="p-6 text-center text-gray-500">
                            No items found for this order
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php 
                                    $subtotal = 0;
                                    $totalDiscount = 0;
                                    
                                    foreach ($orderItems as $item): 
                                        $itemSubtotal = $item['price'] * $item['quantity'];
                                        $itemDiscount = $item['discount'] ? $item['discount'] * $item['quantity'] : 0;
                                        $itemTotal = $itemSubtotal - $itemDiscount;
                                        
                                        $subtotal += $itemSubtotal;
                                        $totalDiscount += $itemDiscount;
                                    ?>
                                        <tr>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center">
                                                    <?php if ($item['image_url']): ?>
                                                        <div class="w-10 h-10 bg-gray-100 rounded overflow-hidden mr-3">
                                                            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="w-full h-full object-cover">
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="w-10 h-10 bg-gray-100 rounded overflow-hidden mr-3 flex items-center justify-center">
                                                            <i data-lucide="flower" class="h-5 w-5 text-gray-400"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <div class="font-medium text-gray-800"><?= htmlspecialchars($item['title']) ?></div>
                                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($item['category']) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-gray-800"><?= $item['quantity'] ?></span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-gray-800">$<?= number_format($item['price'], 2) ?></span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php if ($item['discount']): ?>
                                                    <span class="text-gray-800">$<?= number_format($item['discount'], 2) ?></span>
                                                <?php else: ?>
                                                    <span class="text-gray-500">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="font-medium text-gray-800">$<?= number_format($itemTotal, 2) ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="4" class="px-6 py-3 text-right font-medium">Subtotal:</td>
                                        <td class="px-6 py-3 font-medium">$<?= number_format($subtotal, 2) ?></td>
                                    </tr>
                                    <?php if ($totalDiscount > 0): ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-3 text-right font-medium">Discount:</td>
                                        <td class="px-6 py-3 font-medium text-red-600">-$<?= number_format($totalDiscount, 2) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-3 text-right font-medium">Total:</td>
                                        <td class="px-6 py-3 font-bold text-primary-dark">$<?= number_format($subtotal - $totalDiscount, 2) ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-4 bg-gray-50 border-b border-gray-100">
                        <h2 class="font-medium text-gray-800">All Orders</h2>
                    </div>
                    
                    <?php if (empty($allOrders)): ?>
                        <div class="p-8 text-center text-gray-500">
                            No orders found
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Number</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($allOrders as $o): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="font-medium text-gray-800"><?= htmlspecialchars($o['order_number']) ?></span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-gray-800"><?= htmlspecialchars($o['customer_name']) ?></span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-gray-600"><?= date('M d, Y', strtotime($o['created_at'])) ?></span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php
                                                $statusClass = '';
                                                switch ($o['status']) {
                                                    case 'Pending':
                                                        $statusClass = 'bg-yellow-50 text-yellow-800';
                                                        break;
                                                    case 'Confirmed':
                                                        $statusClass = 'bg-blue-50 text-blue-800';
                                                        break;
                                                    case 'Completed':
                                                        $statusClass = 'bg-green-50 text-green-800';
                                                        break;
                                                    case 'Cancelled':
                                                        $statusClass = 'bg-red-50 text-red-800';
                                                        break;
                                                }
                                                ?>
                                                <span class="px-2 py-1 text-xs rounded-full <?= $statusClass ?>">
                                                    <?= htmlspecialchars($o['status']) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="/flower-lab/admin/orders.php?id=<?= $o['id'] ?>" class="text-primary-dark hover:underline">
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Order List (1/3 width on large screens) -->
        <div>
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 bg-gray-50 border-b border-gray-100">
                    <h2 class="font-medium text-gray-800">Recent Orders</h2>
                </div>
                
                <div class="overflow-y-auto max-h-[600px]">
                    <ul class="divide-y divide-gray-100">
                        <?php foreach (array_slice($allOrders, 0, 10) as $o): ?>
                            <li>
                                <a href="/flower-lab/admin/orders.php?id=<?= $o['id'] ?>" 
                                   class="block p-4 hover:bg-gray-50 <?= (isset($_GET['id']) && $_GET['id'] == $o['id']) ? 'bg-primary-light' : '' ?>">
                                    <div class="flex justify-between">
                                        <h3 class="font-medium text-gray-800"><?= htmlspecialchars($o['order_number']) ?></h3>
                                        <span class="text-sm text-gray-600"><?= date('M d', strtotime($o['created_at'])) ?></span>
                                    </div>
                                    <div class="mt-1 flex justify-between items-center">
                                        <span class="text-sm text-gray-600"><?= htmlspecialchars($o['customer_name']) ?></span>
                                        <?php
                                        $statusClass = '';
                                        switch ($o['status']) {
                                            case 'Pending':
                                                $statusClass = 'bg-yellow-50 text-yellow-800';
                                                break;
                                            case 'Confirmed':
                                                $statusClass = 'bg-blue-50 text-blue-800';
                                                break;
                                            case 'Completed':
                                                $statusClass = 'bg-green-50 text-green-800';
                                                break;
                                            case 'Cancelled':
                                                $statusClass = 'bg-red-50 text-red-800';
                                                break;
                                        }
                                        ?>
                                        <span class="px-2 py-0.5 text-xs rounded-full <?= $statusClass ?>">
                                            <?= htmlspecialchars($o['status']) ?>
                                        </span>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        
                        <?php if (empty($allOrders)): ?>
                            <li class="p-6 text-center text-gray-500">
                                No orders found
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>