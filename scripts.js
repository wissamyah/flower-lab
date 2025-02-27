// Firebase Authentication Handling
document.addEventListener("DOMContentLoaded", function() {
    // Check authentication state
    firebase.auth().onAuthStateChanged(function(user) {
        if (user) {
            // User is signed in
            console.log("User is signed in:", user.email);

            // Store user info in session via AJAX
            syncUserWithDatabase(user);
        } else {
            // User is signed out
            console.log("User is signed out");
        }
    });
});

// Sync Firebase user with database
// Sync Firebase user with database
function syncUserWithDatabase(user) {
    // Get the ID token
    user.getIdToken().then(function(idToken) {
        // Get phone number if available
        const phoneNumber = user.phoneNumber || "";

        // Send to the NEW endpoint
        fetch("/flower-lab/ajax/firebase_sync.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    idToken: idToken,
                    email: user.email,
                    phoneNumber: phoneNumber,
                    displayName: user.displayName || "",
                }),
            })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then((data) => {
                console.log("User synced with database:", data);
                // Redirect if needed
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            })
            .catch((error) => {
                console.error("Error syncing user:", error);
                // Add fallback - try again after a short delay
                setTimeout(() => {
                    console.log("Retrying user sync...");

                    // Try a simpler version with minimal data
                    fetch("/flower-lab/ajax/firebase_sync.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                            },
                            body: JSON.stringify({
                                email: user.email
                            }),
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log("User sync retry result:", data);
                        })
                        .catch(error => {
                            console.error("Error in retry sync:", error);
                        });
                }, 1000);
            });
    });
}

// Sign out user
function signOut() {
    firebase
        .auth()
        .signOut()
        .then(function() {
            // Sign-out successful
            window.location.href = "/flower-lab/index.php";
        })
        .catch(function(error) {
            // An error happened
            console.error("Sign out error:", error);
        });
}

// Create a modern notification system with improved layout
function showModernNotification(options) {
    // Default options
    const defaults = {
        type: 'success', // success, error, info
        title: '',
        message: '',
        duration: 3000,
        productImage: null,
        productTitle: null,
        productPrice: null,
        productQuantity: null
    };

    // Merge options with defaults
    const settings = {...defaults, ...options };

    // Create notification container if it doesn't exist
    let container = document.getElementById('notification-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notification-container';
        container.className = 'fixed top-4 right-4 z-50 flex flex-col space-y-3 max-w-md';
        document.body.appendChild(container);
    }

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `bg-white rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out overflow-hidden`;

    // Set border color based on type
    if (settings.type === 'success') {
        notification.classList.add('border-l-4', 'border-green-500');
    } else if (settings.type === 'error') {
        notification.classList.add('border-l-4', 'border-red-500');
    } else if (settings.type === 'info') {
        notification.classList.add('border-l-4', 'border-blue-500');
    }

    // Build notification content
    let notificationContent = '<div class="p-4">';

    // Header with title and close button
    notificationContent += `
        <div class="flex justify-between items-center mb-2">
            <h4 class="font-medium text-gray-800 text-base">${settings.title}</h4>
            <button class="text-gray-400 hover:text-gray-500 flex-shrink-0" onclick="this.closest('.bg-white').remove()">
                <i data-lucide="x" class="h-5 w-5"></i>
            </button>
        </div>
    `;

    // Message if provided
    if (settings.message) {
        notificationContent += `<p class="text-sm text-gray-600 mb-2">${settings.message}</p>`;
    }

    // If product information is provided
    if (settings.productImage || settings.productTitle) {
        notificationContent += `<div class="flex items-center bg-gray-50 p-3 rounded-md">`;

        // Product image
        if (settings.productImage) {
            notificationContent += `
                <div class="w-16 h-16 bg-white rounded overflow-hidden mr-3 flex-shrink-0 border border-gray-200">
                    <img src="${settings.productImage}" alt="Product" class="w-full h-full object-cover">
                </div>
            `;
        } else {
            notificationContent += `
                <div class="w-16 h-16 bg-white rounded overflow-hidden mr-3 flex-shrink-0 flex items-center justify-center border border-gray-200">
                    <i data-lucide="package" class="h-8 w-8 text-gray-400"></i>
                </div>
            `;
        }

        // Product details
        notificationContent += `
            <div class="flex-grow overflow-hidden">
                ${settings.productTitle ? `<h4 class="font-medium text-gray-800 text-sm truncate">${settings.productTitle}</h4>` : ''}
                <div class="flex flex-wrap text-sm mt-1">
                    ${settings.productPrice ? `<span class="font-medium text-gray-800 mr-3">$${settings.productPrice}</span>` : ''}
                    ${settings.productQuantity ? `<span class="text-gray-500">Qty: ${settings.productQuantity}</span>` : ''}
                </div>
            </div>
        `;
        
        notificationContent += `</div>`;
    }
    
    notificationContent += '</div>';
    
    notification.innerHTML = notificationContent;
    
    // Add to container
    container.appendChild(notification);
    
    // Initialize Lucide icons
    lucide.createIcons({
        scope: notification
    });
    
    // Animate in (after a small delay to allow the DOM to update)
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 10);
    
    // Set timeout to remove
    setTimeout(() => {
        // Animate out
        notification.classList.add('translate-x-full');
        
        // Remove from DOM after animation completes
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
            
            // Remove container if empty
            if (container.children.length === 0) {
                container.remove();
            }
        }, 300);
    }, settings.duration);
    
    return notification;
}

