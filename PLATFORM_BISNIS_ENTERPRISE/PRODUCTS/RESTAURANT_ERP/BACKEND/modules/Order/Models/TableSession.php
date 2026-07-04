<?php

namespace App\Modules\Order\Models;

use App\Core\BaseModel;

class TableSession extends BaseModel
{
    protected $table = 'table_sessions';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'table_id',
        'session_number',
        'session_status',
        'started_at',
        'ended_at',
        'duration_minutes',
        'customer_count',
        'customer_id',
        'order_count',
        'total_amount',
        'server_id',
        'notes'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $tableId = null, $status = null)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($tableId) {
            $where .= " AND table_id = ?";
            $params[] = $tableId;
        }
        
        if ($status) {
            $where .= " AND session_status = ?";
            $params[] = $status;
        }
        
        $sql = "SELECT ts.*, t.table_number, t.table_name, u.username as server_name 
                FROM {$this->table} ts
                LEFT JOIN tables t ON ts.table_id = t.id
                LEFT JOIN users u ON ts.server_id = u.id
                {$where}
                ORDER BY ts.started_at DESC";
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Find by ID
     */
    public function findById($id, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$id, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Get active by table
     */
    public function getActiveByTable($restaurantId, $tableId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? AND table_id = ? AND session_status = 'active'
                ORDER BY started_at DESC
                LIMIT 1";
        $result = $this->db->query($sql, [$restaurantId, $tableId])->fetch();
        return $result ?: null;
    }

    /**
     * Get active sessions
     */
    public function getActive($restaurantId)
    {
        $sql = "SELECT ts.*, t.table_number, t.table_name, u.username as server_name 
                FROM {$this->table} ts
                LEFT JOIN tables t ON ts.table_id = t.id
                LEFT JOIN users u ON ts.server_id = u.id
                WHERE ts.restaurant_id = ? AND ts.session_status = 'active'
                ORDER BY ts.started_at ASC";
        
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Count active sessions
     */
    public function countActive($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND session_status = 'active'";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }
}
