<?php

require_once __DIR__ . '/../Services/AccountingService.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';

class AccountingController
{
    private $service;

    public function __construct()
    {
        $this->service = new AccountingService();
    }

    public function createJournalEntry($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'ACCOUNTING_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->createJournalEntry($data, $user['tenant_id'], $user['branch_id'], $user['user_id']);

        if ($result['success']) {
            Response::success($result['message'], ['journal_id' => $result['journal_id'], 'journal_number' => $result['journal_number']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getTrialBalance($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $params = $request['params'] ?? [];
        $asOfDate = $params['date'] ?? date('Y-m-d');

        $result = $this->service->getTrialBalance($user['tenant_id'], $user['branch_id'], $asOfDate);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getBalanceSheet($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $params = $request['params'] ?? [];
        $asOfDate = $params['date'] ?? date('Y-m-d');

        $result = $this->service->getBalanceSheet($user['tenant_id'], $user['branch_id'], $asOfDate);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getProfitLoss($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $params = $request['params'] ?? [];
        $periodStart = $params['start_date'] ?? date('Y-m-01');
        $periodEnd = $params['end_date'] ?? date('Y-m-t');

        $result = $this->service->getProfitLoss($user['tenant_id'], $user['branch_id'], $periodStart, $periodEnd);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
