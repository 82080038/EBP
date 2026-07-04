<?php

namespace App\Modules\CustomerAnalytics\Controllers;

use App\Core\BaseController;
use App\Modules\CustomerAnalytics\Models\CustomerSegment;
use App\Modules\CustomerAnalytics\Models\CustomerBehavior;
use App\Modules\CustomerAnalytics\Models\CustomerJourney;
use App\Modules\CustomerAnalytics\Models\CustomerCohort;
use App\Modules\CustomerAnalytics\Services\CustomerAnalyticsService;
use App\Core\Auth;

class CustomerAnalyticsController extends BaseController
{
    private $customerAnalyticsService;

    public function __construct()
    {
        parent::__construct();
        $this->customerAnalyticsService = new CustomerAnalyticsService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get customer segments
     * GET /api/customer-analytics/segments
     */
    public function getSegments()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $segments = $this->customerAnalyticsService->getSegments($restaurantId);
        
        $this->jsonResponse($segments);
    }

    /**
     * Create customer segment
     * POST /api/customer-analytics/segments
     */
    public function createSegment()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->customerAnalyticsService->createSegment($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get customer behavior
     * GET /api/customer-analytics/behavior/{id}
     */
    public function getCustomerBehavior($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $behavior = $this->customerAnalyticsService->getCustomerBehavior($id, $restaurantId);
        
        if (!$behavior) {
            $this->jsonResponse(['error' => 'Customer behavior not found'], 404);
            return;
        }
        
        $this->jsonResponse($behavior);
    }

    /**
     * Get customer journey
     * GET /api/customer-analytics/journey/{id}
     */
    public function getCustomerJourney($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $journey = $this->customerAnalyticsService->getCustomerJourney($id, $restaurantId);
        
        $this->jsonResponse($journey);
    }

    /**
     * Get customer cohorts
     * GET /api/customer-analytics/cohorts
     */
    public function getCohorts()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $cohorts = $this->customerAnalyticsService->getCohorts($restaurantId);
        
        $this->jsonResponse($cohorts);
    }

    /**
     * Create customer cohort
     * POST /api/customer-analytics/cohorts
     */
    public function createCohort()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->customerAnalyticsService->createCohort($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get cohort data
     * GET /api/customer-analytics/cohorts/{id}/data
     */
    public function getCohortData($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->customerAnalyticsService->getCohortData($id, $restaurantId);
        
        $this->jsonResponse($data);
    }

    /**
     * Get customer analytics summary
     * GET /api/customer-analytics/summary
     */
    public function getSummary()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $summary = $this->customerAnalyticsService->getSummary($restaurantId);
        
        $this->jsonResponse($summary);
    }
}
