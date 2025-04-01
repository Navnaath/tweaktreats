<?php
// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get categories
$categories = $db->select("SELECT * FROM recipe_categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipes | TweakTreats</title>
    <meta name="description" content="Browse and search for recipes">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <h1 class="page-title">Recipes</h1>

            <div class="tabs">
                <div class="tab-list">
                    <button class="tab-button active" data-tab="search">Search Recipes</button>
                    <button class="tab-button" data-tab="browse">Browse Categories</button>
                </div>
                
                <div class="tab-content active" id="search-tab">
                    <div class="recipe-search-layout">
                        <aside class="recipe-filters">
                            <div class="filter-section">
                                <h3>Filters</h3>
                                
                                <div class="accordion">
                                    <div class="accordion-item">
                                        <button class="accordion-header">
                                            Category
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                        <div class="accordion-content">
                                            <?php foreach ($categories as $category): ?>
                                            <div class="checkbox-group">
                                                <input type="checkbox" id="category-<?php echo $category['id']; ?>" name="category[]" value="<?php echo $category['id']; ?>">
                                                <label for="category-<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></label>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="accordion-item">
                                        <button class="accordion-header">
                                            Time
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                        <div class="accordion-content">
                                            <div class="range-slider">
                                                <label>Prep Time: <span id="prep-time-value">60</span> min</label>
                                                <input type="range" id="prep-time" min="5" max="120" step="5" value="60">
                                            </div>
                                            
                                            <div class="range-slider">
                                                <label>Cook Time: <span id="cook-time-value">60</span> min</label>
                                                <input type="range" id="cook-time" min="5" max="120" step="5" value="60">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="accordion-item">
                                        <button class="accordion-header">
                                            Difficulty
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                        <div class="accordion-content">
                                            <div class="checkbox-group">
                                                <input type="checkbox" id="difficulty-easy" name="difficulty[]" value="easy">
                                                <label for="difficulty-easy">Easy</label>
                                            </div>
                                            <div class="checkbox-group">
                                                <input type="checkbox" id="difficulty-medium" name="difficulty[]" value="medium">
                                                <label for="difficulty-medium">Medium</label>
                                            </div>
                                            <div class="checkbox-group">
                                                <input type="checkbox" id="difficulty-hard" name="difficulty[]" value="hard">
                                                <label for="difficulty-hard">Hard</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <button id="apply-filters" class="btn btn-primary btn-block">Apply Filters</button>
                            </div>
                        </aside>
                        
                        <div class="recipe-search-results">
                            <div class="card">
                                <?php include 'includes/recipe-search.php'; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="tab-content" id="browse-tab">
                    <div class="category-grid">
                        <?php foreach ($categories as $category): ?>
                        <a href="category.php?id=<?php echo $category['id']; ?>" class="category-card">
                            <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                            <p>Browse <?php echo strtolower(htmlspecialchars($category['name'])); ?> recipes</p>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>

