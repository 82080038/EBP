<?php
/**
 * MyWisata Application - Transaction Model
 * 
 * Handles transaction related database operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class Transaction extends Model {
    
    /**
     * Table name
     */
    protected $table = 'transactions';
    
    /**
     * Create transaction
     * 
     * @param array $data Transaction data
     * @return int Transaction ID
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} 
                (transaction_code, user_id, booking_id, type, gross_amount, discount_amount, 
                 tax_amount, net_amount, payment_method, payment_status, created_at)
                VALUES 
                (:transaction_code, :user_id, :booking_id, :type, :gross_amount, :discount_amount,
                 :tax_amount, :net_amount, :payment_method, 'pending', NOW())";
        
        $this->db->query($sql, $data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Get transaction by ID
     * 
     * @param int $id Transaction ID
     * @return array|false
     */
    public function findById($id) {
        $sql = "SELECT t.*, u.name as user_name 
                FROM {$this->table} t 
                LEFT JOIN users u ON t.user_id = u.id 
                WHERE t.id = :id";
        
        return $this->db->query($sql, ['id' => $id])->fetch();
    }
    
    /**
     * Get transactions by user ID
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getByUserId($userId) {
        $sql = "SELECT t.*, b.booking_code 
                FROM {$this->table} t 
                LEFT JOIN bookings b ON t.booking_id = b.id 
                WHERE t.user_id = :user_id 
                ORDER BY t.created_at DESC";
        
        return $this->db->query($sql, ['user_id' => $userId])->fetchAll();
    }
    
    /**
     * Update payment status
     * 
     * @param int $id Transaction ID
     * @param string $status Payment status
     * @return bool
     */
    public function updatePaymentStatus($id, $status) {
        $sql = "UPDATE {$this->table} SET payment_status = :status, updated_at = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $id, 'status' => $status]);
    }
    
    /**
     * Update payment method
     * 
     * @param int $id Transaction ID
     * @param string $method Payment method
     * @return bool
     */
    public function updatePaymentMethod($id, $method) {
        $sql = "UPDATE {$this->table} SET payment_method = :method, updated_at = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $id, 'method' => $method]);
    }
}
