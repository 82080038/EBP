<?php

namespace App\Modules\Compliance\Controllers;

use App\Core\BaseController;
use App\Modules\Compliance\Models\ComplianceRule;
use App\Modules\Compliance\Models\ComplianceCheck;
use App\Modules\Compliance\Models\ComplianceDocument;
use App\Modules\Compliance\Models\ComplianceAlert;
use App\Modules\Compliance\Services\ComplianceService;
use App\Core\Auth;

class ComplianceController extends BaseController
{
    private $complianceService;

    public function __construct()
    {
        parent::__construct();
        $this->complianceService = new ComplianceService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get compliance dashboard
     * GET /api/compliance/dashboard
     */
    public function dashboard()
    {
        $this->requirePermission('can_view_compliance');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $dashboard = $this->complianceService->getDashboard($restaurantId);
        
        $this->jsonResponse($dashboard);
    }

    /**
     * Get compliance rules
     * GET /api/compliance/rules
     */
    public function getRules()
    {
        $this->requirePermission('can_view_compliance');
        
        $restaurantId = Auth::user()->restaurant_id;
        $ruleType = $this->request->get('rule_type', null);
        
        $rules = $this->complianceService->getRules($restaurantId, $ruleType);
        
        $this->jsonResponse($rules);
    }

    /**
     * Add compliance rule
     * POST /api/compliance/rules
     */
    public function addRule()
    {
        $this->requirePermission('can_manage_compliance');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->complianceService->addRule($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Update compliance rule
     * PUT /api/compliance/rules/{id}
     */
    public function updateRule($id)
    {
        $this->requirePermission('can_manage_compliance');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->complianceService->updateRule($id, $restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Delete compliance rule
     * DELETE /api/compliance/rules/{id}
     */
    public function deleteRule($id)
    {
        $this->requirePermission('can_manage_compliance');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $result = $this->complianceService->deleteRule($id, $restaurantId);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse(['message' => 'Rule deleted successfully']);
    }

    /**
     * Run compliance check
     * POST /api/compliance/checks
     */
    public function runCheck()
    {
        $this->requirePermission('can_manage_compliance');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $ruleId = $this->request->get('rule_id', null);
        
        $result = $this->complianceService->runCheck($restaurantId, $userId, $ruleId);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get compliance checks
     * GET /api/compliance/checks
     */
    public function getChecks()
    {
        $this->requirePermission('can_view_compliance');
        
        $restaurantId = Auth::user()->restaurant_id;
        $ruleId = $this->request->get('rule_id', null);
        $status = $this->request->get('status', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->complianceService->getChecks($restaurantId, $ruleId, $status, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Get compliance documents
     * GET /api/compliance/documents
     */
    public function getDocuments()
    {
        $this->requirePermission('can_view_compliance');
        
        $restaurantId = Auth::user()->restaurant_id;
        $documentType = $this->request->get('document_type', null);
        $status = $this->request->get('status', null);
        
        $documents = $this->complianceService->getDocuments($restaurantId, $documentType, $status);
        
        $this->jsonResponse($documents);
    }

    /**
     * Add compliance document
     * POST /api/compliance/documents
     */
    public function addDocument()
    {
        $this->requirePermission('can_manage_compliance');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->complianceService->addDocument($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Update compliance document
     * PUT /api/compliance/documents/{id}
     */
    public function updateDocument($id)
    {
        $this->requirePermission('can_manage_compliance');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->complianceService->updateDocument($id, $restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Delete compliance document
     * DELETE /api/compliance/documents/{id}
     */
    public function deleteDocument($id)
    {
        $this->requirePermission('can_manage_compliance');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $result = $this->complianceService->deleteDocument($id, $restaurantId);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse(['message' => 'Document deleted successfully']);
    }

    /**
     * Get compliance alerts
     * GET /api/compliance/alerts
     */
    public function getAlerts()
    {
        $this->requirePermission('can_view_compliance');
        
        $restaurantId = Auth::user()->restaurant_id;
        $isResolved = $this->request->get('is_resolved', null);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->complianceService->getAlerts($restaurantId, $isResolved, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Resolve compliance alert
     * POST /api/compliance/alerts/{id}/resolve
     */
    public function resolveAlert($id)
    {
        $this->requirePermission('can_manage_compliance');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->complianceService->resolveAlert($id, $restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get labor law compliance
     * GET /api/compliance/labor-law
     */
    public function getLaborLawCompliance()
    {
        $this->requirePermission('can_view_compliance');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $compliance = $this->complianceService->getLaborLawCompliance($restaurantId);
        
        $this->jsonResponse($compliance);
    }

    /**
     * Update labor law compliance
     * PUT /api/compliance/labor-law/{id}
     */
    public function updateLaborLawCompliance($id)
    {
        $this->requirePermission('can_manage_compliance');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->complianceService->updateLaborLawCompliance($id, $restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get tax compliance
     * GET /api/compliance/tax
     */
    public function getTaxCompliance()
    {
        $this->requirePermission('can_view_compliance');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $compliance = $this->complianceService->getTaxCompliance($restaurantId);
        
        $this->jsonResponse($compliance);
    }

    /**
     * Update tax compliance
     * PUT /api/compliance/tax/{id}
     */
    public function updateTaxCompliance($id)
    {
        $this->requirePermission('can_manage_compliance');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->complianceService->updateTaxCompliance($id, $restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get food safety compliance
     * GET /api/compliance/food-safety
     */
    public function getFoodSafetyCompliance()
    {
        $this->requirePermission('can_view_compliance');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $compliance = $this->complianceService->getFoodSafetyCompliance($restaurantId);
        
        $this->jsonResponse($compliance);
    }

    /**
     * Add food safety inspection
     * POST /api/compliance/food-safety
     */
    public function addFoodSafetyInspection()
    {
        $this->requirePermission('can_manage_compliance');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->complianceService->addFoodSafetyInspection($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }
}
