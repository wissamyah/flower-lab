<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect to login if not logged in
requireLogin();

$pageTitle = 'Your Wishlist';
include __DIR__ . '/includes/header.php';

$userId = getCurrentUserId();
$items = [];

if ($userId) {
    $db = getDB();
    $query = "SELECT w.id as wishlist_id, i.* 
              FROM wishlist w 
              JOIN items i ON w.item_id = i.id 
              WHERE w.user_id = ?
              ORDER BY w.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($item = $result->fetch_assoc()) {
        $items[] = $item;
    }
}
?>

<div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-semibold text-gray-800 mb-6">Your Wishlist</h1>
    
    <?php if (empty($items)): ?>
        <div class="bg-white rounded-lg shadow-sm p-8 text-center">
            <i data-lucide="heart" class="mx-auto h-12 w-12 text-gray-400 mb-4"></i>
            <h2 class="text-lg font-medium text-gray-800 mb-2">Your wishlist is empty</h2>
            <p class="text-gray-600 mb-4">Save items you love to find them easily later!</p>
            <a href="/flower-lab/" class="inline-block px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark transition">
                Browse Collection
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($items as $item): ?>
                <?php 
                $currentPrice = $item['price'];
                if ($item['discount']) {
                    $currentPrice -= $item['discount'];
                }
                ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden group">
                    <!-- Product Image -->
                    <div class="relative h-48 bg-gray-100">
                        <?php if ($item['image_url']): ?>
                            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center">
                                <i data-lucide="flower" class="h-12 w-12 text-gray-400"></i>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Actions Overlay -->
                        <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                            <button class="p-2 bg-white rounded-full text-primary-dark mx-1 hover:bg-gray-100" 
                                    onclick="addItemToBasketFromWishlist(<?= $item['id'] ?>)">
                                <i data-lucide="shopping-bag" class="h-5 w-5"></i>
                            </button>
                            <button class="p-2 bg-white rounded-full text-red-500 mx-1 hover:bg-gray-100" 
                                    onclick="removeItemFromWishlist(<?= $item['id'] ?>)">
                                <i data-lucide="trash-2" class="h-5 w-5"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Product Info -->
                    <div class="p-4">
                        <h3 class="font-medium text-gray-800 mb-1"><?= htmlspecialchars($item['title']) ?></h3>
                        <p class="text-sm text-gray-500 mb-3"><?= htmlspecialchars($item['category']) ?></p>
                        
                        <div class="flex justify-between items-center">
                            <?php if ($item['discount']): ?>
                                <div>
                                    <span class="text-sm text-gray-400 line-through">$<?= number_format($item['price'], 2) ?></span>
                                    <span class="font-bold text-primary-dark">$<?= number_format($currentPrice, 2) ?></span>
                                </div>
                            <?php else: ?>
                                <span class="font-bold text-gray-800">$<?= number_format($currentPrice, 2) ?></span>
                            <?php endif; ?>
                            
                            <?php if ($item['stock'] > 0): ?>
                                <span class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded">In Stock</span>
                            <?php else: ?>
                                <span class="text-xs text-red-600 bg-red-50 px-2 py-1 rounded">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Move to Basket Button -->
                    <div class="px-4 pb-4">
                        <button class="w-full px-3 py-2 bg-primary text-white rounded hover:bg-primary-dark transition <?= $item['stock'] > 0 ? '' : 'opacity-50 cursor-not-allowed' ?>" 
                                onclick="addItemToBasketFromWishlist(<?= $item['id'] ?>)" 
                                <?= $item['stock'] > 0 ? '' : 'disabled' ?>>
                            Move to Basket
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
// Function to add item to basket from wishlist
function addItemToBasketFromWishlist(itemId) {
    // First add to basket
    fetch('/flower-lab/ajax/basket.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'add',
            itemId: itemId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Item added to basket');
            
            // Then remove from wishlist
            removeItemFromWishlist(itemId);
        } else {
            showNotification(data.message || 'Error adding item to basket', 'error');
        }
    })
    .catch(error => {
        console.error('Error adding to basket:', error);
        showNotification('Failed to add item to basket', 'error');
    });
}

// Function to remove item from wishlist
function removeItemFromWishlist(itemId) {
    fetch('/flower-lab/ajax/wishlist.php', {
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
            showNotification('Item removed from wishlist');
            
            // Remove the item's card from the UI
            const itemCards = document.querySelectorAll('.grid > div');
            itemCards.forEach(card => {
                // Find cards with the matching item ID in their buttons
                if (card.querySelector(`button[onclick*="${itemId}"]`)) {
                    card.remove();
                }
            });
            
            // If no items left, refresh the page to show empty state
            if (document.querySelectorAll('.grid > div').length === 0) {
                window.location.reload();
            }
        } else {
            showNotification(data.message || 'Error removing item from wishlist', 'error');
        }
    })
    .catch(error => {
        console.error('Error removing from wishlist:', error);
        showNotification('Failed to remove item from wishlist', 'error');
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
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>