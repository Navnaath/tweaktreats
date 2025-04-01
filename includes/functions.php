<?php
// Authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function login($email, $password) {
    global $db;
    
    $user = $db->selectOne("SELECT * FROM users WHERE email = ?", [$email]);
    
    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        return true;
    }
    
    return false;
}

function logout() {
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session
    session_destroy();
    
    // Redirect to home page
    header('Location: index.php');
    exit;
}

function register($name, $email, $password) {
    global $db;
    
    // Check if user already exists
    $existingUser = $db->selectOne("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existingUser) {
        return ['success' => false, 'message' => 'User with this email already exists'];
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $userId = $db->insert('users', [
        'name' => $name,
        'email' => $email,
        'password' => $hashedPassword,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    return ['success' => true, 'user_id' => $userId];
}

// Recipe functions
function searchRecipes($query) {
    global $db;
    
    $searchTerm = "%$query%";
    
    return $db->select(
        "SELECT * FROM recipes WHERE 
        name LIKE ? OR 
        description LIKE ? OR 
        ingredients LIKE ?
        LIMIT 10",
        [$searchTerm, $searchTerm, $searchTerm]
    );
}

function getRecipeById($id) {
    global $db;
    return $db->selectOne("SELECT * FROM recipes WHERE id = ?", [$id]);
}

// Conversion functions
function convertMeasurement($amount, $unit, $ingredient) {
    global $db;
    
    $ingredientData = $db->selectOne(
        "SELECT * FROM ingredient_densities WHERE name = ?", 
        [$ingredient]
    );
    
    if (!$ingredientData) {
        return ['success' => false, 'message' => 'Unknown ingredient'];
    }
    
    $grams = 0;
    
    switch ($unit) {
        case 'cup':
            $grams = $amount * $ingredientData['density_grams_per_cup'];
            break;
        case 'tablespoon':
            $grams = $amount * $ingredientData['density_grams_per_tablespoon'];
            break;
        case 'teaspoon':
            $grams = $amount * $ingredientData['density_grams_per_teaspoon'];
            break;
        case 'ounce':
            $grams = $amount * 28.35; // 1 oz = 28.35 grams
            break;
        default:
            return ['success' => false, 'message' => 'Unknown unit'];
    }
    
    // Round to 1 decimal place
    $grams = round($grams * 10) / 10;
    
    return ['success' => true, 'grams' => $grams];
}

// Favorites functions
function getUserFavorites($userId) {
    global $db;
    
    return $db->select(
        "SELECT r.* FROM recipes r
        JOIN user_favorites f ON r.id = f.recipe_id
        WHERE f.user_id = ?",
        [$userId]
    );
}

function toggleFavorite($userId, $recipeId) {
    global $db;
    
    // Check if already favorited
    $favorite = $db->selectOne(
        "SELECT id FROM user_favorites WHERE user_id = ? AND recipe_id = ?",
        [$userId, $recipeId]
    );
    
    if ($favorite) {
        // Remove favorite
        $db->delete('user_favorites', 'id = ?', [$favorite['id']]);
        return ['success' => true, 'favorited' => false];
    } else {
        // Add favorite
        $db->insert('user_favorites', [
            'user_id' => $userId,
            'recipe_id' => $recipeId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return ['success' => true, 'favorited' => true];
    }
}

// Helper functions
function sanitizeInput($input) {
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = sanitizeInput($value);
        }
    } else {
        $input = trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
    }
    return $input;
}

function redirectTo($location) {
    header("Location: $location");
    exit;
}

function displayAlert($message, $type = 'info') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

function getAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}

// MongoDB connection for community features
function getMongoDBConnection() {
    static $client = null;
    
    // Check if MongoDB extension is installed
    if (!class_exists('MongoDB\Client')) {
        error_log("MongoDB PHP driver is not installed");
        return null;
    }
    
    if ($client === null) {
        try {
            // First try to get MongoDB URI from environment variable
            $mongoUri = getenv('MONGODB_URI');
            
            // If not found in environment, try to read from .env file
            if (!$mongoUri) {
                $envFile = __DIR__ . '/../.env';
                if (file_exists($envFile)) {
                    $envContent = file_get_contents($envFile);
                    preg_match('/MONGODB_URI="([^"]*)"/', $envContent, $matches);
                    if (isset($matches[1])) {
                        $mongoUri = $matches[1];
                    }
                }
            }
            
            // If still not found, use a default value for local development
            if (!$mongoUri) {
                $mongoUri = 'mongodb://localhost:27017/tweaktreats';
            }
            
            // Create MongoDB client
            $client = new MongoDB\Client($mongoUri);
            
            // Test connection by getting server info
            $client->selectDatabase('admin')->command(['ping' => 1]);
        } catch (Exception $e) {
            error_log("MongoDB Connection Error: " . $e->getMessage());
            return null;
        }
    }
    
    return $client;
}

// Community functions using MongoDB
function getPosts($limit = 10, $skip = 0) {
    $client = getMongoDBConnection();
    if (!$client) {
        return [];
    }
    
    try {
        $collection = $client->tweaktreats->posts;
        $posts = $collection->find(
            [],
            [
                'sort' => ['createdAt' => -1],
                'limit' => $limit,
                'skip' => $skip
            ]
        )->toArray();
        
        return $posts;
    } catch (Exception $e) {
        error_log("MongoDB Query Error: " . $e->getMessage());
        return [];
    }
}

function createPost($userId, $userName, $content, $imageUrl = null) {
    $client = getMongoDBConnection();
    if (!$client) {
        throw new Exception("Could not connect to MongoDB");
    }
    
    try {
        $collection = $client->tweaktreats->posts;
        $result = $collection->insertOne([
            'userId' => $userId,
            'userName' => $userName,
            'content' => $content,
            'imageUrl' => $imageUrl,
            'likes' => 0,
            'comments' => [],
            'createdAt' => new MongoDB\BSON\UTCDateTime()
        ]);
        
        return $result->getInsertedId();
    } catch (Exception $e) {
        error_log("MongoDB Insert Error: " . $e->getMessage());
        throw new Exception("Failed to create post: " . $e->getMessage());
    }
}

function likePost($postId, $userId) {
    $client = getMongoDBConnection();
    if (!$client) {
        return false;
    }
    
    try {
        $collection = $client->tweaktreats->likes;
        
        // Check if already liked
        $existingLike = $collection->findOne([
            'postId' => $postId,
            'userId' => $userId
        ]);
        
        if ($existingLike) {
            // Unlike
            $collection->deleteOne([
                'postId' => $postId,
                'userId' => $userId
            ]);
            
            // Decrement like count
            $client->tweaktreats->posts->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($postId)],
                ['$inc' => ['likes' => -1]]
            );
            
            return ['success' => true, 'liked' => false];
        } else {
            // Like
            $collection->insertOne([
                'postId' => $postId,
                'userId' => $userId,
                'createdAt' => new MongoDB\BSON\UTCDateTime()
            ]);
            
            // Increment like count
            $client->tweaktreats->posts->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($postId)],
                ['$inc' => ['likes' => 1]]
            );
            
            return ['success' => true, 'liked' => true];
        }
    } catch (Exception $e) {
        error_log("MongoDB Like Error: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function addComment($postId, $userId, $userName, $content) {
    $client = getMongoDBConnection();
    if (!$client) {
        return false;
    }
    
    try {
        $commentId = new MongoDB\BSON\ObjectId();
        $comment = [
            '_id' => $commentId,
            'userId' => $userId,
            'userName' => $userName,
            'content' => $content,
            'createdAt' => new MongoDB\BSON\UTCDateTime()
        ];
        
        $result = $client->tweaktreats->posts->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($postId)],
            ['$push' => ['comments' => $comment]]
        );
        
        return $commentId;
    } catch (Exception $e) {
        error_log("MongoDB Comment Error: " . $e->getMessage());
        return false;
    }
}

