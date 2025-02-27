<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Redirect to login if not logged in
requireLogin();

$pageTitle = 'Order Confirmation';
include 'includes/header.php';
?>

<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-sm p-8 text-center">
        <div class="mb-4 inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full">
            <i data-lucide="check" class="h-8 w-8 text-green-500"></i>
        </div>
        
        <h1 class="text-2xl font-semibold text-gray-800 mb-2">Thank You for Your Order!</h1>
        <p class="text-gray-600 mb-6" id="order-number">Your order has been received</p>
        
        <div id="whatsapp-section" class="mb-6">
            <p class="text-gray-600 mb-4">Please finalize your order by sending the details to our WhatsApp:</p>
            <a id="whatsapp-link" href="#" class="inline-flex items-center px-6 py-3 bg-green-500 text-white rounded-md hover:bg-green-600 transition" target="_blank">
                <i data-lucide="message-circle" class="h-5 w-5 mr-2"></i>
                Send to WhatsApp
            </a>
        </div>
        
        <div class="text-sm text-gray-500 mb-6">
            <p class="mb-2"><i data-lucide="info" class="inline-block h-4 w-4 mr-1"></i> Payment on delivery</p>
            <p><i data-lucide="truck" class="inline-block h-4 w-4 mr-1"></i> We'll contact you shortly to confirm delivery</p>
        </div>
        
        <div>
            <a href="/flower-lab/" class="inline-block px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark transition">
                Continue Shopping
            </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get order info from localStorage
        const orderInfo = JSON.parse(localStorage.getItem('lastOrder') || '{}');
        
        // Update order number
        if (orderInfo.order_number) {
            document.getElementById('order-number').textContent = `Order #${orderInfo.order_number} has been received`;
        }
        
        // Set WhatsApp link
        if (orderInfo.whatsappLink) {
            document.getElementById('whatsapp-link').href = orderInfo.whatsappLink;
        } else {
            // Hide WhatsApp section if no link
            document.getElementById('whatsapp-section').style.display = 'none';
        }
    });
</script>

<?php include 'includes/footer.php'; ?>