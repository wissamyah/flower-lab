<?php
// /flower-lab/login.php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isset($_SESSION['redirect_after_login'])) {
        $redirect = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
        header('Location: ' . $redirect);
    } else {
        header('Location: /flower-lab/');
    }
    exit;
}

$pageTitle = 'Login';
include 'includes/header.php';
?>

<div class="max-w-md mx-auto px-4 py-12">
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-4 bg-primary-light text-center">
            <h1 class="text-2xl font-semibold text-primary-dark">Welcome to The Flower Lab</h1>
            <p class="text-gray-600 mt-1">Sign in to your account</p>
        </div>
        
        <div class="p-6">
            <div id="firebaseui-auth-container"></div>
            <div id="loader" class="text-center py-4">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary"></div>
                <p class="mt-2 text-sm text-gray-500">Loading authentication options...</p>
            </div>
            
            <div class="mt-6 text-sm text-center text-gray-500">
                <p>Don't have an account? Sign up using the options above.</p>
                <p class="mt-2">By signing in, you agree to our Terms of Service and Privacy Policy.</p>
            </div>
        </div>
    </div>
</div>

<!-- Firebase UI -->
<script src="https://www.gstatic.com/firebasejs/ui/6.0.1/firebase-ui-auth.js"></script>
<link type="text/css" rel="stylesheet" href="https://www.gstatic.com/firebasejs/ui/6.0.1/firebase-ui-auth.css" />

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize the FirebaseUI Widget using Firebase
        var ui = new firebaseui.auth.AuthUI(firebase.auth());
        
        var uiConfig = {
            callbacks: {
                signInSuccessWithAuthResult: function(authResult, redirectUrl) {
                    // User successfully signed in
                    var user = authResult.user;
                    
                    // Manually trigger our sync function
                    if (typeof syncUserWithDatabase === 'function') {
                        syncUserWithDatabase(user);
                    } else {
                        // Fallback if the function isn't loaded
                        console.log("User authenticated, redirecting to home...");
                        window.location.href = '/flower-lab/';
                    }
                    
                    // Return false to prevent automatic redirect
                    return false;
                },
                uiShown: function() {
                    // The widget is rendered
                    document.getElementById('loader').style.display = 'none';
                }
            },
            // Use popup for IDP Providers sign-in flow
            signInFlow: 'popup',
            
            // Allow both new user creation & existing user sign-in
            signInOptions: [
                {
                    provider: firebase.auth.EmailAuthProvider.PROVIDER_ID,
                    requireDisplayName: true,
                    
                    // CRITICAL FIX: Allow account creation + signin
                    signInMethod: firebase.auth.EmailAuthProvider.EMAIL_PASSWORD_SIGN_IN_METHOD,
                    
                    // Enable password reset
                    forgotPasswordLink: '/flower-lab/reset_password.php',
                    
                    // CRITICAL: Allow user creation
                    disableSignUp: {
                        status: false
                    }
                },
                firebase.auth.GoogleAuthProvider.PROVIDER_ID
            ],
            
            // Disable credential helper
            credentialHelper: firebaseui.auth.CredentialHelper.NONE,
            
            // Terms of service url
            tosUrl: '#',
            
            // Privacy policy url
            privacyPolicyUrl: '#',
            
            // Auto upgrade anonymous users - important!
            autoUpgradeAnonymousUsers: true
        };
        
        // The start method will wait until the DOM is loaded.
        ui.start('#firebaseui-auth-container', uiConfig);
    });
</script>

<?php include 'includes/footer.php'; ?>