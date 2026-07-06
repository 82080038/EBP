<?php

require_once __DIR__ . '/../Services/DailyReportsService.php';
require_once __DIR__ . '/../../../core/Response.php';

/**
 * Daily Reports Controller
 */
class DailyReportsController
{
    private $dailyReportsService;

    public function __construct()
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? null;
        $this->dailyReportsService = new DailyReportsService($tenantId, $branchId);
    }

    /**
     * Get daily sales report
     * GET /api/v1/daily-reports/sales
     */
    public function getSalesReport()
    {
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $result = $this->dailyReportsService->getDailySalesReport($date);
        
        Response::json($result, 200);
    }

    /**
     * Get table turnover report
     * GET /api/v1/daily-reports/table-turnover
     */
    public function getTableTurnover()
    {
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $result = $this->dailyReportsService->getTableTurnoverReport($date);
        
        Response::json($result, 200);
    }

    /**
     * Get server performance report
     * GET /api/v1/daily-reports/server-performance
     */
    public function getServerPerformance()
    {
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $result = $this->dailyReportsService->getServerPerformanceReport($date);
        
        Response::json($result, 200);
    }

    /**
     * Get peak hour analysis
     * GET /api/v1/daily-reports/peak-hours
     */
    public function getPeakHours()
    {
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $result = $this->dailyReportsService->getPeakHourAnalysis($date);
        
        Response::json($result, 200);
    }

    /**
     * Get comprehensive daily report
     * GET /api/v1/daily-reports/comprehensive
     */
    public function getComprehensive()
    {
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $result = $this->dailyReportsService->getComprehensiveDailyReport($date);
        
        Response::json($result, 200);
    }
}
