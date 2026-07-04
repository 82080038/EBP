<?php

namespace App\Modules\Loyalty\Models;

use App\Core\BaseModel;

class LoyaltyTier extends BaseModel
{
    protected $table = 'loyalty_tiers';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'program_id',
        'tier_name',
        'tier_description',
        'minimum_points',
        'minimum_spend',
        'minimum_visits',
        'points_multiplier',
        'discount_percentage',
        'free_delivery',
        'priority_seating',
        'special_offers',
        'tier_color',
        'tier_icon',
        'sort_order',
        'is_active'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $programId = null)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($programId) {
            $where .= " AND program_id = ?";
            $params[] = $programId;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY sort_order ASC";
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
     * Get active tiers
     */
    public function getActive($restaurantId, $programId = null)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ? AND is_active = TRUE";
        
        if ($programId) {
            $where .= " AND program_id = ?";
            $params[] = $programId;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY sort_order ASC";
        return $this->db->query($sql, $params)->fetchAll();
    }
}
