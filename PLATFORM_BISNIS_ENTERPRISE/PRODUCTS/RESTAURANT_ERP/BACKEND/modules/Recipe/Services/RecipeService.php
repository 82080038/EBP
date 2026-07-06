<?php

use PDO;
use PDOException;

/**
 * Recipe Management Service
 * 
 * Manages restaurant recipes with ingredients, costs, and nutritional information
 * Essential for food cost control and menu engineering
 */
class RecipeService
{
    private $db;
    private $tenantId;
    private $branchId;

    public function __construct($tenantId = null, $branchId = null)
    {
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
    }

    /**
     * Create a new recipe
     */
    public function createRecipe($data)
    {
        try {
            $sql = "INSERT INTO recipes (tenant_id, branch_id, product_id, recipe_code, recipe_name, 
                    instructions, yield_quantity, yield_unit, preparation_time_minutes, 
                    production_cost_labor, production_cost_equipment, production_cost_overhead, 
                    sourcing_type, difficulty_level, status) 
                    VALUES (:tenant_id, :branch_id, :product_id, :recipe_code, :recipe_name, 
                    :instructions, :yield_quantity, :yield_unit, :preparation_time_minutes, 
                    :production_cost_labor, :production_cost_equipment, :production_cost_overhead, 
                    :sourcing_type, :difficulty_level, :status)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':tenant_id' => $this->tenantId,
                ':branch_id' => $this->branchId,
                ':product_id' => $data['product_id'] ?? null,
                ':recipe_code' => $data['recipe_code'],
                ':recipe_name' => $data['recipe_name'],
                ':instructions' => $data['instructions'] ?? null,
                ':yield_quantity' => $data['yield_quantity'] ?? 1.00,
                ':yield_unit' => $data['yield_unit'] ?? 'portion',
                ':preparation_time_minutes' => $data['preparation_time'] ?? 0,
                ':production_cost_labor' => $data['production_cost_labor'] ?? 0.00,
                ':production_cost_equipment' => $data['production_cost_equipment'] ?? 0.00,
                ':production_cost_overhead' => $data['production_cost_overhead'] ?? 0.00,
                ':sourcing_type' => $data['sourcing_type'] ?? 'supplier_sourced',
                ':difficulty_level' => $data['difficulty_level'] ?? 'MEDIUM',
                ':status' => $data['status'] ?? 'ACTIVE'
            ]);

            $recipeId = $this->db->lastInsertId();

            // Add ingredients if provided
            if (!empty($data['ingredients'])) {
                foreach ($data['ingredients'] as $ingredient) {
                    $this->addRecipeIngredient($recipeId, $ingredient);
                }
            }

            return [
                'success' => true,
                'message' => 'Recipe created successfully',
                'data' => ['recipe_id' => $recipeId]
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to create recipe: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Add ingredient to recipe
     */
    public function addRecipeIngredient($recipeId, $ingredient)
    {
        try {
            $sql = "INSERT INTO recipe_ingredients (recipe_id, inventory_item_id, quantity, unit, 
                    cost_per_unit, is_optional, preparation_note) 
                    VALUES (:recipe_id, :inventory_item_id, :quantity, :unit, 
                    :cost_per_unit, :is_optional, :preparation_note)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':recipe_id' => $recipeId,
                ':inventory_item_id' => $ingredient['inventory_item_id'],
                ':quantity' => $ingredient['quantity'],
                ':unit' => $ingredient['unit'],
                ':cost_per_unit' => $ingredient['cost_per_unit'] ?? 0,
                ':is_optional' => $ingredient['is_optional'] ?? 0,
                ':preparation_note' => $ingredient['preparation_note'] ?? null
            ]);

            return true;
        } catch (PDOException $e) {
            error_log("Failed to add recipe ingredient: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Calculate recipe cost based on ingredients
     */
    public function calculateRecipeCost($recipeId)
    {
        try {
            $sql = "SELECT SUM(ri.quantity * ri.cost_per_unit) as total_cost 
                    FROM recipe_ingredients ri 
                    WHERE ri.recipe_id = :recipe_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':recipe_id' => $recipeId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $totalCost = $result['total_cost'] ?? 0;

            // Get recipe portion size
            $sql = "SELECT portion_size FROM recipes WHERE recipe_id = :recipe_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':recipe_id' => $recipeId]);
            $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

            $portionSize = $recipe['portion_size'] ?? 1;
            $costPerPortion = $portionSize > 0 ? $totalCost / $portionSize : $totalCost;

            // Update recipe with calculated costs
            $sql = "UPDATE recipes SET total_cost = :total_cost, cost_per_portion = :cost_per_portion, 
                    cost_updated_at = NOW() WHERE recipe_id = :recipe_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':total_cost' => $totalCost,
                ':cost_per_portion' => $costPerPortion,
                ':recipe_id' => $recipeId
            ]);

            return [
                'total_cost' => $totalCost,
                'cost_per_portion' => $costPerPortion
            ];
        } catch (PDOException $e) {
            error_log("Failed to calculate recipe cost: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recipe with ingredients
     */
    public function getRecipe($recipeId)
    {
        try {
            $sql = "SELECT r.*, p.product_name, u.username as created_by_name 
                    FROM recipes r 
                    LEFT JOIN products p ON r.product_id = p.product_id 
                    LEFT JOIN users u ON r.created_by = u.user_id 
                    WHERE r.recipe_id = :recipe_id AND r.tenant_id = :tenant_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':recipe_id' => $recipeId,
                ':tenant_id' => $this->tenantId
            ]);
            $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$recipe) {
                return [
                    'success' => false,
                    'message' => 'Recipe not found'
                ];
            }

            // Get ingredients
            $sql = "SELECT ri.*, ii.name as ingredient_name, ii.unit as inventory_unit 
                    FROM recipe_ingredients ri 
                    LEFT JOIN inventory_items ii ON ri.inventory_item_id = ii.inventory_item_id 
                    WHERE ri.recipe_id = :recipe_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':recipe_id' => $recipeId]);
            $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $recipe['ingredients'] = $ingredients;

            return [
                'success' => true,
                'data' => $recipe
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to get recipe: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get all recipes for tenant
     */
    public function getRecipes($filters = [])
    {
        try {
            $sql = "SELECT r.*, p.product_name, 
                    (SELECT COUNT(*) FROM recipe_ingredients WHERE recipe_id = r.recipe_id) as ingredient_count 
                    FROM recipes r 
                    LEFT JOIN products p ON r.product_id = p.product_id 
                    WHERE r.tenant_id = :tenant_id AND r.deleted_at IS NULL";
            
            $params = [':tenant_id' => $this->tenantId];

            if (!empty($filters['product_id'])) {
                $sql .= " AND r.product_id = :product_id";
                $params[':product_id'] = $filters['product_id'];
            }

            if (!empty($filters['search'])) {
                $sql .= " AND (r.recipe_name LIKE :search OR r.recipe_code LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }

            if (!empty($filters['status'])) {
                $sql .= " AND r.status = :status";
                $params[':status'] = $filters['status'];
            }

            $sql .= " ORDER BY r.created_at DESC";

            if (!empty($filters['limit'])) {
                $sql .= " LIMIT :limit";
                $params[':limit'] = (int)$filters['limit'];
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $recipes
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to get recipes: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update recipe
     */
    public function updateRecipe($recipeId, $data)
    {
        try {
            $sql = "UPDATE recipes SET recipe_name = :recipe_name, description = :description, 
                    category_id = :category_id, portion_size = :portion_size, portion_unit = :portion_unit, 
                    preparation_time = :preparation_time, instructions = :instructions, 
                    updated_at = NOW() 
                    WHERE recipe_id = :recipe_id AND tenant_id = :tenant_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':recipe_name' => $data['recipe_name'],
                ':description' => $data['description'] ?? null,
                ':category_id' => $data['category_id'] ?? null,
                ':portion_size' => $data['portion_size'] ?? 1,
                ':portion_unit' => $data['portion_unit'] ?? 'serving',
                ':preparation_time' => $data['preparation_time'] ?? 0,
                ':instructions' => $data['instructions'] ?? null,
                ':recipe_id' => $recipeId,
                ':tenant_id' => $this->tenantId
            ]);

            // Update ingredients if provided
            if (!empty($data['ingredients'])) {
                // Remove existing ingredients
                $this->db->prepare("DELETE FROM recipe_ingredients WHERE recipe_id = :recipe_id")
                    ->execute([':recipe_id' => $recipeId]);
                
                // Add new ingredients
                foreach ($data['ingredients'] as $ingredient) {
                    $this->addRecipeIngredient($recipeId, $ingredient);
                }
            }

            // Recalculate cost
            $this->calculateRecipeCost($recipeId);

            return [
                'success' => true,
                'message' => 'Recipe updated successfully'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to update recipe: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete recipe
     */
    public function deleteRecipe($recipeId)
    {
        try {
            // Check if recipe is used in menu items
            $sql = "SELECT COUNT(*) as count FROM menu_items WHERE recipe_id = :recipe_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':recipe_id' => $recipeId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete recipe: It is used in menu items'
                ];
            }

            // Delete recipe ingredients
            $this->db->prepare("DELETE FROM recipe_ingredients WHERE recipe_id = :recipe_id")
                ->execute([':recipe_id' => $recipeId]);

            // Delete recipe
            $this->db->prepare("DELETE FROM recipes WHERE recipe_id = :recipe_id AND tenant_id = :tenant_id")
                ->execute([
                    ':recipe_id' => $recipeId,
                    ':tenant_id' => $this->tenantId
                ]);

            return [
                'success' => true,
                'message' => 'Recipe deleted successfully'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete recipe: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get recipe cost analysis
     */
    public function getRecipeCostAnalysis($recipeId)
    {
        try {
            $recipe = $this->getRecipe($recipeId);
            if (!$recipe['success']) {
                return $recipe;
            }

            $recipeData = $recipe['data'];

            // Get ingredient cost breakdown
            $sql = "SELECT ri.*, ii.name as ingredient_name, 
                    (ri.quantity * ri.cost_per_unit) as ingredient_cost 
                    FROM recipe_ingredients ri 
                    LEFT JOIN inventory_items ii ON ri.inventory_item_id = ii.inventory_item_id 
                    WHERE ri.recipe_id = :recipe_id 
                    ORDER BY ingredient_cost DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':recipe_id' => $recipeId]);
            $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate cost percentages
            $totalCost = $recipeData['total_cost'] ?? 0;
            foreach ($ingredients as &$ingredient) {
                $ingredient['cost_percentage'] = $totalCost > 0 
                    ? round(($ingredient['ingredient_cost'] / $totalCost) * 100, 2) 
                    : 0;
            }

            return [
                'success' => true,
                'data' => [
                    'recipe' => $recipeData,
                    'ingredients' => $ingredients,
                    'total_cost' => $totalCost,
                    'cost_per_portion' => $recipeData['cost_per_portion'] ?? 0
                ]
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to get recipe cost analysis: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Clone recipe for new version
     */
    public function cloneRecipe($recipeId, $newName)
    {
        try {
            $recipe = $this->getRecipe($recipeId);
            if (!$recipe['success']) {
                return $recipe;
            }

            $recipeData = $recipe['data'];

            // Create new recipe
            $newRecipe = [
                'recipe_code' => $recipeData['recipe_code'] . '_COPY',
                'recipe_name' => $newName,
                'description' => $recipeData['description'],
                'category_id' => $recipeData['category_id'],
                'portion_size' => $recipeData['portion_size'],
                'portion_unit' => $recipeData['portion_unit'],
                'preparation_time' => $recipeData['preparation_time'],
                'instructions' => $recipeData['instructions'],
                'ingredients' => array_map(function($ing) {
                    return [
                        'inventory_item_id' => $ing['inventory_item_id'],
                        'quantity' => $ing['quantity'],
                        'unit' => $ing['unit'],
                        'cost_per_unit' => $ing['cost_per_unit'],
                        'is_optional' => $ing['is_optional'],
                        'preparation_note' => $ing['preparation_note']
                    ];
                }, $recipeData['ingredients'])
            ];

            return $this->createRecipe($newRecipe);
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to clone recipe: ' . $e->getMessage()
            ];
        }
    }
}