// Function to add to basket with detailed notification
function addToBasket(itemId, quantity = 1) {
    // Check if user is logged in
    const user = firebase.auth().currentUser;

    // Get product info for notification
    const productElement = document.querySelector(`[data-item-id="${itemId}"]`)?.closest('.bg-white');
    let productTitle = null;
    let productImage = null;
    let productPrice = null;
    
    if (productElement) {
        productTitle = productElement.querySelector('h3')?.textContent;
        const imgElement = productElement.querySelector('img');
        if (imgElement && imgElement.src) {
            productImage = imgElement.src;
        }
        const priceElement = productElement.querySelector('.font-bold');
        if (priceElement) {
            productPrice = priceElement.textContent.replace('$', '');
        }
    }

    if (!user) {
        // Save item to local storage for guest users
        saveToLocalBasket(itemId, quantity);
        
        showModernNotification({
            type: 'success',
            title: 'Added to Basket',
            message: 'Item has been added to your basket',
            productImage: productImage,
            productTitle: productTitle,
            productPrice: productPrice,
            productQuantity: quantity
        });
        return;
    }

    // Send to server
    fetch("/flower-lab/ajax/basket.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                action: "add",
                itemId: itemId,
                quantity: quantity,
            }),
        })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                showModernNotification({
                    type: 'success',
                    title: 'Added to Basket',
                    message: 'Item has been added to your basket',
                    productImage: productImage,
                    productTitle: productTitle,
                    productPrice: productPrice,
                    productQuantity: quantity
                });
                updateBasketCount();
            } else {
                showModernNotification({
                    type: 'error',
                    title: 'Error',
                    message: data.message || "Error adding item to basket"
                });
            }
        })
        .catch((error) => {
            console.error("Error adding to basket:", error);
            showModernNotification({
                type: 'error',
                title: 'Error',
                message: "Failed to add item to basket"
            });
        });
}

