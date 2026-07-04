<?php

namespace App\Modules\Technology\Controllers;

use App\Core\BaseController;
use App\Modules\Technology\Models\TechnologyIntegration;
use App\Modules\Technology\Models\RoboticsDevice;
use App\Modules\Technology\Models\AutomationWorkflow;
use App\Modules\Technology\Services\TechnologyService;
use App\Core\Auth;

class TechnologyController extends BaseController
{
    private $technologyService;

    public function __construct()
    {
        parent::__construct();
        $this->technologyService = new TechnologyService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get technology integrations
     * GET /api/technology/integrations
     */
    public function getIntegrations()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $type = $this->request->get('type', null);
        $status = $this->request->get('status', null);
        
        $integrations = $this->technologyService->getIntegrations($restaurantId, $type, $status);
        
        $this->jsonResponse($integrations);
    }

    /**
     * Create technology integration
     * POST /api/technology/integrations
     */
    public function createIntegration()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->technologyService->createIntegration($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get robotics devices
     * GET /api/technology/robotics-devices
     */
    public function getRoboticsDevices()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $integrationId = $this->request->get('integration_id', null);
        $status = $this->request->get('status', null);
        
        $devices = $this->technologyService->getRoboticsDevices($restaurantId, $integrationId, $status);
        
        $this->jsonResponse($devices);
    }

    /**
     * Get automation workflows
     * GET /api/technology/automation-workflows
     */
    public function getAutomationWorkflows()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $type = $this->request->get('type', null);
        $status = $this->request->get('status', null);
        
        $workflows = $this->technologyService->getAutomationWorkflows($restaurantId, $type, $status);
        
        $this->jsonResponse($workflows);
    }

    /**
     * Create automation workflow
     * POST /api/technology/automation-workflows
     */
    public function createAutomationWorkflow()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->technologyService->createAutomationWorkflow($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get technology performance
     * GET /api/technology/performance
     */
    public function getPerformance()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $integrationId = $this->request->get('integration_id', null);
        $metricType = $this->request->get('type', 'monthly');
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        $limit = $this->request->get('limit', 12);
        
        $performance = $this->technologyService->getPerformance($restaurantId, $integrationId, $metricType, $dateFrom, $dateTo, $limit);
        
        $this->jsonResponse($performance);
    }

    /**
     * Get technology summary
     * GET /api/technology/summary
     */
    public function getSummary()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $summary = $this->technologyService->getSummary($restaurantId);
        
        $this->jsonResponse($summary);
    }
}
