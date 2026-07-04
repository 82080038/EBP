<?php

namespace App\Modules\Analytics\Controllers;

use App\Core\BaseController;
use App\Modules\Analytics\Models\DashboardConfiguration;
use App\Modules\Analytics\Models\DashboardWidget;
use App\Modules\Analytics\Models\KpiDefinition;
use App\Modules\Analytics\Models\KpiValue;
use App\Modules\Analytics\Models\AlertRule;
use App\Modules\Analytics\Services\AnalyticsService;
use App\Core\Auth;

class AnalyticsController extends BaseController
{
    private $analyticsService;

    public function __construct()
    {
        parent::__construct();
        $this->analyticsService = new AnalyticsService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get dashboards
     * GET /api/analytics/dashboards
     */
    public function getDashboards()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $dashboards = $this->analyticsService->getDashboards($restaurantId, $userId);
        
        $this->jsonResponse($dashboards);
    }

    /**
     * Create dashboard
     * POST /api/analytics/dashboards
     */
    public function createDashboard()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->analyticsService->createDashboard($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get dashboard widgets
     * GET /api/analytics/dashboards/{id}/widgets
     */
    public function getDashboardWidgets($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $widgets = $this->analyticsService->getDashboardWidgets($id, $restaurantId);
        
        $this->jsonResponse($widgets);
    }

    /**
     * Create widget
     * POST /api/analytics/widgets
     */
    public function createWidget()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->analyticsService->createWidget($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get KPI definitions
     * GET /api/analytics/kpis
     */
    public function getKpiDefinitions()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $kpis = $this->analyticsService->getKpiDefinitions($restaurantId);
        
        $this->jsonResponse($kpis);
    }

    /**
     * Create KPI definition
     * POST /api/analytics/kpis
     */
    public function createKpiDefinition()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->analyticsService->createKpiDefinition($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get KPI values
     * GET /api/analytics/kpis/{id}/values
     */
    public function getKpiValues($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        $periodType = $this->request->get('period_type', 'daily');
        $limit = $this->request->get('limit', 30);
        
        $values = $this->analyticsService->getKpiValues($id, $restaurantId, $periodType, $limit);
        
        $this->jsonResponse($values);
    }

    /**
     * Get alert rules
     * GET /api/analytics/alerts/rules
     */
    public function getAlertRules()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $rules = $this->analyticsService->getAlertRules($restaurantId);
        
        $this->jsonResponse($rules);
    }

    /**
     * Create alert rule
     * POST /api/analytics/alerts/rules
     */
    public function createAlertRule()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->analyticsService->createAlertRule($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get alert history
     * GET /api/analytics/alerts/history
     */
    public function getAlertHistory()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $status = $this->request->get('status', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->analyticsService->getAlertHistory($restaurantId, $status, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Get dashboard summary
     * GET /api/analytics/summary
     */
    public function getSummary()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $summary = $this->analyticsService->getSummary($restaurantId);
        
        $this->jsonResponse($summary);
    }
}
