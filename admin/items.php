<?php
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$pageTitle = 'Product Management';
include dirname(__DIR__) . '/includes/header.php';

$db = getDB();

// Handle form submission
$message = '';
$productId = null;
$product = null;

if (isset($_GET['id'])) {
    $productId = (int)$_GET['id'];
    $query = "SELECT * FROM items WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $category = $_POST['category'] ?? '';
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $discount = ($_POST['discount'] !== '') ? (float)$_POST['discount'] : null;
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $imageUrl = $_POST['image_url'] ?? '';
    
    if (isset($_POST['id']) && $_POST['id']) {
        // Update existing product
        $id = (int)$_POST['id'];
        $query = "UPDATE items SET 
                  title = ?, 
                  description = ?, 
                  category = ?, 
                  price = ?, 
                  stock = ?, 
                  discount = ?, 
                  is_featured = ?,
                  image_url = ?,
                  updated_at = NOW()
                  WHERE id = ?";
        
        $stmt = $db->prepare($query);
        $stmt->bind_param("sssdiissi", $title, $description, $category, $price, $stock, $discount, $isFeatured, $imageUrl, $id);
        
        if ($stmt->execute()) {
            $message = 'Product updated successfully!';
            
            // Reload the product data
            $productId = $id;
            $query = "SELECT * FROM items WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $product = $result->fetch_assoc();
            }
        } else {
            $message = 'Error updating product: ' . $db->error;
        }
    } else {
        // Create new product
        $query = "INSERT INTO items (title, description, category, price, stock, discount, is_featured, image_url, created_at, updated_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $db->prepare($query);
        $stmt->bind_param("sssdiiss", $title, $description, $category, $price, $stock, $discount, $isFeatured, $imageUrl);
        
        if ($stmt->execute()) {
            $message = 'Product created successfully!';
            $productId = $db->insert_id;
            
            // Load the new product data
            $query = "SELECT * FROM items WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $product = $result->fetch_assoc();
            }
        } else {
            $message = 'Error creating product: ' . $db->error;
        }
    }
}

// Get all products for the list
$productsQuery = "SELECT * FROM items ORDER BY id DESC";
$productsResult = $db->query($productsQuery);
$products = [];

while ($row = $productsResult->fetch_assoc()) {
    $products[] = $row;
}
?>

