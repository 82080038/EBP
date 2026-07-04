<?php

namespace App\Modules\Menu\Models;

use App\Core\BaseModel;

class ModifierGroup extends BaseModel
{
    protected $table = 'modifier_groups';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'group_name',
        'group_description',
        'min_selection',
        'max_selection',
        'selection_type',
        'display_order',
        'is_active'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId)
    {
        $sql = "SELECT mg.*, COUNT(m.id) as modifier_count 
                FROM {$this->table} mg
                LEFT JOIN modifiers m ON mg.id = m.modifier_group_id AND m.is_active = TRUE
                WHERE mg.restaurant_id = ? AND mg.is_active = TRUE
                GROUP BY mg.id
                ORDER BY mg.display_order ASC, mg.group_name ASC";
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
     * Get modifiers in group
     */
    public function getModifiers($groupId)
    {
        $sql = "SELECT * FROM modifiers WHERE modifier_group_id = ? AND is_active = TRUE ORDER BY display_order ASC";
        return $this->db->query($sql, [$groupId])->fetchAll();
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
