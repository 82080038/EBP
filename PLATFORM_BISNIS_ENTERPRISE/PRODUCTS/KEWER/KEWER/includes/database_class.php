<?php
/**
 * Database Abstraction Layer
 * 
 * Provides a simple database abstraction class for better code organization
 * Wraps MySQLi operations with helper methods
 */

class Database {
    private $conn;
    private $host;
    private $username;
    private $password;
    private $database;
    
    /**
     * Constructor - Initialize database connection
     * Uses existing global connection if available to avoid multiple connections
     */
    public function __construct($host = DB_HOST, $username = DB_USER, $password = DB_PASS, $database = DB_NAME) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        
        // Use existing global connection if available
        global $conn;
        if (isset($conn) && $conn instanceof mysqli && $conn->ping()) {
            $this->conn = $conn;
        } else {
            $this->connect();
        }
    }
    
    /**
     * Establish database connection
     */
    private function connect() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database, 3306, '/opt/lampp/var/mysql/mysql.sock');
        
        if ($this->conn->connect_error) {
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }
        
        $this->conn->set_charset("utf8mb4");
    }
    
    /**
     * Execute SELECT query
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters for prepared statement
     * @return array Query results
     */
    public function select($sql, $params = []) {
        $stmt = $this->prepare($sql, $params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        $stmt->close();
        return $data;
    }
    
    /**
     * Execute SELECT query and return single row
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters for prepared statement
     * @return array|null Single row or null if not found
     */
    public function selectOne($sql, $params = []) {
        $result = $this->select($sql, $params);
        return $result[0] ?? null;
    }
    
    /**
     * Execute INSERT query
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters for prepared statement
     * @return int Inserted ID
     */
    public function insert($sql, $params = []) {
        $stmt = $this->prepare($sql, $params);
        $stmt->execute();
        $insertId = $stmt->insert_id;
        $stmt->close();
        return $insertId;
    }
    
    /**
     * Execute UPDATE query
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters for prepared statement
     * @return int Number of affected rows
     */
    public function update($sql, $params = []) {
        $stmt = $this->prepare($sql, $params);
        $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        return $affectedRows;
    }
    
    /**
     * Execute DELETE query
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters for prepared statement
     * @return int Number of affected rows
     */
    public function delete($sql, $params = []) {
        return $this->update($sql, $params);
    }
    
    /**
     * Execute any query and return affected rows
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters for prepared statement
     * @return int Number of affected rows
     */
    public function execute($sql, $params = []) {
        $stmt = $this->prepare($sql, $params);
        $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        return $affectedRows;
    }
    
    /**
     * Get count of records
     * 
     * @param string $table Table name
     * @param string $where WHERE clause (optional)
     * @param array $params Parameters for WHERE clause
     * @return int Count of records
     */
    public function count($table, $where = '', $params = []) {
        $sql = "SELECT COUNT(*) as count FROM $table";
        if ($where) {
            $sql .= " WHERE $where";
        }
        
        $result = $this->selectOne($sql, $params);
        return (int)($result['count'] ?? 0);
    }
    
    /**
     * Check if record exists
     * 
     * @param string $table Table name
     * @param string $where WHERE clause
     * @param array $params Parameters for WHERE clause
     * @return bool True if exists, false otherwise
     */
    public function exists($table, $where, $params = []) {
        return $this->count($table, $where, $params) > 0;
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        $this->conn->begin_transaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        $this->conn->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        $this->conn->rollback();
    }
    
    /**
     * Prepare statement with parameters
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters for prepared statement
     * @return mysqli_stmt Prepared statement
     */
    private function prepare($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        
        if (!empty($params)) {
            $types = '';
            $bindParams = [];
            
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
                $bindParams[] = $param;
            }
            
            $stmt->bind_param($types, ...$bindParams);
        }
        
        return $stmt;
    }
    
    /**
     * Get last insert ID
     * 
     * @return int Last insert ID
     */
    public function lastInsertId() {
        return $this->conn->insert_id;
    }
    
    /**
     * Escape string (for direct SQL usage, use prepared statements instead)
     * 
     * @param string $value String to escape
     * @return string Escaped string
     */
    public function escape($value) {
        return $this->conn->real_escape_string($value);
    }
    
    /**
     * Get database connection
     * 
     * @return mysqli Database connection
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Close database connection
     */
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
    
    /**
     * Destructor - Close connection
     */
    public function __destruct() {
        $this->close();
    }
}

// Create global database instance
function db() {
    static $db = null;
    if ($db === null) {
        $db = new Database();
    }
    return $db;
}
