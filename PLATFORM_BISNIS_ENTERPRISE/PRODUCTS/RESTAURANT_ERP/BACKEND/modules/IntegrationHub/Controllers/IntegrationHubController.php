<?php

namespace App\Modules\IntegrationHub\Controllers;

use App\Core\BaseController;
use App\Modules\IntegrationHub\Models\ExternalIntegration;
use App\Modules\IntegrationHub\Models\IntegrationMapping;
use App\Modules\IntegrationHub\Models\IntegrationSyncLog;
use App\Modules\IntegrationHub\Services\IntegrationHubService;
use App\Core\Auth;

class IntegrationHubController extends BaseController
{
    private $integrationHubService;

    public function __construct()
    {
        parent::__construct();
        $this->integrationHubService = new IntegrationHubService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get external integrations
     * GET /api/integration-hub/integrations
     */
    public function getIntegrations()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $type = $this->request->get('type', null);
        $status = $this->request->get('status', null);
        
        $integrations = $this->integrationHubService->getIntegrations($restaurantId, $type, $status);
        
        $this->jsonResponse($integrations);
    }

    /**
     * Create external integration
     * POST /api/integration-hub/integrations
     */
    public function createIntegration()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->integrationHubService->createIntegration($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get integration mappings
     * GET /api/integration-hub/mappings
     */
    public function getMappings()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $integrationId = $this->request->get('integration_id', null);
        
        $mappings = $this->integrationHubService->getMappings($restaurantId, $integrationId);
        
        $this->jsonResponse($mappings);
    }

    /**
     * Create integration mapping
     * POST /api/integration-hub/mappings
     */
    public function createMapping()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->integrationHubService->createMapping($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get sync logs
     * GET /api/integration-hub/sync-logs
     */
    public function getSyncLogs()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $integrationId = $this->request->get('integration_id', null);
        $status = $this->request->get('status', null);
        $limit = $this->request->get('limit', 50);
        
        $logs = $this->integrationHubService->getSyncLogs($restaurantId, $integrationId, $status, $limit);
        
        $this->jsonResponse($logs);
    }

    /**
     * Trigger manual sync
     * POST /api/integration-hub/sync
     */
    public function triggerSync()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->integrationHubService->triggerSync($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get integration analytics
     * GET /api/integration-hub/analytics
     */
    public function getAnalytics()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $integrationId = $this->request->get('integration_id', null);
        $metricType = $this->request->get('type', 'monthly');
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        $limit = $this->request->get('limit', 12);
        
        $analytics = $this->integrationHubService->getAnalytics($restaurantId, $integrationId, $metricType, $dateFrom, $dateTo, $limit);
        
        $this->jsonResponse($analytics);
    }

    /**
     * Get integration hub summary
     * GET /api/integration-hub/summary
     */
    public function getSummary()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $summary = $this->integrationHubService->getSummary($restaurantId);
        
        $this->jsonResponse($summary);
    }
}
