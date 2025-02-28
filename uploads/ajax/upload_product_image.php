<?php
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();

// Process AJAX request
header('Content-Type: application/json');

// Check if file was uploaded
if (!isset($_FILES['product_image'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No file uploaded'
    ]);
    exit;
}

// Create upload directory if it doesn't exist
$uploadDir = dirname(__DIR__) . '/uploads/products/';
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create upload directory'
        ]);
        exit;
    }
}

// Get file info
$file = $_FILES['product_image'];
$fileName = $file['name'];
$fileTmpName = $file['tmp_name'];
$fileSize = $file['size'];
$fileError = $file['error'];

// Check for errors
if ($fileError !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
    ];
    
    echo json_encode([
        'success' => false,
        'message' => $errorMessages[$fileError] ?? 'Unknown upload error'
    ]);
    exit;
}

// Validate file size (max 2MB)
if ($fileSize > 2 * 1024 * 1024) {
    echo json_encode([
        'success' => false,
        'message' => 'File size too large (max 2MB)'
    ]);
    exit;
}

// Get file extension and validate file type
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if (!in_array($fileExt, $allowedExtensions)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid file type. Only JPG, JPEG, PNG, GIF, and WEBP are allowed.'
    ]);
    exit;
}

// Generate unique filename to prevent overwriting
$newFileName = uniqid('product_') . '.' . $fileExt;
$uploadPath = $uploadDir . $newFileName;

// Move uploaded file
if (move_uploaded_file($fileTmpName, $uploadPath)) {
    // Success! Return the relative path to be stored in the database
    $relativePath = '/flower-lab/uploads/products/' . $newFileName;
    
    echo json_encode([
        'success' => true,
        'message' => 'File uploaded successfully',
        'file_path' => $relativePath
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to move uploaded file'
    ]);
}