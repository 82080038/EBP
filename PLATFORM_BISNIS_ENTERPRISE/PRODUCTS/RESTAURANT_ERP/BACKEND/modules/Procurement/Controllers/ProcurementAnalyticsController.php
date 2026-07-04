<?php

namespace App\Modules\Procurement\Controllers;

use App\Core\BaseController;
use App\Modules\Procurement\Models\ProcurementSpend;
use App\Modules\Procurement\Models\SupplierSpend;
use App\Modules\Procurement\Models\CategorySpend;
use App\Modules\Procurement\Models\ProcurementTarget;
use App\Modules\Procurement\Services\ProcurementAnalyticsService;
use App\Core\Auth;

class ProcurementAnalyticsController extends BaseController
{
    private $procurementService;

    public function __construct()
    {
        parent::__construct();
        $this->procurementService = new ProcurementAnalyticsService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get procurement spend
     * GET /api/procurement/analytics/spend
     */
    public function getSpend()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $periodType = $this->request->get('type', 'monthly');
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        $limit = $this->request->get('limit', 12);
        
        $spend = $this->procurementService->getSpend($restaurantId, $periodType, $dateFrom, $dateTo, $limit);
        
        $this->jsonResponse($spend);
    }

    /**
     * Get supplier spend
     * GET /api/procurement/analytics/supplier-spend
     */
    public function getSupplierSpend()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $periodType = $this->request->get('type', 'monthly');
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        $limit = $this->request->get('limit', 20);
        
        $spend = $this->procurementService->getSupplierSpend($restaurantId, $periodType, $dateFrom, $dateTo, $limit);
        
        $this->jsonResponse($spend);
    }

    /**
     * Get category spend
     * GET /api/procurement/analytics/category-spend
     */
    public function getCategorySpend()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $periodType = $this->request->get('type', 'monthly');
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        $limit = $this->request->get('limit', 20);
        
        $spend = $this->procurementService->getCategorySpend($restaurantId, $periodType, $dateFrom, $dateTo, $limit);
        
        $this->jsonResponse($spend);
    }

    /**
     * Get procurement targets
     * GET /api/procurement/analytics/targets
     */
    public function getTargets()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $category = $this->request->get('category', null);
        $status = $this->request->get('status', null);
        
        $targets = $this->procurementService->getTargets($restaurantId, $category, $status);
        
        $this->jsonResponse($targets);
    }

    /**
     * Create procurement target
     * POST /api/procurement/analytics/targets
     */
    public function createTarget()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->procurementService->createTarget($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get cost variance
     * GET /api/procurement/analytics/cost-variance
     */
    public function getCostVariance()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        $limit = $this->request->get('limit', 50);
        
        $variance = $this->procurementService->getCostVariance($restaurantId, $dateFrom, $dateTo, $limit);
        
        $this->jsonResponse($variance);
    }

    /**
     * Get procurement summary
     * GET /api/procurement/analytics/summary
     */
    public function getSummary()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        
        $summary = $this->procurementService->getSummary($restaurantId, $dateFrom, $dateTo);
        
        $this->jsonResponse($summary);
    }
}
