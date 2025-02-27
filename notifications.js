// Notification System for User Notifications
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on a login/register page where notifications shouldn't be initialized
    const isLoginPage = window.location.pathname.includes('/login.php') ||
        window.location.pathname.includes('/register.php') ||
        window.location.pathname.includes('/reset_password.php');

    // Skip notification setup on login pages
    if (isLoginPage) {
        console.log('Skipping notification initialization on login page');
        return;
    }

    // Elements
    const profileIcon = document.getElementById('profile-icon');
    const userDropdown = document.getElementById('user-dropdown');
    const notificationDropdown = document.getElementById('notification-dropdown');
    const notificationBackdrop = document.getElementById('notification-backdrop');
    const notificationIndicator = document.getElementById('profile-notification-indicator');
    const dropdownNotificationBadge = document.getElementById('dropdown-notification-badge');
    const notificationList = document.getElementById('notification-list');
    const notificationCount = document.getElementById('notification-count');
    const markAllReadBtn = document.getElementById('mark-all-read');
    const viewNotificationsBtn = document.getElementById('view-notifications');
    const closeNotificationsBtn = document.getElementById('close-notifications');
    const autoReadTimer = document.getElementById('auto-read-timer');

    // Debug elements
    console.log('Notification elements loaded:', {
        notificationDropdown,
        notificationBackdrop,
        viewNotificationsBtn,
        notificationList,
        notificationIndicator,
        dropdownNotificationBadge
    });

    // Timer for auto-marking as read
    let autoMarkAsReadTimer = null;

    // Flag to track if notifications are open
    let notificationsOpen = false;

    // Store loaded notifications
    let cachedNotifications = [];

    // Show notifications panel when clicking on "Notifications" in user dropdown
    if (viewNotificationsBtn && notificationDropdown) {
        viewNotificationsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Stop event bubbling

            console.log('View notifications button clicked');

            // Hide user dropdown
            if (userDropdown) {
                userDropdown.classList.add('hidden');
            }

            // Show notifications panel with animation
            toggleNotificationDropdown(true);

            // Set flag that notifications are open
            notificationsOpen = true;

            // Show loading spinner in notification list right away
            if (notificationList) {
                notificationList.innerHTML = `
                    <div class="p-4 text-center">
                        <div class="inline-block animate-spin rounded-full h-5 w-5 border-t-2 border-b-2 border-primary"></div>
                        <p class="text-sm text-gray-500 mt-1">Loading notifications...</p>
                    </div>
                `;
            }

            // Fetch notifications and wait for them to load before doing anything else
            fetchNotifications().then(() => {
                // Reset both notification counters after we have loaded the notifications
                resetNotificationCounters();

                // Display the loaded notifications (ensuring they appear)
                if (cachedNotifications.length > 0) {
                    updateNotificationUI(cachedNotifications);
                }

                // Hide spinner after everything is done
                if (autoReadTimer) {
                    autoReadTimer.classList.add('hidden');
                }
            });
        });
    } else {
        console.error('Notification button or dropdown not found');
    }

    // Close notifications panel
    if (closeNotificationsBtn) {
        closeNotificationsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Close notifications button clicked');

            // Use the animation function to hide
            toggleNotificationDropdown(false);

            // Set flag that notifications are closed
            notificationsOpen = false;

            // Clear timer if dropdown is closed
            if (autoMarkAsReadTimer) {
                clearTimeout(autoMarkAsReadTimer);
                autoMarkAsReadTimer = null;
            }
        });
    }

    // Close notifications panel when clicking on backdrop
    if (notificationBackdrop) {
        notificationBackdrop.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Notification backdrop clicked');

            // Use the animation function to hide
            toggleNotificationDropdown(false);

            // Set flag that notifications are closed
            notificationsOpen = false;

            // Clear timer if dropdown is closed
            if (autoMarkAsReadTimer) {
                clearTimeout(autoMarkAsReadTimer);
                autoMarkAsReadTimer = null;
            }
        });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (profileIcon && userDropdown && !profileIcon.contains(e.target) && !userDropdown.contains(e.target)) {
            userDropdown.classList.add('hidden');
        }

        // If notification dropdown is visible and click is outside its content
        if (notificationDropdown &&
            !notificationDropdown.classList.contains('hidden') &&
            !e.target.closest('.relative.bg-white')) {

            console.log('Document click detected outside notification content');
            notificationDropdown.style.display = 'none';
            notificationDropdown.classList.add('hidden');

            // Set flag that notifications are closed
            notificationsOpen = false;

            // Clear timer if dropdown is closed
            if (autoMarkAsReadTimer) {
                clearTimeout(autoMarkAsReadTimer);
                autoMarkAsReadTimer = null;
            }
        }
    });

    // Mark all as read (now Clear all)
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            markAllNotificationsAsRead(false); // false = not silent mode, show notification

            // Clear the auto-mark timer if it exists
            if (autoMarkAsReadTimer) {
                clearTimeout(autoMarkAsReadTimer);
                autoMarkAsReadTimer = null;
            }
        });
    }

    // Helper function to safely show/hide notification dropdown with animation
    function toggleNotificationDropdown(show) {
        const notificationDropdown = document.getElementById('notification-dropdown');
        if (!notificationDropdown) return;

        if (show) {
            // Ensure proper styling for the container
            notificationDropdown.style.position = 'fixed';
            notificationDropdown.style.inset = '0';
            notificationDropdown.style.width = '100vw';
            notificationDropdown.style.height = '100vh';
            notificationDropdown.style.display = 'flex';
            notificationDropdown.style.alignItems = 'center';
            notificationDropdown.style.justifyContent = 'center';
            notificationDropdown.style.zIndex = '9999';

            // Remove any closing class
            notificationDropdown.classList.remove('closing');

            // Show dropdown
            notificationDropdown.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevent body scrolling
            console.log('Notification dropdown shown');
        } else {
            // Add closing class for animation
            notificationDropdown.classList.add('closing');

            // Hide after animation completes
            setTimeout(() => {
                notificationDropdown.classList.add('hidden');
                document.body.style.overflow = ''; // Restore body scrolling
                console.log('Notification dropdown hidden');

                // Remove closing class
                notificationDropdown.classList.remove('closing');
            }, 200); // Match the animation duration
        }
    }

    // Function to update the notification badge in the dropdown
    function updateDropdownNotificationBadge() {
        console.log("Updating dropdown notification badge");
        if (!dropdownNotificationBadge) {
            console.error("Dropdown notification badge element not found");
            return;
        }

        const count = parseInt(notificationCount.textContent || '0');
        console.log("Current notification count:", count);

        if (count > 0) {
            dropdownNotificationBadge.textContent = count;
            dropdownNotificationBadge.classList.remove('hidden');
            console.log("Showing badge with count:", count);
        } else {
            dropdownNotificationBadge.classList.add('hidden');
            console.log("Hiding badge");
        }
    }

    // Function to reset all notification counters to 0
    function resetNotificationCounters() {
        console.log("Resetting all notification counters");

        // Reset the profile icon counter to 0
        if (notificationCount) {
            notificationCount.textContent = '0';
            console.log("Reset profile notification count to 0");
        }

        // Hide the profile icon notification indicator
        if (notificationIndicator) {
            notificationIndicator.classList.add('hidden');
            console.log("Hidden profile notification indicator");
        }

        // Also reset the dropdown badge
        if (dropdownNotificationBadge) {
            dropdownNotificationBadge.textContent = '0';
            dropdownNotificationBadge.classList.add('hidden');
            console.log("Reset and hidden dropdown notification badge");
        }

        // Mark all notifications as read on server
        markAllNotificationsAsReadOnServer();
    }

    // Fetch notifications from server (returns a promise)
    window.fetchNotifications = function() {
        console.log("Fetching notifications");
        const user = firebase.auth().currentUser;
        if (!user) {
            console.log("No user logged in, skipping notification fetch");
            return Promise.resolve([]); // Return empty promise
        }

        return fetch('/flower-lab/ajax/get_notifications.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log("Notifications data received:", data);
                if (data.success) {
                    // Store the notifications for later use
                    cachedNotifications = data.notifications || [];

                    // If we're not showing the notification panel, update counts
                    if (!notificationsOpen) {
                        updateNotificationCounts(data.notifications);
                    }

                    return data.notifications || [];
                } else {
                    throw new Error(data.message || 'Unknown error');
                }
            })
            .catch(error => {
                console.error('Error fetching notifications:', error);

                if (notificationList && notificationsOpen) {
                    notificationList.innerHTML = `
                        <div class="p-4 text-center text-sm text-red-500">
                            Error loading notifications. Please try again.
                        </div>
                    `;
                    console.log("Showing error message in notification list");
                }

                return []; // Return empty array on error
            });
    }

    // Update notification counts based on data
    function updateNotificationCounts(notifications) {
        if (!notificationCount || !notificationIndicator) return;

        // Filter unread notifications
        const unreadNotifications = notifications.filter(n => !n.is_read);
        console.log("Found", unreadNotifications.length, "unread notifications");

        if (unreadNotifications.length > 0) {
            // Update count on the profile icon indicator
            notificationCount.textContent = unreadNotifications.length;
            console.log("Setting profile notification count to", unreadNotifications.length);

            // Show the profile icon indicator
            notificationIndicator.classList.remove('hidden');
            console.log("Showing profile notification indicator");

            // Also update dropdown badge
            if (dropdownNotificationBadge) {
                dropdownNotificationBadge.textContent = unreadNotifications.length;
                dropdownNotificationBadge.classList.remove('hidden');
                console.log("Setting dropdown badge to", unreadNotifications.length);
            }
        } else {
            // Hide the profile icon indicator when no notifications
            notificationIndicator.classList.add('hidden');
            console.log("Hiding profile notification indicator");

            // Also hide the dropdown badge
            if (dropdownNotificationBadge) {
                dropdownNotificationBadge.classList.add('hidden');
                console.log("Hiding dropdown badge");
            }
        }
    }

    // Update notification UI
    function updateNotificationUI(notifications) {
        console.log("Updating notification UI with", notifications.length, "notifications");
        if (!notificationList) {
            console.error("Notification list element not found");
            return;
        }

        // Update notification list
        if (notifications.length === 0) {
            notificationList.innerHTML = `
                <div class="p-4 text-center text-sm text-gray-500">
                    No new notifications
                </div>
            `;
            console.log("Showing 'No new notifications' message");
            return;
        }

        // Sort by timestamp (newest first)
        notifications.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

        // Build notification list
        let html = '';

        notifications.slice(0, 5).forEach(notification => {
            // Determine icon and color based on type
            let icon = 'bell';
            let bgColor = 'bg-gray-100';
            let textColor = 'text-gray-600';

            if (notification.type === 'order_confirmed') {
                icon = 'check-circle';
                bgColor = 'bg-green-100';
                textColor = 'text-green-600';
            } else if (notification.type === 'order_completed') {
                icon = 'package';
                bgColor = 'bg-blue-100';
                textColor = 'text-blue-600';
            }

            // Format date
            const date = new Date(notification.created_at);
            const formattedDate = date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            html += `
                <div class="p-3 border-b border-gray-100 hover:bg-gray-50 flex items-start">
                    <div class="p-2 rounded-full ${bgColor} ${textColor} mr-3">
                        <i data-lucide="${icon}" class="h-5 w-5"></i>
                    </div>
                    <div class="flex-grow">
                        <p class="text-sm font-medium text-gray-800">${notification.title}</p>
                        <p class="text-xs text-gray-500">${notification.message}</p>
                        <p class="text-xs text-gray-400 mt-1">${formattedDate}</p>
                    </div>
                </div>
            `;
        });

        notificationList.innerHTML = html;
        console.log("Rendered notification list with", notifications.length, "items");

        // Initialize Lucide icons
        lucide.createIcons({
            scope: notificationList
        });
    }

    // Mark all as read on server without updating UI
    function markAllNotificationsAsReadOnServer() {
        fetch('/flower-lab/ajax/mark_all_notifications_read.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                console.log("Server response for mark all read:", data);
                // No UI updates needed here, as we already updated the UI
            })
            .catch(error => {
                console.error('Error marking all notifications as read on server:', error);
            });
    }

    // Check for notifications on page load (with a slight delay to ensure auth is ready)
    setTimeout(function() {
        const user = firebase.auth().currentUser;
        if (user) {
            console.log("User logged in, fetching initial notifications");
            fetchNotifications();
        } else {
            // Wait for auth state to change
            firebase.auth().onAuthStateChanged(function(user) {
                if (user) {
                    console.log("Auth state changed to logged in, fetching notifications");
                    setTimeout(fetchNotifications, 1000); // Small delay to ensure user data is ready
                }
            });
        }
    }, 1000);

    // Check for new notifications every 30 seconds
    setInterval(function() {
        const user = firebase.auth().currentUser;
        if (user && !notificationsOpen) {
            console.log("Periodic notification check (30s interval)");
            fetchNotifications();
        }
    }, 30000);
});

