// Notification System for User Notifications
document.addEventListener('DOMContentLoaded', function() {
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

            // Timer for auto-marking as read
            let autoMarkAsReadTimer = null;

            // Show/hide user dropdown when clicking profile icon
            if (profileIcon && userDropdown) {
                profileIcon.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    // If notifications dropdown is open, close it
                    if (!notificationDropdown.classList.contains('hidden')) {
                        notificationDropdown.classList.add('hidden');
                    }

                    // Toggle user dropdown
                    userDropdown.classList.toggle('hidden');

                    // If showing dropdown and we have unread notifications, update badge
                    if (!userDropdown.classList.contains('hidden')) {
                        updateDropdownNotificationBadge();
                    }
                });
            }

            // Show notifications panel when clicking on "Notifications" in user dropdown
            if (viewNotificationsBtn && notificationDropdown) {
                viewNotificationsBtn.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Hide user dropdown
                    if (userDropdown) {
                        userDropdown.classList.add('hidden');
                    }

                    // Show notifications panel
                    notificationDropdown.classList.remove('hidden');

                    // Fetch notifications
                    fetchNotifications();

                    // Set timer to auto-mark as read after 5 seconds
                    autoMarkAsReadTimer = setTimeout(function() {
                        // Get count of unread notifications
                        const unreadCount = parseInt(notificationCount.textContent || '0');
                        if (unreadCount > 0) {
                            // Only call if there are unread notifications
                            markAllNotificationsAsRead(true); // true = silent mode (no notification)
                        }
                    }, 5000); // 5 seconds delay
                });
            }

            // Close notifications panel
            if (closeNotificationsBtn) {
                closeNotificationsBtn.addEventListener('click', function() {
                    notificationDropdown.classList.add('hidden');

                    // Clear timer if dropdown is closed
                    if (autoMarkAsReadTimer) {
                        clearTimeout(autoMarkAsReadTimer);
                        autoMarkAsReadTimer = null;
                    }
                });
            }

            // Close notifications panel when clicking on backdrop
            if (notificationBackdrop) {
                notificationBackdrop.addEventListener('click', function() {
                    notificationDropdown.classList.add('hidden');

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
            });

            // Mark all as read
            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    markAllNotificationsAsRead();

                    // Clear the auto-mark timer if it exists
                    if (autoMarkAsReadTimer) {
                        clearTimeout(autoMarkAsReadTimer);
                        autoMarkAsReadTimer = null;
                    }
                });
            }

            // Function to update the notification badge in the dropdown
            function updateDropdownNotificationBadge() {
                if (!dropdownNotificationBadge) return;

                const count = parseInt(notificationCount.textContent || '0');
                if (count > 0) {
                    dropdownNotificationBadge.textContent = count;
                    dropdownNotificationBadge.classList.remove('hidden');
                } else {
                    dropdownNotificationBadge.classList.add('hidden');
                }
            }

            // Fetch notifications from server
            window.fetchNotifications = function() {
                const user = firebase.auth().currentUser;
                if (!user) return;

                // Show loading in notification list
                if (notificationList) {
                    notificationList.innerHTML = `
                <div class="p-4 text-center">
                    <div class="inline-block animate-spin rounded-full h-5 w-5 border-t-2 border-b-2 border-primary"></div>
                    <p class="text-sm text-gray-500 mt-1">Loading notifications...</p>
                </div>
            `;
                }

                fetch('/flower-lab/ajax/get_notifications.php')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            updateNotificationUI(data.notifications);
                        } else {
                            throw new Error(data.message || 'Unknown error');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching notifications:', error);

                        if (notificationList) {
                            notificationList.innerHTML = `
                        <div class="p-4 text-center text-sm text-red-500">
                            Error loading notifications. Please try again.
                        </div>
                    `;
                        }
                    });
            }

            // Update notification UI
            function updateNotificationUI(notifications) {
                if (!notificationList) return;

                // Filter unread notifications
                const unreadNotifications = notifications.filter(n => !n.is_read);

                // Update notification count
                if (notificationCount && notificationIndicator) {
                    if (unreadNotifications.length > 0) {
                        notificationCount.textContent = unreadNotifications.length;
                        notificationIndicator.classList.remove('hidden');

                        // Also update dropdown badge
                        updateDropdownNotificationBadge();
                    } else {
                        notificationIndicator.classList.add('hidden');
                        if (dropdownNotificationBadge) {
                            dropdownNotificationBadge.classList.add('hidden');
                        }
                    }
                }

                // Update notification list
                if (notifications.length === 0) {
                    notificationList.innerHTML = `
                <div class="p-4 text-center text-sm text-gray-500">
                    No new notifications
                </div>
            `;
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
                <div class="p-3 border-b border-gray-100 hover:bg-gray-50 flex items-start ${notification.is_read ? 'opacity-70' : ''}">
                    <div class="p-2 rounded-full ${bgColor} ${textColor} mr-3">
                        <i data-lucide="${icon}" class="h-5 w-5"></i>
                    </div>
                    <div class="flex-grow">
                        <p class="text-sm font-medium text-gray-800">${notification.title}</p>
                        <p class="text-xs text-gray-500">${notification.message}</p>
                        <p class="text-xs text-gray-400 mt-1">${formattedDate}</p>
                    </div>
                    ${notification.is_read ? '' : `
                        <button class="text-xs text-primary-dark hover:underline" 
                                onclick="markNotificationAsRead(${notification.id}, this)">
                            Mark as read
                        </button>
                    `}
                </div>
            `;
        });
        
        notificationList.innerHTML = html;
        
        // Initialize Lucide icons
        lucide.createIcons({
            scope: notificationList
        });
    }
    
    // Check for notifications on page load (with a slight delay to ensure auth is ready)
    setTimeout(function() {
        const user = firebase.auth().currentUser;
        if (user) {
            fetchNotifications();
        } else {
            // Wait for auth state to change
            firebase.auth().onAuthStateChanged(function(user) {
                if (user) {
                    setTimeout(fetchNotifications, 1000); // Small delay to ensure user data is ready
                }
            });
        }
    }, 1000);
    
    // Check for new notifications every 30 seconds
    setInterval(function() {
        const user = firebase.auth().currentUser;
        if (user) {
            fetchNotifications();
        }
    }, 30000);
});


// Mark notification as read
function markNotificationAsRead(notificationId, button) {
    fetch('/flower-lab/ajax/mark_notification_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            notification_id: notificationId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI
            if (button) {
                const notificationDiv = button.closest('div');
                notificationDiv.classList.add('opacity-70');
                button.remove();
            }
            
            // Decrease notification count
            const notificationCount = document.getElementById('notification-count');
            const count = parseInt(notificationCount.textContent);
            if (count > 1) {
                notificationCount.textContent = count - 1;
            } else {
                // Hide indicator if this was the last unread notification
                document.getElementById('profile-notification-indicator').classList.add('hidden');
            }
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

// Mark all notifications as read
function markAllNotificationsAsRead(silent = false) {
    fetch('/flower-lab/ajax/mark_all_notifications_read.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hide notification indicator
            document.getElementById('profile-notification-indicator').classList.add('hidden');
            
            // Update all notification items in the list
            const markReadButtons = document.querySelectorAll('#notification-list button');
            markReadButtons.forEach(button => {
                const notificationDiv = button.closest('div');
                notificationDiv.classList.add('opacity-70');
                button.remove();
            });
            
            // Show success notification unless in silent mode
            if (!silent && typeof showModernNotification === 'function') {
                showModernNotification({
                    type: 'success',
                    title: 'Notifications Cleared',
                    message: 'All notifications have been marked as read'
                });
            }
        }
    })
    .catch(error => {
        console.error('Error marking all notifications as read:', error);
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