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
        'sourcing_type',
        'is_active',
        'halal_certified',
        'halal_certification_id',
        'production_cost_labor',
        'production_cost_equipment',
        'production_cost_overhead'
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
        $ingredientCost = $result['total_cost'] ?? 0;

        // Get production costs
        $recipe = $this->findById($recipeId);
        $productionCost = ($recipe['production_cost_labor'] ?? 0) +
                         ($recipe['production_cost_equipment'] ?? 0) +
                         ($recipe['production_cost_overhead'] ?? 0);

        return $ingredientCost + $productionCost;
    }

    /**
     * Get recipes by sourcing type
     */
    public function getBySourcingType($restaurantId, $sourcingType)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? AND sourcing_type = ? AND is_active = TRUE
                ORDER BY recipe_name ASC";
        return $this->db->query($sql, [$restaurantId, $sourcingType])->fetchAll();
    }

    /**
     * Get halal certified recipes
     */
    public function getHalalCertified($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? AND halal_certified = TRUE AND is_active = TRUE
                ORDER BY recipe_name ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Update sourcing type
     */
    public function updateSourcingType($recipeId, $sourcingType)
    {
        $sql = "UPDATE {$this->table} SET sourcing_type = ? WHERE id = ?";
        return $this->db->query($sql, [$sourcingType, $recipeId]);
    }

    /**
     * Update halal certification
     */
    public function updateHalalCertification($recipeId, $halalCertified, $certificationId = null)
    {
        $sql = "UPDATE {$this->table} SET halal_certified = ?, halal_certification_id = ? WHERE id = ?";
        return $this->db->query($sql, [$halalCertified, $certificationId, $recipeId]);
    }

    /**
     * Update production costs
     */
    public function updateProductionCosts($recipeId, $labor, $equipment, $overhead)
    {
        $sql = "UPDATE {$this->table} 
                SET production_cost_labor = ?, production_cost_equipment = ?, production_cost_overhead = ? 
                WHERE id = ?";
        return $this->db->query($sql, [$labor, $equipment, $overhead, $recipeId]);
    }
}
