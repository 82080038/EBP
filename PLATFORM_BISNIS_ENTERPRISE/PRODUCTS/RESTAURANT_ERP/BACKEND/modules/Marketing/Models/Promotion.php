<?php

namespace App\Modules\Marketing\Models;

use App\Core\BaseModel;

class Promotion extends BaseModel
{
    protected $table = 'promotions';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'promotion_name',
        'promotion_description',
        'promotion_code',
        'promotion_type',
        'discount_value',
        'discount_type',
        'applies_to',
        'applicable_items',
        'applicable_categories',
        'minimum_order_value',
        'minimum_quantity',
        'maximum_discount',
        'start_date',
        'end_date',
        'usage_limit',
        'usage_count',
        'usage_limit_per_customer',
        'promotion_status',
        'created_by'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $status, $type)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($status) {
            $where .= " AND promotion_status = ?";
            $params[] = $status;
        }
        
        if ($type) {
            $where .= " AND promotion_type = ?";
            $params[] = $type;
        }
        
        $sql = "SELECT p.*, u.username as created_by_name 
                FROM {$this->table} p
                LEFT JOIN users u ON p.created_by = u.id
                {$where}
                ORDER BY p.start_date DESC";
        
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
     * Find by code
     */
    public function findByCode($code, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE promotion_code = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$code, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Count by status
     */
    public function countByStatus($restaurantId, $status)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND promotion_status = ?";
        $result = $this->db->query($sql, [$restaurantId, $status])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get active promotions
     */
    public function getActive($restaurantId)
    {
        $today = date('Y-m-d');
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? AND promotion_status = 'active'
                AND start_date <= ? AND end_date >= ?
                ORDER BY end_date ASC";
        return $this->db->query($sql, [$restaurantId, $today, $today])->fetchAll();
    }

    /**
     * Increment usage count
     */
    public function incrementUsage($id)
    {
        $sql = "UPDATE {$this->table} SET usage_count = usage_count + 1 WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    /**
     * Update status to expired
     */
    public function updateExpiredStatus($restaurantId)
    {
        $today = date('Y-m-d');
        $sql = "UPDATE {$this->table} 
                SET promotion_status = 'expired' 
                WHERE restaurant_id = ? AND end_date < ? AND promotion_status = 'active'";
        return $this->db->query($sql, [$restaurantId, $today]);
    }
}
