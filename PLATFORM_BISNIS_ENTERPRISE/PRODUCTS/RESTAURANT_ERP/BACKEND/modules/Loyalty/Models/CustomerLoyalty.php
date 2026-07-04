<?php

namespace App\Modules\Loyalty\Models;

use App\Core\BaseModel;

class CustomerLoyalty extends BaseModel
{
    protected $table = 'customer_loyalty';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'customer_id',
        'program_id',
        'current_points',
        'total_points_earned',
        'total_points_redeemed',
        'current_tier_id',
        'total_visits',
        'total_spend',
        'is_active',
        'enrolled_at'
    ];

    /**
     * Get by customer
     */
    public function getByCustomer($customerId, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE customer_id = ? AND restaurant_id = ? AND is_active = TRUE";
        $result = $this->db->query($sql, [$customerId, $restaurantId])->fetch();
        return $result ?: null;
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
     * Count by restaurant
     */
    public function countByRestaurant($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get by tier
     */
    public function getByTier($tierId)
    {
        $sql = "SELECT cl.*, c.first_name, c.last_name 
                FROM {$this->table} cl
                LEFT JOIN customers c ON cl.customer_id = c.id
                WHERE cl.current_tier_id = ? AND cl.is_active = TRUE";
        return $this->db->query($sql, [$tierId])->fetchAll();
    }

    /**
     * Get top customers by points
     */
    public function getTopByPoints($restaurantId, $limit = 10)
    {
        $sql = "SELECT cl.*, c.first_name, c.last_name 
                FROM {$this->table} cl
                LEFT JOIN customers c ON cl.customer_id = c.id
                WHERE cl.restaurant_id = ? AND cl.is_active = TRUE
                ORDER BY cl.current_points DESC
                LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $limit])->fetchAll();
    }
}
