<?php

if (!class_exists('WhatsAppService')) {
    require_once __DIR__ . '/../Services/WhatsAppService.php';
}
// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';




class WhatsAppController
{
    private $service;

    public function __construct()
    {
        $this->service = new WhatsAppService();
    }

    public function saveSettings($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'SETTINGS_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->saveSettings($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['setting_id' => $result['setting_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getSettings($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $result = $this->service->getSettings($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function sendMessage($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'SETTINGS_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->sendMessage($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['log_id' => $result['log_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function sendReport($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'REPORT_VIEW');

        $reportType = $request['params']['type'] ?? null;
        $dateFrom = $request['params']['start_date'] ?? null;
        $dateTo = $request['params']['end_date'] ?? null;

        if (!$reportType) {
            Response::error(Messages::WHATSAPP_REPORT_TYPE_REQUIRED);
            return;
        }

        $result = $this->service->sendReport($reportType, $user['tenant_id'], $user['branch_id'], $dateFrom, $dateTo);

        if ($result['success']) {
            Response::success($result['message'], ['sent_count' => $result['sent_count'], 'failed_count' => $result['failed_count']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function createReportSchedule($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $permissionMiddleware = new PermissionMiddleware();
        $permissionMiddleware->check($user['user_id'], 'SETTINGS_MANAGE');

        $data = $request['body'] ?? [];

        $result = $this->service->createReportSchedule($data, $user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], ['schedule_id' => $result['schedule_id']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getReportSchedules($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $result = $this->service->getReportSchedules($user['tenant_id'], $user['branch_id']);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function getMessageLogs($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $limit = $request['params']['limit'] ?? 50;

        $result = $this->service->getMessageLogs($user['tenant_id'], $user['branch_id'], $limit);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}
