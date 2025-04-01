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

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_FILES['audio']) || $_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No audio uploaded or upload error']);
    exit;
}

try {
    // In a real app, this would use speech-to-text to transcribe the audio
    // For demo purposes, we'll return a mock result
    
    $mockText = "For my chocolate chip cookie recipe, you'll need 2 and a quarter cups of flour, 1 teaspoon of baking soda, 1 teaspoon of salt, 1 cup of butter, three quarters cup of white sugar, three quarters cup of brown sugar, 2 eggs, 2 teaspoons of vanilla, and 2 cups of chocolate chips. Mix the dry ingredients first, then cream the butter and sugars. Add the eggs and vanilla, then the dry ingredients, and finally fold in the chocolate chips. Bake at 375 degrees for about 10 minutes.";
    
    echo json_encode([
        'success' => true,
        'text' => $mockText,
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

