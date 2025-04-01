<?php
// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Check if MongoDB extension is installed
if (!class_exists('MongoDB\Client')) {
    echo json_encode(['success' => false, 'message' => 'MongoDB PHP driver is not installed. Please install it to use community features.']);
    exit;
}

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$content = sanitizeInput($_POST['content'] ?? '');

if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Content is required']);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    $userName = $_SESSION['user_name'] ?? 'User';
    
    // Handle image upload
    $imageUrl = null;
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $imageUrl = 'uploads/' . $filename;
        }
    }
    
    // Create post in MongoDB
    $postId = createPost($userId, $userName, $content, $imageUrl);
    
    if (!$postId) {
        throw new Exception("Failed to create post");
    }
    
    echo json_encode(['success' => true, 'postId' => (string)$postId]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

