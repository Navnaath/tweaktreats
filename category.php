<?php
// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get category ID from URL
$categoryId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get category details
$category = null;
$recipes = [];

try {
    if ($categoryId > 0) {
        $category = $db->selectOne("SELECT * FROM recipe_categories WHERE id = ?", [$categoryId]);
        
        if ($category) {
            // Get recipes for this category
            $recipes = $db->select(
                "SELECT * FROM recipes WHERE category_id = ? ORDER BY name",
                [$categoryId]
            );
        }
    }
    
    if (!$category) {
        // Category not found, redirect to recipes page
        header('Location: recipes.php');
        exit;
    }
} catch (Exception $e) {
    displayAlert('Error loading category: ' . $e->getMessage(), 'error');
    header('Location: recipes.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['name']); ?> Recipes | TweakTreats</title>
    <meta name="description" content="Browse <?php echo htmlspecialchars($category['name']); ?> recipes">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <h1 class="page-title"><?php echo htmlspecialchars($category['name']); ?> Recipes</h1>

            <?php if (empty($recipes)): ?>
                <div class="empty-state">
                    <p>No recipes found in this category yet.</p>
                    <a href="recipes.php" class="btn btn-primary">Browse All Recipes</a>
                </div>
            <?php else: ?>
                <div class="recipe-grid">
                    <?php foreach ($recipes as $recipe): ?>
                        <div class="recipe-card">
                            <div class="recipe-image">
                                <?php if ($recipe['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($recipe['name']); ?>">
                                <?php else: ?>
                                    <div class="recipe-placeholder">🍰</div>
                                <?php endif; ?>
                            </div>
                            <div class="recipe-header">
                                <h3><?php echo htmlspecialchars($recipe['name']); ?></h3>
                            </div>
                            <div class="recipe-content">
                                <p class="recipe-description">
                                    <?php echo htmlspecialchars($recipe['description']); ?>
                                </p>
                                <div class="recipe-meta">
                                    <div class="recipe-info">
                                        <span>⏱️ <?php echo $recipe['prep_time'] + $recipe['cook_time']; ?> min</span>
                                        <span>👥 <?php echo $recipe['servings']; ?> servings</span>
                                    </div>
                                    <?php if (isLoggedIn()): ?>
                                        <?php 
                                        $isFavorited = false;
                                        if (isset($_SESSION['user_id'])) {
                                            $favorite = $db->selectOne(
                                                "SELECT id FROM user_favorites WHERE user_id = ? AND recipe_id = ?",
                                                [$_SESSION['user_id'], $recipe['id']]
                                            );
                                            $isFavorited = !empty($favorite);
                                        }
                                        ?>
                                        <button class="btn btn-icon btn-outline favorite-btn <?php echo $isFavorited ? 'active' : ''; ?>" 
                                                data-id="<?php echo $recipe['id']; ?>">
                                            <i class="<?php echo $isFavorited ? 'fas' : 'far'; ?> fa-heart"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>

