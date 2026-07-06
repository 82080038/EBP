<?php

require_once __DIR__ . '/../Services/StaffSchedulingService.php';
require_once __DIR__ . '/../../../core/Response.php';

/**
 * Staff Scheduling Controller
 */
class StaffSchedulingController
{
    private $staffSchedulingService;

    public function __construct()
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? 2;
        $this->staffSchedulingService = new StaffSchedulingService($tenantId, $branchId);
    }

    /**
     * Create shift
     * POST /api/v1/staff-scheduling/shifts
     */
    public function createShift()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $result = $this->staffSchedulingService->createShift($data);
        
        if ($result['success']) {
            Response::json($result, 201);
        } else {
            Response::json($result, 400);
        }
    }

    /**
     * Get shifts
     * GET /api/v1/staff-scheduling/shifts
     */
    public function getShifts()
    {
        $result = $this->staffSchedulingService->getShifts();
        
        Response::json($result, 200);
    }

    /**
     * Create schedule
     * POST /api/v1/staff-scheduling/schedules
     */
    public function createSchedule()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $result = $this->staffSchedulingService->createSchedule($data);
        
        if ($result['success']) {
            Response::json($result, 201);
        } else {
            Response::json($result, 400);
        }
    }

    /**
     * Get schedules
     * GET /api/v1/staff-scheduling/schedules
     */
    public function getSchedules()
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        
        $result = $this->staffSchedulingService->getSchedules($startDate, $endDate);
        
        Response::json($result, 200);
    }
}
