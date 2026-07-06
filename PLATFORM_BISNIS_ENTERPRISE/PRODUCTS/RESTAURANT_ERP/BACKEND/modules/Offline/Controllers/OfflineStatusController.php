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
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    // Public endpoint without authentication for offline status check
    public function getPublicStatus($request)
    {
        // Simple health check endpoint
        $result = [
            'success' => true,
            'data' => [
                'status' => 'ONLINE',
                'is_offline' => false,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];

        // Use Response::success instead of Response::json to avoid exit issues
        Response::success($result['data'], 'Status retrieved');
    }
}
