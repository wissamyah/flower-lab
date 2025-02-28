<?php
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAdmin();

// Process AJAX request
header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['delivery_rate'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data'
    ]);
    exit;
}

$deliveryRate = (float)$input['delivery_rate'];
$freeDeliveryThreshold = isset($input['free_delivery_threshold']) ? (float)$input['free_delivery_threshold'] : 0;

// Make sure delivery rate is non-negative
if ($deliveryRate < 0) {
    $deliveryRate = 0;
}

// Make sure threshold is non-negative
if ($freeDeliveryThreshold < 0) {
    $freeDeliveryThreshold = 0;
}

$db = getDB();

// Check if settings table exists, create if not
$checkTableQuery = "SHOW TABLES LIKE 'settings'";
$tableResult = $db->query($checkTableQuery);

if ($tableResult->num_rows === 0) {
    // Create settings table
    $createTableQuery = "CREATE TABLE `settings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `setting_key` varchar(50) NOT NULL,
        `setting_value` text NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if (!$db->query($createTableQuery)) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create settings table: ' . $db->error
        ]);
        exit;
    }
}

// Start transaction for atomicity
$db->begin_transaction();

try {
    // Save delivery rate
    $saveQuery = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                  ON DUPLICATE KEY UPDATE setting_value = ?";
    
    $stmt = $db->prepare($saveQuery);
    
    // Save delivery rate
    $key = 'delivery_rate';
    $value = number_format($deliveryRate, 2, '.', '');
    $stmt->bind_param("sss", $key, $value, $value);
    $stmt->execute();
    
    // Save free delivery threshold
    $key = 'free_delivery_threshold';
    $value = number_format($freeDeliveryThreshold, 2, '.', '');
    $stmt->bind_param("sss", $key, $value, $value);
    $stmt->execute();
    
    // Commit transaction
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Delivery settings saved successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback in case of error
    $db->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'Error saving settings: ' . $e->getMessage()
    ]);
}