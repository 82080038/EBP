<?php

namespace App\Modules\Loyalty\Models;

use App\Core\BaseModel;

class Reward extends BaseModel
{
    protected $table = 'rewards';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'program_id',
        'reward_name',
        'reward_description',
        'reward_type',
        'points_required',
        'discount_percentage',
        'discount_amount',
        'free_item_id',
        'is_available',
        'available_from',
        'available_until',
        'total_quantity',
        'remaining_quantity',
        'max_redemptions_per_customer',
        'max_redemptions_total',
        'image_url',
        'sort_order',
        'is_active'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $programId = null)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ? AND is_active = TRUE";
        
        if ($programId) {
            $where .= " AND program_id = ?";
            $params[] = $programId;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY sort_order ASC, reward_name ASC";
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
     * Get available rewards
     */
    public function getAvailable($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? 
                AND is_active = TRUE 
                AND is_available = TRUE
                AND (available_from IS NULL OR available_from <= CURDATE())
                AND (available_until IS NULL OR available_until >= CURDATE())
                AND (total_quantity IS NULL OR remaining_quantity > 0)
                ORDER BY sort_order ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
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
