<?php

namespace App\Modules\Order\Models;

use App\Core\BaseModel;

class OrderModifier extends BaseModel
{
    protected $table = 'order_modifiers';
    protected $primaryKey = 'id';
    protected $fillable = [
        'order_item_id',
        'modifier_id',
        'modifier_name',
        'modifier_type',
        'price_adjustment'
    ];

    /**
     * Get by order item ID
     */
    public function getByOrderItemId($orderItemId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE order_item_id = ?";
        return $this->db->query($sql, [$orderItemId])->fetchAll();
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
     * Calculate total adjustment for order item
     */
    public function getTotalAdjustment($orderItemId)
    {
        $sql = "SELECT SUM(price_adjustment) as total FROM {$this->table} WHERE order_item_id = ?";
        $result = $this->db->query($sql, [$orderItemId])->fetch();
        return $result['total'] ?? 0;
    }
}
