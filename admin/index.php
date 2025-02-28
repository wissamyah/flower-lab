<?php
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$pageTitle = 'Admin Dashboard';
include dirname(__DIR__) . '/includes/header.php';

$db = getDB();

// Get stats
$statsQuery = "SELECT 
    (SELECT COUNT(*) FROM items) as total_products,
    (SELECT COUNT(*) FROM items WHERE stock = 0) as out_of_stock,
    (SELECT COUNT(*) FROM orders) as total_orders,
    (SELECT COUNT(*) FROM orders WHERE status = 'Pending') as pending_orders,
    (SELECT COUNT(*) FROM users) as total_users";
$statsResult = $db->query($statsQuery);
$stats = $statsResult->fetch_assoc();

// Get recent orders
$ordersQuery = "SELECT o.*, u.name as customer_name, u.email as customer_email 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                ORDER BY o.created_at DESC LIMIT 5";
$ordersResult = $db->query($ordersQuery);
$recentOrders = [];
while ($order = $ordersResult->fetch_assoc()) {
    $recentOrders[] = $order;
}
?>

<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-semibold text-gray-800">Admin Dashboard</h1>
        <a href="/flower-lab/" class="px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded hover:bg-gray-200">
            <i data-lucide="layout" class="inline-block h-4 w-4 mr-1"></i> View Store
        </a>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Products -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-50 text-blue-500 mr-4">
                    <i data-lucide="package" class="h-6 w-6"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Products</p>
                    <h3 class="text-xl font-bold text-gray-800"><?= $stats['total_products'] ?></h3>
                </div>
            </div>
        </div>
        
        <!-- Out of Stock -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-50 text-red-500 mr-4">
                    <i data-lucide="alert-circle" class="h-6 w-6"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Out of Stock</p>
                    <h3 class="text-xl font-bold text-gray-800"><?= $stats['out_of_stock'] ?></h3>
                </div>
            </div>
        </div>
        
        <!-- Total Orders -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-50 text-green-500 mr-4">
                    <i data-lucide="shopping-bag" class="h-6 w-6"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Orders</p>
                    <h3 class="text-xl font-bold text-gray-800"><?= $stats['total_orders'] ?></h3>
                </div>
            </div>
        </div>
        
        <!-- Pending Orders -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-50 text-yellow-500 mr-4">
                    <i data-lucide="clock" class="h-6 w-6"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Pending Orders</p>
                    <h3 class="text-xl font-bold text-gray-800"><?= $stats['pending_orders'] ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Links -->

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <a href="/flower-lab/admin/items.php" class="bg-white rounded-lg shadow-sm p-5 flex items-center hover:bg-gray-50 transition">
            <div class="p-3 rounded-full bg-primary-light text-primary-dark mr-3">
                <i data-lucide="edit" class="h-6 w-6"></i>
            </div>
            <div>
                <h3 class="font-medium text-gray-800">Manage Products</h3>
                <p class="text-sm text-gray-500">Add, edit individual products</p>
            </div>
        </a>
        
        <a href="/flower-lab/admin/bulk.php" class="bg-white rounded-lg shadow-sm p-5 flex items-center hover:bg-gray-50 transition">
            <div class="p-3 rounded-full bg-primary-light text-primary-dark mr-3">
                <i data-lucide="layers" class="h-6 w-6"></i>
            </div>
            <div>
                <h3 class="font-medium text-gray-800">Bulk Management</h3>
                <p class="text-sm text-gray-500">Add/update multiple products</p>
            </div>
        </a>
        
        <a href="/flower-lab/admin/orders.php" class="bg-white rounded-lg shadow-sm p-5 flex items-center hover:bg-gray-50 transition">
            <div class="p-3 rounded-full bg-primary-light text-primary-dark mr-3">
                <i data-lucide="shopping-bag" class="h-6 w-6"></i>
            </div>
            <div>
                <h3 class="font-medium text-gray-800">Order Management</h3>
                <p class="text-sm text-gray-500">View and update orders</p>
            </div>
        </a>
    </div>

    <!-- Add new Delivery Settings Card below the existing cards -->

        <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
            <div class="p-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                <h2 class="font-medium text-gray-800">Delivery Settings</h2>
                <a href="/flower-lab/admin/delivery_settings.php" class="text-primary-dark hover:underline">
                    Edit <i data-lucide="settings" class="h-6 w-6"></i>
                </a>
            </div>
            
            <div class="p-6">
                <?php
                // Get delivery settings
                $deliveryRate = "0.00";
                $freeDeliveryThreshold = "0.00";
                
                $query = "SELECT * FROM settings WHERE setting_key IN ('delivery_rate', 'free_delivery_threshold')";
                $result = $db->query($query);
                
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        if ($row['setting_key'] === 'delivery_rate') {
                            $deliveryRate = $row['setting_value'];
                        } elseif ($row['setting_key'] === 'free_delivery_threshold') {
                            $freeDeliveryThreshold = $row['setting_value'];
                        }
                    }
                }
                ?>
                
                <div class="flex items-center mb-4">
                    <div class="p-3 rounded-full bg-primary-light text-primary-dark mr-4">
                        <i data-lucide="truck" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Standard Delivery Rate</p>
                        <h3 class="text-xl font-bold text-gray-800">
                            <?php if (floatval($deliveryRate) > 0): ?>
                                $<?= $deliveryRate ?>
                            <?php else: ?>
                                Free
                            <?php endif; ?>
                        </h3>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-50 text-green-500 mr-4">
                        <i data-lucide="tag" class="h-6 w-6"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Free Delivery Threshold</p>
                        <h3 class="text-xl font-bold text-gray-800">
                            <?php if (floatval($freeDeliveryThreshold) > 0): ?>
                                Orders over $<?= $freeDeliveryThreshold ?>
                            <?php else: ?>
                                Not set
                            <?php endif; ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
            <div class="p-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                <h2 class="font-medium text-gray-800">Recent Orders</h2>
                <a href="/flower-lab/admin/orders.php" class="text-sm text-primary-dark hover:underline">View All</a>
            </div>
            
            <div class="overflow-x-auto">
                <?php if (empty($recentOrders)): ?>
                    <div class="p-8 text-center">
                        <p class="text-gray-500">No orders yet</p>
                    </div>
                <?php else: ?>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-medium text-gray-800"><?= htmlspecialchars($order['order_number']) ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-800"><?= htmlspecialchars($order['customer_name']) ?></div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($order['customer_email']) ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-600"><?= date('M d, Y', strtotime($order['created_at'])) ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
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
                                        <span class="px-2 py-1 text-xs rounded-full <?= $statusClass ?>">
                                            <?= htmlspecialchars($order['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="/flower-lab/admin/orders.php?id=<?= $order['id'] ?>" class="text-primary-dark hover:underline">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>