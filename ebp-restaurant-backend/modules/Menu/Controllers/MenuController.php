<?php

if (!class_exists('MenuService')) {
    require_once __DIR__ . '/../Services/MenuService.php';
}
if (!class_exists('AuthMiddleware')) {
    require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
}
if (!class_exists('PermissionMiddleware')) {
    require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';
}
if (!class_exists('Response')) {
    require_once __DIR__ . '/../../../core/Response.php';
}

class MenuController
{
    private $menuService;

    public function __construct()
    {
        $this->menuService = new MenuService();
    }

    // Category Endpoints
    public function getCategories(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'MENU_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $categories = $this->menuService->getAllCategories($tenantId);

        return Response::success($categories);
    }

    public function getCategory(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'MENU_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $categoryId = $request['category_id'] ?? 0;

        $category = $this->menuService->getCategory($tenantId, $categoryId);

        if (!$category) {
            return Response::error('Category not found', 404);
        }

        return Response::success($category);
    }

    public function createCategory(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'MENU_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($data['category_code'])) {
            return Response::error('Category code is required', 400);
        }
        if (empty($data['category_name'])) {
            return Response::error('Category name is required', 400);
        }

        $result = $this->menuService->createCategory($tenantId, $data);

        if ($result) {
            return Response::success(['message' => 'Category created successfully']);
        }

        return Response::error('Failed to create category', 500);
    }

    public function updateCategory(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'MENU_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $categoryId = $request['category_id'] ?? 0;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($categoryId)) {
            return Response::error('Category ID is required', 400);
        }
        if (empty($data['category_name'])) {
            return Response::error('Category name is required', 400);
        }

        $result = $this->menuService->updateCategory($tenantId, $categoryId, $data);

        if ($result) {
            return Response::success(['message' => 'Category updated successfully']);
        }

        return Response::error('Failed to update category', 500);
    }

    public function deleteCategory(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'MENU_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $categoryId = $request['category_id'] ?? 0;

        // Validation
        if (empty($categoryId)) {
            return Response::error('Category ID is required', 400);
        }

        $result = $this->menuService->deleteCategory($tenantId, $categoryId);

        if ($result) {
            return Response::success(['message' => 'Category deleted successfully']);
        }

        return Response::error('Failed to delete category', 500);
    }

    // Product Endpoints
    public function getProducts(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'MENU_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $categoryId = $request['category_id'] ?? null;
        $products = $this->menuService->getAllProducts($tenantId, $categoryId);

        return Response::success($products);
    }

    public function getProduct(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'MENU_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $productId = $request['product_id'] ?? 0;

        $product = $this->menuService->getProduct($tenantId, $productId);

        if (!$product) {
            return Response::error('Product not found', 404);
        }

        return Response::success($product);
    }

    public function createProduct(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'MENU_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($data['product_code'])) {
            return Response::error('Product code is required', 400);
        }
        if (empty($data['product_name'])) {
            return Response::error('Product name is required', 400);
        }
        if (empty($data['price'])) {
            return Response::error('Price is required', 400);
        }

        $result = $this->menuService->createProduct($tenantId, $data);

        if ($result) {
            return Response::success(['message' => 'Product created successfully']);
        }

        return Response::error('Failed to create product', 500);
    }

    public function updateProduct(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'MENU_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $productId = $request['product_id'] ?? 0;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($productId)) {
            return Response::error('Product ID is required', 400);
        }
        if (empty($data['product_name'])) {
            return Response::error('Product name is required', 400);
        }
        if (empty($data['price'])) {
            return Response::error('Price is required', 400);
        }

        $result = $this->menuService->updateProduct($tenantId, $productId, $data);

        if ($result) {
            return Response::success(['message' => 'Product updated successfully']);
        }

        return Response::error('Failed to update product', 500);
    }

    public function deleteProduct(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'MENU_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $productId = $request['product_id'] ?? 0;

        // Validation
        if (empty($productId)) {
            return Response::error('Product ID is required', 400);
        }

        $result = $this->menuService->deleteProduct($tenantId, $productId);

        if ($result) {
            return Response::success(['message' => 'Product deleted successfully']);
        }

        return Response::error('Failed to delete product', 500);
    }

    // Recipe Endpoints
    public function getRecipes(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'MENU_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $recipes = $this->menuService->getAllRecipes($tenantId);

        return Response::success($recipes);
    }

    public function getRecipe(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'MENU_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $recipeId = $request['recipe_id'] ?? 0;

        $recipe = $this->menuService->getRecipe($tenantId, $recipeId);

        if (!$recipe) {
            return Response::error('Recipe not found', 404);
        }

        return Response::success($recipe);
    }

    public function createRecipe(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'MENU_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($data['product_id'])) {
            return Response::error('Product ID is required', 400);
        }
        if (empty($data['recipe_code'])) {
            return Response::error('Recipe code is required', 400);
        }
        if (empty($data['recipe_name'])) {
            return Response::error('Recipe name is required', 400);
        }

        $result = $this->menuService->createRecipe($tenantId, $data);

        if ($result) {
            return Response::success(['message' => 'Recipe created successfully']);
        }

        return Response::error('Failed to create recipe', 500);
    }

    public function updateRecipe(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'MENU_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $recipeId = $request['recipe_id'] ?? 0;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($recipeId)) {
            return Response::error('Recipe ID is required', 400);
        }
        if (empty($data['recipe_name'])) {
            return Response::error('Recipe name is required', 400);
        }

        $result = $this->menuService->updateRecipe($tenantId, $recipeId, $data);

        if ($result) {
            return Response::success(['message' => 'Recipe updated successfully']);
        }

        return Response::error('Failed to update recipe', 500);
    }

    public function deleteRecipe(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'MENU_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $recipeId = $request['recipe_id'] ?? 0;

        // Validation
        if (empty($recipeId)) {
            return Response::error('Recipe ID is required', 400);
        }

        $result = $this->menuService->deleteRecipe($tenantId, $recipeId);

        if ($result) {
            return Response::success(['message' => 'Recipe deleted successfully']);
        }

        return Response::error('Failed to delete recipe', 500);
    }
}
