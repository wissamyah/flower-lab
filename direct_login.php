<?php
// /flower-lab/direct_login.php
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

$pageTitle = 'Sign In';
include 'includes/header.php';
?>

<div class="max-w-md mx-auto px-4 py-12">
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-4 bg-primary-light text-center">
            <h1 class="text-2xl font-semibold text-primary-dark">Welcome to The Flower Lab</h1>
            <p class="text-gray-600 mt-1">Sign in to your account</p>
        </div>
        
        <div class="p-6">
            <form id="login-form" class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" id="email" class="w-full p-2 border border-gray-300 rounded focus:border-primary focus:ring-1 focus:ring-primary" required>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="password" class="w-full p-2 border border-gray-300 rounded focus:border-primary focus:ring-1 focus:ring-primary" required>
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" type="checkbox" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                        <label for="remember-me" class="ml-2 block text-sm text-gray-700">
                            Remember me
                        </label>
                    </div>
                    
                    <div class="text-sm">
                        <a href="/flower-lab/reset_password.php" class="text-primary hover:underline">
                            Forgot your password?
                        </a>
                    </div>
                </div>
                
                <div id="login-error" class="text-sm text-red-500 hidden"></div>
                
                <div>
                    <button type="submit" class="w-full px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark transition">
                        Sign In
                    </button>
                </div>
            </form>
            
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">Or continue with</span>
                    </div>
                </div>
                
                <div class="mt-6">
                    <button type="button" id="google-signin" class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                            <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"></path>
                            <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"></path>
                            <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"></path>
                            <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"></path>
                        </svg>
                        Sign in with Google
                    </button>
                </div>
            </div>
            
            <div class="mt-6 text-center text-sm">
                <p>
                    Don't have an account?
                    <a href="/flower-lab/register.php" class="text-primary hover:underline">
                        Create an account
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle login form submission
        const loginForm = document.getElementById('login-form');
        const loginError = document.getElementById('login-error');
        
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const rememberMe = document.getElementById('remember-me').checked;
            
            // Clear previous errors
            loginError.classList.add('hidden');
            
            // Set persistence based on remember me checkbox
            const persistence = rememberMe 
                ? firebase.auth.Auth.Persistence.LOCAL 
                : firebase.auth.Auth.Persistence.SESSION;
            
            firebase.auth().setPersistence(persistence)
                .then(() => {
                    // Sign in with email and password
                    return firebase.auth().signInWithEmailAndPassword(email, password);
                })
                .then((userCredential) => {
                    // Sign in successful
                    const user = userCredential.user;
                    
                    // Sync with database
                    if (typeof syncUserWithDatabase === 'function') {
                        syncUserWithDatabase(user);
                    } else {
                        // Fallback if the function isn't available
                        window.location.href = '/flower-lab/';
                    }
                })
                .catch((error) => {
                    console.error('Login error:', error);
                    
                    // Show error message
                    let errorMessage = 'Failed to sign in. Please check your credentials.';
                    
                    if (error.code === 'auth/wrong-password') {
                        errorMessage = 'Incorrect password. Please try again or reset your password.';
                    } else if (error.code === 'auth/user-not-found') {
                        errorMessage = 'No account found with this email.';
                    } else if (error.code === 'auth/invalid-email') {
                        errorMessage = 'Please enter a valid email address.';
                    } else if (error.code === 'auth/user-disabled') {
                        errorMessage = 'This account has been disabled. Please contact support.';
                    } else if (error.code === 'auth/too-many-requests') {
                        errorMessage = 'Too many unsuccessful login attempts. Please try again later.';
                    }
                    
                    loginError.textContent = errorMessage;
                    loginError.classList.remove('hidden');
                });
        });
        
        // Handle Google sign-in
        const googleSignInButton = document.getElementById('google-signin');
        
        googleSignInButton.addEventListener('click', function() {
            const provider = new firebase.auth.GoogleAuthProvider();
            
            firebase.auth().signInWithPopup(provider)
                .then((result) => {
                    // Google sign-in successful
                    const user = result.user;
                    
                    // Sync with database
                    if (typeof syncUserWithDatabase === 'function') {
                        syncUserWithDatabase(user);
                    } else {
                        // Fallback
                        window.location.href = '/flower-lab/';
                    }
                })
                .catch((error) => {
                    console.error('Google sign-in error:', error);
                    
                    // Show error
                    loginError.textContent = 'Google sign-in failed. Please try again.';
                    loginError.classList.remove('hidden');
                });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>