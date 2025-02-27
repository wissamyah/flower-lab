<?php
// Place this file in the root directory as admin_check.php
// Use it to diagnose admin status issues

require_once 'includes/db.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo '<div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 20px;">
            <h2>Error: Not Logged In</h2>
            <p>You must be logged in to check admin status.</p>
            <p><a href="/flower-lab/login.php" style="color: #721c24; font-weight: bold;">Go to Login Page</a></p>
          </div>';
    exit;
}

// Get current user
$user = getCurrentUser();
$userId = getCurrentUserId();

// HTML header
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Status Check</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
        .card { border: 1px solid #ddd; border-radius: 4px; padding: 15px; margin-bottom: 20px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .warning { background-color: #fff3cd; color: #856404; }
        .info { background-color: #d1ecf1; color: #0c5460; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .btn { display: inline-block; padding: 8px 16px; background-color: #821633; color: white; text-decoration: none; border-radius: 4px; }
        .btn:hover { background-color: #375645; }
    </style>
</head>
<body>
    <h1>Admin Status Diagnostic Tool</h1>';

// Display user information
echo '<div class="card info">
        <h2>User Information</h2>
        <table>
            <tr><th>User ID</th><td>' . htmlspecialchars($userId ?? 'Unknown') . '</td></tr>
            <tr><th>Name</th><td>' . htmlspecialchars($user['name'] ?? 'Unknown') . '</td></tr>
            <tr><th>Email</th><td>' . htmlspecialchars($user['email'] ?? 'Unknown') . '</td></tr>
            <tr><th>Firebase UID</th><td>' . htmlspecialchars($user['firebase_uid'] ?? 'Unknown') . '</td></tr>
            <tr><th>Admin Status in PHP</th><td>' . (isAdmin() ? 'Yes' : 'No') . '</td></tr>
            <tr><th>Admin Flag in DB</th><td>' . htmlspecialchars($user['is_admin'] ?? 'Not set') . '</td></tr>
        </table>
    </div>';

// Check admin status in database directly
$db = getDB();
$stmt = $db->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $dbUser = $result->fetch_assoc();
    $dbAdminStatus = $dbUser['is_admin'];
    
    echo '<div class="card ' . ($dbAdminStatus == 1 ? 'success' : 'warning') . '">
            <h2>Database Check</h2>
            <p>Admin Status in Database: <strong>' . ($dbAdminStatus == 1 ? 'Yes (1)' : 'No (0)') . '</strong></p>';
    
    if ($dbAdminStatus == 1 && !isAdmin()) {
        echo '<p><strong>Warning:</strong> Database shows you as admin but isAdmin() returns false. This could be an issue with type conversion or how the admin status is being checked.</p>';
    }
    echo '</div>';
} else {
    echo '<div class="card error">
            <h2>Database Error</h2>
            <p>Could not find user with ID ' . $userId . ' in the database.</p>
          </div>';
}

// Provide solutions
echo '<div class="card ' . (isAdmin() ? 'success' : 'warning') . '">
        <h2>Admin Status Check</h2>';

if (isAdmin()) {
    echo '<p>You are currently recognized as an admin! If you don\'t see the admin link in the navigation bar, try the following:</p>
          <ol>
            <li>Clear your browser cache and reload the page</li>
            <li>Check if the admin link is properly implemented in header.php</li>
            <li>Verify that CSS isn\'t hiding the admin link</li>
          </ol>';
} else {
    echo '<p>You are not currently recognized as an admin. Here\'s how to fix it:</p>
          <form method="post" action="">
            <button type="submit" name="make_admin" class="btn">Make Me Admin</button>
          </form>';
}
echo '</div>';

// Handle admin promotion if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['make_admin'])) {
    $updateStmt = $db->prepare("UPDATE users SET is_admin = 1 WHERE id = ?");
    $updateStmt->bind_param("i", $userId);
    
    if ($updateStmt->execute()) {
        echo '<div class="card success">
                <h2>Success!</h2>
                <p>Your account has been successfully upgraded to admin status!</p>
                <p><a href="/flower-lab/index.php" class="btn">Return to Home Page</a></p>
              </div>';
    } else {
        echo '<div class="card error">
                <h2>Error</h2>
                <p>Failed to update admin status: ' . $db->error . '</p>
              </div>';
    }
}

// Footer
echo '<p><a href="/flower-lab/index.php" class="btn">Return to Home Page</a></p>
    </body>
    </html>';