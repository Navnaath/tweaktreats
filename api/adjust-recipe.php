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

$recipeText = sanitizeInput($_POST['recipe_text'] ?? '');
$originalServings = intval($_POST['original_servings'] ?? 0);
$targetServings = intval($_POST['target_servings'] ?? 0);

if (empty($recipeText) || $originalServings <= 0 || $targetServings <= 0) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

try {
    // Calculate ratio
    $ratio = $targetServings / $originalServings;
    
    // Mock parsed ingredients (in a real app, this would use AI to parse the recipe text)
    $ingredients = [
        ['name' => 'all-purpose flour', 'amount' => 2 * $ratio, 'unit' => 'cups', 'grams' => 240 * $ratio],
        ['name' => 'granulated sugar', 'amount' => 1.5 * $ratio, 'unit' => 'cups', 'grams' => 300 * $ratio],
        ['name' => 'unsalted butter', 'amount' => 0.5 * $ratio, 'unit' => 'cup', 'grams' => 113 * $ratio],
        ['name' => 'eggs', 'amount' => 2 * $ratio, 'unit' => 'large', 'grams' => 100 * $ratio],
        ['name' => 'vanilla extract', 'amount' => 2 * $ratio, 'unit' => 'teaspoons'],
        ['name' => 'baking powder', 'amount' => 2 * $ratio, 'unit' => 'teaspoons'],
        ['name' => 'salt', 'amount' => 0.5 * $ratio, 'unit' => 'teaspoon'],
        ['name' => 'milk', 'amount' => 1 * $ratio, 'unit' => 'cup', 'grams' => 240 * $ratio],
    ];
    
    // Format ingredient amounts
    foreach ($ingredients as &$ingredient) {
        $ingredient['amount'] = round($ingredient['amount'] * 10) / 10;
        if (isset($ingredient['grams'])) {
            $ingredient['grams'] = round($ingredient['grams']);
        }
    }
    
    // Mock instructions
    $instructions = [
        "Preheat oven to 350°F (175°C).",
        "Mix " . number_format(2 * $ratio, 1) . " cups of flour with baking powder and salt.",
        "Cream " . number_format(0.5 * $ratio, 1) . " cup of butter with " . number_format(1.5 * $ratio, 1) . " cups of sugar.",
        "Add " . round(2 * $ratio) . " eggs and vanilla extract.",
        "Gradually add dry ingredients, alternating with milk.",
        "Pour batter into prepared pans.",
        "Bake for 30-35 minutes until a toothpick comes out clean.",
    ];
    
    echo json_encode([
        'success' => true,
        'ingredients' => $ingredients,
        'instructions' => $instructions,
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

