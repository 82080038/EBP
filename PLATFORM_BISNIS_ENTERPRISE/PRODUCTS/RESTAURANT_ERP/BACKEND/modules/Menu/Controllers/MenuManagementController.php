<?php

namespace App\Modules\Menu\Controllers;

use App\Core\BaseController;
use App\Modules\Menu\Models\MenuItem;
use App\Modules\Menu\Models\MenuCategory;
use App\Modules\Menu\Models\Modifier;
use App\Modules\Menu\Models\ModifierGroup;
use App\Modules\Menu\Models\Recipe;
use App\Modules\Menu\Services\MenuManagementService;
use App\Core\Auth;

class MenuManagementController extends BaseController
{
    private $menuService;

    public function __construct()
    {
        parent::__construct();
        $this->menuService = new MenuManagementService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get menu categories
     * GET /api/menu/categories
     */
    public function getCategories()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $categories = $this->menuService->getCategories($restaurantId);
        
        $this->jsonResponse($categories);
    }

    /**
     * Create menu category
     * POST /api/menu/categories
     */
    public function createCategory()
    {
        $this->requirePermission('can_manage_menu');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->menuService->createCategory($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Update menu category
     * PUT /api/menu/categories/{id}
     */
    public function updateCategory($id)
    {
        $this->requirePermission('can_manage_menu');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->menuService->updateCategory($id, $restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get menu items
     * GET /api/menu/items
     */
    public function getItems()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $categoryId = $this->request->get('category_id', null);
        $isAvailable = $this->request->get('available', null);
        $isFeatured = $this->request->get('featured', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->menuService->getItems($restaurantId, $categoryId, $isAvailable, $isFeatured, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Get single menu item
     * GET /api/menu/items/{id}
     */
    public function getItem($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $item = $this->menuService->getItem($id, $restaurantId);
        
        if (!$item) {
            $this->jsonResponse(['error' => 'Item not found'], 404);
            return;
        }
        
        $this->jsonResponse($item);
    }

    /**
     * Create menu item
     * POST /api/menu/items
     */
    public function createItem()
    {
        $this->requirePermission('can_manage_menu');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->menuService->createItem($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Update menu item
     * PUT /api/menu/items/{id}
     */
    public function updateItem($id)
    {
        $this->requirePermission('can_manage_menu');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->menuService->updateItem($id, $restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Delete menu item
     * DELETE /api/menu/items/{id}
     */
    public function deleteItem($id)
    {
        $this->requirePermission('can_manage_menu');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $result = $this->menuService->deleteItem($id, $restaurantId);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse(['message' => 'Item deleted successfully']);
    }

    /**
     * Get modifier groups
     * GET /api/menu/modifier-groups
     */
    public function getModifierGroups()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $groups = $this->menuService->getModifierGroups($restaurantId);
        
        $this->jsonResponse($groups);
    }

    /**
     * Create modifier group
     * POST /api/menu/modifier-groups
     */
    public function createModifierGroup()
    {
        $this->requirePermission('can_manage_menu');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->menuService->createModifierGroup($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get modifiers
     * GET /api/menu/modifiers
     */
    public function getModifiers()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $groupId = $this->request->get('group_id', null);
        
        $modifiers = $this->menuService->getModifiers($restaurantId, $groupId);
        
        $this->jsonResponse($modifiers);
    }

    /**
     * Create modifier
     * POST /api/menu/modifiers
     */
    public function createModifier()
    {
        $this->requirePermission('can_manage_menu');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->menuService->createModifier($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Assign modifier to menu item
     * POST /api/menu/items/{itemId}/modifiers
     */
    public function assignModifier($itemId)
    {
        $this->requirePermission('can_manage_menu');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->menuService->assignModifier($itemId, $restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get recipes
     * GET /api/menu/recipes
     */
    public function getRecipes()
    {
        $this->requirePermission('can_manage_menu');
        
        $restaurantId = Auth::user()->restaurant_id;
        $itemId = $this->request->get('item_id', null);
        
        $recipes = $this->menuService->getRecipes($restaurantId, $itemId);
        
        $this->jsonResponse($recipes);
    }

    /**
     * Create recipe
     * POST /api/menu/recipes
     */
    public function createRecipe()
    {
        $this->requirePermission('can_manage_menu');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->menuService->createRecipe($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get menu statistics
     * GET /api/menu/statistics
     */
    public function getStatistics()
    {
        $this->requirePermission('can_manage_menu');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $stats = $this->menuService->getStatistics($restaurantId);
        
        $this->jsonResponse($stats);
    }
}
