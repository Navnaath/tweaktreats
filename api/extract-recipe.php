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

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No image uploaded or upload error']);
    exit;
}

try {
    // In a real app, this would use OCR to extract text from the image
    // For demo purposes, we'll return a mock result
    
    $mockText = "Classic Vanilla Cake\n\nIngredients:\n- 2 cups all-purpose flour\n- 1 1/2 cups granulated sugar\n- 1/2 cup unsalted butter\n- 2 large eggs\n- 2 teaspoons vanilla extract\n- 2 teaspoons baking powder\n- 1/2 teaspoon salt\n- 1 cup milk\n\nInstructions:\n1. Preheat oven to 350°F.\n2. Mix dry ingredients in a bowl.\n3. Cream butter and sugar until fluffy.\n4. Add eggs and vanilla.\n5. Gradually add dry ingredients and milk.\n6. Pour into cake pans and bake for 30-35 minutes.";
    
    echo json_encode([
        'success' => true,
        'text' => $mockText,
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

