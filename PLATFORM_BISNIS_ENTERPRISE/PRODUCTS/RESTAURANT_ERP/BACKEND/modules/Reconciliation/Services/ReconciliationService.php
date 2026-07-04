<?php

namespace App\Modules\Reconciliation\Services;

use App\Modules\Reconciliation\Models\ReconciliationTransaction;
use App\Modules\Reconciliation\Models\ReconciliationSource;
use App\Modules\Reconciliation\Models\ReconciliationRule;
use App\Modules\Reconciliation\Models\ReconciliationLog;
use App\Modules\Reconciliation\Models\ReconciliationAlert;
use App\Modules\Reconciliation\Models\ReconciliationBatchJob;
use App\Core\Database;

class ReconciliationService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get dashboard data
     */
    public function getDashboardData($restaurantId)
    {
        $transactionModel = new ReconciliationTransaction();
        $alertModel = new ReconciliationAlert();
        
        // Get summary stats
        $stats = [
            'total_transactions' => $transactionModel->countByRestaurant($restaurantId),
            'matched' => $transactionModel->countByStatus($restaurantId, 'matched'),
            'pending' => $transactionModel->countByStatus($restaurantId, 'pending'),
            'discrepancies' => $transactionModel->countByStatus($restaurantId, 'discrepancy'),
            'unresolved_alerts' => $alertModel->countUnresolved($restaurantId),
            'total_discrepancy_amount' => $transactionModel->sumDiscrepancyAmount($restaurantId)
        ];
        
        // Get recent transactions
        $recentTransactions = $transactionModel->getRecent($restaurantId, 10);
        
        // Get recent alerts
        $recentAlerts = $alertModel->getRecent($restaurantId, 5);
        
        // Get sync status
        $sourceModel = new ReconciliationSource();
        $syncStatus = $sourceModel->getSyncStatus($restaurantId);
        
        return [
            'stats' => $stats,
            'recent_transactions' => $recentTransactions,
            'recent_alerts' => $recentAlerts,
            'sync_status' => $syncStatus
        ];
    }

    /**
     * Get transactions with pagination
     */
    public function getTransactions($restaurantId, $page, $limit, $status, $dateFrom, $dateTo)
    {
        $transactionModel = new ReconciliationTransaction();
        
        return $transactionModel->getPaginated(
            $restaurantId,
            $page,
            $limit,
            $status,
            $dateFrom,
            $dateTo
        );
    }

    /**
     * Get single transaction
     */
    public function getTransaction($id, $restaurantId)
    {
        $transactionModel = new ReconciliationTransaction();
        $transaction = $transactionModel->findById($id, $restaurantId);
        
        if ($transaction) {
            // Get related logs
            $logModel = new ReconciliationLog();
            $transaction['logs'] = $logModel->getByTransactionId($id);
        }
        
        return $transaction;
    }

    /**
     * Manual match transaction
     */
    public function manualMatch($id, $restaurantId, $userId, $data)
    {
        $transactionModel = new ReconciliationTransaction();
        $transaction = $transactionModel->findById($id, $restaurantId);
        
        if (!$transaction) {
            return ['success' => false, 'message' => 'Transaction not found'];
        }
        
        // Update transaction
        $updateData = [
            'manually_matched' => true,
            'matched_by' => $userId,
            'matched_at' => date('Y-m-d H:i:s'),
            'match_notes' => $data->notes ?? null,
            'reconciliation_status' => 'resolved'
        ];
        
        $updated = $transactionModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update transaction'];
        }
        
        // Log the action
        $logModel = new ReconciliationLog();
        $logModel->create([
            'restaurant_id' => $restaurantId,
            'reconciliation_transaction_id' => $id,
            'log_type' => 'manual_action',
            'log_message' => 'Transaction manually matched',
            'log_data' => json_encode($data),
            'action_by' => $userId
        ]);
        
        return ['success' => true, 'message' => 'Transaction matched successfully'];
    }

    /**
     * Get reconciliation sources
     */
    public function getSources($restaurantId)
    {
        $sourceModel = new ReconciliationSource();
        return $sourceModel->getByRestaurant($restaurantId);
    }

    /**
     * Add reconciliation source
     */
    public function addSource($restaurantId, $userId, $data)
    {
        $sourceModel = new ReconciliationSource();
        
        $sourceData = [
            'restaurant_id' => $restaurantId,
            'source_type' => $data->source_type,
            'source_name' => $data->source_name,
            'source_identifier' => $data->source_identifier,
            'api_endpoint' => $data->api_endpoint ?? null,
            'api_key_encrypted' => $this->encrypt($data->api_key ?? null),
            'api_secret_encrypted' => $this->encrypt($data->api_secret ?? null),
            'webhook_url' => $data->webhook_url ?? null,
            'webhook_secret_encrypted' => $this->encrypt($data->webhook_secret ?? null),
            'sync_frequency' => $data->sync_frequency ?? 'daily',
            'is_active' => true
        ];
        
        $sourceId = $sourceModel->create($sourceData);
        
        if (!$sourceId) {
            return ['success' => false, 'message' => 'Failed to create source'];
        }
        
        // Log the action
        $logModel = new ReconciliationLog();
        $logModel->create([
            'restaurant_id' => $restaurantId,
            'log_type' => 'sync',
            'log_message' => 'Reconciliation source added: ' . $data->source_name,
            'log_data' => json_encode(['source_id' => $sourceId]),
            'action_by' => $userId
        ]);
        
        return ['success' => true, 'message' => 'Source added successfully', 'source_id' => $sourceId];
    }

    /**
     * Update reconciliation source
     */
    public function updateSource($id, $restaurantId, $data)
    {
        $sourceModel = new ReconciliationSource();
        $source = $sourceModel->findById($id, $restaurantId);
        
        if (!$source) {
            return ['success' => false, 'message' => 'Source not found'];
        }
        
        $updateData = [];
        
        if (isset($data->source_name)) {
            $updateData['source_name'] = $data->source_name;
        }
        if (isset($data->api_endpoint)) {
            $updateData['api_endpoint'] = $data->api_endpoint;
        }
        if (isset($data->api_key)) {
            $updateData['api_key_encrypted'] = $this->encrypt($data->api_key);
        }
        if (isset($data->api_secret)) {
            $updateData['api_secret_encrypted'] = $this->encrypt($data->api_secret);
        }
        if (isset($data->sync_frequency)) {
            $updateData['sync_frequency'] = $data->sync_frequency;
        }
        if (isset($data->is_active)) {
            $updateData['is_active'] = $data->is_active;
        }
        
        $updated = $sourceModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update source'];
        }
        
        return ['success' => true, 'message' => 'Source updated successfully'];
    }

    /**
     * Delete reconciliation source
     */
    public function deleteSource($id, $restaurantId)
    {
        $sourceModel = new ReconciliationSource();
        $source = $sourceModel->findById($id, $restaurantId);
        
        if (!$source) {
            return ['success' => false, 'message' => 'Source not found'];
        }
        
        $deleted = $sourceModel->delete($id);
        
        if (!$deleted) {
            return ['success' => false, 'message' => 'Failed to delete source'];
        }
        
        return ['success' => true, 'message' => 'Source deleted successfully'];
    }

    /**
     * Trigger manual sync
     */
    public function triggerSync($restaurantId, $userId, $sourceId = null)
    {
        $sourceModel = new ReconciliationSource();
        
        if ($sourceId) {
            $sources = [$sourceModel->findById($sourceId, $restaurantId)];
        } else {
            $sources = $sourceModel->getActiveByRestaurant($restaurantId);
        }
        
        if (empty($sources)) {
            return ['success' => false, 'message' => 'No active sources found'];
        }
        
        // Create batch job
        $batchJobModel = new ReconciliationBatchJob();
        $jobId = $batchJobModel->create([
            'restaurant_id' => $restaurantId,
            'job_type' => 'sync',
            'job_config' => json_encode(['source_ids' => array_column($sources, 'id')]),
            'job_status' => 'running',
            'started_at' => date('Y-m-d H:i:s'),
            'triggered_by' => 'manual',
            'triggered_by_user_id' => $userId
        ]);
        
        // Process sync (in real implementation, this would be a background job)
        $totalProcessed = 0;
        $totalMatched = 0;
        $totalDiscrepancies = 0;
        $totalErrors = 0;
        
        foreach ($sources as $source) {
            $result = $this->syncSource($source, $restaurantId);
            $totalProcessed += $result['processed'];
            $totalMatched += $result['matched'];
            $totalDiscrepancies += $result['discrepancies'];
            $totalErrors += $result['errors'];
        }
        
        // Update batch job
        $batchJobModel->update($jobId, [
            'job_status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s'),
            'total_processed' => $totalProcessed,
            'total_matched' => $totalMatched,
            'total_discrepancies' => $totalDiscrepancies,
            'total_errors' => $totalErrors
        ]);
        
        return [
            'success' => true,
            'message' => 'Sync completed',
            'job_id' => $jobId,
            'summary' => [
                'processed' => $totalProcessed,
                'matched' => $totalMatched,
                'discrepancies' => $totalDiscrepancies,
                'errors' => $totalErrors
            ]
        ];
    }

    /**
     * Sync individual source
     */
    private function syncSource($source, $restaurantId)
    {
        // In real implementation, this would call the source API
        // For now, simulate sync
        
        $logModel = new ReconciliationLog();
        $logModel->create([
            'restaurant_id' => $restaurantId,
            'log_type' => 'sync',
            'log_message' => 'Sync started for source: ' . $source['source_name'],
            'source_type' => $source['source_type'],
            'source_id' => $source['source_identifier']
        ]);
        
        // Update source last sync
        $sourceModel = new ReconciliationSource();
        $sourceModel->update($source['id'], [
            'last_sync_at' => date('Y-m-d H:i:s'),
            'last_sync_status' => 'success'
        ]);
        
        return [
            'processed' => 0,
            'matched' => 0,
            'discrepancies' => 0,
            'errors' => 0
        ];
    }

    /**
     * Get reconciliation rules
     */
    public function getRules($restaurantId)
    {
        $ruleModel = new ReconciliationRule();
        return $ruleModel->getByRestaurant($restaurantId);
    }

    /**
     * Add reconciliation rule
     */
    public function addRule($restaurantId, $data)
    {
        $ruleModel = new ReconciliationRule();
        
        $ruleData = [
            'restaurant_id' => $restaurantId,
            'rule_name' => $data->rule_name,
            'rule_type' => $data->rule_type,
            'rule_config' => json_encode($data->rule_config),
            'priority' => $data->priority ?? 0,
            'is_active' => true
        ];
        
        $ruleId = $ruleModel->create($ruleData);
        
        if (!$ruleId) {
            return ['success' => false, 'message' => 'Failed to create rule'];
        }
        
        return ['success' => true, 'message' => 'Rule added successfully', 'rule_id' => $ruleId];
    }

    /**
     * Update reconciliation rule
     */
    public function updateRule($id, $restaurantId, $data)
    {
        $ruleModel = new ReconciliationRule();
        $rule = $ruleModel->findById($id, $restaurantId);
        
        if (!$rule) {
            return ['success' => false, 'message' => 'Rule not found'];
        }
        
        $updateData = [];
        
        if (isset($data->rule_name)) {
            $updateData['rule_name'] = $data->rule_name;
        }
        if (isset($data->rule_config)) {
            $updateData['rule_config'] = json_encode($data->rule_config);
        }
        if (isset($data->priority)) {
            $updateData['priority'] = $data->priority;
        }
        if (isset($data->is_active)) {
            $updateData['is_active'] = $data->is_active;
        }
        
        $updated = $ruleModel->update($id, $updateData);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update rule'];
        }
        
        return ['success' => true, 'message' => 'Rule updated successfully'];
    }

    /**
     * Delete reconciliation rule
     */
    public function deleteRule($id, $restaurantId)
    {
        $ruleModel = new ReconciliationRule();
        $rule = $ruleModel->findById($id, $restaurantId);
        
        if (!$rule) {
            return ['success' => false, 'message' => 'Rule not found'];
        }
        
        $deleted = $ruleModel->delete($id);
        
        if (!$deleted) {
            return ['success' => false, 'message' => 'Failed to delete rule'];
        }
        
        return ['success' => true, 'message' => 'Rule deleted successfully'];
    }

    /**
     * Get reconciliation alerts
     */
    public function getAlerts($restaurantId, $page, $limit, $isResolved)
    {
        $alertModel = new ReconciliationAlert();
        return $alertModel->getPaginated($restaurantId, $page, $limit, $isResolved);
    }

    /**
     * Resolve alert
     */
    public function resolveAlert($id, $restaurantId, $userId, $data)
    {
        $alertModel = new ReconciliationAlert();
        $alert = $alertModel->findById($id, $restaurantId);
        
        if (!$alert) {
            return ['success' => false, 'message' => 'Alert not found'];
        }
        
        $updated = $alertModel->update($id, [
            'is_resolved' => true,
            'resolved_by' => $userId,
            'resolved_at' => date('Y-m-d H:i:s'),
            'resolution_notes' => $data->notes ?? null
        ]);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to resolve alert'];
        }
        
        return ['success' => true, 'message' => 'Alert resolved successfully'];
    }

    /**
     * Get reconciliation report
     */
    public function getReport($restaurantId, $reportType, $dateFrom, $dateTo)
    {
        $transactionModel = new ReconciliationTransaction();
        
        switch ($reportType) {
            case 'summary':
                return $this->getSummaryReport($restaurantId, $dateFrom, $dateTo);
            case 'discrepancies':
                return $this->getDiscrepancyReport($restaurantId, $dateFrom, $dateTo);
            case 'sources':
                return $this->getSourceReport($restaurantId, $dateFrom, $dateTo);
            default:
                return $this->getSummaryReport($restaurantId, $dateFrom, $dateTo);
        }
    }

    /**
     * Get summary report
     */
    private function getSummaryReport($restaurantId, $dateFrom, $dateTo)
    {
        $transactionModel = new ReconciliationTransaction();
        
        return [
            'total_transactions' => $transactionModel->countByDateRange($restaurantId, $dateFrom, $dateTo),
            'matched' => $transactionModel->countByStatusAndDateRange($restaurantId, 'matched', $dateFrom, $dateTo),
            'discrepancies' => $transactionModel->countByStatusAndDateRange($restaurantId, 'discrepancy', $dateFrom, $dateTo),
            'total_amount' => $transactionModel->sumAmountByDateRange($restaurantId, $dateFrom, $dateTo),
            'discrepancy_amount' => $transactionModel->sumDiscrepancyAmountByDateRange($restaurantId, $dateFrom, $dateTo)
        ];
    }

    /**
     * Get discrepancy report
     */
    private function getDiscrepancyReport($restaurantId, $dateFrom, $dateTo)
    {
        $transactionModel = new ReconciliationTransaction();
        
        return $transactionModel->getDiscrepanciesByDateRange($restaurantId, $dateFrom, $dateTo);
    }

    /**
     * Get source report
     */
    private function getSourceReport($restaurantId, $dateFrom, $dateTo)
    {
        $sourceModel = new ReconciliationSource();
        return $sourceModel->getPerformanceReport($restaurantId, $dateFrom, $dateTo);
    }

    /**
     * Get batch jobs
     */
    public function getBatchJobs($restaurantId, $page, $limit)
    {
        $batchJobModel = new ReconciliationBatchJob();
        return $batchJobModel->getPaginated($restaurantId, $page, $limit);
    }

    /**
     * Trigger batch job
     */
    public function triggerBatchJob($restaurantId, $userId, $data)
    {
        $batchJobModel = new ReconciliationBatchJob();
        
        $jobData = [
            'restaurant_id' => $restaurantId,
            'job_type' => $data->job_type,
            'job_config' => json_encode($data->config ?? []),
            'job_status' => 'pending',
            'triggered_by' => 'manual',
            'triggered_by_user_id' => $userId
        ];
        
        $jobId = $batchJobModel->create($jobData);
        
        if (!$jobId) {
            return ['success' => false, 'message' => 'Failed to create batch job'];
        }
        
        return ['success' => true, 'message' => 'Batch job created', 'job_id' => $jobId];
    }

    /**
     * Encrypt sensitive data
     */
    private function encrypt($data)
    {
        if (empty($data)) {
            return null;
        }
        
        // In real implementation, use proper encryption
        return base64_encode($data);
    }

    /**
     * Decrypt sensitive data
     */
    private function decrypt($data)
    {
        if (empty($data)) {
            return null;
        }
        
        // In real implementation, use proper decryption
        return base64_decode($data);
    }
}
