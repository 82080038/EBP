<?php

require_once __DIR__ . '/../Services/ReportService.php';
require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';
require_once __DIR__ . '/../../../core/Response.php';

class ReportController
{
    private $reportService;

    public function __construct()
    {
        $this->reportService = new ReportService();
    }

    public function getSalesReport(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'REPORT_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $dateFrom = $request['date_from'] ?? date('Y-m-01');
        $dateTo = $request['date_to'] ?? date('Y-m-t');

        $report = $this->reportService->getSalesReport($tenantId, $branchId, $dateFrom, $dateTo);

        return Response::success($report);
    }

    public function getTopSellingProducts(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'REPORT_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $dateFrom = $request['date_from'] ?? date('Y-m-01');
        $dateTo = $request['date_to'] ?? date('Y-m-t');
        $limit = $request['limit'] ?? 10;

        $report = $this->reportService->getTopSellingProducts($tenantId, $branchId, $dateFrom, $dateTo, $limit);

        return Response::success($report);
    }

    public function getInventoryReport(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'REPORT_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;

        $report = $this->reportService->getInventoryReport($tenantId, $branchId);

        return Response::success($report);
    }

    public function getStockMovementReport(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'REPORT_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $dateFrom = $request['date_from'] ?? date('Y-m-01');
        $dateTo = $request['date_to'] ?? date('Y-m-t');

        $report = $this->reportService->getStockMovementReport($tenantId, $branchId, $dateFrom, $dateTo);

        return Response::success($report);
    }

    public function getKitchenPerformanceReport(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'REPORT_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $dateFrom = $request['date_from'] ?? date('Y-m-01');
        $dateTo = $request['date_to'] ?? date('Y-m-t');

        $report = $this->reportService->getKitchenPerformanceReport($tenantId, $branchId, $dateFrom, $dateTo);

        return Response::success($report);
    }

    public function getReservationReport(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'REPORT_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $dateFrom = $request['date_from'] ?? date('Y-m-01');
        $dateTo = $request['date_to'] ?? date('Y-m-t');

        $report = $this->reportService->getReservationReport($tenantId, $branchId, $dateFrom, $dateTo);

        return Response::success($report);
    }

    public function getFinancialReport(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'REPORT_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $dateFrom = $request['date_from'] ?? date('Y-m-01');
        $dateTo = $request['date_to'] ?? date('Y-m-t');

        $report = $this->reportService->getFinancialReport($tenantId, $branchId, $dateFrom, $dateTo);

        return Response::success($report);
    }

    public function getDashboardSummary(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'REPORT_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;

        $summary = $this->reportService->getDashboardSummary($tenantId, $branchId);

        return Response::success($summary);
    }
}
