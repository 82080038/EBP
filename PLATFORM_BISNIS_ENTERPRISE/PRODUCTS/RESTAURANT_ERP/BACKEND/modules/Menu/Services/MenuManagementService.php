<?php

namespace App\Modules\Menu\Services;

use App\Modules\Menu\Models\MenuItem;
use App\Modules\Menu\Models\MenuCategory;
use App\Modules\Menu\Models\Modifier;
use App\Modules\Menu\Models\ModifierGroup;
use App\Modules\Menu\Models\Recipe;
use App\Core\Database;

class MenuManagementService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get menu categories
     */
    public function getCategories($restaurantId)
    {
        $categoryModel = new MenuCategory();
        return $categoryModel->getByRestaurant($restaurantId);
    }

    /**
     * Create menu category
     */
    public function createCategory($restaurantId, $data)
    {
        $categoryModel = new MenuCategory();
        
        $categoryData = [
            'restaurant_id' => $restaurantId,
            'category_name' => $data->category_name,
            'category_description' => $data->category_description ?? null,
            'parent_category_id' => $data->parent_category_id ?? null,
            'color_code' => $data->color_code ?? null,
            'icon_url' => $data->icon_url ?? null,
            'image_url' => $data->image_url ?? null,
            'sort_order' => $data->sort_order ?? 0,
            'is_active' => true,
            'available_from' => $data->available_from ?? null,
            'available_until' => $data->available_until ?? null,
            'available_days' => $data->available_days ?? null
        ];
        
        $categoryId = $categoryModel->create($categoryData);
        
        if (!$categoryId) {
            return ['success' => false, 'message' => 'Failed to create category'];
        }
        
        return ['success' => true, 'message' => 'Category created', 'category_id' => $categoryId];
    }

    /**
     * Update menu category
     */
    public function updateCategory($id, $restaurantId, $data)
    {
        $categoryModel = new MenuCategory();
        $category = $categoryModel->findById($id, $restaurantId);
        
        if (!$category) {
            return ['success' => false, 'message' => 'Category not found'];
        }
        
        $updateData = [];
        
        if (isset($data->category_name)) {
            $updateData['category_name'] = $data->category_name;
        }
        if (isset($data->category_description)) {
            $updateData['category_description'] = $data->category_description;
        }
        if (isset($data->color_code)) {
            $updateData['color_code'] = $data->color_code;
        }
        if (isset($data->sort_order)) {
            $updateData['sort_order'] = $data->sort_order;
        }
        if (isset($data->is_active)) {
            $updateData['is_active'] = $data->is_active;
        }
        
        $updated = $categoryModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update category'];
        }
        
        return ['success' => true, 'message' => 'Category updated'];
    }

    /**
     * Get menu items
     */
    public function getItems($restaurantId, $categoryId, $isAvailable, $isFeatured, $page, $limit)
    {
        $itemModel = new MenuItem();
        return $itemModel->getPaginated($restaurantId, $categoryId, $isAvailable, $isFeatured, $page, $limit);
    }

    /**
     * Get single menu item
     */
    public function getItem($id, $restaurantId)
    {
        $itemModel = new MenuItem();
        $item = $itemModel->findById($id, $restaurantId);
        
        if ($item) {
            // Get modifiers
            $item['modifiers'] = $this->getItemModifiers($id);
            
            // Get pricing
            $item['pricing'] = $this->getItemPricing($id);
            
            // Get recipe if exists
            $recipeModel = new Recipe();
            $recipe = $recipeModel->getByMenuItem($id);
            if ($recipe) {
                $item['recipe'] = $recipe;
            }
        }
        
        return $item;
    }

    /**
     * Get item modifiers
     */
    private function getItemModifiers($itemId)
    {
        $sql = "SELECT m.*, mim.is_default, mim.display_order 
                FROM menu_item_modifiers mim
                LEFT JOIN modifiers m ON mim.modifier_id = m.id
                WHERE mim.menu_item_id = ? AND m.is_active = TRUE
                ORDER BY mim.display_order ASC";
        
        return $this->db->query($sql, [$itemId])->fetchAll();
    }

    /**
     * Get item pricing
     */
    private function getItemPricing($itemId)
    {
        $sql = "SELECT * FROM menu_pricing WHERE menu_item_id = ? AND is_active = TRUE";
        return $this->db->query($sql, [$itemId])->fetchAll();
    }

    /**
     * Create menu item
     */
    public function createItem($restaurantId, $data)
    {
        $itemModel = new MenuItem();
        
        $itemData = [
            'restaurant_id' => $restaurantId,
            'category_id' => $data->category_id,
            'item_code' => $data->item_code,
            'name' => $data->name,
            'description' => $data->description ?? null,
            'base_price' => $data->base_price,
            'cost_price' => $data->cost_price ?? null,
            'is_available' => true,
            'image_url' => $data->image_url ?? null,
            'thumbnail_url' => $data->thumbnail_url ?? null,
            'display_order' => $data->display_order ?? 0,
            'is_featured' => $data->is_featured ?? false,
            'is_new' => $data->is_new ?? false,
            'is_vegetarian' => $data->is_vegetarian ?? false,
            'is_vegan' => $data->is_vegan ?? false,
            'is_gluten_free' => $data->is_gluten_free ?? false,
            'is_spicy' => $data->is_spicy ?? false,
            'spice_level' => $data->spice_level ?? 0,
            'preparation_time' => $data->preparation_time ?? null,
            'preparation_station' => $data->preparation_station ?? null,
            'tax_rate' => $data->tax_rate ?? 0.00,
            'is_active' => true
        ];
        
        $itemId = $itemModel->create($itemData);
        
        if (!$itemId) {
            return ['success' => false, 'message' => 'Failed to create menu item'];
        }
        
        // Assign modifiers if provided
        if (isset($data->modifiers) && is_array($data->modifiers)) {
            foreach ($data->modifiers as $modifier) {
                $this->assignModifierToItem($itemId, $modifier->modifier_id, $modifier->is_default ?? false);
            }
        }
        
        return ['success' => true, 'message' => 'Menu item created', 'item_id' => $itemId];
    }

    /**
     * Update menu item
     */
    public function updateItem($id, $restaurantId, $data)
    {
        $itemModel = new MenuItem();
        $item = $itemModel->findById($id, $restaurantId);
        
        if (!$item) {
            return ['success' => false, 'message' => 'Item not found'];
        }
        
        $updateData = [];
        
        if (isset($data->name)) {
            $updateData['name'] = $data->name;
        }
        if (isset($data->description)) {
            $updateData['description'] = $data->description;
        }
        if (isset($data->base_price)) {
            $updateData['base_price'] = $data->base_price;
        }
        if (isset($data->is_available)) {
            $updateData['is_available'] = $data->is_available;
        }
        if (isset($data->is_featured)) {
            $updateData['is_featured'] = $data->is_featured;
        }
        if (isset($data->is_active)) {
            $updateData['is_active'] = $data->is_active;
        }
        if (isset($data->display_order)) {
            $updateData['display_order'] = $data->display_order;
        }
        
        $updated = $itemModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update item'];
        }
        
        return ['success' => true, 'message' => 'Item updated'];
    }

    /**
     * Delete menu item
     */
    public function deleteItem($id, $restaurantId)
    {
        $itemModel = new MenuItem();
        $item = $itemModel->findById($id, $restaurantId);
        
        if (!$item) {
            return ['success' => false, 'message' => 'Item not found'];
        }
        
        $deleted = $itemModel->delete($id);
        
        if (!$deleted) {
            return ['success' => false, 'message' => 'Failed to delete item'];
        }
        
        return ['success' => true, 'message' => 'Item deleted'];
    }

    /**
     * Get modifier groups
     */
    public function getModifierGroups($restaurantId)
    {
        $groupModel = new ModifierGroup();
        return $groupModel->getByRestaurant($restaurantId);
    }

    /**
     * Create modifier group
     */
    public function createModifierGroup($restaurantId, $data)
    {
        $groupModel = new ModifierGroup();
        
        $groupData = [
            'restaurant_id' => $restaurantId,
            'group_name' => $data->group_name,
            'group_description' => $data->group_description ?? null,
            'min_selection' => $data->min_selection ?? 0,
            'max_selection' => $data->max_selection ?? 1,
            'selection_type' => $data->selection_type ?? 'single',
            'display_order' => $data->display_order ?? 0,
            'is_active' => true
        ];
        
        $groupId = $groupModel->create($groupData);
        
        if (!$groupId) {
            return ['success' => false, 'message' => 'Failed to create modifier group'];
        }
        
        return ['success' => true, 'message' => 'Modifier group created', 'group_id' => $groupId];
    }

    /**
     * Get modifiers
     */
    public function getModifiers($restaurantId, $groupId)
    {
        $modifierModel = new Modifier();
        return $modifierModel->getByRestaurant($restaurantId, $groupId);
    }

    /**
     * Create modifier
     */
    public function createModifier($restaurantId, $data)
    {
        $modifierModel = new Modifier();
        
        $modifierData = [
            'restaurant_id' => $restaurantId,
            'modifier_name' => $data->modifier_name,
            'modifier_description' => $data->modifier_description ?? null,
            'modifier_group_id' => $data->modifier_group_id ?? null,
            'price_adjustment' => $data->price_adjustment ?? 0.00,
            'modifier_type' => $data->modifier_type ?? 'add',
            'is_required' => $data->is_required ?? false,
            'min_selection' => $data->min_selection ?? 0,
            'max_selection' => $data->max_selection ?? 1,
            'display_order' => $data->display_order ?? 0,
            'is_active' => true
        ];
        
        $modifierId = $modifierModel->create($modifierData);
        
        if (!$modifierId) {
            return ['success' => false, 'message' => 'Failed to create modifier'];
        }
        
        return ['success' => true, 'message' => 'Modifier created', 'modifier_id' => $modifierId];
    }

    /**
     * Assign modifier to menu item
     */
    public function assignModifier($itemId, $restaurantId, $data)
    {
        $itemModel = new MenuItem();
        $item = $itemModel->findById($itemId, $restaurantId);
        
        if (!$item) {
            return ['success' => false, 'message' => 'Item not found'];
        }
        
        $assigned = $this->assignModifierToItem($itemId, $data->modifier_id, $data->is_default ?? false);
        
        if (!$assigned) {
            return ['success' => false, 'message' => 'Failed to assign modifier'];
        }
        
        return ['success' => true, 'message' => 'Modifier assigned'];
    }

    /**
     * Assign modifier to item
     */
    private function assignModifierToItem($itemId, $modifierId, $isDefault)
    {
        $sql = "INSERT INTO menu_item_modifiers (menu_item_id, modifier_id, is_default, display_order)
                VALUES (?, ?, ?, 0)
                ON DUPLICATE KEY UPDATE is_default = VALUES(is_default)";
        
        return $this->db->query($sql, [$itemId, $modifierId, $isDefault]);
    }

    /**
     * Get recipes
     */
    public function getRecipes($restaurantId, $itemId)
    {
        $recipeModel = new Recipe();
        return $recipeModel->getByRestaurant($restaurantId, $itemId);
    }

    /**
     * Create recipe
     */
    public function createRecipe($restaurantId, $data)
    {
        $recipeModel = new Recipe();
        
        $recipeData = [
            'restaurant_id' => $restaurantId,
            'menu_item_id' => $data->menu_item_id,
            'recipe_name' => $data->recipe_name,
            'recipe_description' => $data->recipe_description ?? null,
            'yield_quantity' => $data->yield_quantity,
            'yield_unit_id' => $data->yield_unit_id,
            'is_active' => true
        ];
        
        $recipeId = $recipeModel->create($recipeData);
        
        if (!$recipeId) {
            return ['success' => false, 'message' => 'Failed to create recipe'];
        }
        
        // Add recipe ingredients
        if (isset($data->ingredients) && is_array($data->ingredients)) {
            foreach ($data->ingredients as $ingredient) {
                $this->addRecipeIngredient($recipeId, $ingredient);
            }
        }
        
        return ['success' => true, 'message' => 'Recipe created', 'recipe_id' => $recipeId];
    }

    /**
     * Add recipe ingredient
     */
    private function addRecipeIngredient($recipeId, $data)
    {
        $sql = "INSERT INTO recipe_ingredients (recipe_id, inventory_item_id, quantity, unit_id, unit_cost, total_cost)
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $totalCost = ($data->unit_cost ?? 0) * $data->quantity;
        
        $this->db->query($sql, [
            $recipeId,
            $data->inventory_item_id,
            $data->quantity,
            $data->unit_id,
            $data->unit_cost ?? null,
            $totalCost
        ]);
    }

    /**
     * Get statistics
     */
    public function getStatistics($restaurantId)
    {
        $itemModel = new MenuItem();
        $categoryModel = new MenuCategory();
        
        // Total items
        $totalItems = $itemModel->countByRestaurant($restaurantId);
        
        // Active items
        $activeItems = $itemModel->countActive($restaurantId);
        
        // Featured items
        $featuredItems = $itemModel->countFeatured($restaurantId);
        
        // Total categories
        $totalCategories = $categoryModel->countByRestaurant($restaurantId);
        
        return [
            'total_items' => $totalItems,
            'active_items' => $activeItems,
            'featured_items' => $featuredItems,
            'total_categories' => $totalCategories
        ];
    }
}
