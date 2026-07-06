<?php

require_once __DIR__ . '/../Services/RecipeService.php';
require_once __DIR__ . '/../../../core/Response.php';

/**
 * Recipe Management Controller
 * 
 * Handles HTTP requests for recipe management operations
 */
class RecipeController
{
    private $recipeService;

    public function __construct()
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? null;
        $this->recipeService = new RecipeService($tenantId, $branchId);
    }

    /**
     * Create a new recipe
     * POST /api/v1/recipes
     */
    public function create()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $data['created_by'] = $_SESSION['user_id'] ?? null;
        
        $result = $this->recipeService->createRecipe($data);
        
        if ($result['success']) {
            Response::json($result, 201);
        } else {
            Response::json($result, 400);
        }
    }

    /**
     * Get recipe by ID
     * GET /api/v1/recipes/{id}
     */
    public function show($id)
    {
        $result = $this->recipeService->getRecipe($id);
        
        if ($result['success']) {
            Response::json($result, 200);
        } else {
            Response::json($result, 404);
        }
    }

    /**
     * Get all recipes
     * GET /api/v1/recipes
     */
    public function index()
    {
        $filters = [
            'category_id' => $_GET['category_id'] ?? null,
            'search' => $_GET['search'] ?? null,
            'is_active' => $_GET['is_active'] ?? null,
            'limit' => $_GET['limit'] ?? null
        ];
        
        $result = $this->recipeService->getRecipes($filters);
        
        Response::json($result, 200);
    }

    /**
     * Update recipe
     * PUT /api/v1/recipes/{id}
     */
    public function update($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $result = $this->recipeService->updateRecipe($id, $data);
        
        if ($result['success']) {
            Response::json($result, 200);
        } else {
            Response::json($result, 400);
        }
    }

    /**
     * Delete recipe
     * DELETE /api/v1/recipes/{id}
     */
    public function delete($id)
    {
        $result = $this->recipeService->deleteRecipe($id);
        
        if ($result['success']) {
            Response::json($result, 200);
        } else {
            Response::json($result, 400);
        }
    }

    /**
     * Get recipe cost analysis
     * GET /api/v1/recipes/{id}/cost-analysis
     */
    public function costAnalysis($id)
    {
        $result = $this->recipeService->getRecipeCostAnalysis($id);
        
        if ($result['success']) {
            Response::json($result, 200);
        } else {
            Response::json($result, 404);
        }
    }

    /**
     * Clone recipe
     * POST /api/v1/recipes/{id}/clone
     */
    public function clone($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $newName = $data['new_name'] ?? 'Copy of Recipe';
        
        $result = $this->recipeService->cloneRecipe($id, $newName);
        
        if ($result['success']) {
            Response::json($result, 201);
        } else {
            Response::json($result, 400);
        }
    }
}
