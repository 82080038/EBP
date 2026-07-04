<?php

namespace App\Modules\GhostKitchen\Models;

use App\Core\BaseModel;

class VirtualBrandMenuItem extends BaseModel
{
    protected $table = 'virtual_brand_menu_items';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'virtual_brand_id',
        'inventory_item_id',
        'item_name',
        'item_description',
        'price',
        'item_image_url',
        'is_available'
    ];

    /**
     * Get by brand
     */
    public function getByBrand($brandId, $restaurantId)
    {
        $sql = "SELECT vbmi.*, ii.item_name as inventory_item_name 
                FROM {$this->table} vbmi
                LEFT JOIN inventory_items ii ON vbmi.inventory_item_id = ii.id
                WHERE vbmi.virtual_brand_id = ? AND vbmi.restaurant_id = ?
                ORDER BY vbmi.item_name ASC";
        return $this->db->query($sql, [$brandId, $restaurantId])->fetchAll();
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
     * Get available items
     */
    public function getAvailable($brandId, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE virtual_brand_id = ? AND restaurant_id = ? AND is_available = TRUE ORDER BY item_name ASC";
        return $this->db->query($sql, [$brandId, $restaurantId])->fetchAll();
    }

    /**
     * Update availability
     */
    public function updateAvailability($id, $isAvailable)
    {
        return $this->update($id, ['is_available' => $isAvailable]);
    }

    /**
     * Count by brand
     */
    public function countByBrand($brandId, $restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE virtual_brand_id = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$brandId, $restaurantId])->fetch();
        return $result['count'] ?? 0;
    }
}
