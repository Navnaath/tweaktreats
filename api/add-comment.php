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

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$postId = sanitizeInput($_POST['post_id'] ?? '');
$content = sanitizeInput($_POST['content'] ?? '');

if (empty($postId) || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Post ID and content are required']);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    $userName = $_SESSION['user_name'] ?? 'User';
    
    // Add comment
    $commentId = addComment($postId, $userId, $userName, $content);
    
    if (!$commentId) {
        throw new Exception("Failed to add comment");
    }
    
    echo json_encode(['success' => true, 'commentId' => (string)$commentId]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

