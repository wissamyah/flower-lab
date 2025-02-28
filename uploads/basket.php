<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$pageTitle = 'Your Basket';
include 'includes/header.php';

$userId = getCurrentUserId();
$items = [];
$totalPrice = 0;

// Get basket items for logged in users
if ($userId) {
    $db = getDB();
    $query = "SELECT b.id as basket_id, b.quantity, i.* 
              FROM basket b 
              JOIN items i ON b.item_id = i.id 
              WHERE b.user_id = ?";
    
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($item = $result->fetch_assoc()) {
        $items[] = $item;
        $itemPrice = $item['price'];
        if ($item['discount']) {
            $itemPrice -= $item['discount'];
        }
        $totalPrice += $itemPrice * $item['quantity'];
    }
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

// Calculate if this order qualifies for free delivery
$freeDelivery = ($freeDeliveryThreshold > 0 && $totalPrice >= $freeDeliveryThreshold) || $deliveryRate <= 0;

// Calculate the final total with delivery
$finalTotal = $totalPrice;
if (!$freeDelivery) {
    $finalTotal += $deliveryRate;
}
?>

<div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-semibold text-gray-800 mb-6">Your Basket</h1>
    
    <div id="basket-container">
        <?php if (empty($items) && (!isset($_SESSION['guest_basket']) || empty($_SESSION['guest_basket']))): ?>
            <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                <i data-lucide="shopping-bag" class="mx-auto h-12 w-12 text-gray-400 mb-4"></i>
                <h2 class="text-lg font-medium text-gray-800 mb-2">Your basket is empty</h2>
                <p class="text-gray-600 mb-4">Add some beautiful flowers to get started!</p>
                <a href="/flower-lab/" class="inline-block px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark transition">
                    Continue Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Basket Items (2/3 width on large screens) -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="p-4 bg-gray-50 border-b border-gray-100">
                            <h2 class="font-medium text-gray-800">Shopping Basket</h2>
                        </div>
                        
                        <ul class="divide-y divide-gray-100">
                            <?php foreach ($items as $item): ?>
                                <?php 
                                $currentPrice = $item['price'];
                                if ($item['discount']) {
                                    $currentPrice -= $item['discount'];
                                }
                                ?>
                                <li class="p-4 flex flex-col sm:flex-row items-start sm:items-center">
                                    <!-- Product Image -->
                                    <div class="w-full sm:w-20 h-20 bg-gray-100 rounded overflow-hidden mb-4 sm:mb-0 sm:mr-4 flex-shrink-0">
                                        <?php if ($item['image_url']): ?>
                                            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center">
                                                <i data-lucide="flower" class="h-8 w-8 text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Product Info -->
                                    <div class="flex-grow">
                                        <h3 class="font-medium text-gray-800"><?= htmlspecialchars($item['title']) ?></h3>
                                        <p class="text-sm text-gray-500"><?= htmlspecialchars($item['category']) ?></p>
                                    </div>
                                    
                                    <!-- Quantity Selector -->
                                    <div class="flex items-center mt-4 sm:mt-0 sm:ml-4">
                                        <button class="p-1 rounded border border-gray-300 text-gray-500 hover:bg-gray-100" 
                                                onclick="decrementQuantity(<?= $item['id'] ?>, <?= $item['quantity'] ?>)">
                                            <i data-lucide="minus" class="h-4 w-4"></i>
                                        </button>
                                        <span class="w-8 text-center"><?= $item['quantity'] ?></span>
                                        <button class="p-1 rounded border border-gray-300 text-gray-500 hover:bg-gray-100" 
                                                onclick="incrementQuantity(<?= $item['id'] ?>, <?= $item['quantity'] ?>)">
                                            <i data-lucide="plus" class="h-4 w-4"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Price -->
                                    <div class="mt-4 sm:mt-0 sm:ml-6 flex items-center">
                                        <?php if ($item['discount']): ?>
                                            <span class="text-sm text-gray-400 line-through mr-2">$<?= number_format($item['price'], 2) ?></span>
                                            <span class="font-bold text-primary-dark">$<?= number_format($currentPrice, 2) ?></span>
                                        <?php else: ?>
                                            <span class="font-bold text-gray-800">$<?= number_format($currentPrice, 2) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Remove Button -->
                                    <button class="mt-4 sm:mt-0 sm:ml-4 text-gray-400 hover:text-red-500" 
                                            onclick="removeItemFromBasket(<?= $item['id'] ?>)">
                                        <i data-lucide="trash-2" class="h-5 w-5"></i>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <?php if (empty($items)): ?>
                            <div class="p-8 text-center">
                                <p class="text-gray-500">Your basket is empty</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Order Summary (1/3 width on large screens) -->
                <div>
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h2 class="font-medium text-gray-800 mb-4">Order Summary</h2>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium">$<?= number_format($totalPrice, 2) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Delivery</span>
                                <?php if ($freeDelivery): ?>
                                    <span class="font-medium text-green-600">Free</span>
                                <?php else: ?>
                                    <span class="font-medium">$<?= number_format($deliveryRate, 2) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($freeDeliveryThreshold > 0 && !$freeDelivery): ?>
                            <div class="flex justify-between" style="font-size: 8pt;">
                                <span class="text-green-600">Free delivery on orders over $<?= number_format($freeDeliveryThreshold, 2) ?></span>
                                <span class="text-green-600">
                                    $<?= number_format($freeDeliveryThreshold - $totalPrice, 2) ?> away
                                </span>
                            </div>
                            <?php endif; ?>
                            <div class="border-t border-gray-100 pt-2 mt-2">
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-800">Total</span>
                                    <span class="font-bold text-primary-dark">$<?= number_format($finalTotal, 2) ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <a href="/flower-lab/checkout.php" class="block w-full px-4 py-2 text-center bg-primary text-white rounded hover:bg-primary-dark transition">
                            Proceed to Checkout
                        </a>
                        
                        <div class="mt-4 text-center">
                            <a href="/flower-lab/" class="text-sm text-gray-500 hover:text-primary-dark">
                                <i data-lucide="arrow-left" class="inline-block h-4 w-4 mr-1"></i>
                                Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Function to refresh just the basket contents via AJAX
function refreshBasket() {
    fetch('/flower-lab/ajax/get_basket.php')
        .then(response => response.text())
        .then(html => {
            document.getElementById('basket-container').innerHTML = html;
            // Reinitialize Lucide icons after refreshing the content
            lucide.createIcons();
        })
        .catch(error => {
            console.error('Error refreshing basket:', error);
        });
}

// Function to remove an item from the basket
function removeItemFromBasket(itemId) {
    fetch('/flower-lab/ajax/basket.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'remove',
            itemId: itemId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Item removed from basket');
            // Refresh the basket contents
            refreshBasket();
        } else {
            showNotification(data.message || 'Error removing item from basket', 'error');
        }
    })
    .catch(error => {
        console.error('Error removing from basket:', error);
        showNotification('Failed to remove item from basket', 'error');
    });
}

