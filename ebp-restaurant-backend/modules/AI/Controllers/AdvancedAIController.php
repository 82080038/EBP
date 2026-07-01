<?php

require_once __DIR__ . '/../Services/AdvancedAIService.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';

class AdvancedAIController
{
    private $service;

    public function __construct()
    {
        $this->service = new AdvancedAIService();
    }

    public function analyzeMenu($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $params = $request['params'] ?? [];
        $date = $params['date'] ?? date('Y-m-d');

        $result = $this->service->analyzeMenuEngineering($user['tenant_id'], $user['branch_id'], $date);

        if ($result['success']) {
            Response::success($result['message'], ['analyzed_products' => $result['analyzed_products']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function optimizeStaff($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $params = $request['params'] ?? [];
        $date = $params['date'] ?? date('Y-m-d');

        $result = $this->service->optimizeStaff($user['tenant_id'], $user['branch_id'], $date);

        if ($result['success']) {
            Response::success($result['message'], ['optimized_hours' => $result['optimized_hours']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function detectFraud($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $params = $request['params'] ?? [];
        $date = $params['date'] ?? date('Y-m-d');

        $result = $this->service->detectFraud($user['tenant_id'], $user['branch_id'], $date);

        if ($result['success']) {
            Response::success($result['message'], ['alerts_detected' => $result['alerts_detected']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function generateInsights($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $params = $request['params'] ?? [];
        $date = $params['date'] ?? date('Y-m-d');

        $result = $this->service->generateExecutiveInsights($user['tenant_id'], $user['branch_id'], $date);

        if ($result['success']) {
            Response::success($result['message'], ['insights_count' => $result['insights_count']]);
        } else {
            Response::error($result['message']);
        }
    }

    public function getMenuAnalysis($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $params = $request['params'] ?? [];
        $date = $params['date'] ?? date('Y-m-d');

        $analysis = $this->service->repository->getMenuEngineering($user['tenant_id'], $user['branch_id'], $date);

        Response::success('Menu analysis retrieved', $analysis);
    }

    public function getFraudAlerts($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $params = $request['params'] ?? [];
        $status = $params['status'] ?? null;

        $alerts = $this->service->repository->getFraudAlerts($user['tenant_id'], $user['branch_id'], $status);

        Response::success('Fraud alerts retrieved', $alerts);
    }

    public function getExecutiveInsights($request)
    {
        $authMiddleware = new AuthMiddleware();
        $user = $authMiddleware->authenticate();

        $params = $request['params'] ?? [];
        $status = $params['status'] ?? null;

        $insights = $this->service->repository->getExecutiveInsights($user['tenant_id'], $user['branch_id'], $status);

        Response::success('Executive insights retrieved', $insights);
    }
}
