<?php

if (!class_exists('WhatsAppOrderingService')) {
    require_once __DIR__ . '/../Services/WhatsAppOrderingService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



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

        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->processWhatsAppOrder($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['order_id' => $result['order_id'], 'order_number' => $result['order_number']]);
        } else {
            Response::error($result['message']);
        }
    }
}
