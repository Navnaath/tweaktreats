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

$ingredientName = sanitizeInput($_POST['ingredient_name'] ?? '');
$densityCup = floatval($_POST['density_cup'] ?? 0);
$densityTablespoon = floatval($_POST['density_tablespoon'] ?? 0);
$densityTeaspoon = floatval($_POST['density_teaspoon'] ?? 0);

if (empty($ingredientName) || $densityCup <= 0 || $densityTablespoon <= 0 || $densityTeaspoon <= 0) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

try {
    // Submit density correction
    $userId = $_SESSION['user_id'];
    
    $db->insert('ingredient_densities', [
        'name' => $ingredientName,
        'density_grams_per_cup' => $densityCup,
        'density_grams_per_tablespoon' => $densityTablespoon,
        'density_grams_per_teaspoon' => $densityTeaspoon,
        'submitted_by' => $userId,
        'verified' => 0,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

