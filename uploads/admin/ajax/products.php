<?php
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAdmin();

// Process AJAX request
header('Content-Type: application/json');

$db = getDB();

// Get all products
$query = "SELECT * FROM items ORDER BY id DESC";
$result = $db->query($query);
$products = [];

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode([
    'success' => true,
    'products' => $products
]);