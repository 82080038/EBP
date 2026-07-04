<?php

namespace App\Modules\Marketing\Models;

use App\Core\BaseModel;

class BrandAsset extends BaseModel
{
    protected $table = 'brand_assets';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'asset_name',
        'asset_type',
        'asset_category',
        'file_url',
        'file_name',
        'file_size',
        'file_format',
        'usage_context',
        'dimensions',
        'is_active',
        'uploaded_by'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $type)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($type) {
            $where .= " AND asset_type = ?";
            $params[] = $type;
        }
        
        $sql = "SELECT ba.*, u.username as uploaded_by_name 
                FROM {$this->table} ba
                LEFT JOIN users u ON ba.uploaded_by = u.id
                {$where}
                ORDER BY ba.asset_name ASC";
        
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
     * Get by type
     */
    public function getByType($restaurantId, $assetType)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND asset_type = ? AND is_active = TRUE ORDER BY asset_name ASC";
        return $this->db->query($sql, [$restaurantId, $assetType])->fetchAll();
    }

    /**
     * Get active assets
     */
    public function getActive($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE ORDER BY asset_name ASC";
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
