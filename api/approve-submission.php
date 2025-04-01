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

// Check if user is logged in and is admin
if (!isLoggedIn() || $_SESSION['user_email'] !== 'admin@example.com') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$submissionId = sanitizeInput($_POST['submission_id'] ?? '');

if (empty($submissionId)) {
    echo json_encode(['success' => false, 'message' => 'Submission ID is required']);
    exit;
}

try {
    // Approve submission
    $db->update('ingredient_densities', 
        ['verified' => 1, 'updated_at' => date('Y-m-d H:i:s')],
        'id = ?',
        [$submissionId]
    );
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

