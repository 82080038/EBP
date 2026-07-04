<?php

namespace App\Modules\Menu\Models;

use App\Core\BaseModel;

class Modifier extends BaseModel
{
    protected $table = 'modifiers';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'modifier_name',
        'modifier_description',
        'modifier_group_id',
        'price_adjustment',
        'modifier_type',
        'is_required',
        'min_selection',
        'max_selection',
        'display_order',
        'is_active'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $groupId = null)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($groupId) {
            $where .= " AND modifier_group_id = ?";
            $params[] = $groupId;
        }
        
        $sql = "SELECT m.*, mg.group_name 
                FROM {$this->table} m
                LEFT JOIN modifier_groups mg ON m.modifier_group_id = mg.id
                {$where}
                ORDER BY m.display_order ASC, m.modifier_name ASC";
        
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
     * Get by menu item
     */
    public function getByMenuItem($menuItemId)
    {
        $sql = "SELECT m.*, mim.is_default 
                FROM {$this->table} m
                INNER JOIN menu_item_modifiers mim ON m.id = mim.modifier_id
                WHERE mim.menu_item_id = ? AND m.is_active = TRUE
                ORDER BY mim.display_order ASC";
        
        return $this->db->query($sql, [$menuItemId])->fetchAll();
    }

    /**
     * Get active modifiers
     */
    public function getActive($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE ORDER BY display_order ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }
}
