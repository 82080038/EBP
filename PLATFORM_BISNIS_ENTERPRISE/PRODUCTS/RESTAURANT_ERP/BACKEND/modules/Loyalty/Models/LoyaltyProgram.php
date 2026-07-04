<?php

namespace App\Modules\Loyalty\Models;

use App\Core\BaseModel;

class LoyaltyProgram extends BaseModel
{
    protected $table = 'loyalty_programs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'program_name',
        'program_description',
        'points_per_currency',
        'points_per_visit',
        'minimum_spend_for_points',
        'points_to_currency_ratio',
        'minimum_points_to_redeem',
        'points_expiration_days',
        'is_active'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? ORDER BY created_at DESC";
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
     * Get active programs
     */
    public function getActive($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Count by restaurant
     */
    public function countByRestaurant($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ?";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }
}
