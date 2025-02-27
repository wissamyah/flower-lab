<?php
require_once 'includes/db.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $name = $_POST['name'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate input
    if (empty($email) || empty($name) || empty($password)) {
        $message = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format.';
    } elseif ($password !== $confirmPassword) {
        $message = 'Passwords do not match.';
    } else {
        $db = getDB();
        
        // Check if user already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // User exists, update to admin
            $user = $result->fetch_assoc();
            $userId = $user['id'];
            
            $updateStmt = $db->prepare("UPDATE users SET is_admin = 1, name = ? WHERE id = ?");
            $updateStmt->bind_param("si", $name, $userId);
            
            if ($updateStmt->execute()) {
                $message = 'Existing user updated to admin successfully!';
                $success = true;
            } else {
                $message = 'Error updating user: ' . $db->error;
            }
        } else {
            // Create new admin user (in a real app, we'd use Firebase, but this is for setup)
            $firebase_uid = 'setup_' . uniqid();
            $phone_number = ''; // Not required for admin setup
            
            $insertStmt = $db->prepare("INSERT INTO users (firebase_uid, email, name, phone_number, is_admin) VALUES (?, ?, ?, ?, 1)");
            $insertStmt->bind_param("ssss", $firebase_uid, $email, $name, $phone_number);
            
            if ($insertStmt->execute()) {
                $message = 'Admin user created successfully!';
                $success = true;
            } else {
                $message = 'Error creating user: ' . $db->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup - The Flower Lab</title>
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
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-4 bg-primary-light text-center">
            <h1 class="text-2xl font-bold text-primary-dark">The Flower Lab Admin Setup</h1>
        </div>
        
        <div class="p-6">
            <?php if ($message): ?>
                <div class="mb-4 p-3 rounded <?= $success ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' ?>">
                    <?= $message ?>
                    
                    <?php if ($success): ?>
                        <p class="mt-2 text-sm">
                            <a href="admin/index.php" class="underline">Go to Admin Dashboard</a>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" id="email" name="email" class="w-full p-2 border border-gray-300 rounded" required>
                </div>
                
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" id="name" name="name" class="w-full p-2 border border-gray-300 rounded" required>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="password" name="password" class="w-full p-2 border border-gray-300 rounded" required>
                </div>
                
                <div class="mb-6">
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="w-full p-2 border border-gray-300 rounded" required>
                </div>
                
                <button type="submit" class="w-full px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark">
                    Create Admin User
                </button>
            </form>
            
            <div class="mt-4 text-center text-sm text-gray-500">
                <p>This page is for initial setup only. After creating an admin user, you should secure or delete this file.</p>
            </div>
        </div>
    </div>
</body>
</html>