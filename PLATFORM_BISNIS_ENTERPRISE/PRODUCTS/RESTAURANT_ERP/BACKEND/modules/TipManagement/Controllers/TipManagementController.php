<?php

require_once __DIR__ . '/../Services/TipManagementService.php';
require_once __DIR__ . '/../../../core/Response.php';

/**
 * Tip Management Controller
 */
class TipManagementController
{
    private $tipManagementService;

    public function __construct()
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? null;
        $this->tipManagementService = new TipManagementService($tenantId, $branchId);
    }

    /**
     * Record tip
     * POST /api/v1/tips
     */
    public function create()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $data['recorded_by'] = $_SESSION['user_id'] ?? null;
        
        $result = $this->tipManagementService->recordTip($data);
        
        if ($result['success']) {
            Response::json($result, 201);
        } else {
            Response::json($result, 400);
        }
    }

    /**
     * Get tips
     * GET /api/v1/tips
     */
    public function index()
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        $userId = $_GET['user_id'] ?? null;
        
        $result = $this->tipManagementService->getTips($startDate, $endDate, $userId);
        
        Response::json($result, 200);
    }

    /**
     * Get tip summary
     * GET /api/v1/tips/summary
     */
    public function summary()
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        
        $result = $this->tipManagementService->getTipSummary($startDate, $endDate);
        
        Response::json($result, 200);
    }
}
