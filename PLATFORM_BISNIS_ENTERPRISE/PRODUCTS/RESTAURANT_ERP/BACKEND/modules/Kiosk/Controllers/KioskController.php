<?php

if (!class_exists('KioskService')) {
    require_once __DIR__ . '/../Services/KioskService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';


class KioskController
{
    private $service;

    public function __construct()
    {
        $this->service = new KioskService();
    }

    public function getMenu($request)
    {
        $tenantId = $request['query']['tenant_id'] ?? null;
        $branchId = $request['query']['branch_id'] ?? null;

        if (!$tenantId || !$branchId) {
            Response::error(Messages::KIOSK_TENANT_BRANCH_REQUIRED);
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
        $tenantId = $request['query']['tenant_id'] ?? null;
        $branchId = $request['query']['branch_id'] ?? null;
        $data = $request['body'] ?? [];

        if (!$tenantId || !$branchId) {
            Response::error(Messages::KIOSK_TENANT_BRANCH_REQUIRED);
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