// Recipe conversion functions
function convertRecipeServings($recipeText, $originalServings, $targetServings) {
    // Calculate ratio
    $ratio = $targetServings / $originalServings;
    
    // Parse recipe text to extract ingredients and instructions
    $ingredients = [];
    $instructions = [];
    
    // Simple parsing logic - in a real app, this would be more sophisticated
    $lines = explode("\n", $recipeText);
    $currentSection = null;
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        if (stripos($line, 'ingredient') !== false) {
            $currentSection = 'ingredients';
            continue;
        } elseif (stripos($line, 'instruction') !== false || stripos($line, 'direction') !== false) {
            $currentSection = 'instructions';
            continue;
        }
        
        if ($currentSection === 'ingredients') {
            // Try to parse ingredient line
            if (preg_match('/^([\d.\/]+)\s+(\w+)\s+(.+)$/', $line, $matches)) {
                $amount = convertFractionToDecimal($matches[1]);
                $unit = $matches[2];
                $name = $matches[3];
                
                // Adjust amount based on ratio
                $adjustedAmount = $amount * $ratio;
                
                // Format for display
                $ingredients[] = [
                    'amount' => round($adjustedAmount * 10) / 10,
                    'unit' => $unit,
                    'name' => $name
                ];
            } else {
                // If we can't parse, just add as is
                $ingredients[] = ['name' => $line];
            }
        } elseif ($currentSection === 'instructions') {
            $instructions[] = $line;
        }
    }
    
    return [
        'success' => true,
        'ingredients' => $ingredients,
        'instructions' => $instructions
    ];
}

function convertFractionToDecimal($fraction) {
    // Handle mixed numbers like "1 1/2"
    if (strpos($fraction, ' ') !== false) {
        list($whole, $fraction) = explode(' ', $fraction, 2);
        return $whole + convertFractionToDecimal($fraction);
    }
    
    // Handle simple fractions like "1/2"
    if (strpos($fraction, '/') !== false) {
        list($numerator, $denominator) = explode('/', $fraction, 2);
        return $numerator / $denominator;
    }
    
    // Handle decimal numbers
    return floatval($fraction);
}