function removeFromBasket(itemId) {
    // Check if user is logged in
    const user = firebase.auth().currentUser;
    
    // Get product info for the notification
    let productTitle = null;
    let productImage = null;
    let productPrice = null;
    let productQuantity = null;
    
    // Try to find the product in the basket
    const basketItems = document.querySelectorAll(`li:has([onclick*="removeItemFromBasket(${itemId})"])`);
    if (basketItems.length > 0) {
        const item = basketItems[0];
        if (item) {
            productTitle = item.querySelector('h3')?.textContent || null;
            const imgElement = item.querySelector('img');
            if (imgElement && imgElement.src) {
                productImage = imgElement.src;
            }
            const priceElement = item.querySelector('.font-bold');
            if (priceElement) {
                productPrice = priceElement.textContent.replace('$', '');
            }
            productQuantity = item.querySelector('.flex.items-center .w-8')?.textContent || null;
        }
    }

    if (!user) {
        // Remove from local storage for guest users
        removeFromLocalBasket(itemId);
        
        // Show notification
        showModernNotification({
            type: 'success',
            title: 'Removed from Basket',
            message: 'Item has been removed from your basket',
            productImage: productImage,
            productTitle: productTitle,
            productPrice: productPrice,
            productQuantity: productQuantity
        });
        return;
    }

    // Send to server
    fetch("/flower-lab/ajax/basket.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                action: "remove",
                itemId: itemId,
            }),
        })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                // Show modern notification
                showModernNotification({
                    type: 'success',
                    title: 'Removed from Basket',
                    message: 'Item has been removed from your basket',
                    productImage: productImage,
                    productTitle: productTitle,
                    productPrice: productPrice,
                    productQuantity: productQuantity
                });
                
                updateBasketCount();

                // Refresh the basket page if we're on it
                if (window.location.pathname.includes("/basket.php")) {
                    refreshBasket();
                }
            } else {
                showModernNotification({
                    type: 'error',
                    title: 'Error',
                    message: data.message || "Error removing item from basket"
                });
            }
        })
        .catch((error) => {
            console.error("Error removing from basket:", error);
            showModernNotification({
                type: 'error',
                title: 'Error',
                message: "Failed to remove item from basket"
            });
        });
}

function updateBasketQuantity(itemId, quantity) {
    // Check if user is logged in
    const user = firebase.auth().currentUser;

    if (!user) {
        // Update in local storage for guest users
        updateLocalBasketQuantity(itemId, quantity);
        return;
    }

    // Send to server
    fetch("/flower-lab/ajax/basket.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                action: "update",
                itemId: itemId,
                quantity: quantity,
            }),
        })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                updateBasketCount();

                // If on basket page, refresh the basket
                if (window.location.pathname.includes("/basket.php")) {
                    refreshBasket();
                }
            } else {
                showNotification(data.message || "Error updating quantity", "error");
            }
        })
        .catch((error) => {
            console.error("Error updating quantity:", error);
            showNotification("Failed to update quantity", "error");
        });
}

// Local storage for guest users
function saveToLocalBasket(itemId, quantity) {
    let basket = JSON.parse(localStorage.getItem("guestBasket")) || {};

    // Add or update item
    if (basket[itemId]) {
        basket[itemId] += quantity;
    } else {
        basket[itemId] = quantity;
    }

    localStorage.setItem("guestBasket", JSON.stringify(basket));
    updateBasketCount();
}

function removeFromLocalBasket(itemId) {
    let basket = JSON.parse(localStorage.getItem("guestBasket")) || {};

    // Remove item
    if (basket[itemId]) {
        delete basket[itemId];
    }

    localStorage.setItem("guestBasket", JSON.stringify(basket));
    updateBasketCount();
}

function updateLocalBasketQuantity(itemId, quantity) {
    let basket = JSON.parse(localStorage.getItem("guestBasket")) || {};

    // Update quantity
    if (basket[itemId]) {
        basket[itemId] = quantity;
    }

    localStorage.setItem("guestBasket", JSON.stringify(basket));
    updateBasketCount();
}

