<?php
/**
 * MyWisata Application - Model Class
 * 
 * Base model class that all models extend.
 * Provides common database operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-06-30
 */

class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    /**
     * Constructor - Initialize database connection
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all records
     * 
     * @param array $conditions WHERE conditions
     * @param string $orderBy ORDER BY clause
     * @param int $limit LIMIT
     * @param int $offset OFFSET
     * @return array
     */
    public function getAll($conditions = [], $orderBy = null, $limit = null, $offset = 0) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Find record by ID
     * 
     * @param int $id Record ID
     * @return array|false
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        return $this->db->query($sql, ['id' => $id])->fetch();
    }
    
    /**
     * Find record by condition
     * 
     * @param array $conditions WHERE conditions
     * @return array|false
     */
    public function findBy($conditions) {
        $where = [];
        $params = [];
        
        foreach ($conditions as $key => $value) {
            $where[] = "{$key} = :{$key}";
            $params[$key] = $value;
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $where) . " LIMIT 1";
        return $this->db->query($sql, $params)->fetch();
    }
    
    /**
     * Insert new record
     * 
     * @param array $data Data to insert
     * @return int Last insert ID
     */
    public function insert($data) {
        $columns = array_keys($data);
        $placeholders = array_map(function($col) { return ":{$col}"; }, $columns);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->db->query($sql, $data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update record
     * 
     * @param int $id Record ID
     * @param array $data Data to update
     * @return bool
     */
    public function update($id, $data) {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "{$column} = :{$column}";
        }
        
        $data[$this->primaryKey] = $id;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " 
                WHERE {$this->primaryKey} = :{$this->primaryKey}";
        
        return $this->db->query($sql, $data)->rowCount() > 0;
    }
    
    /**
     * Delete record
     * 
     * @param int $id Record ID
     * @return bool
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->db->query($sql, ['id' => $id])->rowCount() > 0;
    }
    
    /**
     * Count records
     * 
     * @param array $conditions WHERE conditions
     * @return int
     */
    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $result = $this->db->query($sql, $params)->fetch();
        return (int) $result['count'];
    }
    
    /**
     * Execute custom query
     * 
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        return $this->db->query($sql, $params);
    }
}
