<?php
require_once 'includes/db.php';
$pageTitle = 'Home';
include 'includes/header.php';

// Get featured items
$db = getDB();
$featured_query = "SELECT * FROM items WHERE is_featured = 1 AND stock > 0 ORDER BY id DESC LIMIT 4";
$featured_result = $db->query($featured_query);

// Get all items grouped by category
$categories_query = "SELECT DISTINCT category FROM items WHERE stock > 0 ORDER BY category";
$categories_result = $db->query($categories_query);
$categories = [];
while ($category = $categories_result->fetch_assoc()) {
    $categories[] = $category['category'];
}
?>

<!-- Hero Section -->
<div class="bg-primary-light py-12 mb-8">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center">
            <h1 class="text-3xl md:text-4xl font-bold text-primary-dark mb-4">Welcome to The Flower Lab</h1>
            <p class="text-lg text-gray-700 mb-6">Beautiful flowers for every occasion</p>
            <a href="#featured" class="inline-block px-6 py-3 bg-primary text-white rounded-md shadow-sm hover:bg-primary-dark transition">
                Shop Now
            </a>
        </div>
    </div>
</div>

<!-- Featured Items Section -->
<section id="featured" class="max-w-6xl mx-auto px-4 mb-12">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Featured Collections</h2>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php if ($featured_result->num_rows > 0): ?>
            <?php while ($item = $featured_result->fetch_assoc()): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden transition-transform hover:scale-105">
                    <?php if ($item['image_url']): ?>
                        <div class="h-48 bg-gray-200">
                            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="w-full h-full object-cover">
                        </div>
                    <?php else: ?>
                        <div class="h-48 bg-gray-200 flex items-center justify-center">
                            <i data-lucide="flower" class="h-12 w-12 text-gray-400"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="p-4">
                        <h3 class="text-lg font-medium text-gray-800 mb-1"><?= htmlspecialchars($item['title']) ?></h3>
                        <p class="text-sm text-gray-500 mb-2"><?= htmlspecialchars($item['category']) ?></p>
                        
                        <div class="flex justify-between items-center mb-3">
                            <?php if ($item['discount']): ?>
                                <div>
                                    <span class="text-sm text-gray-400 line-through">$<?= number_format($item['price'], 2) ?></span>
                                    <span class="text-md font-bold text-primary-dark ml-1">$<?= number_format($item['price'] - $item['discount'], 2) ?></span>
                                </div>
                            <?php else: ?>
                                <span class="text-md font-bold text-gray-800">$<?= number_format($item['price'], 2) ?></span>
                            <?php endif; ?>
                            
                            <!-- Stock Badge -->
                            <?php if ($item['stock'] > 0): ?>
                                <span class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded">In Stock</span>
                            <?php else: ?>
                                <span class="text-xs text-red-600 bg-red-50 px-2 py-1 rounded">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex space-x-2">
                            <button class="flex-1 px-3 py-1.5 text-sm text-white bg-primary rounded hover:bg-primary-dark transition" 
                                    onclick="addToBasket(<?= $item['id'] ?>)">
                                Add to Basket
                            </button>
                            <button class="p-1.5 text-gray-400 bg-gray-100 rounded hover:text-primary hover:bg-gray-200 transition wishlist-btn" 
                                    data-item-id="<?= $item['id'] ?>"
                                    onclick="globalToggleWishlist(<?= $item['id'] ?>, this)">
                                <i data-lucide="heart" class="h-5 w-5"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-8">
                <p class="text-gray-500">No featured items available at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Category Sections -->
<?php foreach ($categories as $category): ?>
    <?php
        $items_query = "SELECT * FROM items WHERE category = '{$db->real_escape_string($category)}' AND stock > 0 ORDER BY id DESC LIMIT 8";
        $items_result = $db->query($items_query);
    ?>
    
    <section class="max-w-6xl mx-auto px-4 mb-12">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6"><?= htmlspecialchars($category) ?></h2>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php while ($item = $items_result->fetch_assoc()): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden transition-transform hover:scale-105">
                    <?php if ($item['image_url']): ?>
                        <div class="h-48 bg-gray-200">
                            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="w-full h-full object-cover">
                        </div>
                    <?php else: ?>
                        <div class="h-48 bg-gray-200 flex items-center justify-center">
                            <i data-lucide="flower" class="h-12 w-12 text-gray-400"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="p-4">
                        <h3 class="text-lg font-medium text-gray-800 mb-1"><?= htmlspecialchars($item['title']) ?></h3>
                        
                        <div class="flex justify-between items-center mb-3">
                            <?php if ($item['discount']): ?>
                                <div>
                                    <span class="text-sm text-gray-400 line-through">$<?= number_format($item['price'], 2) ?></span>
                                    <span class="text-md font-bold text-primary-dark ml-1">$<?= number_format($item['price'] - $item['discount'], 2) ?></span>
                                </div>
                            <?php else: ?>
                                <span class="text-md font-bold text-gray-800">$<?= number_format($item['price'], 2) ?></span>
                            <?php endif; ?>
                            
                            <!-- Stock Badge -->
                            <?php if ($item['stock'] > 0): ?>
                                <span class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded">In Stock</span>
                            <?php else: ?>
                                <span class="text-xs text-red-600 bg-red-50 px-2 py-1 rounded">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex space-x-2">
                            <button class="flex-1 px-3 py-1.5 text-sm text-white bg-primary rounded hover:bg-primary-dark transition" 
                                    onclick="addToBasket(<?= $item['id'] ?>)">
                                Add to Basket
                            </button>
                            <button class="p-1.5 text-gray-400 bg-gray-100 rounded hover:text-primary hover:bg-gray-200 transition wishlist-btn" 
                                    data-item-id="<?= $item['id'] ?>"
                                    onclick="globalToggleWishlist(<?= $item['id'] ?>, this)">
                                <i data-lucide="heart" class="h-5 w-5"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>
