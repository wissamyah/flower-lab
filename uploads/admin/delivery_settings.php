<?php
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$pageTitle = 'Delivery Settings';
include dirname(__DIR__) . '/includes/header.php';

$db = getDB();

// Get current delivery rate setting
$query = "SELECT * FROM settings WHERE setting_key = 'delivery_rate' LIMIT 1";
$result = $db->query($query);
$deliveryRate = "0.00";

if ($result && $result->num_rows > 0) {
    $setting = $result->fetch_assoc();
    $deliveryRate = $setting['setting_value'];
}

// Handle free delivery threshold
$query = "SELECT * FROM settings WHERE setting_key = 'free_delivery_threshold' LIMIT 1";
$result = $db->query($query);
$freeDeliveryThreshold = "50.00";

if ($result && $result->num_rows > 0) {
    $setting = $result->fetch_assoc();
    $freeDeliveryThreshold = $setting['setting_value'];
}

?>

<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Delivery Settings</h1>
        <a href="/flower-lab/admin/" class="px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded hover:bg-gray-200">
            <i data-lucide="arrow-left" class="inline-block h-4 w-4 mr-1"></i> Back to Dashboard
        </a>
    </div>
    
    <div id="status-message" class="mb-6 hidden">
        <!-- Status messages will appear here -->
    </div>
    
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-4 bg-gray-50 border-b border-gray-100">
            <h2 class="font-medium text-gray-800">Delivery Rate Configuration</h2>
        </div>
        
        <div class="p-6">
            <form id="delivery-settings-form" class="space-y-6">
                <div>
                    <label for="delivery_rate" class="block text-sm font-medium text-gray-700 mb-1">Standard Delivery Rate ($)</label>
                    <div class="relative mt-1 rounded-md shadow-sm max-w-xs">
                        <input type="number" step="0.01" min="0" id="delivery_rate" name="delivery_rate" 
                               value="<?= htmlspecialchars($deliveryRate) ?>"
                               class="focus:ring-primary focus:border-primary block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md"
                               placeholder="0.00">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">USD</span>
                        </div>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Set to 0 to make delivery free for all orders</p>
                </div>
                
                <div>
                    <label for="free_delivery_threshold" class="block text-sm font-medium text-gray-700 mb-1">Free Delivery Threshold ($)</label>
                    <div class="relative mt-1 rounded-md shadow-sm max-w-xs">
                        <input type="number" step="0.01" min="0" id="free_delivery_threshold" name="free_delivery_threshold" 
                               value="<?= htmlspecialchars($freeDeliveryThreshold) ?>"
                               class="focus:ring-primary focus:border-primary block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md"
                               placeholder="0.00">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">USD</span>
                        </div>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Orders above this amount qualify for free delivery (set to 0 to disable)</p>
                </div>
                
                <div>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark transition">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Lucide icons
        if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
        }
        
        // Handle form submission
        const form = document.getElementById('delivery-settings-form');
        const statusMessage = document.getElementById('status-message');
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const deliveryRate = document.getElementById('delivery_rate').value;
            const freeDeliveryThreshold = document.getElementById('free_delivery_threshold').value;
            
            // Validate
            if (deliveryRate === '') {
                showStatus('Please enter a delivery rate', 'error');
                return;
            }
            
            // Prepare data
            const data = {
                delivery_rate: deliveryRate,
                free_delivery_threshold: freeDeliveryThreshold
            };
            
            // Show loading state
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.innerHTML = '<span class="inline-block animate-spin mr-2">↻</span> Saving...';
            submitButton.disabled = true;
            
            // Send request
            fetch('/flower-lab/admin/ajax/save_delivery_settings.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                // Reset button
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
                
                if (data.success) {
                    showStatus('Delivery settings saved successfully!', 'success');
                } else {
                    showStatus(data.message || 'Error saving settings', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
                showStatus('An error occurred while saving settings', 'error');
            });
        });
        
        // Helper function to show status messages
        function showStatus(message, type = 'success') {
            statusMessage.innerHTML = `
                <div class="p-4 rounded ${type === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'}">
                    ${message}
                </div>
            `;
            statusMessage.classList.remove('hidden');
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                statusMessage.classList.add('hidden');
            }, 5000);
        }
    });
</script>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>