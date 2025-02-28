// Path: /flower-lab/utility.js
// Create this new file to add utility functions

/**
 * Utility functions for Flower Lab
 */

// Check if current page is a login/registration page
function isAuthPage() {
    return window.location.pathname.includes('/login.php') ||
        window.location.pathname.includes('/register.php') ||
        window.location.pathname.includes('/reset_password.php') ||
        window.location.pathname.includes('/direct_login.php');
}

// Check if user is currently logged in
function isUserLoggedIn() {
    // Check Firebase auth
    return firebase.auth().currentUser !== null;
}

// Debug helper function
function debugUI() {
    console.log('------------ UI DEBUG INFO ------------');
    console.log('Current page:', window.location.pathname);
    console.log('Is auth page:', isAuthPage());
    console.log('User logged in:', isUserLoggedIn());

    // Check profile elements
    const profileIcon = document.getElementById('profile-icon');
    const userDropdown = document.getElementById('user-dropdown');

    console.log('Profile Icon:', profileIcon ? 'Found' : 'Not found');
    console.log('User Dropdown:', userDropdown ? 'Found' : 'Not found');

    // Check notification elements
    const notificationBtn = document.getElementById('view-notifications');
    const notificationDropdown = document.getElementById('notification-dropdown');

    console.log('Notification Button:', notificationBtn ? 'Found' : 'Not found');
    console.log('Notification Dropdown:', notificationDropdown ? 'Found' : 'Not found');
    console.log('--------------------------------------');
}