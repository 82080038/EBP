<?php

if (!class_exists('WhatsAppOrderingService')) {
    require_once __DIR__ . '/../Services/WhatsAppOrderingService.php';
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

class WhatsAppOrderingController
{
    private $service;

    public function __construct()
    {
        $this->service = new WhatsAppOrderingService();
    }

    public function processOrder($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'ORDER_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->processWhatsAppOrder($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['order_id' => $result['order_id'], 'order_number' => $result['order_number']]);
        } else {
            Response::error($result['message']);
        }
    }
}
