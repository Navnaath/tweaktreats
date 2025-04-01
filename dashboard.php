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

// Get user data with proper error handling
$userId = $_SESSION['user_id'] ?? 0;
$userName = $_SESSION['user_name'] ?? 'Guest';

// Get user favorites count
try {
    $favoritesCount = count(getUserFavorites($userId));
} catch (Exception $e) {
    $favoritesCount = 0;
}

// Get recipe count
try {
    $recipeCount = $db->selectOne("SELECT COUNT(*) as count FROM recipes")['count'] ?? 0;
} catch (Exception $e) {
    $recipeCount = 0;
}

// Get conversion count from MongoDB
$conversionCount = 0;
try {
    $client = getMongoDBConnection();
    if ($client) {
        $collection = $client->tweaktreats->activity;
        $conversionCount = $collection->countDocuments([
            'userId' => (string)$userId,
            'type' => 'conversion'
        ]);
    }
} catch (Exception $e) {
    // Silently handle MongoDB errors
    error_log("MongoDB Error in dashboard.php: " . $e->getMessage());
}

// Get community submission count
$submissionCount = 0;
try {
    $client = getMongoDBConnection();
    if ($client) {
        $collection = $client->tweaktreats->posts;
        $submissionCount = $collection->countDocuments([
            'userId' => (string)$userId
        ]);
    }
} catch (Exception $e) {
    // Silently handle MongoDB errors
    error_log("MongoDB Error in dashboard.php: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | TweakTreats</title>
    <meta name="description" content="Your TweakTreats dashboard">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <h1 class="page-title">Welcome, <?php echo htmlspecialchars($userName); ?></h1>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <h3>Favorite Recipes</h3>
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $favoritesCount; ?></div>
                        <p class="stat-label">Recipes you've saved</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <h3>Conversions</h3>
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $conversionCount; ?></div>
                        <p class="stat-label">Measurements converted</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <h3>Recipes</h3>
                        <i class="fas fa-utensils"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $recipeCount; ?></div>
                        <p class="stat-label">Recipes in our database</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <h3>Community</h3>
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $submissionCount; ?></div>
                        <p class="stat-label">Posts shared</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="card">
                    <div class="card-header">
                        <h2>Recipe Converter</h2>
                        <p>Convert recipe for different serving sizes</p>
                    </div>
                    <div class="card-content">
                        <form id="recipe-converter-form" class="recipe-converter-form">
                            <div class="form-group">
                                <label for="dish-name">Dish Name</label>
                                <input type="text" id="dish-name" name="dish-name" placeholder="e.g., Chocolate Chip Cookies" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="recipe-text">Recipe</label>
                                <textarea id="recipe-text" name="recipe-text" rows="6" placeholder="Paste your recipe here including ingredients and instructions..." required></textarea>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="original-servings">Original Servings</label>
                                    <input type="number" id="original-servings" name="original-servings" min="1" value="4" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="target-servings">Target Servings</label>
                                    <div class="number-input">
                                        <button type="button" class="number-decrement">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" id="target-servings" name="target-servings" min="1" value="4" required>
                                        <button type="button" class="number-increment">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-utensils"></i> Convert Recipe
                            </button>
                        </form>
                        
                        <div id="converted-recipe" class="converted-recipe"></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2>Quick Actions</h2>
                        <p>Get started with these common tasks</p>
                    </div>
                    <div class="card-content">
                        <div class="action-buttons">
                            <a href="recipes.php" class="btn btn-primary">
                                <i class="fas fa-utensils"></i> Browse Recipes
                            </a>
                            <a href="conversion.php" class="btn btn-outline">
                                <i class="fas fa-balance-scale"></i> Convert Measurements
                            </a>
                            <a href="community.php" class="btn btn-outline">
                                <i class="fas fa-users"></i> Join Community
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Recipe converter form
        const recipeConverterForm = document.getElementById('recipe-converter-form');
        const convertedRecipe = document.getElementById('converted-recipe');
        
        if (recipeConverterForm) {
            recipeConverterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const dishName = document.getElementById('dish-name').value;
                const recipeText = document.getElementById('recipe-text').value;
                const originalServings = document.getElementById('original-servings').value;
                const targetServings = document.getElementById('target-servings').value;
                
                // Show loading state
                convertedRecipe.innerHTML = '<div class="loading">Converting recipe...</div>';
                convertedRecipe.classList.add('active');
                
                // Send AJAX request
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'api/convert-recipe.php');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            
                            if (response.success) {
                                // Build HTML for converted recipe
                                let html = `
                                    <h3>${dishName} (${targetServings} servings)</h3>
                                    <div class="recipe-section">
                                        <h4>Ingredients:</h4>
                                        <ul>
                                `;
                                
                                // Add ingredients
                                response.ingredients.forEach(function(ingredient) {
                                    if (ingredient.amount && ingredient.unit) {
                                        html += `<li>${ingredient.amount} ${ingredient.unit} ${ingredient.name}</li>`;
                                    } else {
                                        html += `<li>${ingredient.name}</li>`;
                                    }
                                });
                                
                                html += `
                                        </ul>
                                    </div>
                                    <div class="recipe-section">
                                        <h4>Instructions:</h4>
                                        <ol>
                                `;
                                
                                // Add instructions
                                response.instructions.forEach(function(instruction) {
                                    html += `<li>${instruction}</li>`;
                                });
                                
                                html += `
                                        </ol>
                                    </div>
                                    <button id="print-recipe" class="btn btn-outline">
                                        <i class="fas fa-print"></i> Print Recipe
                                    </button>
                                `;
                                
                                convertedRecipe.innerHTML = html;
                                
                                // Add print functionality
                                document.getElementById('print-recipe').addEventListener('click', function() {
                                    window.print();
                                });
                            } else {
                                convertedRecipe.innerHTML = `<div class="error">${response.message || 'Error converting recipe. Please try again.'}</div>`;
                            }
                        } catch (e) {
                            convertedRecipe.innerHTML = '<div class="error">Error parsing response. Please try again.</div>';
                        }
                    } else {
                        convertedRecipe.innerHTML = '<div class="error">Server error. Please try again.</div>';
                    }
                };
                xhr.onerror = function() {
                    convertedRecipe.innerHTML = '<div class="error">Network error. Please try again.</div>';
                };
                xhr.send(`dish_name=${encodeURIComponent(dishName)}&recipe_text=${encodeURIComponent(recipeText)}&original_servings=${originalServings}&target_servings=${targetServings}`);
            });
        }
        
        // Number input increment/decrement
        const numberDecrementButtons = document.querySelectorAll('.number-decrement');
        const numberIncrementButtons = document.querySelectorAll('.number-increment');
        
        numberDecrementButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const input = this.parentNode.querySelector('input');
                const min = parseInt(input.getAttribute('min') || 0);
                let value = parseInt(input.value) - 1;
                
                if (value < min) {
                    value = min;
                }
                
                input.value = value;
            });
        });
        
        numberIncrementButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const input = this.parentNode.querySelector('input');
                const value = parseInt(input.value) + 1;
                input.value = value;
            });
        });
    });
    </script>

    <script src="assets/js/main.js"></script>
</body>
</html>

