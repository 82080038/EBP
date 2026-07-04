<?php

namespace App\Modules\CustomerAnalytics\Models;

use App\Core\BaseModel;

class CustomerSegment extends BaseModel
{
    protected $table = 'customer_segments';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'segment_name',
        'segment_description',
        'segment_criteria',
        'customer_count',
        'average_lifetime_value',
        'average_order_frequency',
        'segment_color',
        'sort_order',
        'is_active'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE ORDER BY sort_order ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
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
     * Get customers in segment
     */
    public function getCustomers($segmentId, $restaurantId)
    {
        $sql = "SELECT c.*, csa.assigned_at 
                FROM customers c
                INNER JOIN customer_segment_assignments csa ON c.id = csa.customer_id
                WHERE csa.segment_id = ? AND csa.restaurant_id = ?";
        return $this->db->query($sql, [$segmentId, $restaurantId])->fetchAll();
    }

    /**
     * Count by restaurant
     */
    public function countByRestaurant($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }
}
