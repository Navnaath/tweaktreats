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

// Get user data - with proper error handling
$userId = $_SESSION['user_id'] ?? 0;
$userName = $_SESSION['user_name'] ?? 'Guest';
$userEmail = $_SESSION['user_email'] ?? '';

// Get user favorites with error handling
try {
    $favorites = getUserFavorites($userId);
} catch (Exception $e) {
    $favorites = [];
    displayAlert('Error loading favorites: ' . $e->getMessage(), 'error');
}

// Get recent activity from MongoDB
$recentActivity = [];
try {
    $client = getMongoDBConnection();
    if ($client) {
        $collection = $client->tweaktreats->activity;
        $recentActivity = $collection->find(
            ['userId' => (string)$userId],
            [
                'sort' => ['createdAt' => -1],
                'limit' => 5
            ]
        )->toArray();
    }
} catch (Exception $e) {
    // Silently handle MongoDB errors
    error_log("MongoDB Error in profile.php: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | TweakTreats</title>
    <meta name="description" content="Your TweakTreats profile">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="profile-container">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php echo !empty($userName) ? htmlspecialchars(substr($userName, 0, 1)) : 'G'; ?>
                    </div>
                    
                    <div class="profile-info">
                        <h1><?php echo htmlspecialchars($userName); ?></h1>
                        <p><?php echo htmlspecialchars($userEmail); ?></p>
                        
                        <div class="profile-actions">
                            <a href="settings.php" class="btn btn-outline">
                                <i class="fas fa-cog"></i> Edit Profile
                            </a>
                            <a href="favorites.php" class="btn btn-outline">
                                <i class="fas fa-heart"></i> Favorites (<?php echo count($favorites); ?>)
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="profile-content">
                    <div class="card">
                        <div class="card-header">
                            <h2>Recent Activity</h2>
                        </div>
                        <div class="card-content">
                            <div class="activity-list">
                                <?php if (empty($recentActivity)): ?>
                                    <div class="empty-state">
                                        <p>No recent activity yet.</p>
                                        <p class="hint">Start by browsing recipes or converting measurements!</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($recentActivity as $activity): ?>
                                        <div class="activity-item">
                                            <div class="activity-icon">
                                                <i class="fas fa-<?php echo htmlspecialchars($activity['icon'] ?? 'history'); ?>"></i>
                                            </div>
                                            <div class="activity-details">
                                                <p class="activity-title"><?php echo htmlspecialchars($activity['description'] ?? 'Activity'); ?></p>
                                                <p class="activity-date">
                                                    <?php 
                                                    $date = $activity['createdAt'] ?? new MongoDB\BSON\UTCDateTime();
                                                    echo $date->toDateTime()->format('M j, Y'); 
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h2>Recent Favorites</h2>
                        </div>
                        <div class="card-content">
                            <?php if (empty($favorites)): ?>
                                <div class="empty-state">
                                    <p>No favorites yet.</p>
                                    <p class="hint">Start by browsing recipes!</p>
                                </div>
                            <?php else: ?>
                                <div class="activity-list">
                                    <?php foreach (array_slice($favorites, 0, 3) as $recipe): ?>
                                        <div class="activity-item">
                                            <div class="activity-icon">
                                                <i class="fas fa-heart"></i>
                                            </div>
                                            <div class="activity-details">
                                                <p class="activity-title"><?php echo htmlspecialchars($recipe['name'] ?? 'Recipe'); ?></p>
                                                <p class="activity-date">Added to favorites</p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>

