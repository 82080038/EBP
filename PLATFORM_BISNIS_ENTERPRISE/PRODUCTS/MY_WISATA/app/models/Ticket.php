<?php
/**
 * MyWisata Application - Ticket Model
 * 
 * Handles ticket related database operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class Ticket extends Model {
    
    /**
     * Table name
     */
    protected $table = 'ticket_orders';
    
    /**
     * Create ticket order
     * 
     * @param array $data Ticket order data
     * @return int Ticket order ID
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} 
                (order_code, user_id, destination_id, quantity, unit_price, total_amount, 
                 visit_date, status, created_at)
                VALUES 
                (:order_code, :user_id, :destination_id, :quantity, :unit_price, :total_amount,
                 :visit_date, 'pending', NOW())";
        
        $this->db->query($sql, $data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Get ticket order by ID
     * 
     * @param int $id Ticket order ID
     * @return array|false
     */
    public function findById($id) {
        $sql = "SELECT to.*, u.name as user_name, d.name as destination_name, d.main_image
                FROM {$this->table} to 
                LEFT JOIN users u ON to.user_id = u.id 
                LEFT JOIN destinations d ON to.destination_id = d.id 
                WHERE to.id = :id";
        
        return $this->db->query($sql, ['id' => $id])->fetch();
    }
    
    /**
     * Get ticket orders by user ID
     * 
     * @param int $userId User ID
     * @param string $status Optional status filter
     * @return array
     */
    public function getByUserId($userId, $status = null) {
        $where = "to.user_id = :user_id";
        $params = ['user_id' => $userId];
        
        if ($status) {
            $where .= " AND to.status = :status";
            $params['status'] = $status;
        }
        
        $sql = "SELECT to.*, d.name as destination_name, d.main_image, d.city
                FROM {$this->table} to 
                LEFT JOIN destinations d ON to.destination_id = d.id 
                WHERE {$where} 
                ORDER BY to.created_at DESC";
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Update ticket status
     * 
     * @param int $id Ticket order ID
     * @param string $status New status
     * @return bool
     */
    public function updateStatus($id, $status) {
        $sql = "UPDATE {$this->table} SET status = :status, updated_at = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $id, 'status' => $status]);
    }
}
