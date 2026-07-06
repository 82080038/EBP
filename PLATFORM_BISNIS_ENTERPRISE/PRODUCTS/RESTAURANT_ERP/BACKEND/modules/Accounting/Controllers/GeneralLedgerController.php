<?php

if (!class_exists('GeneralLedgerService')) {
    require_once __DIR__ . '/../Services/GeneralLedgerService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class GeneralLedgerController
{
    private $service;

    public function __construct()
    {
        $this->service = new GeneralLedgerService();
    }

    public function getLedger($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $params = $request['query'] ?? [];
        $startDate = $params['start_date'] ?? date('Y-m-01');
        $endDate = $params['end_date'] ?? date('Y-m-t');
        $accountId = $params['account_id'] ?? null;

        $result = $this->service->getLedger($user['tenant_id'], $user['branch_id'], $startDate, $endDate, $accountId);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getAccountBalance($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $params = $request['params'] ?? [];
        $accountId = $params['account_id'];
        $asOfDate = $params['date'] ?? date('Y-m-d');

        if (!$accountId) {
            Response::error('Account ID is required');
        }

        $result = $this->service->getAccountBalance($user['tenant_id'], $user['branch_id'], $accountId, $asOfDate);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getCashFlowStatement($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        // $permissionMiddleware = new PermissionMiddleware();

        $params = $request['query'] ?? [];
        $startDate = $params['start_date'] ?? date('Y-m-01');
        $endDate = $params['end_date'] ?? date('Y-m-t');

        $result = $this->service->getCashFlowStatement($user['tenant_id'], $user['branch_id'], $startDate, $endDate);

        if ($result['success']) {
            Response::success($result['data'], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
