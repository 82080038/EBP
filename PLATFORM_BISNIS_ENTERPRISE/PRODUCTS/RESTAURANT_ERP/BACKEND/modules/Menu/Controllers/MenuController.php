<?php

if (!class_exists('MenuService')) {
    require_once __DIR__ . '/../Services/MenuService.php';
}


// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';


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
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $hasPermission = $permissionMiddleware->check($user['user_id'], 'CATEGORY_VIEW');
        
        if (!$hasPermission) {
            Response::error("Permission denied: CATEGORY_VIEW", 403);
        }

        $tenantId = $user['tenant_id'] ?? 1;
        $categories = $this->menuService->getAllCategories($tenantId);

        Response::success($categories);
    }

    public function getCategory(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'MENU_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $categoryId = $request['category_id'] ?? 0;

        $category = $this->menuService->getCategory($tenantId, $categoryId);

        if (!$category) {
            return Response::error(Messages::CATEGORY_NOT_FOUND, 404);
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
            return Response::error(Messages::CATEGORY_CODE_REQUIRED, 400);
        }
        if (empty($data['category_name'])) {
            return Response::error(Messages::CATEGORY_NAME_REQUIRED, 400);
        }

        $result = $this->menuService->createCategory($tenantId, $data);

        if ($result) {
            return Response::success(['message' => Messages::CATEGORY_CREATED]);
        }

        return Response::error(Messages::CATEGORY_FAILED_CREATE, 500);
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
            return Response::error(Messages::CATEGORY_ID_REQUIRED, 400);
        }
        if (empty($data['category_name'])) {
            return Response::error(Messages::CATEGORY_NAME_REQUIRED, 400);
        }

        $result = $this->menuService->updateCategory($tenantId, $categoryId, $data);

        if ($result) {
            return Response::success(['message' => Messages::CATEGORY_UPDATED]);
        }

        return Response::error(Messages::CATEGORY_FAILED_UPDATE, 500);
    }

    public function deleteCategory(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'MENU_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $categoryId = $request['category_id'] ?? 0;

        // Validation
        if (empty($categoryId)) {
            return Response::error(Messages::CATEGORY_ID_REQUIRED, 400);
        }

        $result = $this->menuService->deleteCategory($tenantId, $categoryId);

        if ($result) {
            return Response::success(['message' => Messages::CATEGORY_DELETED]);
        }

        return Response::error(Messages::CATEGORY_FAILED_DELETE, 500);
    }

    // Product Endpoints
    public function getProducts(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'PRODUCT_VIEW');

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
            return Response::error(Messages::PRODUCT_NOT_FOUND, 404);
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
            return Response::error(Messages::PRODUCT_CODE_REQUIRED, 400);
        }
        if (empty($data['product_name'])) {
            return Response::error(Messages::PRODUCT_NAME_REQUIRED, 400);
        }
        if (empty($data['price'])) {
            return Response::error(Messages::PRODUCT_PRICE_REQUIRED, 400);
        }

        $result = $this->menuService->createProduct($tenantId, $data);

        if ($result) {
            return Response::success(['message' => Messages::PRODUCT_CREATED]);
        }

        return Response::error(Messages::PRODUCT_FAILED_CREATE, 500);
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
            return Response::error(Messages::PRODUCT_ID_REQUIRED, 400);
        }
        if (empty($data['product_name'])) {
            return Response::error(Messages::PRODUCT_NAME_REQUIRED, 400);
        }
        if (empty($data['price'])) {
            return Response::error(Messages::PRODUCT_PRICE_REQUIRED, 400);
        }

        $result = $this->menuService->updateProduct($tenantId, $productId, $data);

        if ($result) {
            return Response::success(['message' => Messages::PRODUCT_UPDATED]);
        }

        return Response::error(Messages::PRODUCT_FAILED_UPDATE, 500);
    }

    public function deleteProduct(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'MENU_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $productId = $request['product_id'] ?? 0;

        // Validation
        if (empty($productId)) {
            return Response::error(Messages::PRODUCT_ID_REQUIRED, 400);
        }

        $result = $this->menuService->deleteProduct($tenantId, $productId);

        if ($result) {
            return Response::success(['message' => Messages::PRODUCT_DELETED]);
        }

        return Response::error(Messages::PRODUCT_FAILED_DELETE, 500);
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
            return Response::error(Messages::RECIPE_NOT_FOUND, 404);
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
            return Response::error(Messages::PRODUCT_ID_REQUIRED, 400);
        }
        if (empty($data['recipe_code'])) {
            return Response::error(Messages::RECIPE_CODE_REQUIRED, 400);
        }
        if (empty($data['recipe_name'])) {
            return Response::error(Messages::RECIPE_NAME_REQUIRED, 400);
        }

        $result = $this->menuService->createRecipe($tenantId, $data);

        if ($result) {
            return Response::success(['message' => Messages::RECIPE_CREATED]);
        }

        return Response::error(Messages::RECIPE_FAILED_CREATE, 500);
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
            return Response::error(Messages::RECIPE_ID_REQUIRED, 400);
        }
        if (empty($data['recipe_name'])) {
            return Response::error(Messages::RECIPE_NAME_REQUIRED, 400);
        }

        $result = $this->menuService->updateRecipe($tenantId, $recipeId, $data);

        if ($result) {
            return Response::success(['message' => Messages::RECIPE_UPDATED]);
        }

        return Response::error(Messages::RECIPE_FAILED_UPDATE, 500);
    }

    public function deleteRecipe(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'MENU_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $recipeId = $request['recipe_id'] ?? 0;

        // Validation
        if (empty($recipeId)) {
            return Response::error(Messages::RECIPE_ID_REQUIRED, 400);
        }

        $result = $this->menuService->deleteRecipe($tenantId, $recipeId);

        if ($result) {
            return Response::success(['message' => Messages::RECIPE_DELETED]);
        }

        return Response::error(Messages::RECIPE_FAILED_DELETE, 500);
    }
}
