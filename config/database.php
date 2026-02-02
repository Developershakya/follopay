<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'earning_app');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private $pdo;
    private static $instance = null;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    // Helper methods
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    public function queryAll($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function insert($table, $data) {
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = ':' . implode(', :', $keys);
        
        $sql = "INSERT INTO $table ($fields) VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }
    
    public function update($table, $data, $where) {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "$key = :$key";
        }
        
        $whereClause = [];
        $params = $data;
        foreach ($where as $key => $value) {
            $whereClause[] = "$key = :where_$key";
            $params["where_$key"] = $value;
        }
        
        $sql = "UPDATE $table SET " . implode(', ', $set) . 
               " WHERE " . implode(' AND ', $whereClause);
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete($table, $where) {
        $whereClause = [];
        $params = [];
        foreach ($where as $key => $value) {
            $whereClause[] = "$key = :$key";
            $params[$key] = $value;
        }
        
        $sql = "DELETE FROM $table WHERE " . implode(' AND ', $whereClause);
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
}
?>