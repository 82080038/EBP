<?php

namespace App\Modules\GhostKitchen\Models;

use App\Core\BaseModel;

class VirtualBrand extends BaseModel
{
    protected $table = 'virtual_brands';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'brand_name',
        'brand_code',
        'brand_description',
        'brand_logo_url',
        'brand_color_hex',
        'cuisine_type',
        'price_range',
        'brand_status',
        'target_audience',
        'created_by'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $status)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($status) {
            $where .= " AND brand_status = ?";
            $params[] = $status;
        }
        
        $sql = "SELECT vb.*, u.username as created_by_name 
                FROM {$this->table} vb
                LEFT JOIN users u ON vb.created_by = u.id
                {$where}
                ORDER BY vb.brand_name ASC";
        
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
    public function findByCode($brandCode, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE brand_code = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$brandCode, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Count by status
     */
    public function countByStatus($restaurantId, $status)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND brand_status = ?";
        $result = $this->db->query($sql, [$restaurantId, $status])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get active
     */
    public function getActive($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND brand_status = 'active' ORDER BY brand_name ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }
}
