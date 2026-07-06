<?php

if (!class_exists('ComboService')) {
    require_once __DIR__ . '/../Services/ComboService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



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

        // $permissionMiddleware = new PermissionMiddleware();

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
            Response::success($result['data'], $result['message']);
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
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
