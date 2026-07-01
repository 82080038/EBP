<?php

require_once __DIR__ . '/../Services/ProductVariantService.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';

class ProductVariantController
{
    private $service;

    public function __construct()
    {
        $this->service = new ProductVariantService();
    }

    public function create($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'MENU_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->createVariant($data, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message'], ['variant_id' => $result['variant_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getByProduct($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $productId = $request['params']['id'] ?? null;

        $result = $this->service->getVariantsByProduct($productId, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function update($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'MENU_MANAGE');

        $variantId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];

        $result = $this->service->updateVariant($variantId, $data, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function delete($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'MENU_MANAGE');

        $variantId = $request['params']['id'] ?? null;

        $result = $this->service->deleteVariant($variantId, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
