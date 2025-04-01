<?php
// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TweakTreats - AI-Powered Baking Assistant</title>
    <meta name="description" content="Convert vague recipe measurements into precise grams and find the perfect baking recipes.">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <header class="page-header">
                <h1>TweakTreats</h1>
                <p>Your AI-powered baking assistant for precise measurements and perfect recipes</p>
            </header>

            <div class="grid-2-col">
                <div class="card">
                    <?php include 'includes/recipe-search.php'; ?>
                </div>
                <div class="card">
                    <?php include 'includes/measurement-converter.php'; ?>
                </div>
            </div>

            <section class="features-section">
                <h2>Features</h2>
                <div class="grid-3-col">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <h3>Precise Measurements</h3>
                        <p>Convert vague recipe measurements into precise grams based on ingredient density.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>Smart Recipe Search</h3>
                        <p>Find the perfect recipe with our AI-powered search that understands what you're looking for.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <h3>Real-time Adjustments</h3>
                        <p>Modify serving sizes and get automatic adjustments for all ingredients.</p>
                    </div>
                </div>
            </section>

            <footer class="page-footer">
                <p>&copy; <?php echo date('Y'); ?> TweakTreats. All rights reserved.</p>
            </footer>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>

