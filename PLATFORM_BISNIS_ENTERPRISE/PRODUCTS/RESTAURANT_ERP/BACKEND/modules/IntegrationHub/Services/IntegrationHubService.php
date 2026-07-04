<?php

namespace App\Modules\IntegrationHub\Services;

use App\Modules\IntegrationHub\Models\ExternalIntegration;
use App\Modules\IntegrationHub\Models\IntegrationMapping;
use App\Modules\IntegrationHub\Models\IntegrationSyncLog;
use App\Core\Database;

class IntegrationHubService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get external integrations
     */
    public function getIntegrations($restaurantId, $type, $status)
    {
        $integrationModel = new ExternalIntegration();
        return $integrationModel->getByRestaurant($restaurantId, $type, $status);
    }

    /**
     * Create external integration
     */
    public function createIntegration($restaurantId, $userId, $data)
    {
        $integrationModel = new ExternalIntegration();
        
        $integrationData = [
            'restaurant_id' => $restaurantId,
            'integration_name' => $data->integration_name,
            'integration_type' => $data->integration_type,
            'provider_name' => $data->provider_name,
            'api_endpoint' => $data->api_endpoint ?? null,
            'api_key' => $data->api_key ?? null,
            'api_secret' => $data->api_secret ?? null,
            'webhook_url' => $data->webhook_url ?? null,
            'integration_config' => json_encode($data->integration_config ?? []),
            'sync_frequency' => $data->sync_frequency ?? 'daily',
            'integration_status' => 'disconnected',
            'created_by' => $userId
        ];
        
        $integrationId = $integrationModel->create($integrationData);
        
        if (!$integrationId) {
            return ['success' => false, 'message' => 'Failed to create integration'];
        }
        
        return ['success' => true, 'message' => 'Integration created', 'integration_id' => $integrationId];
    }

    /**
     * Get integration mappings
     */
    public function getMappings($restaurantId, $integrationId)
    {
        $mappingModel = new IntegrationMapping();
        return $mappingModel->getByRestaurant($restaurantId, $integrationId);
    }

    /**
     * Create integration mapping
     */
    public function createMapping($restaurantId, $data)
    {
        $mappingModel = new IntegrationMapping();
        
        $mappingData = [
            'restaurant_id' => $restaurantId,
            'integration_id' => $data->integration_id,
            'mapping_name' => $data->mapping_name,
            'mapping_type' => $data->mapping_type,
            'source_system' => $data->source_system,
            'source_entity' => $data->source_entity,
            'source_field' => $data->source_field ?? null,
            'target_system' => $data->target_system,
            'target_entity' => $data->target_entity,
            'target_field' => $data->target_field ?? null,
            'transformation_rules' => json_encode($data->transformation_rules ?? []),
            'is_active' => true
        ];
        
        $mappingId = $mappingModel->create($mappingData);
        
        if (!$mappingId) {
            return ['success' => false, 'message' => 'Failed to create mapping'];
        }
        
        return ['success' => true, 'message' => 'Mapping created', 'mapping_id' => $mappingId];
    }

    /**
     * Get sync logs
     */
    public function getSyncLogs($restaurantId, $integrationId, $status, $limit)
    {
        $syncLogModel = new IntegrationSyncLog();
        return $syncLogModel->getByRestaurant($restaurantId, $integrationId, $status, $limit);
    }

    /**
     * Trigger manual sync
     */
    public function triggerSync($restaurantId, $data)
    {
        $integrationModel = new ExternalIntegration();
        $integration = $integrationModel->findById($data->integration_id, $restaurantId);
        
        if (!$integration) {
            return ['success' => false, 'message' => 'Integration not found'];
        }
        
        // Create sync log entry
        $syncLogModel = new IntegrationSyncLog();
        $syncLogData = [
            'restaurant_id' => $restaurantId,
            'integration_id' => $data->integration_id,
            'sync_type' => 'manual',
            'sync_direction' => $data->sync_direction ?? 'bidirectional',
            'sync_status' => 'running',
            'started_at' => date('Y-m-d H:i:s')
        ];
        
        $syncLogId = $syncLogModel->create($syncLogData);
        
        if (!$syncLogId) {
            return ['success' => false, 'message' => 'Failed to create sync log'];
        }
        
        return ['success' => true, 'message' => 'Sync triggered', 'sync_log_id' => $syncLogId];
    }

    /**
     * Get analytics
     */
    public function getAnalytics($restaurantId, $integrationId, $metricType, $dateFrom, $dateTo, $limit)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($integrationId) {
            $where .= " AND integration_id = ?";
            $params[] = $integrationId;
        }
        
        $where .= " AND metric_type = ?";
        $params[] = $metricType;
        
        if ($dateFrom) {
            $where .= " AND metric_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND metric_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT * FROM integration_analytics {$where} ORDER BY metric_date DESC LIMIT ?";
        $params[] = $limit;
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get summary
     */
    public function getSummary($restaurantId)
    {
        $integrationModel = new ExternalIntegration();
        $syncLogModel = new IntegrationSyncLog();
        
        // Active integrations
        $activeIntegrations = $integrationModel->countByStatus($restaurantId, 'active');
        
        // Total integrations
        $totalIntegrations = $integrationModel->countByRestaurant($restaurantId);
        
        // Recent syncs
        $recentSyncs = $syncLogModel->getRecent($restaurantId, 5);
        
        // Latest analytics
        $latestAnalytics = $this->getAnalytics($restaurantId, null, 'monthly', null, null, 1);
        
        return [
            'total_integrations' => $totalIntegrations,
            'active_integrations' => $activeIntegrations,
            'recent_syncs' => $recentSyncs,
            'latest_analytics' => $latestAnalytics[0] ?? null
        ];
    }
}
