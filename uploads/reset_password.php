<?php
// /flower-lab/reset_password.php
require_once 'includes/db.php';

$pageTitle = 'Reset Password';
include 'includes/header.php';
?>

<div class="max-w-md mx-auto px-4 py-12">
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-4 bg-primary-light text-center">
            <h1 class="text-2xl font-semibold text-primary-dark">Reset Your Password</h1>
            <p class="text-gray-600 mt-1">We'll send you instructions to reset your password</p>
        </div>
        
        <div class="p-6">
            <div id="reset-password-container">
                <form id="reset-form" class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" id="email" name="email" class="w-full p-2 border border-gray-300 rounded focus:border-primary focus:ring-1 focus:ring-primary" required>
                    </div>
                    
                    <div id="reset-status" class="text-sm py-2 hidden">
                        <!-- Status messages will appear here -->
                    </div>
                    
                    <div>
                        <button type="submit" class="w-full px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark transition">
                            Send Reset Instructions
                        </button>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="/flower-lab/login.php" class="text-sm text-primary hover:underline">
                            Back to Sign In
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const resetForm = document.getElementById('reset-form');
        const resetStatus = document.getElementById('reset-status');
        
        resetForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            
            // Show loading message
            resetStatus.innerHTML = `
                <div class="text-gray-600 flex items-center">
                    <div class="inline-block animate-spin rounded-full h-4 w-4 border-t-2 border-b-2 border-primary mr-2"></div>
                    Sending reset instructions...
                </div>
            `;
            resetStatus.classList.remove('hidden');
            
            // Send password reset email via Firebase
            firebase.auth().sendPasswordResetEmail(email)
                .then(function() {
                    // Password reset email sent successfully
                    resetStatus.innerHTML = `
                        <div class="text-green-600">
                            Password reset email sent to ${email}. Please check your inbox.
                        </div>
                    `;
                })
                .catch(function(error) {
                    // Handle errors
                    console.error('Error sending reset email:', error);
                    
                    let errorMessage = 'Failed to send reset email. Please try again.';
                    
                    // More descriptive error messages
                    if (error.code === 'auth/user-not-found') {
                        errorMessage = 'No account found with this email address.';
                    } else if (error.code === 'auth/invalid-email') {
                        errorMessage = 'Please enter a valid email address.';
                    }
                    
                    resetStatus.innerHTML = `
                        <div class="text-red-600">
                            ${errorMessage}
                        </div>
                    `;
                });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>