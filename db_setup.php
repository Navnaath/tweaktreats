<?php
// Database setup script
// Run this script to create the database and tables

// Database configuration
$host = 'localhost';
$user = 'root'; // Change this to your MySQL username
$pass = 'navnaath'; // Add your MySQL password here if needed
$dbname = 'tweaktreats';

try {
    // Connect to MySQL without selecting a database
    $conn = new PDO("mysql:host=$host", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $conn->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    echo "Database created successfully or already exists<br>";
    
    // Select the database
    $conn->exec("USE $dbname");
    
    // Create tables
    $sql = file_get_contents('database.sql');
    $conn->exec($sql);
    
    echo "Database setup completed successfully!<br>";
    echo "You can now <a href='index.php'>go to the homepage</a>.";
    
} catch(PDOException $e) {
    echo "<div style='background-color: #ffebee; color: #c62828; padding: 15px; margin: 20px; border-radius: 5px;'>";
    echo "<h3>Database Setup Error</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please make sure:</p>";
    echo "<ul>";
    echo "<li>MySQL server is running</li>";
    echo "<li>Username and password are correct</li>";
    echo "<li>User has privileges to create databases and tables</li>";
    echo "</ul>";
    echo "</div>";
}
?>

