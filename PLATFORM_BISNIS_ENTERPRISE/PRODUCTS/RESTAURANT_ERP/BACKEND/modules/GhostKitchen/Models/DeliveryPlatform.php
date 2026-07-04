<?php

namespace App\Modules\GhostKitchen\Models;

use App\Core\BaseModel;

class DeliveryPlatform extends BaseModel
{
    protected $table = 'delivery_platforms';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'platform_name',
        'platform_type',
        'api_key',
        'api_secret',
        'webhook_url',
        'platform_config',
        'is_active'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? ORDER BY platform_name ASC";
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
     * Get active
     */
    public function getActive($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE ORDER BY platform_name ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Count active
     */
    public function countActive($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get by type
     */
    public function getByType($restaurantId, $platformType)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND platform_type = ? ORDER BY platform_name ASC";
        return $this->db->query($sql, [$restaurantId, $platformType])->fetchAll();
    }
}
