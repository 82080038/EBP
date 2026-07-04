<?php

namespace App\Modules\Reconciliation\Controllers;

use App\Core\BaseController;
use App\Modules\Reconciliation\Models\ReconciliationTransaction;
use App\Modules\Reconciliation\Models\ReconciliationSource;
use App\Modules\Reconciliation\Models\ReconciliationRule;
use App\Modules\Reconciliation\Models\ReconciliationLog;
use App\Modules\Reconciliation\Models\ReconciliationAlert;
use App\Modules\Reconciliation\Models\ReconciliationBatchJob;
use App\Modules\Reconciliation\Services\ReconciliationService;
use App\Core\Auth;

class ReconciliationController extends BaseController
{
    private $reconciliationService;

    public function __construct()
    {
        parent::__construct();
        $this->reconciliationService = new ReconciliationService();
        
        // Check permissions
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get reconciliation dashboard data
     * GET /api/reconciliation/dashboard
     */
    public function dashboard()
    {
        $this->requirePermission('can_view_reconciliation');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $dashboard = $this->reconciliationService->getDashboardData($restaurantId);
        
        $this->jsonResponse($dashboard);
    }

    /**
     * Get reconciliation transactions list
     * GET /api/reconciliation/transactions
     */
    public function getTransactions()
    {
        $this->requirePermission('can_view_reconciliation');
        
        $restaurantId = Auth::user()->restaurant_id;
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        $status = $this->request->get('status', null);
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        
        $result = $this->reconciliationService->getTransactions(
            $restaurantId,
            $page,
            $limit,
            $status,
            $dateFrom,
            $dateTo
        );
        
        $this->jsonResponse($result);
    }

    /**
     * Get single reconciliation transaction
     * GET /api/reconciliation/transactions/{id}
     */
    public function getTransaction($id)
    {
        $this->requirePermission('can_view_reconciliation');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $transaction = $this->reconciliationService->getTransaction($id, $restaurantId);
        
        if (!$transaction) {
            $this->jsonResponse(['error' => 'Transaction not found'], 404);
            return;
        }
        
        $this->jsonResponse($transaction);
    }

    /**
     * Manual match transaction
     * POST /api/reconciliation/transactions/{id}/match
     */
    public function manualMatch($id)
    {
        $this->requirePermission('can_resolve_discrepancies');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->reconciliationService->manualMatch(
            $id,
            $restaurantId,
            $userId,
            $data
        );
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get reconciliation sources
     * GET /api/reconciliation/sources
     */
    public function getSources()
    {
        $this->requirePermission('can_view_reconciliation');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $sources = $this->reconciliationService->getSources($restaurantId);
        
        $this->jsonResponse($sources);
    }

    /**
     * Add reconciliation source
     * POST /api/reconciliation/sources
     */
    public function addSource()
    {
        $this->requirePermission('can_manage_reconciliation');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $validation = $this->validateSourceData($data);
        if (!$validation['valid']) {
            $this->jsonResponse(['error' => $validation['message']], 400);
            return;
        }
        
        $result = $this->reconciliationService->addSource($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Update reconciliation source
     * PUT /api/reconciliation/sources/{id}
     */
    public function updateSource($id)
    {
        $this->requirePermission('can_manage_reconciliation');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->reconciliationService->updateSource($id, $restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Delete reconciliation source
     * DELETE /api/reconciliation/sources/{id}
     */
    public function deleteSource($id)
    {
        $this->requirePermission('can_manage_reconciliation');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $result = $this->reconciliationService->deleteSource($id, $restaurantId);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse(['message' => 'Source deleted successfully']);
    }

    /**
     * Trigger manual sync
     * POST /api/reconciliation/sync
     */
    public function triggerSync()
    {
        $this->requirePermission('can_manage_reconciliation');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $sourceId = $this->request->get('source_id', null);
        
        $result = $this->reconciliationService->triggerSync($restaurantId, $userId, $sourceId);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get reconciliation rules
     * GET /api/reconciliation/rules
     */
    public function getRules()
    {
        $this->requirePermission('can_view_reconciliation');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $rules = $this->reconciliationService->getRules($restaurantId);
        
        $this->jsonResponse($rules);
    }

    /**
     * Add reconciliation rule
     * POST /api/reconciliation/rules
     */
    public function addRule()
    {
        $this->requirePermission('can_manage_reconciliation');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $validation = $this->validateRuleData($data);
        if (!$validation['valid']) {
            $this->jsonResponse(['error' => $validation['message']], 400);
            return;
        }
        
        $result = $this->reconciliationService->addRule($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Update reconciliation rule
     * PUT /api/reconciliation/rules/{id}
     */
    public function updateRule($id)
    {
        $this->requirePermission('can_manage_reconciliation');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->reconciliationService->updateRule($id, $restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Delete reconciliation rule
     * DELETE /api/reconciliation/rules/{id}
     */
    public function deleteRule($id)
    {
        $this->requirePermission('can_manage_reconciliation');
        
        $restaurantId = Auth::user()->restaurant_id;
        
        $result = $this->reconciliationService->deleteRule($id, $restaurantId);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse(['message' => 'Rule deleted successfully']);
    }

    /**
     * Get reconciliation alerts
     * GET /api/reconciliation/alerts
     */
    public function getAlerts()
    {
        $this->requirePermission('can_view_reconciliation');
        
        $restaurantId = Auth::user()->restaurant_id;
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        $isResolved = $this->request->get('is_resolved', null);
        
        $result = $this->reconciliationService->getAlerts($restaurantId, $page, $limit, $isResolved);
        
        $this->jsonResponse($result);
    }

    /**
     * Resolve alert
     * POST /api/reconciliation/alerts/{id}/resolve
     */
    public function resolveAlert($id)
    {
        $this->requirePermission('can_resolve_discrepancies');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->reconciliationService->resolveAlert($id, $restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get reconciliation reports
     * GET /api/reconciliation/reports
     */
    public function getReports()
    {
        $this->requirePermission('can_view_reconciliation');
        
        $restaurantId = Auth::user()->restaurant_id;
        $reportType = $this->request->get('type', 'summary');
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        
        $result = $this->reconciliationService->getReport($restaurantId, $reportType, $dateFrom, $dateTo);
        
        $this->jsonResponse($result);
    }

    /**
     * Get batch jobs
     * GET /api/reconciliation/batch-jobs
     */
    public function getBatchJobs()
    {
        $this->requirePermission('can_view_reconciliation');
        
        $restaurantId = Auth::user()->restaurant_id;
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $result = $this->reconciliationService->getBatchJobs($restaurantId, $page, $limit);
        
        $this->jsonResponse($result);
    }

    /**
     * Trigger batch job
     * POST /api/reconciliation/batch-jobs
     */
    public function triggerBatchJob()
    {
        $this->requirePermission('can_manage_reconciliation');
        
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->reconciliationService->triggerBatchJob($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Validate source data
     */
    private function validateSourceData($data)
    {
        if (empty($data->source_type)) {
            return ['valid' => false, 'message' => 'Source type is required'];
        }
        
        if (empty($data->source_name)) {
            return ['valid' => false, 'message' => 'Source name is required'];
        }
        
        if (empty($data->source_identifier)) {
            return ['valid' => false, 'message' => 'Source identifier is required'];
        }
        
        $validTypes = ['pos', 'payment_processor', 'delivery_platform', 'other'];
        if (!in_array($data->source_type, $validTypes)) {
            return ['valid' => false, 'message' => 'Invalid source type'];
        }
        
        return ['valid' => true];
    }

    /**
     * Validate rule data
     */
    private function validateRuleData($data)
    {
        if (empty($data->rule_name)) {
            return ['valid' => false, 'message' => 'Rule name is required'];
        }
        
        if (empty($data->rule_type)) {
            return ['valid' => false, 'message' => 'Rule type is required'];
        }
        
        if (empty($data->rule_config)) {
            return ['valid' => false, 'message' => 'Rule configuration is required'];
        }
        
        $validTypes = ['amount_tolerance', 'time_tolerance', 'auto_match', 'alert_threshold'];
        if (!in_array($data->rule_type, $validTypes)) {
            return ['valid' => false, 'message' => 'Invalid rule type'];
        }
        
        return ['valid' => true];
    }
}
