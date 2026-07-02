<?php

if (!class_exists('KioskService')) {
    require_once __DIR__ . '/../Services/KioskService.php';
}
if (!class_exists('Response')) {
    require_once __DIR__ . '/../../../core/Response.php';
}

class KioskController
{
    private $service;

    public function __construct()
    {
        $this->service = new KioskService();
    }

    public function getMenu($request)
    {
        $tenantId = $request['params']['tenant_id'] ?? null;
        $branchId = $request['params']['branch_id'] ?? null;

        if (!$tenantId || !$branchId) {
            Response::error('Tenant ID and Branch ID are required');
            return;
        }

        $result = $this->service->getKioskMenu($tenantId, $branchId);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function createOrder($request)
    {
        $tenantId = $request['params']['tenant_id'] ?? null;
        $branchId = $request['params']['branch_id'] ?? null;
        $data = $request['body'] ?? [];

        if (!$tenantId || !$branchId) {
            Response::error('Tenant ID and Branch ID are required');
            return;
        }

        $result = $this->service->createKioskOrder($data, $tenantId, $branchId);

        if ($result['success']) {
            Response::success($result['message'], ['order_id' => $result['order_id'], 'order_number' => $result['order_number']]);
        } else {
            Response::error($result['message']);
        }
    }
}
