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
    <title>Measurement Conversion | TweakTreats</title>
    <meta name="description" content="Convert recipe measurements">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <h1 class="page-title">Measurement Conversion</h1>

            <div class="tabs">
                <div class="tab-list">
                    <button class="tab-button active" data-tab="single">Single Ingredient</button>
                    <button class="tab-button" data-tab="recipe">Full Recipe</button>
                    <button class="tab-button" data-tab="voice-image">Voice & Image</button>
                </div>
                
                <div class="tab-content active" id="single-tab">
                    <div class="conversion-container">
                        <div class="card">
                            <?php include 'includes/measurement-converter.php'; ?>
                        </div>
                    </div>
                </div>
                
                <div class="tab-content" id="recipe-tab">
                    <div class="conversion-container">
                        <div class="card">
                            <div class="card-header">
                                <h2>Adjust Recipe Servings</h2>
                            </div>
                            <div class="card-content">
                                <form id="recipe-adjust-form" class="recipe-adjust-form">
                                    <div class="form-group">
                                        <label for="recipe-text">Paste your recipe here</label>
                                        <textarea 
                                            id="recipe-text" 
                                            name="recipe-text" 
                                            placeholder="Paste your full recipe including ingredients and instructions..."
                                            rows="8"
                                            required
                                        ></textarea>
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
                                        <i class="fas fa-utensils"></i> Adjust Recipe
                                    </button>
                                </form>
                                
                                <div id="adjusted-recipe" class="adjusted-recipe"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="tab-content" id="voice-image-tab">
                    <div class="voice-image-grid">
                        <div class="card">
                            <div class="card-header">
                                <h2>Image Input</h2>
                            </div>
                            <div class="card-content">
                                <div id="image-drop-area" class="drop-area">
                                    <div class="drop-message">
                                        <i class="fas fa-upload"></i>
                                        <p>Drag and drop an image here, or click to select</p>
                                        <p class="hint">Supports JPG, PNG, GIF</p>
                                    </div>
                                    <div id="image-preview" class="image-preview hidden">
                                        <img src="/placeholder.svg" alt="Recipe preview">
                                        <button type="button" class="image-remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <input type="file" id="image-input" accept="image/*" class="hidden">
                                </div>
                                
                                <button id="extract-recipe" class="btn btn-primary btn-block">
                                    <i class="fas fa-upload"></i> Extract Recipe
                                </button>
                                
                                <div id="extracted-text-container" class="extracted-text-container hidden">
                                    <textarea id="extracted-text" rows="6" placeholder="Extracted recipe will appear here..."></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h2>Voice Input</h2>
                            </div>
                            <div class="card-content">
                                <div class="voice-recorder">
                                    <div id="recorder-status" class="recorder-status">
                                        <div class="recorder-icon">
                                            <i class="fas fa-microphone"></i>
                                        </div>
                                        <p>Ready to record</p>
                                    </div>
                                    
                                    <div class="recorder-controls">
                                        <button id="start-recording" class="btn btn-primary btn-round">
                                            <i class="fas fa-play"></i> Start Recording
                                        </button>
                                        <button id="stop-recording" class="btn btn-danger btn-round hidden">
                                            <i class="fas fa-stop"></i> Stop Recording
                                        </button>
                                    </div>
                                </div>
                                
                                <div id="transcribed-text-container" class="transcribed-text-container hidden">
                                    <textarea id="transcribed-text" rows="6" placeholder="Transcribed recipe will appear here..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>

