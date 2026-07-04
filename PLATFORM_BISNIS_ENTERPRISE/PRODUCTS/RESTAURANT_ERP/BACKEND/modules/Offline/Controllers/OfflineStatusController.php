<?php

if (!class_exists('OfflineStatusService')) {
    require_once __DIR__ . '/../Services/OfflineStatusService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';


class OfflineStatusController
{
    private $service;

    public function __construct()
    {
        $this->service = new OfflineStatusService();
    }

    public function getStatus($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $result = $this->service->getOfflineStatus($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
