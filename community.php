<?php
// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if MongoDB extension is installed
$mongodbInstalled = class_exists('MongoDB\Client');

// Get posts from MongoDB only if extension is installed
$posts = [];
if ($mongodbInstalled) {
    try {
        $client = getMongoDBConnection();
        if ($client) {
            $collection = $client->tweaktreats->posts;
            $posts = $collection->find(
                [],
                [
                    'sort' => ['createdAt' => -1],
                    'limit' => 20
                ]
            )->toArray();
        }
    } catch (Exception $e) {
        // Silently handle MongoDB errors
        error_log("MongoDB Error in community.php: " . $e->getMessage());
    }
}

// Check if user has liked each post
$likedPosts = [];
if ($mongodbInstalled && isLoggedIn()) {
    try {
        $userId = $_SESSION['user_id'];
        $client = getMongoDBConnection();
        if ($client) {
            $collection = $client->tweaktreats->likes;
            $likes = $collection->find(['userId' => (string)$userId])->toArray();
            
            foreach ($likes as $like) {
                $likedPosts[] = (string)$like['postId'];
            }
        }
    } catch (Exception $e) {
        // Silently handle MongoDB errors
        error_log("MongoDB Error getting likes in community.php: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community | TweakTreats</title>
    <meta name="description" content="Join the TweakTreats community">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Additional styles for community page */
        .post-card {
            margin-bottom: 1.5rem;
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .post-header {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .post-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background-color: var(--color-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 0.75rem;
        }
        
        .post-user {
            font-weight: 600;
        }
        
        .post-date {
            font-size: 0.75rem;
            color: #666;
        }
        
        .post-content {
            padding: 1rem;
        }
        
        .post-text {
            margin-bottom: 1rem;
            white-space: pre-line;
        }
        
        .post-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 0.25rem;
            margin-bottom: 1rem;
        }
        
        .post-actions {
            display: flex;
            padding: 0.5rem 1rem;
            border-top: 1px solid #f0f0f0;
        }
        
        .post-action {
            display: flex;
            align-items: center;
            margin-right: 1.5rem;
            color: #666;
            cursor: pointer;
        }
        
        .post-action.liked {
            color: #e53e3e;
        }
        
        .post-action i {
            margin-right: 0.25rem;
        }
        
        .post-comments {
            padding: 1rem;
            background-color: #f9f9f9;
            border-top: 1px solid #f0f0f0;
        }
        
        .comment {
            display: flex;
            margin-bottom: 0.75rem;
        }
        
        .comment-avatar {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            background-color: var(--color-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 0.75rem;
            font-size: 0.75rem;
        }
        
        .comment-content {
            flex: 1;
            background-color: white;
            padding: 0.75rem;
            border-radius: 0.5rem;
        }
        
        .comment-user {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .comment-text {
            font-size: 0.875rem;
        }
        
        .comment-form {
            display: flex;
            margin-top: 1rem;
        }
        
        .comment-input {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 1.5rem;
            padding: 0.5rem 1rem;
            margin-right: 0.5rem;
        }
        
        .comment-submit {
            background-color: var(--color-primary);
            color: white;
            border: none;
            border-radius: 1.5rem;
            padding: 0.5rem 1rem;
            cursor: pointer;
        }
        
        .create-post {
            margin-bottom: 2rem;
        }
        
        .create-post-card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1rem;
        }
        
        .create-post-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .create-post-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background-color: var(--color-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 0.75rem;
        }
        
        .create-post-input {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 1.5rem;
            padding: 0.75rem 1rem;
            cursor: pointer;
        }
        
        .create-post-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            display: none;
        }
        
        .create-post-modal.active {
            display: flex;
        }
        
        .create-post-form {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            padding: 1.5rem;
        }
        
        .create-post-form-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .create-post-form-title {
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .create-post-form-close {
            background: none;
            border: none;
            font-size: 1.25rem;
            cursor: pointer;
        }
        
        .create-post-form-content {
            margin-bottom: 1rem;
        }
        
        .create-post-form-textarea {
            width: 100%;
            min-height: 100px;
            border: 1px solid #ddd;
            border-radius: 0.5rem;
            padding: 0.75rem;
            margin-bottom: 1rem;
            resize: vertical;
        }
        
        .create-post-form-image {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .create-post-form-image-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 0.5rem;
            margin-right: 0.75rem;
            display: none;
        }
        
        .create-post-form-image-preview.active {
            display: block;
        }
        
        .create-post-form-image-remove {
            background: none;
            border: none;
            color: #e53e3e;
            cursor: pointer;
            display: none;
        }
        
        .create-post-form-image-remove.active {
            display: block;
        }
        
        .create-post-form-image-upload {
            display: flex;
            align-items: center;
            color: #666;
            cursor: pointer;
        }
        
        .create-post-form-image-upload i {
            margin-right: 0.5rem;
        }
        
        .create-post-form-image-input {
            display: none;
        }
        
        .create-post-form-submit {
            width: 100%;
            background-color: var(--color-primary);
            color: white;
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem;
            font-weight: 600;
            cursor: pointer;
        }
        
        .create-post-form-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .mongodb-notice {
            background-color: #fff3cd;
            color: #856404;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #ffeeba;
        }

        .mongodb-notice h3 {
            margin-top: 0;
            margin-bottom: 0.5rem;
        }

        .mongodb-notice p {
            margin-bottom: 0.5rem;
        }

        .mongodb-notice code {
            background-color: #f8f9fa;
            padding: 0.2rem 0.4rem;
            border-radius: 0.25rem;
            font-family: monospace;
        }

        .mongodb-notice a {
            color: #0056b3;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <h1 class="page-title">Community</h1>
            
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
                <?php if (isLoggedIn()): ?>
                    <div class="create-post">
                        <div class="create-post-card">
                            <div class="create-post-header">
                                <div class="create-post-avatar">
                                    <?php echo !empty($_SESSION['user_name']) ? htmlspecialchars(substr($_SESSION['user_name'], 0, 1)) : 'G'; ?>
                                </div>
                                <div class="create-post-input" id="create-post-trigger">
                                    What's on your mind?
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="create-post-modal" id="create-post-modal">
                        <div class="create-post-form">
                            <div class="create-post-form-header">
                                <div class="create-post-form-title">Create Post</div>
                                <button class="create-post-form-close" id="create-post-close">&times;</button>
                            </div>
                            <div class="create-post-form-content">
                                <textarea class="create-post-form-textarea" id="post-content" placeholder="What's on your mind?"></textarea>
                                <div class="create-post-form-image">
                                    <img src="/placeholder.svg" alt="Preview" class="create-post-form-image-preview" id="image-preview">
                                    <button class="create-post-form-image-remove" id="image-remove">Remove</button>
                                    <label class="create-post-form-image-upload">
                                        <i class="fas fa-image"></i> Add Photo
                                        <input type="file" class="create-post-form-image-input" id="image-upload" accept="image/*">
                                    </label>
                                </div>
                            </div>
                            <button class="create-post-form-submit" id="post-submit">Post</button>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="posts">
                    <?php if (empty($posts)): ?>
                        <div class="empty-state">
                            <p>No posts yet. Be the first to share something!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <div class="post-card" data-id="<?php echo htmlspecialchars((string)$post['_id']); ?>">
                                <div class="post-header">
                                    <div class="post-avatar">
                                        <?php echo !empty($post['userName']) ? htmlspecialchars(substr($post['userName'], 0, 1)) : 'U'; ?>
                                    </div>
                                    <div>
                                        <div class="post-user"><?php echo htmlspecialchars($post['userName'] ?? 'User'); ?></div>
                                        <div class="post-date">
                                            <?php 
                                            $date = $post['createdAt'] ?? new MongoDB\BSON\UTCDateTime();
                                            echo $date->toDateTime()->format('M j, Y \a\t g:i a'); 
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="post-content">
                                    <div class="post-text"><?php echo htmlspecialchars($post['content'] ?? ''); ?></div>
                                    <?php if (!empty($post['imageUrl'])): ?>
                                        <img src="<?php echo htmlspecialchars($post['imageUrl']); ?>" alt="Post image" class="post-image">
                                    <?php endif; ?>
                                </div>
                                <div class="post-actions">
                                    <div class="post-action like-button <?php echo in_array((string)$post['_id'], $likedPosts) ? 'liked' : ''; ?>" data-id="<?php echo htmlspecialchars((string)$post['_id']); ?>">
                                        <i class="<?php echo in_array((string)$post['_id'], $likedPosts) ? 'fas' : 'far'; ?> fa-heart"></i>
                                        <span class="like-count"><?php echo $post['likes'] ?? 0; ?></span>
                                    </div>
                                    <div class="post-action comment-button" data-id="<?php echo htmlspecialchars((string)$post['_id']); ?>">
                                        <i class="far fa-comment"></i>
                                        <span class="comment-count"><?php echo count($post['comments'] ?? []); ?></span>
                                    </div>
                                    <div class="post-action share-button" data-id="<?php echo htmlspecialchars((string)$post['_id']); ?>">
                                        <i class="far fa-share-square"></i>
                                        <span>Share</span>
                                    </div>
                                </div>
                                <div class="post-comments" id="comments-<?php echo htmlspecialchars((string)$post['_id']); ?>" style="display: none;">
                                    <?php if (!empty($post['comments'])): ?>
                                        <?php foreach ($post['comments'] as $comment): ?>
                                            <div class="comment">
                                                <div class="comment-avatar">
                                                    <?php echo !empty($comment['userName']) ? htmlspecialchars(substr($comment['userName'], 0, 1)) : 'U'; ?>
                                                </div>
                                                <div class="comment-content">
                                                    <div class="comment-user"><?php echo htmlspecialchars($comment['userName'] ?? 'User'); ?></div>
                                                    <div class="comment-text"><?php echo htmlspecialchars($comment['content'] ?? ''); ?></div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="no-comments">No comments yet. Be the first to comment!</div>
                                    <?php endif; ?>
                                    
                                    <?php if (isLoggedIn()): ?>
                                        <form class="comment-form" data-id="<?php echo htmlspecialchars((string)$post['_id']); ?>">
                                            <input type="text" class="comment-input" placeholder="Write a comment...">
                                            <button type="submit" class="comment-submit">Post</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Create post modal
        const createPostTrigger = document.getElementById('create-post-trigger');
        const createPostModal = document.getElementById('create-post-modal');
        const createPostClose = document.getElementById('create-post-close');
        const postContent = document.getElementById('post-content');
        const postSubmit = document.getElementById('post-submit');
        const imageUpload = document.getElementById('image-upload');
        const imagePreview = document.getElementById('image-preview');
        const imageRemove = document.getElementById('image-remove');
        
        if (createPostTrigger) {
            createPostTrigger.addEventListener('click', function() {
                createPostModal.classList.add('active');
            });
        }
        
        if (createPostClose) {
            createPostClose.addEventListener('click', function() {
                createPostModal.classList.remove('active');
            });
        }
        
        // Image upload preview
        if (imageUpload) {
            imageUpload.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                        imagePreview.classList.add('active');
                        imageRemove.classList.add('active');
                    };
                    
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
        
        // Remove image
        if (imageRemove) {
            imageRemove.addEventListener('click', function() {
                imageUpload.value = '';
                imagePreview.src = '';
                imagePreview.classList.remove('active');
                imageRemove.classList.remove('active');
            });
        }
        
        // Submit post
        if (postSubmit) {
            postSubmit.addEventListener('click', function() {
                const content = postContent.value.trim();
                
                if (!content) {
                    alert('Please enter some content for your post.');
                    return;
                }
                
                // Disable submit button
                postSubmit.disabled = true;
                postSubmit.textContent = 'Posting...';
                
                // Create FormData for file upload
                const formData = new FormData();
                formData.append('content', content);
                
                if (imageUpload.files && imageUpload.files[0]) {
                    formData.append('image', imageUpload.files[0]);
                }
                
                // Send AJAX request
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'api/create-post.php');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            
                            if (response.success) {
                                // Reload page to show new post
                                window.location.reload();
                            } else {
                                alert(response.message || 'Error creating post. Please try again.');
                                postSubmit.disabled = false;
                                postSubmit.textContent = 'Post';
                            }
                        } catch (e) {
                            alert('Error parsing response. Please try again.');
                            postSubmit.disabled = false;
                            postSubmit.textContent = 'Post';
                        }
                    } else {
                        alert('Server error. Please try again.');
                        postSubmit.disabled = false;
                        postSubmit.textContent = 'Post';
                    }
                };
                xhr.onerror = function() {
                    alert('Network error. Please try again.');
                    postSubmit.disabled = false;
                    postSubmit.textContent = 'Post';
                };
                xhr.send(formData);
            });
        }
        
        // Like button
        const likeButtons = document.querySelectorAll('.like-button');
        
        likeButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const postId = this.getAttribute('data-id');
                const likeCount = this.querySelector('.like-count');
                const likeIcon = this.querySelector('i');
                
                // Send AJAX request
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'api/like-post.php');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            
                            if (response.success) {
                                if (response.liked) {
                                    button.classList.add('liked');
                                    likeIcon.classList.remove('far');
                                    likeIcon.classList.add('fas');
                                    likeCount.textContent = parseInt(likeCount.textContent) + 1;
                                } else {
                                    button.classList.remove('liked');
                                    likeIcon.classList.remove('fas');
                                    likeIcon.classList.add('far');
                                    likeCount.textContent = parseInt(likeCount.textContent) - 1;
                                }
                            } else {
                                if (response.message === 'Not logged in') {
                                    window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname);
                                } else {
                                    alert(response.message || 'Error liking post. Please try again.');
                                }
                            }
                        } catch (e) {
                            alert('Error parsing response. Please try again.');
                        }
                    } else {
                        alert('Server error. Please try again.');
                    }
                };
                xhr.onerror = function() {
                    alert('Network error. Please try again.');
                };
                xhr.send('post_id=' + postId);
            });
        });
        
        // Comment button
        const commentButtons = document.querySelectorAll('.comment-button');
        
        commentButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const postId = this.getAttribute('data-id');
                const commentsSection = document.getElementById('comments-' + postId);
                
                if (commentsSection.style.display === 'none') {
                    commentsSection.style.display = 'block';
                } else {
                    commentsSection.style.display = 'none';
                }
            });
        });
        
        // Comment form
        const commentForms = document.querySelectorAll('.comment-form');
        
        commentForms.forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const postId = this.getAttribute('data-id');
                const commentInput = this.querySelector('.comment-input');
                const content = commentInput.value.trim();
                
                if (!content) {
                    return;
                }
                
                // Send AJAX request
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'api/add-comment.php');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            
                            if (response.success) {
                                // Reload page to show new comment
                                window.location.reload();
                            } else {
                                if (response.message === 'Not logged in') {
                                    window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname);
                                } else {
                                    alert(response.message || 'Error adding comment. Please try again.');
                                }
                            }
                        } catch (e) {
                            alert('Error parsing response. Please try again.');
                        }
                    } else {
                        alert('Server error. Please try again.');
                    }
                };
                xhr.onerror = function() {
                    alert('Network error. Please try again.');
                };
                xhr.send('post_id=' + postId + '&content=' + encodeURIComponent(content));
                
                // Clear input
                commentInput.value = '';
            });
        });
        
        // Share button
        const shareButtons = document.querySelectorAll('.share-button');
        
        shareButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const postId = this.getAttribute('data-id');
                const url = window.location.origin + window.location.pathname + '?post=' + postId;
                
                // Copy to clipboard
                const tempInput = document.createElement('input');
                document.body.appendChild(tempInput);
                tempInput.value = url;
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                
                alert('Link copied to clipboard!');
            });
        });
    });
    </script>

    <script src="assets/js/main.js"></script>
</body>
</html>