<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800"><?= $product ? 'Edit Product' : 'Add New Product' ?></h1>
        <div>
            <?php if ($product): ?>
                <a href="/flower-lab/admin/items.php" class="px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded hover:bg-gray-200 mr-2">
                    Add New
                </a>
            <?php endif; ?>
            <a href="/flower-lab/admin/" class="px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded hover:bg-gray-200">
                <i data-lucide="arrow-left" class="inline-block h-4 w-4 mr-1"></i> Back to Dashboard
            </a>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Product Form (2/3 width on large screens) -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 bg-gray-50 border-b border-gray-100">
                    <h2 class="font-medium text-gray-800"><?= $product ? 'Edit Product Details' : 'Product Details' ?></h2>
                </div>
                
                <form method="post" action="" class="p-6">
                    <?php if ($message): ?>
                        <div class="mb-4 p-3 rounded <?= strpos($message, 'Error') === false ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' ?>">
                            <?= $message ?>
                        </div>
                    <?php endif; ?>
                    
                    <input type="hidden" name="id" value="<?= $product ? $product['id'] : '' ?>">
                    
                    <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Product Title</label>
                        <input type="text" id="title" name="title" value="<?= $product ? htmlspecialchars($product['title']) : '' ?>" 
                               class="w-full p-2 border border-gray-300 rounded" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select id="category" name="category" class="w-full p-2 border border-gray-300 rounded" required>
                            <option value="">Select Category</option>
                            <option value="Roses" <?= ($product && $product['category'] === 'Roses') ? 'selected' : '' ?>>Roses</option>
                            <option value="Bouquets" <?= ($product && $product['category'] === 'Bouquets') ? 'selected' : '' ?>>Bouquets</option>
                            <option value="Arrangements" <?= ($product && $product['category'] === 'Arrangements') ? 'selected' : '' ?>>Arrangements</option>
                            <option value="Plants" <?= ($product && $product['category'] === 'Plants') ? 'selected' : '' ?>>Plants</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="description" name="description" rows="4" 
                                  class="w-full p-2 border border-gray-300 rounded"><?= $product ? htmlspecialchars($product['description']) : '' ?></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price ($)</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" 
                                   value="<?= $product ? $product['price'] : '' ?>" 
                                   class="w-full p-2 border border-gray-300 rounded" required>
                        </div>
                        
                        <div>
                            <label for="discount" class="block text-sm font-medium text-gray-700 mb-1">Discount ($)</label>
                            <input type="number" id="discount" name="discount" step="0.01" min="0" 
                                   value="<?= ($product && $product['discount']) ? $product['discount'] : '' ?>" 
                                   class="w-full p-2 border border-gray-300 rounded">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity</label>
                            <input type="number" id="stock" name="stock" min="0" 
                                   value="<?= $product ? $product['stock'] : '0' ?>" 
                                   class="w-full p-2 border border-gray-300 rounded" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="image_upload" class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
                            
                            <div class="flex items-stretch">
                                <div class="relative flex-grow mb-2">
                                    <input type="text" id="image_url" name="image_url" 
                                        class="w-full h-10 px-3 py-2 border border-gray-300 rounded-l focus:border-primary focus:ring-0" 
                                        placeholder="Image URL or upload an image">
                                    <div id="image_url_preview" class="absolute right-2 top-1/2 transform -translate-y-1/2 hidden">
                                        <i data-lucide="check" class="h-5 w-5 text-green-500"></i>
                                    </div>
                                </div>
                                <label for="file_upload" class="inline-flex items-center justify-center h-10 px-4 border border-gray-300 border-l-0 rounded-r bg-gray-50 text-sm text-gray-700 cursor-pointer hover:bg-gray-100 transition">
                                    <i data-lucide="upload" class="h-4 w-4 mr-1"></i>
                                    Browse
                                </label>
                            </div>
                            
                            <input type="file" id="file_upload" name="product_image" class="hidden" accept=".jpg,.jpeg,.png,.gif,.webp">
                            
                            <!-- Image preview -->
                            <div id="image_preview" class="mt-2 hidden">
                                <div class="relative w-40 h-40 bg-gray-100 rounded overflow-hidden border border-gray-200">
                                    <img id="preview_image" src="#" alt="Preview" class="w-full h-full object-cover">
                                    <button type="button" id="remove_image" class="absolute top-2 right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-700">
                                        <i data-lucide="x" class="h-4 w-4"></i>
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Image Preview</p>
                            </div>
                            
                            <div class="mt-1">
                                <div id="upload_progress" class="hidden">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div id="upload_progress_bar" class="bg-primary h-2.5 rounded-full" style="width: 0%"></div>
                                    </div>
                                    <p id="upload_status" class="text-xs text-gray-500 mt-1">Uploading... 0%</p>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Max file size: 2MB. Allowed formats: JPG, PNG, GIF, WEBP</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_featured" 
                                   <?= ($product && $product['is_featured']) ? 'checked' : '' ?> 
                                   class="h-4 w-4 text-primary border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Feature this product (will appear in Featured Collections)</span>
                        </label>
                    </div>
                    
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark transition">
                        <?= $product ? 'Update Product' : 'Create Product' ?>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Product List (1/3 width on large screens) -->
        <div>
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                    <h2 class="font-medium text-gray-800">Products</h2>
                    <a href="/flower-lab/admin/bulk.php" class="text-sm text-primary-dark hover:underline">
                        Bulk Edit
                    </a>
                </div>
                
                <div class="overflow-y-auto max-h-[600px]">
                    <ul class="divide-y divide-gray-100">
                    <?php foreach ($products as $p): ?>
                        <li>
                            <a href="/flower-lab/admin/items.php?id=<?= $p['id'] ?>" 
                            class="block p-4 hover:bg-gray-50 <?= ($productId == $p['id']) ? 'bg-primary-light' : '' ?>">
                                <div class="flex justify-between">
                                    <h3 class="font-medium text-gray-800"><?= htmlspecialchars($p['title']) ?></h3>
                                    <span class="text-sm">
                                        <?php if ($p['stock'] <= 0): ?>
                                            <span class="text-red-600">Out of Stock</span>
                                        <?php else: ?>
                                            <span class="text-green-600">In Stock (<?= $p['stock'] ?>)</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="mt-1 flex justify-between text-sm">
                                    <span class="text-gray-500"><?= htmlspecialchars($p['category']) ?></span>
                                    <span class="font-medium">
                                        <?php if ($p['discount']): ?>
                                            <span class="line-through text-gray-400 mr-1">$<?= number_format($p['price'], 2) ?></span>
                                            <span class="text-primary-dark">$<?= number_format($p['price'] - $p['discount'], 2) ?></span>
                                        <?php else: ?>
                                            <span>$<?= number_format($p['price'], 2) ?></span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </a>
                            <!-- Add delete button outside the link but within the list item -->
                            <div class="px-4 pb-2 text-right">
                                <button type="button" 
                                        class="text-xs text-red-600 hover:text-red-800" 
                                        onclick="deleteProduct(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['title'])) ?>')">
                                    <i data-lucide="trash-2" class="h-3 w-3 inline-block mr-1"></i>
                                    Delete
                                </button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                        
                        <?php if (empty($products)): ?>
                            <li class="p-6 text-center text-gray-500">
                                No products found
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // File upload handling
    const fileUpload = document.getElementById('file_upload');
    const imageUrl = document.getElementById('image_url');
    const imagePreview = document.getElementById('image_preview');
    const previewImage = document.getElementById('preview_image');
    const removeImage = document.getElementById('remove_image');
    const uploadProgress = document.getElementById('upload_progress');
    const uploadProgressBar = document.getElementById('upload_progress_bar');
    const uploadStatus = document.getElementById('upload_status');
    const imageUrlPreview = document.getElementById('image_url_preview');
    
    // Initialize image preview if URL exists
    if (imageUrl.value) {
        previewImage.src = imageUrl.value;
        imagePreview.classList.remove('hidden');
        imageUrlPreview.classList.remove('hidden');
    }
    
    // Handle file selection
    fileUpload.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        // Validate file type
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            alert('Invalid file type. Please select an image file (JPG, PNG, GIF, WEBP).');
            return;
        }
        
        // Validate file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('File size too large. Maximum allowed size is 2MB.');
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            imagePreview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
        
        // Upload file
        uploadFile(file);
    });
    
    // Remove image preview
    removeImage.addEventListener('click', function() {
        fileUpload.value = '';
        imageUrl.value = '';
        imagePreview.classList.add('hidden');
        imageUrlPreview.classList.add('hidden');
    });
    
    // Handle URL input change
    imageUrl.addEventListener('input', function() {
        if (this.value) {
            previewImage.src = this.value;
            imagePreview.classList.remove('hidden');
            imageUrlPreview.classList.remove('hidden');
        } else {
            imagePreview.classList.add('hidden');
            imageUrlPreview.classList.add('hidden');
        }
    });
    
    // Upload file to server
    function uploadFile(file) {
        const formData = new FormData();
        formData.append('product_image', file);
        
        // Show progress bar
        uploadProgress.classList.remove('hidden');
        uploadProgressBar.style.width = '0%';
        uploadStatus.textContent = 'Uploading... 0%';
        
        const xhr = new XMLHttpRequest();
        
        // Progress handler
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percentComplete = Math.round((e.loaded / e.total) * 100);
                uploadProgressBar.style.width = percentComplete + '%';
                uploadStatus.textContent = `Uploading... ${percentComplete}%`;
            }
        });
        
        // Load handler
        xhr.addEventListener('load', function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        imageUrl.value = response.file_path;
                        uploadStatus.textContent = 'Upload complete!';
                        imageUrlPreview.classList.remove('hidden');
                        
                        // Hide progress bar after a delay
                        setTimeout(function() {
                            uploadProgress.classList.add('hidden');
                        }, 2000);
                    } else {
                        uploadStatus.textContent = 'Error: ' + response.message;
                    }
                } catch (e) {
                    uploadStatus.textContent = 'Error parsing server response';
                }
            } else {
                uploadStatus.textContent = 'Upload failed with status: ' + xhr.status;
            }
        });
        
        // Error handler
        xhr.addEventListener('error', function() {
            uploadStatus.textContent = 'Upload failed due to network error';
        });
        
        // Open and send request
        xhr.open('POST', '/flower-lab/ajax/upload_product_image.php', true);
        xhr.send(formData);
    }
});
</script>
<script>
// Delete product functionality
function deleteProduct(productId, productTitle) {
    // Confirm deletion
    if (!confirm(`Are you sure you want to delete "${productTitle}"?`)) {
        return; // User cancelled
    }
    
    // Show loading state
    const loadingIndicator = document.createElement('div');
    loadingIndicator.className = 'fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50';
    loadingIndicator.innerHTML = `
        <div class="bg-white p-4 rounded-lg shadow-md">
            <p class="text-gray-800">Deleting product...</p>
        </div>
    `;
    document.body.appendChild(loadingIndicator);
    
    // Send delete request
    fetch('/flower-lab/admin/ajax/delete_product.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        // Remove loading indicator
        document.body.removeChild(loadingIndicator);
        
        if (data.success) {
            // Show success message
            alert('Product deleted successfully');
            
            // Reload the page to update the product list
            window.location.reload();
        } else {
            // Show error message
            alert('Error deleting product: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        // Remove loading indicator
        document.body.removeChild(loadingIndicator);
        
        console.error('Error deleting product:', error);
        alert('Failed to delete product. Please try again.');
    });
}
</script>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>