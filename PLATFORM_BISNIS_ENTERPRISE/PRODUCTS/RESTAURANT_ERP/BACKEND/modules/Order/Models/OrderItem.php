<?php

namespace App\Modules\Order\Models;

use App\Core\BaseModel;

class OrderItem extends BaseModel
{
    protected $table = 'order_items';
    protected $primaryKey = 'id';
    protected $fillable = [
        'order_id',
        'menu_item_id',
        'item_name',
        'quantity',
        'unit_price',
        'subtotal',
        'modifiers',
        'special_instructions',
        'preparation_station',
        'preparation_status',
        'preparation_started_at',
        'preparation_ready_at',
        'is_cancelled',
        'cancelled_at',
        'cancellation_reason'
    ];

    /**
     * Get by order ID
     */
    public function getByOrderId($orderId)
    {
        $sql = "SELECT oi.*, mi.name as menu_item_name, mi.image_url 
                FROM {$this->table} oi
                LEFT JOIN menu_items mi ON oi.menu_item_id = mi.id
                WHERE oi.order_id = ? AND oi.is_cancelled = FALSE
                ORDER BY oi.created_at ASC";
        return $this->db->query($sql, [$orderId])->fetchAll();
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
     * Get by preparation status
     */
    public function getByPreparationStatus($restaurantId, $status)
    {
        $sql = "SELECT oi.*, o.order_number, o.table_id 
                FROM {$this->table} oi
                LEFT JOIN orders o ON oi.order_id = o.id
                WHERE o.restaurant_id = ? AND oi.preparation_status = ? AND oi.is_cancelled = FALSE
                ORDER BY oi.created_at ASC";
        return $this->db->query($sql, [$restaurantId, $status])->fetchAll();
    }

    /**
     * Get by station
     */
    public function getByStation($restaurantId, $station)
    {
        $sql = "SELECT oi.*, o.order_number, o.table_id 
                FROM {$this->table} oi
                LEFT JOIN orders o ON oi.order_id = o.id
                WHERE o.restaurant_id = ? AND oi.preparation_station = ? AND oi.is_cancelled = FALSE
                ORDER BY oi.created_at ASC";
        return $this->db->query($sql, [$restaurantId, $station])->fetchAll();
    }
}
