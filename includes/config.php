<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Change this to your MySQL username
define('DB_PASS', 'navnaath'); // Add your MySQL password here if needed
define('DB_NAME', 'tweaktreats');

// Application configuration
define('SITE_NAME', 'TweakTreats');
define('SITE_URL', 'http://localhost/tweaktreats');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Time zone
date_default_timezone_set('UTC');

// Session configuration - MOVED to before session_start() in each file

