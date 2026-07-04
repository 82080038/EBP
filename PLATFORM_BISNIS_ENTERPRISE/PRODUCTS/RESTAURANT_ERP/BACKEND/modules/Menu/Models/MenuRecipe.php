<?php

namespace App\Modules\Menu\Models;

use App\Core\BaseModel;

class MenuRecipe extends BaseModel
{
    protected $table = 'recipes';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'menu_item_id',
        'recipe_name',
        'recipe_description',
        'yield_quantity',
        'yield_unit_id',
        'is_active'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $itemId = null)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($itemId) {
            $where .= " AND menu_item_id = ?";
            $params[] = $itemId;
        }
        
        $sql = "SELECT r.*, mi.name as menu_item_name 
                FROM {$this->table} r
                LEFT JOIN menu_items mi ON r.menu_item_id = mi.id
                {$where}
                ORDER BY r.recipe_name ASC";
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get by menu item
     */
    public function getByMenuItem($menuItemId)
    {
        $sql = "SELECT r.*, iu.unit_abbreviation as yield_unit_name 
                FROM {$this->table} r
                LEFT JOIN inventory_units iu ON r.yield_unit_id = iu.id
                WHERE r.menu_item_id = ? AND r.is_active = TRUE
                LIMIT 1";
        $result = $this->db->query($sql, [$menuItemId])->fetch();
        return $result ?: null;
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
     * Get recipe ingredients
     */
    public function getIngredients($recipeId)
    {
        $sql = "SELECT ri.*, ii.item_name, ii.item_code, iu.unit_abbreviation 
                FROM recipe_ingredients ri
                LEFT JOIN inventory_items ii ON ri.inventory_item_id = ii.id
                LEFT JOIN inventory_units iu ON ri.unit_id = iu.id
                WHERE ri.recipe_id = ?";
        return $this->db->query($sql, [$recipeId])->fetchAll();
    }

    /**
     * Calculate recipe cost
     */
    public function calculateCost($recipeId)
    {
        $sql = "SELECT SUM(total_cost) as total_cost FROM recipe_ingredients WHERE recipe_id = ?";
        $result = $this->db->query($sql, [$recipeId])->fetch();
        return $result['total_cost'] ?? 0;
    }
}
