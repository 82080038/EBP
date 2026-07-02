<?php

if (!class_exists('ReportService')) {
    require_once __DIR__ . '/../Services/ReportService.php';
}
if (!class_exists('AuthMiddleware')) {
    require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
}
if (!class_exists('PermissionMiddleware')) {
    require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';
}
if (!class_exists('Response')) {
    require_once __DIR__ . '/../../../core/Response.php';
}

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

    public function getSalesByHour(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'REPORT_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $dateFrom = $request['date_from'] ?? date('Y-m-01');
        $dateTo = $request['date_to'] ?? date('Y-m-t');

        $report = $this->reportService->getSalesByHour($tenantId, $branchId, $dateFrom, $dateTo);

        return Response::success($report);
    }

    public function getPaymentMethodBreakdown(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'REPORT_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $dateFrom = $request['date_from'] ?? date('Y-m-01');
        $dateTo = $request['date_to'] ?? date('Y-m-t');

        $report = $this->reportService->getPaymentMethodBreakdown($tenantId, $branchId, $dateFrom, $dateTo);

        return Response::success($report);
    }

    public function getInventoryUsageReport(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'REPORT_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $dateFrom = $request['date_from'] ?? date('Y-m-01');
        $dateTo = $request['date_to'] ?? date('Y-m-t');

        $report = $this->reportService->getInventoryUsageReport($tenantId, $branchId, $dateFrom, $dateTo);

        return Response::success($report);
    }

    public function getLaborCostAnalysis(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'REPORT_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $dateFrom = $request['date_from'] ?? date('Y-m-01');
        $dateTo = $request['date_to'] ?? date('Y-m-t');

        $report = $this->reportService->getLaborCostAnalysis($tenantId, $branchId, $dateFrom, $dateTo);

        return Response::success($report);
    }

    public function getTaxReport(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'REPORT_VIEW');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $dateFrom = $request['date_from'] ?? date('Y-m-01');
        $dateTo = $request['date_to'] ?? date('Y-m-t');

        $report = $this->reportService->getTaxReport($tenantId, $branchId, $dateFrom, $dateTo);

        return Response::success($report);
    }

    public function exportReport(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'REPORT_VIEW');

        $reportType = $request['params']['type'] ?? null;
        $format = $request['params']['format'] ?? 'csv';
        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $dateFrom = $request['date_from'] ?? date('Y-m-01');
        $dateTo = $request['date_to'] ?? date('Y-m-t');

        $data = [];
        $filename = '';

        switch ($reportType) {
            case 'sales':
                $data = $this->reportService->getSalesReport($tenantId, $branchId, $dateFrom, $dateTo);
                $filename = 'sales_report_' . date('Y-m-d');
                break;
            case 'inventory':
                $data = $this->reportService->getInventoryReport($tenantId, $branchId);
                $filename = 'inventory_report_' . date('Y-m-d');
                break;
            case 'top_products':
                $data = $this->reportService->getTopSellingProducts($tenantId, $branchId, $dateFrom, $dateTo);
                $filename = 'top_products_' . date('Y-m-d');
                break;
            default:
                Response::error('Invalid report type');
                return;
        }

        if ($format === 'csv') {
            $csv = $this->reportService->exportToCSV($data, $filename);
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
            echo $csv;
            exit;
        }

        Response::error('Unsupported format');
    }
}
