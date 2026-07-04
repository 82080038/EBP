<?php

namespace App\Modules\Innovation\Controllers;

use App\Core\BaseController;
use App\Modules\Innovation\Models\InnovationIdea;
use App\Modules\Innovation\Models\InnovationProject;
use App\Modules\Innovation\Models\InnovationMetric;
use App\Modules\Innovation\Services\InnovationService;
use App\Core\Auth;

class InnovationController extends BaseController
{
    private $innovationService;

    public function __construct()
    {
        parent::__construct();
        $this->innovationService = new InnovationService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get innovation ideas
     * GET /api/innovation/ideas
     */
    public function getIdeas()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $category = $this->request->get('category', null);
        $status = $this->request->get('status', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->innovationService->getIdeas($restaurantId, $category, $status, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Create innovation idea
     * POST /api/innovation/ideas
     */
    public function createIdea()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->innovationService->createIdea($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Update idea status
     * PUT /api/innovation/ideas/{id}/status
     */
    public function updateIdeaStatus($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->innovationService->updateIdeaStatus($id, $restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get innovation projects
     * GET /api/innovation/projects
     */
    public function getProjects()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $status = $this->request->get('status', null);
        $type = $this->request->get('type', null);
        
        $projects = $this->innovationService->getProjects($restaurantId, $status, $type);
        
        $this->jsonResponse($projects);
    }

    /**
     * Create innovation project
     * POST /api/innovation/projects
     */
    public function createProject()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->innovationService->createProject($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get innovation metrics
     * GET /api/innovation/metrics
     */
    public function getMetrics()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $metricType = $this->request->get('type', 'monthly');
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        $limit = $this->request->get('limit', 12);
        
        $metrics = $this->innovationService->getMetrics($restaurantId, $metricType, $dateFrom, $dateTo, $limit);
        
        $this->jsonResponse($metrics);
    }

    /**
     * Get innovation summary
     * GET /api/innovation/summary
     */
    public function getSummary()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $summary = $this->innovationService->getSummary($restaurantId);
        
        $this->jsonResponse($summary);
    }
}
