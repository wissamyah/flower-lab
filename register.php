<?php
// /flower-lab/register.php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /flower-lab/');
    exit;
}

$pageTitle = 'Create Account';
include 'includes/header.php';
?>

<div class="max-w-md mx-auto px-4 py-12">
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-4 bg-primary-light text-center">
            <h1 class="text-2xl font-semibold text-primary-dark">Create Your Account</h1>
            <p class="text-gray-600 mt-1">Join The Flower Lab</p>
        </div>
        
        <div class="p-6">
            <form id="register-form" class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" id="name" class="w-full p-2 border border-gray-300 rounded focus:border-primary focus:ring-1 focus:ring-primary" required>
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" id="email" class="w-full p-2 border border-gray-300 rounded focus:border-primary focus:ring-1 focus:ring-primary" required>
                </div>
                
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="tel" id="phone" class="w-full p-2 border border-gray-300 rounded focus:border-primary focus:ring-1 focus:ring-primary" required>
                    <p class="text-xs text-gray-500 mt-1">Required for delivery notifications</p>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="password" class="w-full p-2 border border-gray-300 rounded focus:border-primary focus:ring-1 focus:ring-primary" required minlength="6">
                    <p class="text-xs text-gray-500 mt-1">Password must be at least 6 characters long</p>
                </div>
                
                <div>
                    <label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" id="confirm-password" class="w-full p-2 border border-gray-300 rounded focus:border-primary focus:ring-1 focus:ring-primary" required>
                </div>
                
                <div id="password-match-error" class="text-sm text-red-500 hidden">
                    Passwords do not match
                </div>
                
                <div id="register-error" class="text-sm text-red-500 hidden"></div>
                
                <div>
                    <button type="submit" class="w-full px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark transition">
                        Create Account
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
                    <button type="button" id="google-signup" class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
                            <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"></path>
                            <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"></path>
                            <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"></path>
                            <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"></path>
                        </svg>
                        Sign up with Google
                    </button>
                </div>
            </div>
            
            <div class="mt-6 text-center text-sm">
                <p>
                    Already have an account?
                    <a href="/flower-lab/direct_login.php" class="text-primary hover:underline">
                        Sign in
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Register form handling
        const registerForm = document.getElementById('register-form');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm-password');
        const passwordMatchError = document.getElementById('password-match-error');
        const registerError = document.getElementById('register-error');
        
        // Check password match on input
        confirmPassword.addEventListener('input', function() {
            if (password.value !== confirmPassword.value) {
                passwordMatchError.classList.remove('hidden');
            } else {
                passwordMatchError.classList.add('hidden');
            }
        });
        
        // Handle form submission
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Hide previous errors
            passwordMatchError.classList.add('hidden');
            registerError.classList.add('hidden');
            
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const pwd = password.value;
            const confirmPwd = confirmPassword.value;
            
            // Validate phone number
            if (!phone.trim()) {
                registerError.textContent = 'Phone number is required';
                registerError.classList.remove('hidden');
                return;
            }
            
            // Validate passwords match
            if (pwd !== confirmPwd) {
                passwordMatchError.classList.remove('hidden');
                return;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="inline-block animate-spin mr-2">↻</span> Creating Account...';
            submitBtn.disabled = true;
            
            // Create user account
            firebase.auth().createUserWithEmailAndPassword(email, pwd)
                .then((userCredential) => {
                    // Update display name
                    return userCredential.user.updateProfile({
                        displayName: name
                    }).then(() => {
                        return userCredential.user;
                    });
                })
                .then((user) => {
                    // Account created successfully
                    console.log('Account created for:', email);
                    
                    // Sync with database including phone number
                    syncUserWithDatabase({
                        uid: user.uid,
                        email: user.email,
                        displayName: name,
                        phoneNumber: phone
                    });
                    
                    // Redirect to home page
                    window.location.href = '/flower-lab/';
                })
                .catch((error) => {
                    console.error('Registration error:', error);
                    
                    // Reset button
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    // Show appropriate error message
                    let errorMessage = 'Failed to create account. Please try again.';
                    
                    if (error.code === 'auth/email-already-in-use') {
                        errorMessage = 'This email is already in use. Please sign in instead.';
                    } else if (error.code === 'auth/invalid-email') {
                        errorMessage = 'Please enter a valid email address.';
                    } else if (error.code === 'auth/weak-password') {
                        errorMessage = 'Password is too weak. Please choose a stronger password.';
                    }
                    
                    registerError.textContent = errorMessage;
                    registerError.classList.remove('hidden');
                });
        });
        
        // Google sign-up with proper redirect
        const googleSignUpButton = document.getElementById('google-signup');
        
        googleSignUpButton.addEventListener('click', function() {
            // Show loading state
            const loadingElement = document.createElement('div');
            loadingElement.id = 'loading-indicator';
            loadingElement.className = 'fixed inset-0 bg-black bg-opacity-25 flex items-center justify-center z-50';
            loadingElement.innerHTML = '<div class="bg-white p-4 rounded-lg shadow-lg"><p class="text-gray-800">Signing in with Google...</p></div>';
            document.body.appendChild(loadingElement);
            
            const provider = new firebase.auth.GoogleAuthProvider();
            
            firebase.auth().signInWithPopup(provider)
                .then((result) => {
                    // Google sign-in successful
                    const user = result.user;
                    
                    // Update loading message
                    loadingElement.innerHTML = '<div class="bg-white p-4 rounded-lg shadow-lg"><p class="text-gray-800">Login successful! Redirecting...</p></div>';
                    
                    // Sync with database and pass override to skip redirect check
                    syncUserWithDatabase({
                        uid: user.uid,
                        email: user.email,
                        displayName: user.displayName,
                        phoneNumber: user.phoneNumber || ''
                    });
                    
                    // Explicit redirect to home page
                    setTimeout(() => {
                        window.location.href = '/flower-lab/'; 
                    }, 500);
                })
                .catch((error) => {
                    console.error('Google sign-up error:', error);
                    
                    // Remove loading indicator
                    if (loadingElement) {
                        document.body.removeChild(loadingElement);
                    }
                    
                    // Show error
                    registerError.textContent = 'Google sign-up failed. Please try again.';
                    registerError.classList.remove('hidden');
                });
        });
    });
    
    // Custom sync function to handle phone number
    function syncUserWithDatabase(userData) {
        fetch("/flower-lab/ajax/firebase_sync.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                uid: userData.uid,
                email: userData.email,
                displayName: userData.displayName || "",
                phoneNumber: userData.phoneNumber || "",
                skipRedirect: false  // Always redirect to home page
            }),
        })
        .then(response => response.json())
        .then(data => {
            console.log("User synced with database:", data);
            
            // Explicit redirect to home page
            window.location.href = '/flower-lab/';
        })
        .catch(error => {
            console.error("Error syncing user:", error);
            // Still redirect to home page on error
            window.location.href = '/flower-lab/';
        });
    }
</script>

<?php include 'includes/footer.php'; ?>