<?php

if (!class_exists('ProductModifierService')) {
    require_once __DIR__ . '/../Services/ProductModifierService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



class ProductModifierController
{
    private $service;

    public function __construct()
    {
        $this->service = new ProductModifierService();
    }

    public function createGroup($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->createModifierGroup($data, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message'], ['modifier_group_id' => $result['modifier_group_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function createModifier($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->createModifier($data, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message'], ['modifier_id' => $result['modifier_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function assignToProduct($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->assignModifierToProduct($data, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message'], ['assignment_id' => $result['assignment_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getGroups($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $result = $this->service->getModifierGroups($user['tenant_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getModifiersByGroup($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $groupId = $request['params']['id'] ?? null;

        $result = $this->service->getModifiersByGroup($groupId, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getProductModifiers($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $productId = $request['params']['id'] ?? null;

        $result = $this->service->getProductModifiers($productId, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
