<?php
class Database {
    private $conn;
    
    public function __construct() {
        try {
            // Add error handling for database connection
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Display a more user-friendly error message
            echo "<div style='background-color: #ffebee; color: #c62828; padding: 15px; margin: 20px; border-radius: 5px;'>";
            echo "<h3>Database Connection Error</h3>";
            echo "<p>Could not connect to the database. Please check your database credentials.</p>";
            echo "<p>Error details: " . $e->getMessage() . "</p>";
            echo "<p>Please make sure:</p>";
            echo "<ul>";
            echo "<li>MySQL server is running</li>";
            echo "<li>Database 'tweaktreats' exists</li>";
            echo "<li>Username and password are correct in includes/config.php</li>";
            echo "</ul>";
            echo "</div>";
            die();
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function select($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function selectOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        $this->query($sql, array_values($data));
        return $this->conn->lastInsertId();
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "$column = ?";
        }
        $setClause = implode(', ', $set);
        
        $sql = "UPDATE $table SET $setClause WHERE $where";
        
        $params = array_merge(array_values($data), $whereParams);
        $this->query($sql, $params);
    }
    
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        $this->query($sql, $params);
    }
}

// Initialize database connection
$db = new Database();