function updateBasketCount() {
    // Check if user is logged in
    const user = firebase.auth().currentUser;

    if (!user) {
        // Count items in local storage
        const basket = JSON.parse(localStorage.getItem("guestBasket")) || {};
        let count = 0;

        for (const itemId in basket) {
            count += basket[itemId];
        }

        // Update UI
        const basketCountElements = document.querySelectorAll(".basket-count");
        basketCountElements.forEach((element) => {
            element.textContent = count > 0 ? count : "";
            element.style.display = count > 0 ? "block" : "none";
        });

        return;
    }

    // Get count from server
    fetch("/flower-lab/ajax/basket.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                action: "count",
            }),
        })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                // Update UI
                const basketCountElements = document.querySelectorAll(".basket-count");
                basketCountElements.forEach((element) => {
                    element.textContent = data.count > 0 ? data.count : "";
                    element.style.display = data.count > 0 ? "block" : "none";
                });
            }
        })
        .catch((error) => {
            console.error("Error getting basket count:", error);
        });
}

// Wishlist Functions
function toggleWishlist(itemId, buttonElement) {
    // Check if user is logged in
    const user = firebase.auth().currentUser;

    if (!user) {
        // Prompt to login
        showNotification("Please login to add items to your wishlist");
        return;
    }

    // If buttonElement isn't passed, try to find it
    if (!buttonElement) {
        buttonElement = document.querySelector(
            `[data-item-id="${itemId}"], [onclick*="toggleWishlist(${itemId})"]`
        );
    }

    const isInWishlist = buttonElement && buttonElement.classList.contains("text-primary");

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
                if (action === "add") {
                    showNotification("Item added to wishlist");

                    // Update button style to filled heart
                    if (buttonElement) {
                        buttonElement.classList.remove("text-gray-400");
                        buttonElement.classList.add("text-primary");

                        // Find and fill the heart icon
                        const heartIcon = buttonElement.querySelector('[data-lucide="heart"]');
                        if (heartIcon) {
                            heartIcon.setAttribute("fill", "currentColor");
                        }
                    }
                } else {
                    showNotification("Item removed from wishlist");

                    // Update button style to empty heart
                    if (buttonElement) {
                        buttonElement.classList.remove("text-primary");
                        buttonElement.classList.add("text-gray-400");

                        // Find and unfill the heart icon
                        const heartIcon = buttonElement.querySelector('[data-lucide="heart"]');
                        if (heartIcon) {
                            heartIcon.removeAttribute("fill");
                        }
                    }

                    // If on wishlist page, refresh
                    if (window.location.pathname.includes("/wishlist.php")) {
                        window.location.reload();
                    }
                }
            } else {
                showNotification(data.message || `Error ${action === "add" ? "adding to" : "removing from"} wishlist`, "error");
            }
        })
        .catch((error) => {
            console.error(`Error ${action === "add" ? "adding to" : "removing from"} wishlist:`, error);
            showNotification(`Failed to ${action === "add" ? "add to" : "remove from"} wishlist`, "error");
        });
}

function moveToBasket(itemId) {
    // Add to basket first
    fetch("/flower-lab/ajax/basket.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                action: "add",
                itemId: itemId,
                quantity: 1,
            }),
        })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                showNotification("Item added to basket");

                // Then remove from wishlist
                fetch("/flower-lab/ajax/wishlist.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify({
                            action: "remove",
                            itemId: itemId,
                        }),
                    })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.success) {
                            showNotification("Item removed from wishlist");

                            // If on wishlist page, reload to refresh the list
                            if (window.location.pathname.includes("/wishlist.php")) {
                                window.location.reload();
                            }
                        }
                    })
                    .catch((error) => {
                        console.error("Error removing from wishlist:", error);
                    });
            } else {
                showNotification(data.message || "Error adding item to basket", "error");
            }
        })
        .catch((error) => {
            console.error("Error adding to basket:", error);
            showNotification("Failed to add item to basket", "error");
        });
}

// Order Functions
function createOrder(data) {
    return fetch("/flower-lab/ajax/order.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            action: "create",
            ...data,
        }),
    }).then((response) => response.json());
}

