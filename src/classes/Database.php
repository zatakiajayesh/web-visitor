<?php
/**
 * Database Connection Class
 * Handles all database operations
 */

class Database {
    private $host;
    private $db;
    private $user;
    private $pass;
    private $charset = 'utf8mb4';
    private $connection;
    private $statement;
    
    public function __construct() {
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->db = getenv('DB_NAME');
        $this->user = getenv('DB_USER');
        $this->pass = getenv('DB_PASS');
        
        $this->connect();
    }
    
    /**
     * Connect to database
     */
    private function connect() {
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db . ";charset=" . $this->charset;
            $this->connection = new PDO($dsn, $this->user, $this->pass);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    /**
     * Execute a prepared statement
     */
    public function query($sql) {
        $this->statement = $this->connection->prepare($sql);
        return $this;
    }
    
    /**
     * Bind values to prepared statement
     */
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            $type = match(true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };
        }
        
        $this->statement->bindValue($param, $value, $type);
        return $this;
    }
    
    /**
     * Execute the query
     */
    public function execute() {
        try {
            return $this->statement->execute();
        } catch (PDOException $e) {
            error_log("Query Execution Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get single row result
     */
    public function single() {
        $this->execute();
        return $this->statement->fetch();
    }
    
    /**
     * Get all results
     */
    public function resultSet() {
        $this->execute();
        return $this->statement->fetchAll();
    }
    
    /**
     * Get row count
     */
    public function rowCount() {
        return $this->statement->rowCount();
    }
    
    /**
     * Get last inserted ID
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollBack() {
        return $this->connection->rollBack();
    }
    
    /**
     * Close connection
     */
    public function closeConnection() {
        $this->connection = null;
    }
}
