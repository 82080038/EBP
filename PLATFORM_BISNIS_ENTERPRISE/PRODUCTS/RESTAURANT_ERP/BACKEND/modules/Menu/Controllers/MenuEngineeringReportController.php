<?php

declare(strict_types=1);

namespace Modules\Menu\Controllers;

use Modules\Menu\Services\MenuEngineeringReportService;
use Response;

class MenuEngineeringReportController
{
    private MenuEngineeringReportService $reportService;

    public function __construct()
    {
        $db = Database::getInstance()->connect();
        $this->reportService = new MenuEngineeringReportService($db);
    }

    public function getMenuPerformanceReport(array $request): void
    {
        try {
            $tenantId = (int)$request['tenant_id'];
            $branchId = isset($request['branch_id']) ? (int)$request['branch_id'] : null;
            $startDate = $request['start_date'] ?? null;
            $endDate = $request['end_date'] ?? null;
            
            $report = $this->reportService->getMenuPerformanceReport($tenantId, $branchId, $startDate, $endDate);
            Response::success($report);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function getCategoryPerformanceReport(array $request): void
    {
        try {
            $tenantId = (int)$request['tenant_id'];
            $branchId = isset($request['branch_id']) ? (int)$request['branch_id'] : null;
            $startDate = $request['start_date'] ?? null;
            $endDate = $request['end_date'] ?? null;
            
            $report = $this->reportService->getCategoryPerformanceReport($tenantId, $branchId, $startDate, $endDate);
            Response::success($report);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function getMenuMixReport(array $request): void
    {
        try {
            $tenantId = (int)$request['tenant_id'];
            $branchId = isset($request['branch_id']) ? (int)$request['branch_id'] : null;
            $startDate = $request['start_date'] ?? null;
            $endDate = $request['end_date'] ?? null;
            
            $report = $this->reportService->getMenuMixReport($tenantId, $branchId, $startDate, $endDate);
            Response::success($report);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function getMenuProfitabilityReport(array $request): void
    {
        try {
            $tenantId = (int)$request['tenant_id'];
            $branchId = isset($request['branch_id']) ? (int)$request['branch_id'] : null;
            $startDate = $request['start_date'] ?? null;
            $endDate = $request['end_date'] ?? null;
            
            $report = $this->reportService->getMenuProfitabilityReport($tenantId, $branchId, $startDate, $endDate);
            Response::success($report);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function getMenuTrendReport(array $request): void
    {
        try {
            $tenantId = (int)$request['tenant_id'];
            $branchId = isset($request['branch_id']) ? (int)$request['branch_id'] : null;
            $startDate = $request['start_date'] ?? null;
            $endDate = $request['end_date'] ?? null;
            
            $report = $this->reportService->getMenuTrendReport($tenantId, $branchId, $startDate, $endDate);
            Response::success($report);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function getMenuOptimizationRecommendations(array $request): void
    {
        try {
            $tenantId = (int)$request['tenant_id'];
            $branchId = isset($request['branch_id']) ? (int)$request['branch_id'] : null;
            
            $recommendations = $this->reportService->getMenuOptimizationRecommendations($tenantId, $branchId);
            Response::success($recommendations);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
}