function generateWhatsAppLink(orderNumber) {
    // Get order details
    return fetch(
            `/flower-lab/ajax/order.php?action=details&orderNumber=${orderNumber}`
        )
        .then((response) => response.json())
        .then((data) => {
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

                    message += `${item.quantity}x ${item.title} - $${(
            price * item.quantity
          ).toFixed(2)}\n`;
                });

                message += `\n*Total: $${order.total.toFixed(2)}*\n\n`;
                message += `*Delivery Address:*\n${order.address}\n\n`;
                message += `*Contact:*\n${order.phone}\n\n`;

                if (order.gift_message) {
                    message += `*Gift Message:*\n${order.gift_message}\n\n`;
                }

                // Encode for URL
                const encodedMessage = encodeURIComponent(message);

                // Generate WhatsApp link
                const whatsappLink = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodedMessage}`;

                return whatsappLink;
            } else {
                throw new Error(data.message || "Error generating WhatsApp link");
            }
        });
}

// Utility Functions
function showNotification(message, type = "success") {
    // Create notification element
    const notification = document.createElement("div");
    notification.className = `fixed bottom-4 right-4 px-4 py-2 rounded-md shadow-md z-50 ${
    type === "success" ? "bg-green-500 text-white" : "bg-red-500 text-white"
  }`;
    notification.textContent = message;

    // Add to document
    document.body.appendChild(notification);

    // Remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Helper function to refresh basket
function refreshBasket() {
    if (window.location.pathname.includes("/basket.php")) {
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
}

// Check wishlist status for all heart icons
document.addEventListener("DOMContentLoaded", function() {
    // Initialize lucide icons if needed
    if (window.lucide && typeof lucide.createIcons === "function") {
        lucide.createIcons();
    }

    // Check wishlist status when user is logged in
    firebase.auth().onAuthStateChanged(function(user) {
        if (user) {
            // Add a slight delay to ensure the DOM is fully ready
            setTimeout(checkWishlistStatus, 500);
        }
    });

    // Mobile menu toggle
    const mobileMenuButton = document.querySelector('button[aria-label="Menu"]');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            if (mobileMenu.style.display === 'none' || mobileMenu.style.display === '') {
                mobileMenu.style.display = 'block';
                mobileMenuButton.innerHTML = '<i data-lucide="x" class="h-6 w-6"></i>';
            } else {
                mobileMenu.style.display = 'none';
                mobileMenuButton.innerHTML = '<i data-lucide="menu" class="h-6 w-6"></i>';
            }
            lucide.createIcons();
        });
    }
});

function checkWishlistStatus() {
    // Find all wishlist buttons
    const wishlistButtons = document.querySelectorAll('.wishlist-btn, [onclick*="toggleWishlist"]');
    if (wishlistButtons.length === 0) return;

    // Check if user is logged in
    const user = firebase.auth().currentUser;
    if (!user) return;

    // Get all item IDs
    const itemIds = [];
    wishlistButtons.forEach(button => {
        let itemId;

        // Check for data attribute first
        if (button.hasAttribute('data-item-id')) {
            itemId = button.getAttribute('data-item-id');
        } else {
            // Otherwise, try to parse from onclick attribute
            const onclick = button.getAttribute('onclick');
            if (onclick) {
                const match = onclick.match(/toggleWishlist\((\d+)/);
                if (match && match[1]) {
                    itemId = match[1];
                }
            }
        }

        if (itemId && !itemIds.includes(itemId)) {
            itemIds.push(itemId);
        }
    });

    if (itemIds.length === 0) return;

    // Check which items are in wishlist
    itemIds.forEach(itemId => {
        fetch('/flower-lab/ajax/wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'check',
                    itemId: itemId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.inWishlist) {
                    // Find buttons for this item ID
                    const buttons = document.querySelectorAll(`[data-item-id="${itemId}"], [onclick*="toggleWishlist(${itemId})"]`);

                    buttons.forEach(button => {
                        // Update the button appearance
                        button.classList.remove('text-gray-400');
                        button.classList.add('text-primary');

                        // Fill the heart icon
                        const heartIcon = button.querySelector('[data-lucide="heart"]');
                        if (heartIcon) {
                            heartIcon.setAttribute('fill', 'currentColor');
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error checking wishlist status:', error);
            });
    });
}