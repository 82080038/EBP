<?php

namespace App\Modules\Reservation\Models;

use App\Core\BaseModel;

class TableAvailability extends BaseModel
{
    protected $table = 'table_availability';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'table_id',
        'availability_date',
        'availability_time',
        'is_available',
        'reservation_id',
        'notes'
    ];

    /**
     * Get by date and time
     */
    public function getByDateTime($restaurantId, $date, $time)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($date) {
            $where .= " AND availability_date = ?";
            $params[] = $date;
        }
        
        if ($time) {
            $where .= " AND availability_time = ?";
            $params[] = $time;
        }
        
        $sql = "SELECT ta.*, t.table_number, t.capacity, t.table_type 
                FROM {$this->table} ta
                LEFT JOIN tables t ON ta.table_id = t.id
                {$where}
                ORDER BY t.table_number ASC";
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get available tables
     */
    public function getAvailable($restaurantId, $date, $time, $partySize)
    {
        $sql = "SELECT t.*, ta.id as availability_id 
                FROM tables t
                LEFT JOIN table_availability ta ON t.id = ta.table_id 
                    AND ta.availability_date = ? 
                    AND ta.availability_time = ?
                WHERE t.restaurant_id = ? 
                AND t.is_active = TRUE
                AND t.capacity >= ?
                AND (ta.id IS NULL OR ta.is_available = TRUE)
                ORDER BY t.table_number ASC";
        
        return $this->db->query($sql, [$date, $time, $restaurantId, $partySize])->fetchAll();
    }

    /**
     * Find by ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $result = $this->db->query($sql, [$id])->fetch();
        return $result ?: null;
    }

    /**
     * Create availability slot
     */
    public function createSlot($restaurantId, $tableId, $date, $time, $isAvailable = true)
    {
        $sql = "INSERT INTO table_availability (restaurant_id, table_id, availability_date, availability_time, is_available)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE is_available = VALUES(is_available)";
        
        return $this->db->query($sql, [$restaurantId, $tableId, $date, $time, $isAvailable]);
    }

    /**
     * Get by table
     */
    public function getByTable($tableId, $date)
    {
        $sql = "SELECT * FROM {$this->table} WHERE table_id = ? AND availability_date = ? ORDER BY availability_time ASC";
        return $this->db->query($sql, [$tableId, $date])->fetchAll();
    }

    /**
     * Check availability
     */
    public function checkAvailability($tableId, $date, $time)
    {
        $sql = "SELECT is_available, reservation_id FROM {$this->table} 
                WHERE table_id = ? AND availability_date = ? AND availability_time = ?
                LIMIT 1";
        $result = $this->db->query($sql, [$tableId, $date, $time])->fetch();
        return $result ?: ['is_available' => true, 'reservation_id' => null];
    }
}
