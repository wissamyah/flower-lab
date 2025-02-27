<?php require_once dirname(__DIR__) . '/config.php'; ?>
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
                        <a href="/flower-lab/wishlist.php" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary-dark hover:bg-gray-50">Wishlist</a>
                        <a href="/flower-lab/profile.php" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary-dark hover:bg-gray-50">Profile</a>
                    </div>
                </div>
                
                <div class="flex items-center space-x-2">
                    <a href="/flower-lab/basket.php" class="p-2 rounded-full text-gray-600 hover:text-primary-dark hover:bg-gray-100" aria-label="Basket">
                        <i data-lucide="shopping-bag" class="h-5 w-5"></i>
                    </a>
                    <a href="/flower-lab/wishlist.php" class="p-2 rounded-full text-gray-600 hover:text-primary-dark hover:bg-gray-100" aria-label="Wishlist">
                        <i data-lucide="heart" class="h-5 w-5"></i>
                    </a>
                    <div class="relative">
                        <div id="profile-notification-indicator" class="hidden absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full flex items-center justify-center text-xs text-white font-bold">
                            <span id="notification-count">0</span>
                        </div>
                        <a href="#" id="profile-icon" class="p-2 rounded-full text-gray-600 hover:text-primary-dark hover:bg-gray-100 block" aria-label="Profile">
                            <i data-lucide="user" class="h-5 w-5"></i>
                        </a>
                        
                        <!-- Notification dropdown (hidden by default) -->
                        <div id="notification-dropdown" class="hidden absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-lg z-50 overflow-hidden border border-gray-200">
                            <div class="p-3 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                                <h3 class="font-medium text-gray-800">Notifications</h3>
                                <div>
                                    <span id="auto-read-timer" class="text-xs text-gray-500 mr-2 hidden">Auto-reading in 5s...</span>
                                    <a href="#" id="mark-all-read" class="text-xs text-primary-dark hover:underline">Mark all as read</a>
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
                </div>
            </div>
        </div>
        
        <!-- Mobile menu (hidden by default) -->
        <div class="md:hidden bg-white border-t border-gray-100" id="mobile-menu" style="display: none;">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="/flower-lab/" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-dark hover:bg-gray-50">Home</a>
                <a href="/flower-lab/basket.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-dark hover:bg-gray-50">Basket</a>
                <a href="/flower-lab/wishlist.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-dark hover:bg-gray-50">Wishlist</a>
                <a href="/flower-lab/profile.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-primary-dark hover:bg-gray-50">Profile</a>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="flex-grow">