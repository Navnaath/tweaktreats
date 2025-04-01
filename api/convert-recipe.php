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

$dishName = sanitizeInput($_POST['dish_name'] ?? '');
$recipeText = sanitizeInput($_POST['recipe_text'] ?? '');
$originalServings = intval($_POST['original_servings'] ?? 0);
$targetServings = intval($_POST['target_servings'] ?? 0);

if (empty($dishName) || empty($recipeText) || $originalServings <= 0 || $targetServings <= 0) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

try {
    // Convert recipe
    $result = convertRecipeServings($recipeText, $originalServings, $targetServings);
    
    // Log activity in MongoDB
    $userId = $_SESSION['user_id'];
    $userName = $_SESSION['user_name'] ?? 'User';
    
    $client = getMongoDBConnection();
    if ($client) {
        $collection = $client->tweaktreats->activity;
        $collection->insertOne([
            'userId' => (string)$userId,
            'userName' => $userName,
            'type' => 'conversion',
            'description' => "Converted $dishName recipe",
            'details' => [
                'dishName' => $dishName,
                'originalServings' => $originalServings,
                'targetServings' => $targetServings
            ],
            'icon' => 'utensils',
            'createdAt' => new MongoDB\BSON\UTCDateTime()
        ]);
    }
    
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

