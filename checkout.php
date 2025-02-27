<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Redirect to login if not logged in
requireLogin();

$pageTitle = 'Checkout';
include 'includes/header.php';

$userId = getCurrentUserId();
$items = [];
$totalPrice = 0;

// Get basket items
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

// Get user info
$user = getCurrentUser();
?>

<div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-semibold text-gray-800 mb-6">Checkout</h1>
    
    <?php if (empty($items)): ?>
        <div class="bg-white rounded-lg shadow-sm p-8 text-center">
            <i data-lucide="shopping-bag" class="mx-auto h-12 w-12 text-gray-400 mb-4"></i>
            <h2 class="text-lg font-medium text-gray-800 mb-2">Your basket is empty</h2>
            <p class="text-gray-600 mb-4">Add some beautiful flowers before checkout!</p>
            <a href="/flower-lab/" class="inline-block px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark transition">
                Continue Shopping
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Order Form (2/3 width on large screens) -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-4 bg-gray-50 border-b border-gray-100">
                        <h2 class="font-medium text-gray-800">Delivery Information</h2>
                    </div>
                    
                    <form id="checkout-form" class="p-6">
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" 
                                   class="w-full p-2 border border-gray-300 rounded" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>" 
                                   class="w-full p-2 border border-gray-300 rounded" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                            <textarea id="address" name="address" rows="3" 
                                      class="w-full p-2 border border-gray-300 rounded" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label for="gift_message" class="block text-sm font-medium text-gray-700 mb-1">Gift Message (optional)</label>
                            <textarea id="gift_message" name="gift_message" rows="3" 
                                      class="w-full p-2 border border-gray-300 rounded" 
                                      placeholder="Add a personalized message to be included with your flowers"></textarea>
                        </div>
                        
                        <div id="message-preview" class="mb-6 p-4 border border-dashed border-gray-300 rounded bg-gray-50 hidden">
                            <h3 class="text-sm font-medium text-gray-700 mb-1">Message Preview</h3>
                            <p id="preview-text" class="text-sm italic text-gray-600"></p>
                        </div>
                        
                        <button type="submit" class="w-full px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark transition">
                            Place Order
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Order Summary (1/3 width on large screens) -->
            <div>
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <h2 class="font-medium text-gray-800 mb-4">Order Summary</h2>
                    
                    <ul class="divide-y divide-gray-100 mb-4">
                        <?php foreach ($items as $item): ?>
                            <?php 
                            $currentPrice = $item['price'];
                            if ($item['discount']) {
                                $currentPrice -= $item['discount'];
                            }
                            ?>
                            <li class="py-2 flex justify-between">
                                <div>
                                    <span class="text-gray-800"><?= htmlspecialchars($item['title']) ?></span>
                                    <span class="text-gray-500 ml-1">x<?= $item['quantity'] ?></span>
                                </div>
                                <span class="font-medium">$<?= number_format($currentPrice * $item['quantity'], 2) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium">$<?= number_format($totalPrice, 2) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Delivery</span>
                            <span class="font-medium">Free</span>
                        </div>
                        <div class="border-t border-gray-100 pt-2 mt-2">
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-800">Total</span>
                                <span class="font-bold text-primary-dark">$<?= number_format($totalPrice, 2) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-sm text-gray-500">
                        <p class="mb-2"><i data-lucide="info" class="inline-block h-4 w-4 mr-1"></i> Payment on delivery</p>
                        <p><i data-lucide="truck" class="inline-block h-4 w-4 mr-1"></i> Free delivery in local area</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>

    // Only define WHATSAPP_NUMBER if not already defined
    if (typeof WHATSAPP_NUMBER === 'undefined') {
        const WHATSAPP_NUMBER = "<?= WHATSAPP_NUMBER ?>";
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Show message preview when typing gift message
        const messageField = document.getElementById('gift_message');
        const previewContainer = document.getElementById('message-preview');
        const previewText = document.getElementById('preview-text');
        
        if (messageField) {
            messageField.addEventListener('input', function() {
                if (this.value.trim()) {
                    previewText.textContent = this.value;
                    previewContainer.classList.remove('hidden');
                } else {
                    previewContainer.classList.add('hidden');
                }
            });
        }
        
        // Handle form submission
        const checkoutForm = document.getElementById('checkout-form');
        
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get form data
                const formData = {
                    name: document.getElementById('name').value,
                    phone: document.getElementById('phone').value,
                    address: document.getElementById('address').value,
                    gift_message: document.getElementById('gift_message').value
                };
                
                // Create order
                createOrder(formData)
                    .then(data => {
                        if (data.success) {
                            // Generate WhatsApp link and redirect
                            return generateWhatsAppLink(data.order_number)
                                .then(whatsappLink => {
                                    // Store order info in localStorage for confirmation page
                                    localStorage.setItem('lastOrder', JSON.stringify({
                                        order_number: data.order_number,
                                        whatsappLink: whatsappLink
                                    }));
                                    
                                    // Redirect to confirmation page
                                    window.location.href = '/flower-lab/confirmation.php';
                                });
                        } else {
                            alert(data.message || 'Error creating order. Please try again.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                    });
            });
        }

            // Function to generate WhatsApp link
            function generateWhatsAppLink(orderNumber) {
        return fetch(`/flower-lab/ajax/order.php?action=details&orderNumber=${orderNumber}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const order = data.order;
                    
                    // Build message
                    let message = `*New Order #${order.order_number}*\n\n`;
                    message += `*Items:*\n`;
                    
                    order.items.forEach((item) => {
                        let price = item.price;
                        if (item.discount) {
                            price -= item.discount;
                        }
                        
                        message += `${item.quantity}x ${item.title} - $${(price * item.quantity).toFixed(2)}\n`;
                    });
                    
                    message += `\n*Total: $${order.total.toFixed(2)}*\n\n`;
                    message += `*Delivery Address:*\n${order.address}\n\n`;
                    message += `*Contact:*\n${order.phone}\n\n`;
                    
                    if (order.gift_message) {
                        message += `*Gift Message:*\n${order.gift_message}\n\n`;
                    }
                    
                    // Encode for URL
                    const encodedMessage = encodeURIComponent(message);
                    
                    // Generate WhatsApp link using the global constant
                    const whatsappLink = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodedMessage}`;
                    
                    return whatsappLink;
                } else {
                    throw new Error(data.message || "Error generating WhatsApp link");
                }
            });
    }
    });
</script>

<?php include 'includes/footer.php'; ?>