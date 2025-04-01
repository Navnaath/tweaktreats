<?php
// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Require login
requireLogin();

// Get user favorites
$userId = $_SESSION['user_id'];
$favorites = getUserFavorites($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorites | TweakTreats</title>
    <meta name="description" content="Your favorite recipes">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <h1 class="page-title">My Favorite Recipes</h1>

            <?php if (empty($favorites)): ?>
                <div class="empty-favorites">
                    <i class="fas fa-heart"></i>
                    <h2>No favorites yet</h2>
                    <p>You haven't added any recipes to your favorites yet.</p>
                    <a href="recipes.php" class="btn btn-primary">Browse Recipes</a>
                </div>
            <?php else: ?>
                <div class="recipe-grid">
                    <?php foreach ($favorites as $recipe): ?>
                        <div class="recipe-card">
                            <div class="recipe-image">
                                <?php if ($recipe['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="<?php echo htmlspecialchars($recipe['name']); ?>">
                                <?php else: ?>
                                    <div class="recipe-placeholder">🍰</div>
                                <?php endif; ?>
                            </div>
                            <div class="recipe-header">
                                <h3><?php echo htmlspecialchars($recipe['name']); ?></h3>
                            </div>
                            <div class="recipe-content">
                                <p class="recipe-description"><?php echo htmlspecialchars($recipe['description']); ?></p>
                                <div class="recipe-meta">
                                    <div class="recipe-info">
                                        <span>⏱️ <?php echo $recipe['prep_time'] + $recipe['cook_time']; ?> min</span>
                                        <span>👥 <?php echo $recipe['servings']; ?> servings</span>
                                    </div>
                                    <button class="btn btn-icon btn-outline favorite-btn active" data-id="<?php echo $recipe['id']; ?>">
                                        <i class="fas fa-heart"></i>
                                    </button>
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

