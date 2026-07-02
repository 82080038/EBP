<?php

if (!class_exists('ComboService')) {
    require_once __DIR__ . '/../Services/ComboService.php';
}
if (!class_exists('Response')) {
    require_once __DIR__ . '/../../../core/Response.php';
}
if (!class_exists('AuthMiddleware')) {
    require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
}
if (!class_exists('PermissionMiddleware')) {
    require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';
}

class ComboController
{
    private $service;

    public function __construct()
    {
        $this->service = new ComboService();
    }

    public function create($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'MENU_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->createCombo($data, $user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message'], ['combo_id' => $result['combo_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getAll($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $result = $this->service->getCombos($user['tenant_id']);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function calculatePrice($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $comboId = $request['params']['id'] ?? null;
        $data = $request['body'] ?? [];
        $selections = $data['selections'] ?? [];

        $result = $this->service->calculateComboPrice($comboId, $selections);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
