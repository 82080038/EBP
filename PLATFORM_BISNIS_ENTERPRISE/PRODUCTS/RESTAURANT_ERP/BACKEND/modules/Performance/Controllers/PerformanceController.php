<?php

namespace App\Modules\Performance\Controllers;

use App\Core\BaseController;
use App\Modules\Performance\Models\StaffPerformance;
use App\Modules\Performance\Models\OperationalMetric;
use App\Modules\Performance\Models\PerformanceTarget;
use App\Modules\Performance\Models\EfficiencyMetric;
use App\Modules\Performance\Services\PerformanceService;
use App\Core\Auth;

class PerformanceController extends BaseController
{
    private $performanceService;

    public function __construct()
    {
        parent::__construct();
        $this->performanceService = new PerformanceService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get staff performance
     * GET /api/performance/staff
     */
    public function getStaffPerformance()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $staffId = $this->request->get('staff_id', null);
        $periodType = $this->request->get('period_type', 'daily');
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        $limit = $this->request->get('limit', 30);
        
        $performance = $this->performanceService->getStaffPerformance($restaurantId, $staffId, $periodType, $dateFrom, $dateTo, $limit);
        
        $this->jsonResponse($performance);
    }

    /**
     * Get operational metrics
     * GET /api/performance/operational
     */
    public function getOperationalMetrics()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $metricType = $this->request->get('type', 'daily');
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        $limit = $this->request->get('limit', 30);
        
        $metrics = $this->performanceService->getOperationalMetrics($restaurantId, $metricType, $dateFrom, $dateTo, $limit);
        
        $this->jsonResponse($metrics);
    }

    /**
     * Get performance targets
     * GET /api/performance/targets
     */
    public function getPerformanceTargets()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $category = $this->request->get('category', null);
        $status = $this->request->get('status', null);
        
        $targets = $this->performanceService->getPerformanceTargets($restaurantId, $category, $status);
        
        $this->jsonResponse($targets);
    }

    /**
     * Create performance target
     * POST /api/performance/targets
     */
    public function createPerformanceTarget()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->performanceService->createPerformanceTarget($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get efficiency metrics
     * GET /api/performance/efficiency
     */
    public function getEfficiencyMetrics()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        $limit = $this->request->get('limit', 30);
        
        $metrics = $this->performanceService->getEfficiencyMetrics($restaurantId, $dateFrom, $dateTo, $limit);
        
        $this->jsonResponse($metrics);
    }

    /**
     * Get performance summary
     * GET /api/performance/summary
     */
    public function getSummary()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        
        $summary = $this->performanceService->getSummary($restaurantId, $dateFrom, $dateTo);
        
        $this->jsonResponse($summary);
    }
}
