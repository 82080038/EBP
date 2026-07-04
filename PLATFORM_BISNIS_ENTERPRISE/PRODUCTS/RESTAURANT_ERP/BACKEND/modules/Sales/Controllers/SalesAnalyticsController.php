<?php

namespace App\Modules\Sales\Controllers;

use App\Core\BaseController;
use App\Modules\Sales\Models\SalesAggregate;
use App\Modules\Sales\Models\ProductSales;
use App\Modules\Sales\Models\CategorySales;
use App\Modules\Sales\Models\HourlySales;
use App\Modules\Sales\Models\SalesTarget;
use App\Modules\Sales\Services\SalesAnalyticsService;
use App\Core\Auth;

class SalesAnalyticsController extends BaseController
{
    private $salesService;

    public function __construct()
    {
        parent::__construct();
        $this->salesService = new SalesAnalyticsService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get sales aggregates
     * GET /api/sales/analytics/aggregates
     */
    public function getAggregates()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $aggregateType = $this->request->get('type', 'daily');
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        $limit = $this->request->get('limit', 30);
        
        $aggregates = $this->salesService->getAggregates($restaurantId, $aggregateType, $dateFrom, $dateTo, $limit);
        
        $this->jsonResponse($aggregates);
    }

    /**
     * Get product sales
     * GET /api/sales/analytics/products
     */
    public function getProductSales()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $aggregateType = $this->request->get('type', 'daily');
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        $limit = $this->request->get('limit', 50);
        
        $sales = $this->salesService->getProductSales($restaurantId, $aggregateType, $dateFrom, $dateTo, $limit);
        
        $this->jsonResponse($sales);
    }

    /**
     * Get category sales
     * GET /api/sales/analytics/categories
     */
    public function getCategorySales()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $aggregateType = $this->request->get('type', 'daily');
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        $limit = $this->request->get('limit', 50);
        
        $sales = $this->salesService->getCategorySales($restaurantId, $aggregateType, $dateFrom, $dateTo, $limit);
        
        $this->jsonResponse($sales);
    }

    /**
     * Get hourly sales
     * GET /api/sales/analytics/hourly
     */
    public function getHourlySales()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $date = $this->request->get('date', null);
        
        $sales = $this->salesService->getHourlySales($restaurantId, $date);
        
        $this->jsonResponse($sales);
    }

    /**
     * Get sales targets
     * GET /api/sales/analytics/targets
     */
    public function getSalesTargets()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $status = $this->request->get('status', null);
        
        $targets = $this->salesService->getSalesTargets($restaurantId, $status);
        
        $this->jsonResponse($targets);
    }

    /**
     * Create sales target
     * POST /api/sales/analytics/targets
     */
    public function createSalesTarget()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->salesService->createSalesTarget($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get sales trends
     * GET /api/sales/analytics/trends
     */
    public function getSalesTrends()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $trendType = $this->request->get('type', 'revenue');
        $trendPeriod = $this->request->get('period', 'daily');
        $limit = $this->request->get('limit', 30);
        
        $trends = $this->salesService->getSalesTrends($restaurantId, $trendType, $trendPeriod, $limit);
        
        $this->jsonResponse($trends);
    }

    /**
     * Get sales summary
     * GET /api/sales/analytics/summary
     */
    public function getSummary()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        
        $summary = $this->salesService->getSummary($restaurantId, $dateFrom, $dateTo);
        
        $this->jsonResponse($summary);
    }
}
