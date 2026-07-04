<?php

namespace App\Modules\Sustainability\Controllers;

use App\Core\BaseController;
use App\Modules\Sustainability\Models\SustainabilityMetric;
use App\Modules\Sustainability\Models\WasteTracking;
use App\Modules\Sustainability\Models\SustainabilityGoal;
use App\Modules\Sustainability\Models\SustainabilityCertification;
use App\Modules\Sustainability\Services\SustainabilityService;
use App\Core\Auth;

class SustainabilityManagementController extends BaseController
{
    private $sustainabilityService;

    public function __construct()
    {
        parent::__construct();
        $this->sustainabilityService = new SustainabilityService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get sustainability metrics
     * GET /api/sustainability/metrics
     */
    public function getMetrics()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $metricType = $this->request->get('type', 'monthly');
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        $limit = $this->request->get('limit', 12);
        
        $metrics = $this->sustainabilityService->getMetrics($restaurantId, $metricType, $dateFrom, $dateTo, $limit);
        
        $this->jsonResponse($metrics);
    }

    /**
     * Get waste tracking
     * GET /api/sustainability/waste
     */
    public function getWasteTracking()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $wasteType = $this->request->get('type', null);
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->sustainabilityService->getWasteTracking($restaurantId, $wasteType, $dateFrom, $dateTo, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Create waste record
     * POST /api/sustainability/waste
     */
    public function createWasteRecord()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->sustainabilityService->createWasteRecord($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get sustainability goals
     * GET /api/sustainability/goals
     */
    public function getGoals()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $category = $this->request->get('category', null);
        $status = $this->request->get('status', null);
        
        $goals = $this->sustainabilityService->getGoals($restaurantId, $category, $status);
        
        $this->jsonResponse($goals);
    }

    /**
     * Create sustainability goal
     * POST /api/sustainability/goals
     */
    public function createGoal()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->sustainabilityService->createGoal($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get certifications
     * GET /api/sustainability/certifications
     */
    public function getCertifications()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $certType = $this->request->get('type', null);
        $status = $this->request->get('status', null);
        
        $certifications = $this->sustainabilityService->getCertifications($restaurantId, $certType, $status);
        
        $this->jsonResponse($certifications);
    }

    /**
     * Create certification
     * POST /api/sustainability/certifications
     */
    public function createCertification()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->sustainabilityService->createCertification($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get sustainability summary
     * GET /api/sustainability/summary
     */
    public function getSummary()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $summary = $this->sustainabilityService->getSummary($restaurantId);
        
        $this->jsonResponse($summary);
    }
}
