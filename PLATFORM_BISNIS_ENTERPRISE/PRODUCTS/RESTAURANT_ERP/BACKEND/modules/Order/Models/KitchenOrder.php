<?php

namespace App\Modules\Order\Models;

use App\Core\BaseModel;

class KitchenOrder extends BaseModel
{
    protected $table = 'kitchen_orders';
    protected $primaryKey = 'id';
    protected $fillable = [
        'order_id',
        'order_item_id',
        'station',
        'display_order',
        'status',
        'sent_to_kitchen_at',
        'started_at',
        'ready_at',
        'served_at',
        'prepared_by',
        'kitchen_notes'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $station = null, $status = null)
    {
        $params = [$restaurantId];
        $where = "WHERE o.restaurant_id = ?";
        
        if ($station) {
            $where .= " AND ko.station = ?";
            $params[] = $station;
        }
        
        if ($status) {
            $where .= " AND ko.status = ?";
            $params[] = $status;
        }
        
        $sql = "SELECT ko.*, o.order_number, o.table_id, t.table_number, oi.item_name, oi.quantity
                FROM {$this->table} ko
                LEFT JOIN orders o ON ko.order_id = o.id
                LEFT JOIN tables t ON o.table_id = t.id
                LEFT JOIN order_items oi ON ko.order_item_id = oi.id
                {$where}
                ORDER BY ko.sent_to_kitchen_at ASC, ko.display_order ASC";
        
        return $this->db->query($sql, $params)->fetchAll();
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
     * Get by station
     */
    public function getByStation($restaurantId, $station)
    {
        $sql = "SELECT ko.*, o.order_number, o.table_id, t.table_number, oi.item_name, oi.quantity
                FROM {$this->table} ko
                LEFT JOIN orders o ON ko.order_id = o.id
                LEFT JOIN tables t ON o.table_id = t.id
                LEFT JOIN order_items oi ON ko.order_item_id = oi.id
                WHERE o.restaurant_id = ? AND ko.station = ? AND ko.status IN ('pending', 'in_progress')
                ORDER BY ko.sent_to_kitchen_at ASC, ko.display_order ASC";
        
        return $this->db->query($sql, [$restaurantId, $station])->fetchAll();
    }

    /**
     * Get by order ID
     */
    public function getByOrderId($orderId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE order_id = ? ORDER BY sent_to_kitchen_at ASC";
        return $this->db->query($sql, [$orderId])->fetchAll();
    }

    /**
     * Count by status
     */
    public function countByStatus($restaurantId, $status)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} ko
                LEFT JOIN orders o ON ko.order_id = o.id
                WHERE o.restaurant_id = ? AND ko.status = ?";
        $result = $this->db->query($sql, [$restaurantId, $status])->fetch();
        return $result['count'] ?? 0;
    }
}
