<?php
/**
 * MyWisata Application - Booking Model
 * 
 * Handles booking related database operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class Booking extends Model {
    
    /**
     * Table name
     */
    protected $table = 'bookings';
    
    /**
     * Create booking
     * 
     * @param array $data Booking data
     * @return int Booking ID
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} 
                (booking_code, user_id, guide_id, booking_date, booking_time, 
                 duration_hours, participants, special_requests, status, total_amount, created_at)
                VALUES 
                (:booking_code, :user_id, :guide_id, :booking_date, :booking_time,
                 :duration_hours, :participants, :special_requests, 'pending', :total_amount, NOW())";
        
        $this->db->query($sql, $data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Get booking by ID
     * 
     * @param int $id Booking ID
     * @return array|false
     */
    public function findById($id) {
        $sql = "SELECT b.*, u.name as user_name, u.email as user_email, u.phone as user_phone,
                tg.name as guide_name, tg.phone as guide_phone, tg.hourly_rate, tg.daily_rate
                FROM {$this->table} b 
                LEFT JOIN users u ON b.user_id = u.id 
                LEFT JOIN tour_guides tg ON b.guide_id = tg.id 
                WHERE b.id = :id";
        
        return $this->db->query($sql, ['id' => $id])->fetch();
    }
    
    /**
     * Get bookings by user ID
     * 
     * @param int $userId User ID
     * @param string $status Optional status filter
     * @return array
     */
    public function getByUserId($userId, $status = null) {
        $where = "b.user_id = :user_id";
        $params = ['user_id' => $userId];
        
        if ($status) {
            $where .= " AND b.status = :status";
            $params['status'] = $status;
        }
        
        $sql = "SELECT b.*, tg.name as guide_name, tg.avatar, tg.rating_avg
                FROM {$this->table} b 
                LEFT JOIN tour_guides tg ON b.guide_id = tg.id 
                WHERE {$where} 
                ORDER BY b.created_at DESC";
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Update booking status
     * 
     * @param int $id Booking ID
     * @param string $status New status
     * @return bool
     */
    public function updateStatus($id, $status) {
        $sql = "UPDATE {$this->table} SET status = :status, updated_at = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $id, 'status' => $status]);
    }
    
    /**
     * Cancel booking
     * 
     * @param int $id Booking ID
     * @param string $reason Cancellation reason
     * @return bool
     */
    public function cancel($id, $reason = null) {
        $sql = "UPDATE {$this->table} SET status = 'cancelled', cancellation_reason = :reason, updated_at = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $id, 'reason' => $reason]);
    }
}
