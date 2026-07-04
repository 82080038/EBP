<?php

namespace App\Modules\Segment\Controllers;

use App\Core\BaseController;
use App\Modules\Segment\Models\SegmentConfiguration;
use App\Modules\Segment\Models\SegmentWorkflow;
use App\Modules\Segment\Models\SegmentTemplate;
use App\Modules\Segment\Services\SegmentService;
use App\Core\Auth;

class SegmentController extends BaseController
{
    private $segmentService;

    public function __construct()
    {
        parent::__construct();
        $this->segmentService = new SegmentService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get segment configuration
     * GET /api/segment/configuration
     */
    public function getConfiguration()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $configuration = $this->segmentService->getConfiguration($restaurantId);
        
        $this->jsonResponse($configuration);
    }

    /**
     * Create segment configuration
     * POST /api/segment/configuration
     */
    public function createConfiguration()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->segmentService->createConfiguration($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get segment workflows
     * GET /api/segment/workflows
     */
    public function getWorkflows()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $type = $this->request->get('type', null);
        
        $workflows = $this->segmentService->getWorkflows($restaurantId, $type);
        
        $this->jsonResponse($workflows);
    }

    /**
     * Create segment workflow
     * POST /api/segment/workflows
     */
    public function createWorkflow()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->segmentService->createWorkflow($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get segment templates
     * GET /api/segment/templates
     */
    public function getTemplates()
    {
        $segmentType = $this->request->get('type', null);
        
        $templates = $this->segmentService->getTemplates($segmentType);
        
        $this->jsonResponse($templates);
    }

    /**
     * Apply template to restaurant
     * POST /api/segment/apply-template
     */
    public function applyTemplate()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->segmentService->applyTemplate($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get segment analytics
     * GET /api/segment/analytics
     */
    public function getAnalytics()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $metricType = $this->request->get('type', 'monthly');
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        $limit = $this->request->get('limit', 12);
        
        $analytics = $this->segmentService->getAnalytics($restaurantId, $metricType, $dateFrom, $dateTo, $limit);
        
        $this->jsonResponse($analytics);
    }

    /**
     * Get segment summary
     * GET /api/segment/summary
     */
    public function getSummary()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $summary = $this->segmentService->getSummary($restaurantId);
        
        $this->jsonResponse($summary);
    }
}
