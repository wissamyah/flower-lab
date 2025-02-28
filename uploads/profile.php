<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect to login if not logged in
requireLogin();

$pageTitle = 'Your Profile';
include __DIR__ . '/includes/header.php';

$userId = getCurrentUserId();
$user = getCurrentUser();

// Get order history
$orders = [];
if ($userId) {
    $db = getDB();
    $query = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($order = $result->fetch_assoc()) {
        $orders[] = $order;
    }
}
?>

<div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-semibold text-gray-800 mb-6">Your Profile</h1>
    
    <div id="profile-alert" class="mb-6 p-4 rounded-lg bg-green-50 text-green-800" style="display: none;"></div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Info (1/3 width on large screens) -->
        <div>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-medium text-gray-800 mb-4">Your Information</h2>
                
                <form id="profile-form" class="profile-form">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" 
                               class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" 
                               class="w-full p-2 border border-gray-200 bg-gray-50 rounded" readonly>
                        <p class="text-xs text-gray-500 mt-1">Email cannot be changed</p>
                    </div>
                    
                    <div class="mb-4">
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>" 
                               class="w-full p-2 border border-gray-300 rounded" required>
                        <p class="text-xs text-gray-500 mt-1">Required for delivery</p>
                    </div>
                    
                    <div class="mb-6">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                        <textarea id="address" name="address" rows="3" 
                                  class="w-full p-2 border border-gray-300 rounded"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>
                    
                    <button type="submit" class="w-full px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark transition">
                        Save Changes
                    </button>
                </form>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
                <h3 class="text-lg font-medium text-gray-800 mb-4">Account Actions</h3>
                
                <button onclick="signOut()" class="w-full px-4 py-2 text-gray-700 bg-gray-100 rounded hover:bg-gray-200 transition">
                    Sign Out
                </button>
            </div>
        </div>
        
        <!-- Order History (2/3 width on large screens) -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 bg-gray-50 border-b border-gray-100">
                    <h2 class="font-medium text-gray-800">Order History</h2>
                </div>
                
                <?php if (empty($orders)): ?>
                    <div class="p-8 text-center">
                        <i data-lucide="shopping-bag" class="mx-auto h-12 w-12 text-gray-400 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">No orders yet</h3>
                        <p class="text-gray-600 mb-4">Start shopping to see your order history</p>
                        <a href="/flower-lab/" class="inline-block px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark transition">
                            Shop Now
                        </a>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="font-medium text-gray-800"><?= htmlspecialchars($order['order_number']) ?></span>
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
                                            <button onclick="viewOrderDetails('<?= $order['order_number'] ?>')" class="text-primary-dark hover:underline">
                                                View Details
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Order Details Modal (Hidden by default) -->
    <div id="order-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-lg shadow-lg max-w-4xl w-full max-h-screen overflow-y-auto">
            <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-800" id="order-modal-title">Order Details</h3>
                <button type="button" onclick="closeOrderModal()" class="text-gray-400 hover:text-gray-500">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>
            <div class="p-4" id="order-modal-content">
                <!-- Order content will be loaded here -->
                <div class="flex justify-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Profile form submission
    document.getElementById('profile-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const name = document.getElementById('name').value;
        const phone = document.getElementById('phone').value;
        const address = document.getElementById('address').value;
        
        // Validate
        if (!name.trim()) {
            showModernNotification({
                type: 'error',
                title: 'Error',
                message: 'Name is required'
            });
            return;
        }
        
        if (!phone.trim()) {
            showModernNotification({
                type: 'error',
                title: 'Error',
                message: 'Phone number is required'
            });
            return;
        }
        
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="inline-block animate-spin mr-2">↻</span> Saving...';
        submitBtn.disabled = true;
        
        // Send to server using fetch
        fetch('/flower-lab/ajax/update_profile.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                name: name,
                phone: phone,
                address: address
            })
        })
        .then(response => response.json())
        .then(data => {
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            
            if (data.success) {
                // Show success notification
                showModernNotification({
                    type: 'success',
                    title: 'Success',
                    message: data.message
                });
                
                // Refresh the page to show updated data
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                // Show error notification
                showModernNotification({
                    type: 'error',
                    title: 'Error',
                    message: data.message
                });
            }
        })
        .catch(error => {
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            
            console.error('Error updating profile:', error);
            
            // Show error notification
            showModernNotification({
                type: 'error',
                title: 'Error',
                message: 'Failed to update profile. Please try again.'
            });
        });
    });
    
    // View order details
    function viewOrderDetails(orderNumber) {
        const modal = document.getElementById('order-modal');
        const modalContent = document.getElementById('order-modal-content');
        
        // Show modal with loading spinner
        modal.style.display = 'flex';
        modalContent.innerHTML = `
            <div class="flex justify-center">
                <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
            </div>
        `;
        
        // Fetch order details
        fetch(`/flower-lab/ajax/order.php?action=details&orderNumber=${orderNumber}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const order = data.order;
                    let html = '';
                    
                    // Order info
                    html += `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <h4 class="font-medium text-gray-800 mb-2">Order Information</h4>
                                <p class="text-sm"><span class="font-medium">Order Number:</span> ${order.order_number}</p>
                                <p class="text-sm"><span class="font-medium">Date:</span> ${new Date(order.created_at).toLocaleDateString()}</p>
                                <p class="text-sm"><span class="font-medium">Status:</span> ${order.status}</p>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-800 mb-2">Delivery Information</h4>
                                <p class="text-sm"><span class="font-medium">Name:</span> ${order.name}</p>
                                <p class="text-sm"><span class="font-medium">Address:</span> ${order.address}</p>
                                <p class="text-sm"><span class="font-medium">Phone:</span> ${order.phone}</p>
                            </div>
                        </div>
                    `;
                    
                    // Order items
                    html += `
                        <h4 class="font-medium text-gray-800 mb-2">Order Items</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 mb-4">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    // Add each item
                    order.items.forEach(item => {
                        const itemPrice = item.price - (item.discount || 0);
                        const itemTotal = itemPrice * item.quantity;
                        
                        html += `
                            <tr>
                                <td class="px-4 py-2">
                                    <div class="font-medium">${item.title}</div>
                                    <div class="text-sm text-gray-500">${item.category}</div>
                                </td>
                                <td class="px-4 py-2">$${itemPrice.toFixed(2)}</td>
                                <td class="px-4 py-2">${item.quantity}</td>
                                <td class="px-4 py-2">$${itemTotal.toFixed(2)}</td>
                            </tr>
                        `;
                    });
                    
                    // Order total
                    html += `
                                </tbody>
                                <tfoot>
                                    <tr class="border-t">
                                        <td colspan="3" class="px-4 py-2 text-right font-medium">Total:</td>
                                        <td class="px-4 py-2 font-bold">$${order.total.toFixed(2)}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    `;
                    
                    // Gift message if any
                    if (order.gift_message) {
                        html += `
                            <div class="mt-4">
                                <h4 class="font-medium text-gray-800 mb-2">Gift Message</h4>
                                <div class="bg-gray-50 p-3 rounded text-sm">${order.gift_message}</div>
                            </div>
                        `;
                    }
                    
                    // Contact via WhatsApp button
                    html += `
                        <div class="mt-6 flex justify-end">
                            <button onclick="contactAboutOrder('${order.order_number}')" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition flex items-center">
                                <i data-lucide="message-circle" class="h-4 w-4 mr-2"></i>
                                Contact via WhatsApp
                            </button>
                        </div>
                    `;
                    
                    // Update modal content
                    modalContent.innerHTML = html;
                    
                    // Initialize Lucide icons
                    lucide.createIcons({
                        scope: modalContent
                    });
                } else {
                    modalContent.innerHTML = `
                        <div class="text-center text-red-500">
                            Error loading order details: ${data.message || 'Unknown error'}
                        </div>
                    `;
                }
            })
            .catch(error => {
                modalContent.innerHTML = `
                    <div class="text-center text-red-500">
                        Error loading order details. Please try again.
                    </div>
                `;
                console.error('Error fetching order details:', error);
            });
    }
    
    // Close order modal
    function closeOrderModal() {
        document.getElementById('order-modal').style.display = 'none';
    }
    
    // Contact about order via WhatsApp
    function contactAboutOrder(orderNumber) {
        // Generate WhatsApp link
        fetch(`/flower-lab/ajax/order.php?action=details&orderNumber=${orderNumber}`)
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
                    
                    // Use the globally defined WhatsApp number
                    const whatsappNumber = "<?= WHATSAPP_NUMBER ?>";
                    const whatsappLink = `https://wa.me/${whatsappNumber}?text=${encodedMessage}`;
                    
                    // Open WhatsApp link
                    window.open(whatsappLink, '_blank');
                } else {
                    showModernNotification({
                        type: 'error',
                        title: 'Error',
                        message: 'Error generating WhatsApp link: ' + (data.message || 'Unknown error')
                    });
                }
            })
            .catch(error => {
                console.error('Error generating WhatsApp link:', error);
                showModernNotification({
                    type: 'error',
                    title: 'Error',
                    message: 'Failed to generate WhatsApp link'
                });
            });
    }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>