<?php endforeach; ?>

<script>
    // Global wishlist toggle function that updates all instances of an item
    function globalToggleWishlist(itemId, clickedButton) {
        // Check if user is logged in
        const user = firebase.auth().currentUser;

        if (!user) {
            // Prompt to login
            showModernNotification({
                type: 'info',
                title: 'Login Required',
                message: "Please login to add items to your wishlist"
            });
            return;
        }

        // Get product info for notification
        const productElement = clickedButton.closest('.bg-white');
        let productTitle = null;
        let productImage = null;
        
        if (productElement) {
            productTitle = productElement.querySelector('h3')?.textContent;
            const imgElement = productElement.querySelector('img');
            if (imgElement && imgElement.src) {
                productImage = imgElement.src;
            }
        }

        // Determine if the item is already in the wishlist based on the clicked button
        const isInWishlist = clickedButton.classList.contains("text-primary");
        
        // Action to perform
        const action = isInWishlist ? "remove" : "add";

        // Send request to server
        fetch("/flower-lab/ajax/wishlist.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                action: action,
                itemId: itemId,
            }),
        })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                // Find all wishlist buttons for this item and update them
                const allButtons = document.querySelectorAll(`.wishlist-btn[data-item-id="${itemId}"]`);
                
                allButtons.forEach(button => {
                    if (action === "add") {
                        // Update button style to filled heart
                        button.classList.remove("text-gray-400");
                        button.classList.add("text-primary");
                        
                        // Find and fill the heart icon
                        const heartIcon = button.querySelector('[data-lucide="heart"]');
                        if (heartIcon) {
                            heartIcon.setAttribute("fill", "currentColor");
                        }
                    } else {
                        // Update button style to empty heart
                        button.classList.remove("text-primary");
                        button.classList.add("text-gray-400");
                        
                        // Find and unfill the heart icon
                        const heartIcon = button.querySelector('[data-lucide="heart"]');
                        if (heartIcon) {
                            heartIcon.removeAttribute("fill");
                        }
                    }
                });
                
                // Show notification
                showModernNotification({
                    type: 'success',
                    title: action === "add" ? 'Added to Wishlist' : 'Removed from Wishlist',
                    message: action === "add" ? 'Item has been added to your wishlist' : 'Item has been removed from your wishlist',
                    productImage: productImage,
                    productTitle: productTitle
                });
            } else {
                showModernNotification({
                    type: 'error',
                    title: 'Error',
                    message: data.message || `Error ${action === "add" ? "adding to" : "removing from"} wishlist`
                });
            }
            
            // Re-initialize Lucide icons to ensure proper rendering
            lucide.createIcons();
        })
        .catch((error) => {
            console.error(`Error ${action === "add" ? "adding to" : "removing from"} wishlist:`, error);
            showModernNotification({
                type: 'error',
                title: 'Error',
                message: `Failed to ${action === "add" ? "add to" : "remove from"} wishlist`
            });
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

    // Initialize wishlist buttons when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        // Check if user is logged in
        firebase.auth().onAuthStateChanged(function(user) {
            if (user) {
                // Get all items on the page
                const wishlistButtons = document.querySelectorAll('.wishlist-btn');
                const itemIds = Array.from(wishlistButtons)
                    .map(button => button.getAttribute('data-item-id'))
                    .filter((value, index, self) => value && self.indexOf(value) === index); // Get unique IDs
                
                if (itemIds.length === 0) return;
                
                // Batch check which items are in the wishlist
                const formData = new FormData();
                formData.append('action', 'batch_check');
                formData.append('itemIds', JSON.stringify(itemIds));
                
                fetch('/flower-lab/ajax/wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'batch_check',
                        itemIds: itemIds
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.inWishlist && data.inWishlist.length > 0) {
                        // Update all items in the wishlist
                        data.inWishlist.forEach(itemId => {
                            const buttons = document.querySelectorAll(`.wishlist-btn[data-item-id="${itemId}"]`);
                            
                            buttons.forEach(button => {
                                // Update button appearance
                                button.classList.remove('text-gray-400');
                                button.classList.add('text-primary');
                                
                                // Fill the heart icon
                                const heartIcon = button.querySelector('[data-lucide="heart"]');
                                if (heartIcon) {
                                    heartIcon.setAttribute('fill', 'currentColor');
                                }
                            });
                        });
                        
                        // Re-initialize Lucide icons to ensure proper rendering
                        lucide.createIcons();
                    }
                })
                .catch(error => {
                    console.error('Error checking wishlist status:', error);
                });
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>