<?php
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

$pageTitle = 'Bulk Product Management';
include dirname(__DIR__) . '/includes/header.php';
?>

<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Bulk Product Management</h1>
        <a href="/flower-lab/admin/" class="px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded hover:bg-gray-200">
            <i data-lucide="arrow-left" class="inline-block h-4 w-4 mr-1"></i> Back to Dashboard
        </a>
    </div>
    
    <!-- Tab Navigation -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="flex -mb-px" aria-label="Tabs">
            <button id="grid-tab" class="px-4 py-2 font-medium text-pink-600 border-b-2 border-pink-600" 
                    onclick="showTab('grid')">
                Grid Editor
            </button>
            <button id="csv-tab" class="px-4 py-2 font-medium text-gray-500" 
                    onclick="showTab('csv')">
                CSV Upload
            </button>
        </nav>
    </div>
    
    <!-- Grid Editor -->
    <div id="grid-panel">
        <div class="bg-gray-50 p-4 rounded-lg">
            <div class="overflow-x-auto">
                <table id="product-grid" class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Featured</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="product-grid-body">
                        <!-- Products will be loaded dynamically -->
                        <tr>
                            <td colspan="7" class="px-3 py-8 text-center text-sm text-gray-500">
                                Loading products...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                <button type="button" id="add-row" class="flex items-center px-3 py-1.5 text-sm text-gray-600 bg-gray-100 rounded hover:bg-gray-200">
                    <i data-lucide="plus" class="h-4 w-4 mr-1"></i>
                    Add Row
                </button>
            </div>
        </div>
        
        <div class="flex justify-between mt-6">
            <div>
                <button type="button" id="save-grid" class="px-4 py-2 text-sm text-white bg-pink-500 rounded hover:bg-pink-600 shadow-sm">
                    <i data-lucide="save" class="inline-block h-4 w-4 mr-1"></i>
                    Save All
                </button>
                <button type="button" id="preview-grid" class="ml-2 px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded hover:bg-gray-200">
                    <i data-lucide="eye" class="inline-block h-4 w-4 mr-1"></i>
                    Preview
                </button>
            </div>
            <button type="button" id="export-csv" class="px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded hover:bg-gray-200">
                <i data-lucide="download" class="inline-block h-4 w-4 mr-1"></i>
                Export as CSV
            </button>
        </div>
    </div>
    
    <!-- CSV Upload -->
    <div id="csv-panel" style="display: none;">
        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
            <i data-lucide="upload" class="mx-auto h-12 w-12 text-gray-400"></i>
            <p class="mt-2 text-sm font-medium text-gray-600">
                Drag and drop your CSV file here, or 
                <label class="ml-1 text-pink-500 hover:text-pink-600 cursor-pointer">
                    browse
                    <input type="file" id="csv-file" class="hidden" accept=".csv">
                </label>
            </p>
            <p class="mt-1 text-xs text-gray-500">
                CSV should include columns: title, description, price, stock, discount, category, is_featured
            </p>
            <button type="button" id="download-template" class="mt-4 px-4 py-2 text-sm text-white bg-pink-500 rounded hover:bg-pink-600 shadow-sm">
                <i data-lucide="file-text" class="inline-block h-4 w-4 mr-1"></i>
                Download Template
            </button>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg mt-6">
            <h3 class="font-medium text-gray-700 mb-2">CSV Preview</h3>
            <p class="text-sm text-gray-500 mb-4">Upload a CSV file to preview before importing</p>
            
            <div class="overflow-x-auto bg-white border border-gray-200 rounded-lg">
                <table id="csv-preview" class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Featured</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="px-3 py-8 text-center text-sm text-gray-500" colspan="6">
                                No CSV uploaded yet
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="flex justify-end mt-6">
            <button type="button" id="import-csv" class="px-4 py-2 text-sm text-white bg-pink-500 rounded hover:bg-pink-600 shadow-sm" disabled>
                <i data-lucide="save" class="inline-block h-4 w-4 mr-1"></i>
                Import Products
            </button>
        </div>
    </div>
    
    <!-- Preview Modal (Hidden by default) -->
    <div id="preview-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-lg shadow-lg max-w-4xl w-full max-h-screen overflow-y-auto">
            <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-800">Preview Changes</h3>
                <button type="button" id="close-preview" class="text-gray-400 hover:text-gray-500">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>
            <div class="p-4" id="preview-content">
                <!-- Preview content will be inserted here -->
            </div>
            <div class="p-4 border-t border-gray-200 flex justify-end">
                <button type="button" id="confirm-changes" class="px-4 py-2 text-sm text-white bg-pink-500 rounded hover:bg-pink-600 shadow-sm">
                    Confirm Changes
                </button>
                <button type="button" id="cancel-preview" class="ml-2 px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded hover:bg-gray-200">
                    Cancel
                </button>
            </div>
        </div>
    </div>
    
    <!-- Template row for creating new product rows (will be cloned by JavaScript) -->
    <template id="row-template">
        <tr class="hover:bg-gray-50">
            <td class="px-3 py-2">
                <input type="text" name="title[]" placeholder="Product name" class="w-full p-1 border border-gray-200 rounded text-sm" required>
                <input type="hidden" name="id[]" value="">
            </td>
            <td class="px-3 py-2">
                <select name="category[]" class="w-full p-1 border border-gray-200 rounded text-sm" required>
                    <option value="">Select Category</option>
                    <option value="Roses">Roses</option>
                    <option value="Bouquets">Bouquets</option>
                    <option value="Arrangements">Arrangements</option>
                    <option value="Plants">Plants</option>
                </select>
            </td>
            <td class="px-3 py-2">
                <input type="number" name="price[]" placeholder="0.00" min="0" step="0.01" class="w-20 p-1 border border-gray-200 rounded text-sm" required>
            </td>
            <td class="px-3 py-2">
                <input type="number" name="stock[]" placeholder="0" min="0" class="w-16 p-1 border border-gray-200 rounded text-sm" required>
            </td>
            <td class="px-3 py-2">
                <input type="number" name="discount[]" placeholder="0.00" min="0" step="0.01" class="w-20 p-1 border border-gray-200 rounded text-sm">
            </td>
            <td class="px-3 py-2">
                <input type="checkbox" name="is_featured[]" class="h-4 w-4 text-pink-600 rounded">
            </td>
            <td class="px-3 py-2">
                <button type="button" class="text-gray-400 hover:text-red-500 delete-row-btn">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </td>
        </tr>
    </template>