// Function to increment quantity
function incrementQuantity(itemId, currentQty) {
    updateBasketQuantity(itemId, currentQty + 1);
}

// Function to decrement quantity
function decrementQuantity(itemId, currentQty) {
    if (currentQty > 1) {
        updateBasketQuantity(itemId, currentQty - 1);
    } else {
        removeItemFromBasket(itemId);
    }
}

// Function to update basket quantity
function updateBasketQuantity(itemId, quantity) {
    if (quantity < 1) return;
    
    // Show loading state
    const loadingElement = document.createElement('div');
    loadingElement.id = 'loading-indicator';
    loadingElement.className = 'fixed inset-0 bg-black bg-opacity-25 flex items-center justify-center z-50';
    loadingElement.innerHTML = '<div class="bg-white p-4 rounded-lg shadow-lg"><p class="text-gray-800">Updating basket...</p></div>';
    document.body.appendChild(loadingElement);
    
    fetch('/flower-lab/ajax/basket.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'update',
            itemId: itemId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        // Remove loading indicator
        document.getElementById('loading-indicator').remove();
        
        if (data.success) {
            // Refresh only the basket contents
            refreshBasket();
        } else {
            showNotification(data.message || 'Error updating quantity', 'error');
        }
    })
    .catch(error => {
        // Remove loading indicator
        if (document.getElementById('loading-indicator')) {
            document.getElementById('loading-indicator').remove();
        }
        
        console.error('Error updating quantity:', error);
        showNotification('Failed to update basket', 'error');
    });
}

// Helper function to show notifications
function showNotification(message, type = 'success') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed bottom-4 right-4 px-4 py-2 rounded-md shadow-md z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = message;

    // Add to document
    document.body.appendChild(notification);

    // Remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Initialize basket when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Re-initialize Lucide icons
    lucide.createIcons();
});
</script>

<?php include 'includes/footer.php'; ?>