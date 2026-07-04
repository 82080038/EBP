<?php

namespace App\Modules\Menu\Models;

use App\Core\BaseModel;

class MenuCategory extends BaseModel
{
    protected $table = 'menu_categories';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'category_name',
        'category_description',
        'parent_category_id',
        'color_code',
        'icon_url',
        'image_url',
        'sort_order',
        'is_active',
        'available_from',
        'available_until',
        'available_days'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId)
    {
        $sql = "SELECT mc.*, COUNT(mi.id) as item_count 
                FROM {$this->table} mc
                LEFT JOIN menu_items mi ON mc.id = mi.category_id AND mi.is_active = TRUE
                WHERE mc.restaurant_id = ? AND mc.is_active = TRUE
                GROUP BY mc.id
                ORDER BY mc.sort_order ASC, mc.category_name ASC";
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
     * Count by restaurant
     */
    public function countByRestaurant($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get active categories
     */
    public function getActive($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE ORDER BY sort_order ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }
}
