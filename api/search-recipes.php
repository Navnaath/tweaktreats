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
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$query = sanitizeInput($_GET['query'] ?? '');

if (empty($query)) {
    echo json_encode(['success' => false, 'message' => 'Query is required']);
    exit;
}

try {
    // Search recipes
    $recipes = searchRecipes($query);
    
    // Format response
    $formattedRecipes = [];
    
    foreach ($recipes as $recipe) {
        // Parse ingredients and instructions from JSON
        $ingredients = json_decode($recipe['ingredients'], true) ?? [];
        $instructions = json_decode($recipe['instructions'], true) ?? [];
        
        $formattedRecipes[] = [
            'id' => $recipe['id'],
            'name' => $recipe['name'],
            'description' => $recipe['description'],
            'ingredients' => $ingredients,
            'instructions' => $instructions,
            'prepTime' => $recipe['prep_time'],
            'cookTime' => $recipe['cook_time'],
            'servings' => $recipe['servings'],
        ];
    }
    
    echo json_encode($formattedRecipes);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