// Mark all notifications as read (now Clear all)
function markAllNotificationsAsRead(silent = false) {
    console.log("Clearing all notifications, silent mode:", silent);

    fetch('/flower-lab/ajax/mark_all_notifications_read.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            console.log("Server response for clear all:", data);
            if (data.success) {
                // Hide notification indicator on profile icon
                const profileNotificationIndicator = document.getElementById('profile-notification-indicator');
                if (profileNotificationIndicator) {
                    profileNotificationIndicator.classList.add('hidden');
                    console.log("Hidden profile notification indicator");
                }

                // Hide notification badge in dropdown and set to 0
                const dropdownNotificationBadge = document.getElementById('dropdown-notification-badge');
                if (dropdownNotificationBadge) {
                    dropdownNotificationBadge.textContent = '0';
                    dropdownNotificationBadge.classList.add('hidden');
                    console.log("Reset and hidden dropdown notification badge");
                }

                // Reset notification count
                const notificationCount = document.getElementById('notification-count');
                if (notificationCount) {
                    notificationCount.textContent = '0';
                    console.log("Reset notification count to 0");
                }

                // Clear all notifications from the list
                const notificationList = document.getElementById('notification-list');
                if (notificationList) {
                    notificationList.innerHTML = `
                    <div class="p-4 text-center text-sm text-gray-500">
                        No new notifications
                    </div>
                `;
                    console.log("Updated notification list to show 'No new notifications'");
                }

                // Show success notification unless in silent mode
                if (!silent && typeof showModernNotification === 'function') {
                    showModernNotification({
                        type: 'success',
                        title: 'Notifications Cleared',
                        message: 'All notifications have been cleared'
                    });
                    console.log("Displayed success notification");
                }
            }
        })
        .catch(error => {
            console.error('Error clearing all notifications:', error);
        });
}

// Create a test notification (for development purposes)
function createTestNotification() {
    fetch('/flower-lab/ajax/create_test_notification.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fetchNotifications();
                console.log('Test notification created');
            }
        })
        .catch(error => {
            console.error('Error creating test notification:', error);
        });
}