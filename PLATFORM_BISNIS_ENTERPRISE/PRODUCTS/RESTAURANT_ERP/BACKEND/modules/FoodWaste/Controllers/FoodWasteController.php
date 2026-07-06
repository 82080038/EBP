<?php

require_once __DIR__ . '/../Services/FoodWasteService.php';
require_once __DIR__ . '/../../../core/Response.php';

/**
 * Food Waste Controller
 */
class FoodWasteController
{
    private $foodWasteService;

    public function __construct()
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? null;
        $this->foodWasteService = new FoodWasteService($tenantId, $branchId);
    }

    /**
     * Record food waste
     * POST /api/v1/food-waste
     */
    public function create()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $data['recorded_by'] = $_SESSION['user_id'] ?? null;
        
        $result = $this->foodWasteService->recordWaste($data);
        
        if ($result['success']) {
            Response::json($result, 201);
        } else {
            Response::json($result, 400);
        }
    }

    /**
     * Get waste records
     * GET /api/v1/food-waste
     */
    public function index()
    {
        $filters = [
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
            'waste_type' => $_GET['waste_type'] ?? null,
            'limit' => $_GET['limit'] ?? null
        ];
        
        $result = $this->foodWasteService->getWasteRecords($filters);
        
        Response::json($result, 200);
    }

    /**
     * Get waste analysis
     * GET /api/v1/food-waste/analysis
     */
    public function analysis()
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        
        $result = $this->foodWasteService->getWasteAnalysis($startDate, $endDate);
        
        Response::json($result, 200);
    }
}
