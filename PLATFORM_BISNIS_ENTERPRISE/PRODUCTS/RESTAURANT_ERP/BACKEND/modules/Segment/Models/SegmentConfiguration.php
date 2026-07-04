<?php

namespace App\Modules\Segment\Models;

use App\Core\BaseModel;

class SegmentConfiguration extends BaseModel
{
    protected $table = 'segment_configurations';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'segment_type',
        'segment_name',
        'segment_description',
        'segment_config',
        'enabled_features',
        'is_active',
        'created_by'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId)
    {
        $sql = "SELECT sc.*, u.username as created_by_name 
                FROM {$this->table} sc
                LEFT JOIN users u ON sc.created_by = u.id
                WHERE sc.restaurant_id = ? 
                ORDER BY sc.is_active DESC, sc.created_at DESC";
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
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE ORDER BY created_at DESC LIMIT 1";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Get by segment type
     */
    public function getBySegmentType($restaurantId, $segmentType)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND segment_type = ? ORDER BY created_at DESC";
        return $this->db->query($sql, [$restaurantId, $segmentType])->fetchAll();
    }

    /**
     * Update configuration
     */
    public function updateConfiguration($id, $segmentConfig, $enabledFeatures)
    {
        return $this->update($id, [
            'segment_config' => json_encode($segmentConfig),
            'enabled_features' => json_encode($enabledFeatures)
        ]);
    }
}
