<?php
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
                    // User successfully signed in.
                    // Return type determines whether we continue the redirect automatically
                    // or whether we leave that to developer to handle.
                    return false;
                },
                uiShown: function() {
                    // The widget is rendered.
                    // Hide the loader.
                    document.getElementById('loader').style.display = 'none';
                }
            },
            // Will use popup for IDP Providers sign-in flow instead of the default, redirect.
            signInFlow: 'popup',
            signInSuccessUrl: '/flower-lab/',
            signInOptions: [
                // Leave the lines as is for the providers you want to offer your users.
                firebase.auth.GoogleAuthProvider.PROVIDER_ID,
                firebase.auth.EmailAuthProvider.PROVIDER_ID,
            ],
            // Terms of service url.
            tosUrl: '#',
            // Privacy policy url.
            privacyPolicyUrl: '#'
        };
        
        // The start method will wait until the DOM is loaded.
        ui.start('#firebaseui-auth-container', uiConfig);
    });
</script>

<?php include 'includes/footer.php'; ?>