</div>

<script>
    // Tab switching
    function showTab(tabId) {
        // Hide all panels
        document.getElementById('grid-panel').style.display = 'none';
        document.getElementById('csv-panel').style.display = 'none';
        
        // Show the selected panel
        document.getElementById(tabId + '-panel').style.display = 'block';
        
        // Update tab styles
        document.getElementById('grid-tab').className = `px-4 py-2 font-medium ${tabId === 'grid' ? 'text-pink-600 border-b-2 border-pink-600' : 'text-gray-500'}`;
        document.getElementById('csv-tab').className = `px-4 py-2 font-medium ${tabId === 'csv' ? 'text-pink-600 border-b-2 border-pink-600' : 'text-gray-500'}`;
    }
    
    // Add row to grid
    document.getElementById('add-row').addEventListener('click', function() {
        addNewRow();
    });
    
    // Function to add a new row
    function addNewRow() {
        const template = document.getElementById('row-template');
        const tbody = document.getElementById('product-grid-body');
        
        // Check if template exists
        if (!template) {
            console.error('Row template not found');
            return;
        }
        
        // Clone the template row - using content property for templates
        const newRow = template.content.cloneNode(true).querySelector('tr');
        
        // Clear any placeholder rows
        if (tbody.querySelector('td[colspan]')) {
            tbody.innerHTML = '';
        }
        
        // Add the new row
        tbody.appendChild(newRow);
        
        // Add event listener to the delete button
        const deleteBtn = newRow.querySelector('.delete-row-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                removeRow(this);
            });
        }
        
        // Initialize the Lucide icons
        lucide.createIcons({
            scope: newRow
        });
    }
    
    // Remove row from grid
    function removeRow(button) {
        const row = button.closest('tr');
        row.remove();
        
        // If no rows left, add placeholder
        const tbody = document.getElementById('product-grid-body');
        if (tbody.children.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-3 py-8 text-center text-sm text-gray-500">
                        Add new rows to manage products
                    </td>
                </tr>
            `;
        }
    }
    
    // Load existing products for editing
    function loadExistingProducts() {
        const tbody = document.getElementById('product-grid-body');
        
        // Show loading message
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-3 py-8 text-center text-sm text-gray-500">
                    Loading products...
                </td>
            </tr>
        `;
        
        fetch('/flower-lab/admin/ajax/products.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const products = data.products;
                    
                    // Clear the loading message
                    tbody.innerHTML = '';
                    
                    if (products.length === 0) {
                        // If no products, add placeholder
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="7" class="px-3 py-8 text-center text-sm text-gray-500">
                                    No products yet. Add new rows to create products.
                                </td>
                            </tr>
                        `;
                        return;
                    }
                    
                    // Add each product as a row
                    products.forEach(product => {
                        addProductRow(product);
                    });
                } else {
                    console.error('Error loading products:', data.message);
                    alert('Error loading products: ' + (data.message || 'Unknown error'));
                    
                    // Show error message
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="7" class="px-3 py-8 text-center text-sm text-red-500">
                                Failed to load products. ${data.message || 'Please try again.'}
                            </td>
                        </tr>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading products:', error);
                
                // Show error message
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-3 py-8 text-center text-sm text-red-500">
                            Failed to load products. Please try again.
                        </td>
                    </tr>
                `;
            });
    }
    
    // Add a product row with data
    function addProductRow(product) {
        const template = document.getElementById('row-template');
        const tbody = document.getElementById('product-grid-body');
        
        // Check if template exists
        if (!template) {
            console.error('Row template not found');
            return;
        }
        
        // Clone the template row - using content property for templates
        const newRow = template.content.cloneNode(true).querySelector('tr');
        
        // Set field values
        newRow.querySelector('input[name="id[]"]').value = product.id;
        newRow.querySelector('input[name="title[]"]').value = product.title || '';
        
        const categorySelect = newRow.querySelector('select[name="category[]"]');
        if (categorySelect) {
            const categoryOption = categorySelect.querySelector(`option[value="${product.category}"]`);
            if (categoryOption) {
                categoryOption.selected = true;
            }
        }
        
        newRow.querySelector('input[name="price[]"]').value = product.price || '';
        newRow.querySelector('input[name="stock[]"]').value = product.stock || '0';
        newRow.querySelector('input[name="discount[]"]').value = product.discount || '';
        
        const featuredCheckbox = newRow.querySelector('input[name="is_featured[]"]');
        if (featuredCheckbox) {
            featuredCheckbox.checked = product.is_featured === '1' || product.is_featured === 1;
        }
        
        // Add event listener to the delete button
        const deleteBtn = newRow.querySelector('.delete-row-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                removeRow(this);
            });
        }
        
        // Add the row to the table
        tbody.appendChild(newRow);
        
        // Initialize Lucide icons for this row
        lucide.createIcons({
            scope: newRow
        });
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Load existing products
        loadExistingProducts();
        
        // Handle grid save
        document.getElementById('save-grid').addEventListener('click', function() {
            saveGridProducts(false);
        });
        
        // Handle grid preview
        document.getElementById('preview-grid').addEventListener('click', function() {
            previewGridChanges();
        });
        
        // Handle CSV file upload
        document.getElementById('csv-file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                parseCSV(file);
            }
        });
        
        // Handle template download
        document.getElementById('download-template').addEventListener('click', function() {
            downloadCSVTemplate();
        });
        
        // Handle CSV import
        document.getElementById('import-csv').addEventListener('click', function() {
            importCSV();
        });
        
        // Initialize modal events
        document.getElementById('close-preview').addEventListener('click', function() {
            document.getElementById('preview-modal').style.display = 'none';
        });
        
        document.getElementById('cancel-preview').addEventListener('click', function() {
            document.getElementById('preview-modal').style.display = 'none';
        });
        
        document.getElementById('confirm-changes').addEventListener('click', function() {
            document.getElementById('preview-modal').style.display = 'none';
            saveGridProducts(true);
        });
    });
    
    // Preview grid changes
    function previewGridChanges() {
        // Collect data from the grid
        const products = [];
        const rows = document.querySelectorAll('#product-grid-body tr:not([id="row-template"])');
        
        rows.forEach(row => {
            // Skip placeholder row
            if (row.querySelector('td[colspan]')) {
                return;
            }
            
            const id = row.querySelector('input[name="id[]"]')?.value || '';
            const title = row.querySelector('input[name="title[]"]')?.value || '';
            const category = row.querySelector('select[name="category[]"]')?.value || '';
            const price = row.querySelector('input[name="price[]"]')?.value || '';
            const stock = row.querySelector('input[name="stock[]"]')?.value || '';
            const discount = row.querySelector('input[name="discount[]"]')?.value || '';
            const isFeatured = row.querySelector('input[name="is_featured[]"]')?.checked || false;
            
            products.push({
                id,
                title,
                category,
                price,
                stock,
                discount,
                is_featured: isFeatured
            });
        });
        
        // Generate preview HTML
        let previewHTML = '<div class="overflow-x-auto">';
        previewHTML += '<table class="min-w-full divide-y divide-gray-200">';
        previewHTML += '<thead><tr class="bg-gray-50">';
        previewHTML += '<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>';
        previewHTML += '<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>';
        previewHTML += '<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>';
        previewHTML += '<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>';
        previewHTML += '<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>';
        previewHTML += '<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Featured</th>';
        previewHTML += '</tr></thead>';
        previewHTML += '<tbody class="bg-white divide-y divide-gray-200">';
        
        products.forEach(product => {
            previewHTML += '<tr>';
            previewHTML += `<td class="px-3 py-2">${product.title || 'N/A'}</td>`;
            previewHTML += `<td class="px-3 py-2">${product.category || 'N/A'}</td>`;
            previewHTML += `<td class="px-3 py-2">$${parseFloat(product.price || 0).toFixed(2)}</td>`;
            previewHTML += `<td class="px-3 py-2">${product.stock || 0}</td>`;
            previewHTML += `<td class="px-3 py-2">${product.discount ? '$' + parseFloat(product.discount).toFixed(2) : 'None'}</td>`;
            previewHTML += `<td class="px-3 py-2">${product.is_featured ? 'Yes' : 'No'}</td>`;
            previewHTML += '</tr>';
        });
        
        if (products.length === 0) {
            previewHTML += '<tr><td colspan="6" class="px-3 py-8 text-center text-sm text-gray-500">No products to preview</td></tr>';
        }
        
        previewHTML += '</tbody></table></div>';
        
        // Show the preview
        document.getElementById('preview-content').innerHTML = previewHTML;
        document.getElementById('preview-modal').style.display = 'flex';
    }
    
    // Save grid products
    function saveGridProducts(confirmed) {
        // If not confirmed and there are many products, show preview first
        const rows = document.querySelectorAll('#product-grid-body tr:not([id="row-template"])');
        if (!confirmed && rows.length > 5) {
            previewGridChanges();
            return;
        }
        
        // Collect data from the grid
        const products = [];
        
        rows.forEach(row => {
            // Skip placeholder row
            if (row.querySelector('td[colspan]')) {
                return;
            }
            
            const idInput = row.querySelector('input[name="id[]"]');
            const titleInput = row.querySelector('input[name="title[]"]');
            const categorySelect = row.querySelector('select[name="category[]"]');
            const priceInput = row.querySelector('input[name="price[]"]');
            const stockInput = row.querySelector('input[name="stock[]"]');
            const discountInput = row.querySelector('input[name="discount[]"]');
            const featuredCheckbox = row.querySelector('input[name="is_featured[]"]');
            
            // Skip if required elements are missing
            if (!titleInput || !categorySelect || !priceInput) {
                return;
            }
            
            const id = idInput ? idInput.value : '';
            const title = titleInput.value;
            const category = categorySelect.value;
            const price = priceInput.value;
            const stock = stockInput ? stockInput.value : 0;
            const discount = discountInput && discountInput.value ? discountInput.value : null;
            const isFeatured = featuredCheckbox ? (featuredCheckbox.checked ? 1 : 0) : 0;
            
            // Basic validation
            if (!title || !category || !price) {
                row.classList.add('bg-red-50');
                setTimeout(() => {
                    row.classList.remove('bg-red-50');
                }, 3000);
                return;
            }
            
            products.push({
                id,
                title,
                category,
                price,
                stock,
                discount,
                is_featured: isFeatured
            });
        });
        
        // Show loading indicator
        const loadingIndicator = document.createElement('div');
        loadingIndicator.className = 'fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50';
        loadingIndicator.innerHTML = `
            <div class="bg-white p-4 rounded-lg shadow-md">
                <p class="text-gray-800">Saving products...</p>
            </div>
        `;
        document.body.appendChild(loadingIndicator);
        
        // Send to server
        fetch('/flower-lab/admin/ajax/save_products.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                products
            })
        })
        .then(response => response.json())
        .then(data => {
            // Remove loading indicator
            document.body.removeChild(loadingIndicator);
            
            if (data.success) {
                alert('Products saved successfully!');
                
                // Reload products
                loadExistingProducts();
            } else {
                alert('Error saving products: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            // Remove loading indicator
            document.body.removeChild(loadingIndicator);
            
            console.error('Error saving products:', error);
            alert('Failed to save products. Please try again.');
        });
    }
    
    // CSV functions
    function parseCSV(file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const csv = e.target.result;
            const lines = csv.split('\n');
            
            // Parse headers
            const headers = lines[0].split(',').map(header => header.trim());
            
            // Validate headers
            const requiredHeaders = ['title', 'category', 'price', 'stock'];
            const missingHeaders = requiredHeaders.filter(header => !headers.includes(header));
            
            if (missingHeaders.length > 0) {
                alert(`CSV is missing required headers: ${missingHeaders.join(', ')}`);
                return;
            }
            
            // Parse data
            const products = [];
            
            for (let i = 1; i < lines.length; i++) {
                if (!lines[i].trim()) continue;
                
                const values = lines[i].split(',').map(value => value.trim());
                const product = {};
                
                headers.forEach((header, index) => {
                    product[header] = values[index] || '';
                });
                
                products.push(product);
            }
            
            // Show preview
            showCSVPreview(products);
            
            // Enable import button
            document.getElementById('import-csv').disabled = false;
        };
        
        reader.readAsText(file);
    }
    
    function showCSVPreview(products) {
        const table = document.getElementById('csv-preview');
        const tbody = table.querySelector('tbody');
        
        // Clear existing rows
        tbody.innerHTML = '';
        
        if (products.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-3 py-8 text-center text-sm text-gray-500">No data found in CSV</td></tr>';
            return;
        }
        
        // Add product rows
        products.forEach(product => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50';
            
            row.innerHTML = `
                <td class="px-3 py-2">${product.title || 'N/A'}</td>
                <td class="px-3 py-2">${product.category || 'N/A'}</td>
                <td class="px-3 py-2">${product.price ? '$' + parseFloat(product.price).toFixed(2) : 'N/A'}</td>
                <td class="px-3 py-2">${product.stock || '0'}</td>
                <td class="px-3 py-2">${product.discount ? '$' + parseFloat(product.discount).toFixed(2) : 'None'}</td>
                <td class="px-3 py-2">${product.is_featured === '1' || product.is_featured === 'true' ? 'Yes' : 'No'}</td>
            `;
            
            tbody.appendChild(row);
        });
    }
    
    function downloadCSVTemplate() {
        const headers = 'title,category,description,price,stock,discount,is_featured\n';
        const examples = 'Red Rose Bouquet,Roses,Beautiful red roses arrangement,49.99,10,0,0\nPink Carnations,Bouquets,Lovely pink carnations,39.99,15,5.00,1\n';
        
        const csvContent = headers + examples;
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        
        const a = document.createElement('a');
        a.href = url;
        a.download = 'flower_products_template.csv';
        a.click();
        
        URL.revokeObjectURL(url);
    }
    
    function importCSV() {
        // Get CSV data from preview
        const rows = document.querySelectorAll('#csv-preview tbody tr');
        const products = [];
        
        rows.forEach(row => {
            // Skip placeholder row
            if (row.querySelector('td[colspan]')) {
                return;
            }
            
            const cells = row.querySelectorAll('td');
            
            const product = {
                title: cells[0].textContent,
                category: cells[1].textContent,
                price: parseFloat(cells[2].textContent.replace(/[^\d.-]/g, '')),
                stock: parseInt(cells[3].textContent),
                discount: cells[4].textContent !== 'None' ? parseFloat(cells[4].textContent.replace(/[^\d.-]/g, '')) : null,
                is_featured: cells[5].textContent === 'Yes' ? 1 : 0
            };
            
            products.push(product);
        });
        
        // Show loading indicator
        const loadingIndicator = document.createElement('div');
        loadingIndicator.className = 'fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50';
        loadingIndicator.innerHTML = `
            <div class="bg-white p-4 rounded-lg shadow-md">
                <p class="text-gray-800">Importing products...</p>
            </div>
        `;
        document.body.appendChild(loadingIndicator);
        
        // Send to server
        fetch('/flower-lab/admin/ajax/import_products.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                products
            })
        })
        .then(response => response.json())
        .then(data => {
            // Remove loading indicator
            document.body.removeChild(loadingIndicator);
            
            if (data.success) {
                alert('Products imported successfully!');
                
                // Switch to grid tab and reload products
                showTab('grid');
                loadExistingProducts();
            } else {
                alert('Error importing products: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            // Remove loading indicator
            document.body.removeChild(loadingIndicator);
            
            console.error('Error importing products:', error);
            alert('Failed to import products. Please try again.');
        });
    }
</script>
<script>
// Add image upload functionality for bulk editor
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Dropzone for bulk image uploads
    const dropzoneArea = document.createElement('div');
    dropzoneArea.id = 'bulk-image-dropzone';
    dropzoneArea.className = 'hidden border-2 border-dashed border-gray-300 rounded-lg p-6 text-center mb-4';
    dropzoneArea.innerHTML = `
        <i data-lucide="upload-cloud" class="mx-auto h-12 w-12 text-gray-400 mb-3"></i>
        <p class="text-sm font-medium text-gray-700 mb-1">Drag and drop product images here</p>
        <p class="text-xs text-gray-500">or click to browse</p>
        <div id="upload-previews" class="flex flex-wrap gap-2 mt-4"></div>
        <input type="file" id="bulk-file-upload" class="hidden" accept=".jpg,.jpeg,.png,.gif,.webp" multiple>
    `;
    
    // Add dropzone before the grid
    const gridPanel = document.getElementById('grid-panel');
    if (gridPanel) {
        gridPanel.insertBefore(dropzoneArea, gridPanel.firstChild);
        
        // Add toggle button for dropzone
        const buttonContainer = document.createElement('div');
        buttonContainer.className = 'flex justify-end mb-4';
        buttonContainer.innerHTML = `
            <button type="button" id="toggle-image-upload" class="px-3 py-1.5 text-sm text-gray-600 bg-gray-100 rounded hover:bg-gray-200 flex items-center">
                <i data-lucide="image" class="h-4 w-4 mr-1"></i>
                Upload Images
            </button>
        `;
        
        gridPanel.insertBefore(buttonContainer, gridPanel.firstChild);
        
        // Initialize Lucide icons for the new elements
        lucide.createIcons({
            scope: dropzoneArea
        });
        
        lucide.createIcons({
            scope: buttonContainer
        });
        
        // Toggle dropzone visibility
        const toggleButton = document.getElementById('toggle-image-upload');
        toggleButton.addEventListener('click', function() {
            dropzoneArea.classList.toggle('hidden');
            if (!dropzoneArea.classList.contains('hidden')) {
                this.textContent = 'Hide Upload Area';
                this.innerHTML = '<i data-lucide="x" class="h-4 w-4 mr-1"></i> Hide Upload Area';
                lucide.createIcons({
                    scope: this
                });
            } else {
                this.textContent = 'Upload Images';
                this.innerHTML = '<i data-lucide="image" class="h-4 w-4 mr-1"></i> Upload Images';
                lucide.createIcons({
                    scope: this
                });
            }
        });
        
        // Set up dropzone event listeners
        const dropzone = document.getElementById('bulk-image-dropzone');
        const fileInput = document.getElementById('bulk-file-upload');
        const previewsContainer = document.getElementById('upload-previews');
        
        // Trigger file selection when clicking on dropzone
        dropzone.addEventListener('click', function(e) {
            if (e.target.id !== 'bulk-file-upload') {
                fileInput.click();
            }
        });
        
        // Handle drag and drop
        dropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('border-primary');
            this.classList.add('bg-primary-light');
            this.classList.add('bg-opacity-10');
        });
        
        dropzone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('border-primary');
            this.classList.remove('bg-primary-light');
            this.classList.remove('bg-opacity-10');
        });
        
        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('border-primary');
            this.classList.remove('bg-primary-light');
            this.classList.remove('bg-opacity-10');
            
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                handleFiles(e.dataTransfer.files);
            }
        });
        
        // Handle file selection
        fileInput.addEventListener('change', function() {
            handleFiles(this.files);
        });
        
        // Process selected files
        function handleFiles(files) {
            for (let i = 0; i < files.length; i++) {
                uploadFile(files[i]);
            }
        }
        
        // Upload file to server
        function uploadFile(file) {
            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                showNotification('Invalid file type. Please select an image file (JPG, PNG, GIF, WEBP).', 'error');
                return;
            }
            
            // Validate file size (max a2MB)
            if (file.size > 2 * 1024 * 1024) {
                showNotification('File size too large. Maximum allowed size is 2MB.', 'error');
                return;
            }
            
            // Create preview element
            const previewContainer = document.createElement('div');
            previewContainer.className = 'relative w-20 h-20 bg-gray-100 rounded overflow-hidden border border-gray-200';
            
            const previewImage = document.createElement('img');
            previewImage.className = 'w-full h-full object-cover';
            
            const progressOverlay = document.createElement('div');
            progressOverlay.className = 'absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center';
            
            const progressBar = document.createElement('div');
            progressBar.className = 'w-16 h-1.5 bg-gray-500 rounded-full overflow-hidden';
            
            const progressIndicator = document.createElement('div');
            progressIndicator.className = 'h-full bg-white';
            progressIndicator.style.width = '0%';
            
            // Append elements
            progressBar.appendChild(progressIndicator);
            progressOverlay.appendChild(progressBar);
            previewContainer.appendChild(previewImage);
            previewContainer.appendChild(progressOverlay);
            previewsContainer.appendChild(previewContainer);
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
            };
            reader.readAsDataURL(file);
            
            // Create form data for upload
            const formData = new FormData();
            formData.append('product_image', file);
            
            // Upload with progress tracking
            const xhr = new XMLHttpRequest();
            
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    progressIndicator.style.width = percent + '%';
                }
            });
            
            xhr.addEventListener('load', function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Successfully uploaded
                            progressOverlay.remove();
                            
                            // Add a "copy URL" button
                            const copyButton = document.createElement('button');
                            copyButton.className = 'absolute bottom-1 right-1 p-1 bg-primary text-white rounded-full hover:bg-primary-dark text-xs';
                            copyButton.innerHTML = '<i data-lucide="copy" class="h-3 w-3"></i>';
                            copyButton.title = 'Copy image URL';
                            copyButton.setAttribute('data-url', response.file_path);
                            
                            copyButton.addEventListener('click', function(e) {
                                e.stopPropagation();
                                const url = this.getAttribute('data-url');
                                
                                // Copy to clipboard
                                navigator.clipboard.writeText(url).then(function() {
                                    showNotification('Image URL copied to clipboard');
                                }).catch(function() {
                                    // Fallback if clipboard API fails
                                    const tempInput = document.createElement('input');
                                    tempInput.value = url;
                                    document.body.appendChild(tempInput);
                                    tempInput.select();
                                    document.execCommand('copy');
                                    document.body.removeChild(tempInput);
                                    showNotification('Image URL copied to clipboard');
                                });
                            });
                            
                            previewContainer.appendChild(copyButton);
                            lucide.createIcons({
                                scope: copyButton
                            });
                            
                            showNotification('Image uploaded successfully');
                        } else {
                            // Upload failed
                            progressOverlay.innerHTML = '<span class="text-white text-xs p-1">Failed</span>';
                            showNotification('Upload failed: ' + response.message, 'error');
                        }
                    } catch (e) {
                        progressOverlay.innerHTML = '<span class="text-white text-xs p-1">Error</span>';
                        showNotification('Error processing upload response', 'error');
                    }
                } else {
                    progressOverlay.innerHTML = '<span class="text-white text-xs p-1">Failed</span>';
                    showNotification('Upload failed with status: ' + xhr.status, 'error');
                }
            });
            
            xhr.addEventListener('error', function() {
                progressOverlay.innerHTML = '<span class="text-white text-xs p-1">Error</span>';
                showNotification('Network error during upload', 'error');
            });
            
            xhr.open('POST', '/flower-lab/ajax/upload_product_image.php', true);
            xhr.send(formData);
        }
        
        // Helper notification function
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed bottom-4 right-4 px-4 py-2 rounded shadow-lg z-50 ${type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(function() {
                notification.remove();
            }, 3000);
        }
    }
});
</script>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>