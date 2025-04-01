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

// Get user data
$userId = $_SESSION['user_id'];
$userData = $db->selectOne("SELECT * FROM users WHERE id = ?", [$userId]);

// Process form submission
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle profile update
    if (isset($_POST['update_profile'])) {
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate inputs
        $errors = [];
        
        if (empty($name)) {
            $errors['name'] = 'Name is required';
        }
        
        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }
        
        // Check if email is already in use by another user
        if ($email !== $userData['email']) {
            $existingUser = $db->selectOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $userId]);
            if ($existingUser) {
                $errors['email'] = 'Email is already in use by another account';
            }
        }
        
        // Password validation
        if (!empty($newPassword)) {
            // Verify current password
            if (empty($currentPassword) || !password_verify($currentPassword, $userData['password'])) {
                $errors['current_password'] = 'Current password is incorrect';
            }
            
            if (strlen($newPassword) < 6) {
                $errors['new_password'] = 'New password must be at least 6 characters';
            }
            
            if ($newPassword !== $confirmPassword) {
                $errors['confirm_password'] = 'Passwords do not match';
            }
        }
        
        // Update profile if no errors
        if (empty($errors)) {
            try {
                // Prepare update data
                $updateData = [
                    'name' => $name,
                    'email' => $email,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                // Add new password if provided
                if (!empty($newPassword)) {
                    $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                }
                
                // Update user in database
                $db->update('users', $updateData, 'id = ?', [$userId]);
                
                // Update session variables
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                
                $successMessage = 'Profile updated successfully';
                
                // Refresh user data
                $userData = $db->selectOne("SELECT * FROM users WHERE id = ?", [$userId]);
            } catch (Exception $e) {
                $errorMessage = 'Error updating profile: ' . $e->getMessage();
            }
        }
    }
    
    // Handle MongoDB settings update
    if (isset($_POST['update_mongodb'])) {
        $mongoUri = sanitizeInput($_POST['mongodb_uri'] ?? '');
        
        if (!empty($mongoUri)) {
            // Save MongoDB URI to .env file or similar
            $envFile = __DIR__ . '/.env';
            $envContent = file_exists($envFile) ? file_get_contents($envFile) : '';
            
            // Check if MONGODB_URI already exists
            if (preg_match('/MONGODB_URI=.*/', $envContent)) {
                // Replace existing value
                $envContent = preg_replace('/MONGODB_URI=.*/', "MONGODB_URI=\"$mongoUri\"", $envContent);
            } else {
                // Add new value
                $envContent .= "\nMONGODBURI=\"$mongoUri\"";
            }
            
            // Write to file
            if (file_put_contents($envFile, $envContent)) {
                $successMessage = 'MongoDB settings updated successfully';
                
                // Set environment variable for current session
                putenv("MONGODB_URI=$mongoUri");
                $_ENV['MONGODB_URI'] = $mongoUri;
            } else {
                $errorMessage = 'Error writing MongoDB settings to file';
            }
        }
    }
    
    // Handle notification settings update
    if (isset($_POST['update_notifications'])) {
        $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
        
        try {
            // Check if settings exist
            $existingSettings = $db->selectOne("SELECT id FROM user_settings WHERE user_id = ?", [$userId]);
            
            if ($existingSettings) {
                // Update existing settings
                $db->update('user_settings', 
                    ['email_notifications' => $emailNotifications, 'updated_at' => date('Y-m-d H:i:s')],
                    'user_id = ?',
                    [$userId]
                );
            } else {
                // Insert new settings
                $db->insert('user_settings', [
                    'user_id' => $userId,
                    'email_notifications' => $emailNotifications,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            $successMessage = 'Notification settings updated successfully';
        } catch (Exception $e) {
            $errorMessage = 'Error updating notification settings: ' . $e->getMessage();
        }
    }
}

// Get user settings
$userSettings = $db->selectOne("SELECT * FROM user_settings WHERE user_id = ?", [$userId]) ?? [
    'email_notifications' => 1
];

// Get MongoDB URI from environment
$mongodbUri = getenv('MONGODB_URI') ?: '';

// Check if MongoDB extension is installed
$mongodbInstalled = class_exists('MongoDB\Client');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | TweakTreats</title>
    <meta name="description" content="Manage your TweakTreats account settings">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .connected-account {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--color-border);
        }
        
        .connected-account:last-child {
            border-bottom: none;
        }
        
        .account-info {
            display: flex;
            align-items: center;
        }
        
        .account-info i {
            font-size: 1.5rem;
            margin-right: 1rem;
            width: 1.5rem;
            text-align: center;
        }
        
        .system-info {
            display: grid;
            gap: 1rem;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--color-border);
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 500;
        }
        
        .status-ok {
            color: var(--color-success);
        }
        
        .status-error {
            color: var(--color-error);
        }
        
        .settings-form {
            max-width: 600px;
        }
        
        .mongodb-notice {
            background-color: #fff3cd;
            color: #856404;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #ffeeba;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <h1 class="page-title">Settings</h1>
            
            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success">
                    <?php echo $successMessage; ?>
                    <button class="alert-close">&times;</button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-error">
                    <?php echo $errorMessage; ?>
                    <button class="alert-close">&times;</button>
                </div>
            <?php endif; ?>
            
            <div class="tabs">
                <div class="tab-list">
                    <button class="tab-button active" data-tab="profile">Profile</button>
                    <button class="tab-button" data-tab="notifications">Notifications</button>
                    <button class="tab-button" data-tab="connections">Connections</button>
                    <?php if ($_SESSION['user_email'] === 'admin@example.com'): ?>
                        <button class="tab-button" data-tab="admin">Admin Settings</button>
                    <?php endif; ?>
                </div>
                
                <div class="tab-content active" id="profile-tab">
                    <div class="card">
                        <div class="card-header">
                            <h2>Profile Information</h2>
                            <p>Update your account information</p>
                        </div>
                        <div class="card-content">
                            <form method="POST" class="settings-form">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input 
                                        type="text" 
                                        id="name" 
                                        name="name" 
                                        value="<?php echo htmlspecialchars($userData['name'] ?? ''); ?>"
                                        class="<?php echo isset($errors['name']) ? 'error' : ''; ?>"
                                    >
                                    <?php if (isset($errors['name'])): ?>
                                        <div class="error-message"><?php echo $errors['name']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input 
                                        type="email" 
                                        id="email" 
                                        name="email" 
                                        value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>"
                                        class="<?php echo isset($errors['email']) ? 'error' : ''; ?>"
                                    >
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="error-message"><?php echo $errors['email']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input 
                                        type="password" 
                                        id="current_password" 
                                        name="current_password"
                                        class="<?php echo isset($errors['current_password']) ? 'error' : ''; ?>"
                                    >
                                    <?php if (isset($errors['current_password'])): ?>
                                        <div class="error-message"><?php echo $errors['current_password']; ?></div>
                                    <?php endif; ?>
                                    <div class="form-hint">Leave password fields empty if you don't want to change it</div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input 
                                        type="password" 
                                        id="new_password" 
                                        name="new_password"
                                        class="<?php echo isset($errors['new_password']) ? 'error' : ''; ?>"
                                    >
                                    <?php if (isset($errors['new_password'])): ?>
                                        <div class="error-message"><?php echo $errors['new_password']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input 
                                        type="password" 
                                        id="confirm_password" 
                                        name="confirm_password"
                                        class="<?php echo isset($errors['confirm_password']) ? 'error' : ''; ?>"
                                    >
                                    <?php if (isset($errors['confirm_password'])): ?>
                                        <div class="error-message"><?php echo $errors['confirm_password']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="tab-content" id="notifications-tab">
                    <div class="card">
                        <div class="card-header">
                            <h2>Notification Preferences</h2>
                            <p>Manage how you receive notifications</p>
                        </div>
                        <div class="card-content">
                            <form method="POST" class="settings-form">
                                <div class="checkbox-group">
                                    <input 
                                        type="checkbox" 
                                        id="email_notifications" 
                                        name="email_notifications"
                                        <?php echo ($userSettings['email_notifications'] ?? 1) ? 'checked' : ''; ?>
                                    >
                                    <label for="email_notifications">Email Notifications</label>
                                    <p class="form-hint">Receive email notifications for new comments, likes, and community updates</p>
                                </div>
                                
                                <button type="submit" name="update_notifications" class="btn btn-primary">Save Preferences</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="tab-content" id="connections-tab">
                    <div class="card">
                        <div class="card-header">
                            <h2>Connected Accounts</h2>
                            <p>Manage your connected social accounts</p>
                        </div>
                        <div class="card-content">
                            <div class="connected-account">
                                <div class="account-info">
                                    <i class="fab fa-google"></i>
                                    <div>
                                        <h3>Google</h3>
                                        <p>Not connected</p>
                                    </div>
                                </div>
                                <button class="btn btn-outline">Connect</button>
                            </div>
                            
                            <div class="connected-account">
                                <div class="account-info">
                                    <i class="fab fa-facebook"></i>
                                    <div>
                                        <h3>Facebook</h3>
                                        <p>Not connected</p>
                                    </div>
                                </div>
                                <button class="btn btn-outline">Connect</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($_SESSION['user_email'] === 'admin@example.com'): ?>
                <div class="tab-content" id="admin-tab">
                    <div class="card">
                        <div class="card-header">
                            <h2>MongoDB Settings</h2>
                            <p>Configure MongoDB connection for community features</p>
                        </div>
                        <div class="card-content">
                            <?php if (!$mongodbInstalled): ?>
                            <div class="mongodb-notice">
                                <h3><i class="fas fa-exclamation-triangle"></i> MongoDB PHP Driver Not Installed</h3>
                                <p>The MongoDB PHP driver is required for community features. Please install it to enable posting, commenting, and other community features.</p>
                                <p><strong>Installation Instructions:</strong></p>
                                <ol>
                                    <li>Install the MongoDB PHP extension:
                                        <ul>
                                            <li>For Windows: Download and install the appropriate DLL from <a href="https://pecl.php.net/package/mongodb" target="_blank">PECL</a></li>
                                            <li>For Linux: <code>sudo pecl install mongodb</code></li>
                                            <li>For macOS: <code>brew install php-mongodb</code> or <code>sudo pecl install mongodb</code></li>
                                        </ul>
                                    </li>
                                    <li>Install the MongoDB PHP library via Composer: <code>composer require mongodb/mongodb</code></li>
                                    <li>Add <code>extension=mongodb.so</code> (Linux/macOS) or <code>extension=php_mongodb.dll</code> (Windows) to your php.ini file</li>
                                    <li>Restart your web server</li>
                                </ol>
                                <p>After installation, refresh this page to access community features.</p>
                                <p>For more information, visit the <a href="https://www.php.net/manual/en/mongodb.installation.php" target="_blank">PHP MongoDB Installation Guide</a>.</p>
                            </div>
                            <?php else: ?>
                            <form method="POST" class="settings-form">
                                <div class="form-group">
                                    <label for="mongodb_uri">MongoDB URI</label>
                                    <input 
                                        type="text" 
                                        id="mongodb_uri" 
                                        name="mongodb_uri" 
                                        value="<?php echo htmlspecialchars($mongodbUri); ?>"
                                        placeholder="mongodb+srv://username:password@cluster.mongodb.net/database"
                                    >
                                    <div class="form-hint">Example: mongodb+srv://username:password@cluster.mongodb.net/database</div>
                                </div>
                                
                                <button type="submit" name="update_mongodb" class="btn btn-primary">Save MongoDB Settings</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h2>System Information</h2>
                            <p>View system information and status</p>
                        </div>
                        <div class="card-content">
                            <div class="system-info">
                                <div class="info-item">
                                    <span class="info-label">PHP Version:</span>
                                    <span class="info-value"><?php echo phpversion(); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">MySQL Version:</span>
                                    <span class="info-value">
                                        <?php 
                                        try {
                                            $version = $db->selectOne("SELECT VERSION() as version")['version'] ?? 'Unknown';
                                            echo $version;
                                        } catch (Exception $e) {
                                            echo 'Error getting version';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">MongoDB Status:</span>
                                    <span class="info-value">
                                        <?php 
                                        if (!$mongodbInstalled) {
                                            echo '<span class="status-error">PHP Driver Not Installed</span>';
                                        } else {
                                            try {
                                                $client = getMongoDBConnection();
                                                echo $client ? '<span class="status-ok">Connected</span>' : '<span class="status-error">Not Connected</span>';
                                            } catch (Exception $e) {
                                                echo '<span class="status-error">Error: ' . $e->getMessage() . '</span>';
                                            }
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Upload Directory:</span>
                                    <span class="info-value">
                                        <?php 
                                        $uploadDir = __DIR__ . '/uploads/';
                                        echo is_dir($uploadDir) && is_writable($uploadDir) 
                                            ? '<span class="status-ok">Writable</span>' 
                                            : '<span class="status-error">Not Writable</span>';
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>

