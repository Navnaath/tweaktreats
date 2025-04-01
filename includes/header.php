<header class="site-header">
    <div class="container">
        <div class="header-content">
            <div class="logo-section">
                <a href="index.php" class="logo-container animate-fade-in">
                    <img src="assets/image/1.jpg" alt="TweakTreats Logo" class="logo-image animate-float" style="width: 80px; height: 80px;">
                    <h1 class="logo-text animate-slide-up">TweakTreats</h1>
                </a>
            </div>
            
            <nav class="main-nav animate-fade-in">
                <ul>
                    <li>
                        <a href="index.php" class="nav-link hover-scale <?php echo ($_SERVER['PHP_SELF'] == '/index.php') ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li>
                        <a href="recipes.php" class="nav-link hover-scale <?php echo ($_SERVER['PHP_SELF'] == '/recipes.php') ? 'active' : ''; ?>">
                            <i class="fas fa-utensils"></i> Recipes
                        </a>
                    </li>
                    <li>
                        <a href="conversion.php" class="nav-link hover-scale <?php echo ($_SERVER['PHP_SELF'] == '/conversion.php') ? 'active' : ''; ?>">
                            <i class="fas fa-balance-scale"></i> Conversion
                        </a>
                    </li>
                    <li>
                        <a href="favorites.php" class="nav-link hover-scale <?php echo ($_SERVER['PHP_SELF'] == '/favorites.php') ? 'active' : ''; ?>">
                            <i class="fas fa-heart"></i> Favorites
                        </a>
                    </li>
                    <li>
                        <a href="community.php" class="nav-link hover-scale <?php echo ($_SERVER['PHP_SELF'] == '/community.php') ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i> Community
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="user-nav animate-fade-in">
                <?php if (isLoggedIn()): ?>
                    <div class="dropdown">
                        <button class="dropdown-toggle hover-scale">
                            <div class="avatar animate-pulse">
                                <?php echo (isLoggedIn() && isset($_SESSION['user_name']) && !empty($_SESSION['user_name'])) ? substr($_SESSION['user_name'], 0, 1) : 'G'; ?>
                            </div>
                        </button>
                        <div class="dropdown-menu animate-slide-up">
                            <div class="dropdown-header">
                                <p class="user-name"><?php echo (isset($_SESSION['user_name']) && !empty($_SESSION['user_name'])) ? $_SESSION['user_name'] : 'Guest'; ?></p>
                                <p class="user-email"><?php echo (isset($_SESSION['user_email']) && !empty($_SESSION['user_email'])) ? $_SESSION['user_email'] : ''; ?></p>
                            </div>
                            <a href="profile.php" class="hover-scale"><i class="fas fa-user"></i> Profile</a>
                            <a href="favorites.php" class="hover-scale"><i class="fas fa-heart"></i> Favorites</a>
                            <a href="settings.php" class="hover-scale"><i class="fas fa-cog"></i> Settings</a>
                            <a href="logout.php" class="hover-scale"><i class="fas fa-sign-out-alt"></i> Log out</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-ghost hover-scale animate-fade-in">Sign In</a>
                    <a href="register.php" class="btn btn-primary hover-scale animate-fade-in">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<div class="mobile-menu">
    <nav>
        <ul>
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="recipes.php"><i class="fas fa-utensils"></i> Recipes</a></li>
            <li><a href="conversion.php"><i class="fas fa-balance-scale"></i> Conversion</a></li>
            <li><a href="favorites.php"><i class="fas fa-heart"></i> Favorites</a></li>
            <li><a href="community.php"><i class="fas fa-users"></i> Community</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Log out</a></li>
            <?php else: ?>
                <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Sign In</a></li>
                <li><a href="register.php"><i class="fas fa-user-plus"></i> Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

<?php if ($alert = getAlert()): ?>
<div class="alert alert-<?php echo $alert['type']; ?> animate-slide-up">
    <?php echo $alert['message']; ?>
    <button class="alert-close hover-scale">&times;</button>
</div>
<?php endif; ?>

