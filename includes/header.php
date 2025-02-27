<?php 
// /flower-lab/includes/header.php
require_once dirname(__DIR__) . '/config.php'; 
require_once dirname(__DIR__) . '/includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME ?></title>
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            light: '#FFD6EC',
                            DEFAULT: '#FF90BC',
                            dark: '#FF5A8C'
                        },
                        secondary: {
                            light: '#E0F7FA',
                            DEFAULT: '#80DEEA',
                            dark: '#4DD0E1'
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Lucide Icons via CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Firebase SDK via CDN -->
    <script src="https://www.gstatic.com/firebasejs/10.6.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.6.0/firebase-auth-compat.js"></script>
    
    <!-- Custom styles -->
    <link rel="stylesheet" href="/flower-lab/styles.css">
    <link rel="stylesheet" href="/flower-lab/css/firebase-ui-custom.css">
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">
    <!-- Firebase initialization -->
    <script>
        // Firebase configuration - minimal exposure
        const firebaseConfig = {
            apiKey: "<?= FIREBASE_API_KEY ?>",
            authDomain: "<?= FIREBASE_AUTH_DOMAIN ?>",
            projectId: "<?= FIREBASE_PROJECT_ID ?>"
            // Exclude unnecessary details
        };
        
        // Initialize Firebase with minimal config
        firebase.initializeApp(firebaseConfig);
        
        // Make PHP constants available to JavaScript
        const WHATSAPP_NUMBER = "<?= WHATSAPP_NUMBER ?>";
    </script>

    <!-- Flash Message Handler -->
    <?php if (isset($_COOKIE['flash_message'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            try {
                const flashMessage = JSON.parse('<?= $_COOKIE['flash_message'] ?>');
                if (typeof showModernNotification === 'function') {
                    setTimeout(function() {
                        showModernNotification(flashMessage);
                    }, 500);
                }
            } catch (e) {
                console.error('Error processing flash message:', e);
            }
            
            // Clear the cookie
            document.cookie = 'flash_message=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        });
    </script>
    <?php endif; ?>

    <!-- Sticky Navigation Bar -->
    <nav class="sticky top-0 bg-white shadow-sm z-50">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex-shrink-0">
                    <a href="/flower-lab/" class="text-xl font-medium text-primary-dark">
                        <?= SITE_NAME ?>
                    </a>
                </div>
                
                <div class="hidden md:block">
                    <div class="ml-10 flex items-center space-x-4">
                        <a href="/flower-lab/" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary-dark hover:bg-gray-50">Home</a>
                        <a href="/flower-lab/basket.php" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary-dark hover:bg-gray-50">Basket</a>
                        <a href="/flower-lab/wishlist.php" class="user-only px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary-dark hover:bg-gray-50" <?= !isLoggedIn() ? 'style="display:none;"' : '' ?>>Wishlist</a>
                        <?php if (!isLoggedIn()): ?>
                            <a href="/flower-lab/login.php" class="login-button px-3 py-2 rounded-md text-sm font-medium text-white bg-primary hover:bg-primary-dark">Sign In</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="flex items-center space-x-2">
                    <a href="/flower-lab/basket.php" class="p-2 rounded-full text-gray-600 hover:text-primary-dark hover:bg-gray-100" aria-label="Basket">
                        <i data-lucide="shopping-bag" class="h-5 w-5"></i>
                    </a>
                    <?php if (isLoggedIn()): ?>
                        <a href="/flower-lab/wishlist.php" class="user-only p-2 rounded-full text-gray-600 hover:text-primary-dark hover:bg-gray-100" aria-label="Wishlist">
                            <i data-lucide="heart" class="h-5 w-5"></i>
                        </a>
                    <?php endif; ?>
                    <div class="relative">
                        <div id="profile-notification-indicator" class="hidden absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full flex items-center justify-center text-xs text-white font-bold">
                            <span id="notification-count">0</span>
                        </div>
                        <a href="<?= isLoggedIn() ? '/flower-lab/profile.php' : '/flower-lab/login.php' ?>" id="profile-icon" class="p-2 rounded-full text-gray-600 hover:text-primary-dark hover:bg-gray-100 block" aria-label="Profile">
                            <?php if (isLoggedIn() && ($user = getCurrentUser()) && !empty($user['name'])): ?>
                                <span class="w-full h-full flex items-center justify-center text-sm font-medium text-white bg-primary rounded-full">
                                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                </span>
                            <?php else: ?>
                                <i data-lucide="user" class="h-5 w-5"></i>
                            <?php endif; ?>
                        </a>
                        
                        <?php if (isLoggedIn()): ?>
                        <!-- User dropdown menu -->
                        <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg z-50 overflow-hidden border border-gray-200">
                            <div class="p-3 bg-gray-50 border-b border-gray-200">
                                <h3 class="font-medium text-gray-800">
                                    <?php 
                                    $user = getCurrentUser();
                                    echo !empty($user['name']) ? htmlspecialchars($user['name']) : 'Your Account';
                                    ?>
                                </h3>
                                <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($user['email'] ?? '') ?></p>
                            </div>
                            <div class="py-1">
                                <a href="/flower-lab/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Your Profile
                                </a>
                                <a href="/flower-lab/wishlist.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Your Wishlist
                                </a>
                                <button type="button" id="view-notifications" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Notifications
                                    <span id="dropdown-notification-badge" class="ml-1 px-1.5 py-0.5 text-xs bg-red-500 text-white rounded-full hidden">0</span>
                                </button>
                                <a href="#" onclick="signOut(); return false;" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    Sign Out
                                </a>
                            </div>
                        </div>
                        
                        <!-- Notification dropdown (as a separate panel, not part of the user dropdown) -->
                        <div id="notification-dropdown" class="hidden fixed inset-0 z-50 flex items-center justify-center">
                            <div class="absolute inset-0 bg-black bg-opacity-25" id="notification-backdrop"></div>
                            <div class="relative bg-white w-full max-w-md rounded-lg shadow-lg overflow-hidden border border-gray-200 mx-4">
                                <div class="p-3 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                                    <h3 class="font-medium text-gray-800">Notifications</h3>
                                    <div class="flex items-center">
                                        <span id="auto-read-timer" class="text-xs text-gray-500 mr-2 hidden">Auto-reading in 5s...</span>
                                        <a href="#" id="mark-all-read" class="text-xs text-primary-dark hover:underline">Mark all as read</a>
                                        <button type="button" id="close-notifications" class="ml-2 text-gray-400 hover:text-gray-500">
                                            <i data-lucide="x" class="h-4 w-4"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="notification-list" class="max-h-80 overflow-y-auto">
                                    <div class="p-4 text-center text-sm text-gray-500">
                                        No new notifications
                                    </div>
                                </div>
                                <div class="p-3 bg-gray-50 border-t border-gray-200 text-center">
                                    <a href="/flower-lab/profile.php" class="text-xs text-primary-dark hover:underline">View all in profile</a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu (hidden by default) -->
        <div class="md:hidden bg-white border-t border-gray-100" id="mobile-menu" style="display: none;">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="/flower-lab/" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-dark hover:bg-gray-50">Home</a>
                <a href="/flower-lab/basket.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-dark hover:bg-gray-50">Basket</a>
                <a href="/flower-lab/wishlist.php" class="user-only block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-dark hover:bg-gray-50" <?= !isLoggedIn() ? 'style="display:none;"' : '' ?>>Wishlist</a>
                <a href="/flower-lab/profile.php" class="user-only block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-dark hover:bg-gray-50" <?= !isLoggedIn() ? 'style="display:none;"' : '' ?>>Profile</a>
                <?php if (isLoggedIn()): ?>
                    <a href="#" onclick="signOut(); return false;" class="user-only block px-3 py-2 rounded-md text-base font-medium text-red-600 hover:bg-red-50">Sign Out</a>
                <?php else: ?>
                    <a href="/flower-lab/login.php" class="login-button block px-3 py-2 rounded-md text-base font-medium text-white bg-primary hover:bg-primary-dark text-center">Sign In</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="flex-grow">