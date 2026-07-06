<?php

if (!class_exists('TipService')) {
    require_once __DIR__ . '/../Services/TipService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';



class TipController
{
    private $service;

    public function __construct()
    {
        $this->service = new TipService();
    }

    public function distributeTip($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $data = $request['body'] ?? [];

        $result = $this->service->distributeTip($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['tip_id' => $result['tip_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getTipDistributions($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $date = $request['params']['date'] ?? null;

        $result = $this->service->getTipDistributions($user['tenant_id'], $user['branch_id'], $date);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getEmployeeTips($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $employeeId = $request['params']['employee_id'] ?? null;
        $startDate = $request['params']['start_date'] ?? null;
        $endDate = $request['params']['end_date'] ?? null;

        if (!$employeeId) {
            Response::error('Employee ID is required');
            return;
        }

        $result = $this->service->getEmployeeTips($user['tenant_id'], $user['branch_id'], $employeeId, $startDate, $endDate);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
