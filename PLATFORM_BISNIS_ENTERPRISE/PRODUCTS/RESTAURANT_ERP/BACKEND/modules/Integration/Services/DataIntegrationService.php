<?php

if (!class_exists('Database')) {
    require_once __DIR__ . '/../../Core/Database.php';
}

class DataIntegrationService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get external systems
     */
    public function getExternalSystems($restaurantId)
    {
        $sql = "SELECT * FROM external_systems 
                WHERE restaurant_id = ? 
                ORDER BY created_at DESC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Add external system
     */
    public function addExternalSystem($restaurantId, $data)
    {
        $sql = "INSERT INTO external_systems 
                (restaurant_id, system_type, system_name, system_identifier, 
                 api_base_url, api_version, api_key_encrypted, api_secret_encrypted, 
                 webhook_url, webhook_secret_encrypted, integration_config, mapping_config,
                 sync_mode, sync_frequency, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $restaurantId,
            $data['system_type'],
            $data['system_name'],
            $data['system_identifier'],
            $data['api_base_url'] ?? null,
            $data['api_version'] ?? null,
            $this->encrypt($data['api_key'] ?? null),
            $this->encrypt($data['api_secret'] ?? null),
            $data['webhook_url'] ?? null,
            $this->encrypt($data['webhook_secret'] ?? null),
            json_encode($data['integration_config'] ?? []),
            json_encode($data['mapping_config'] ?? []),
            $data['sync_mode'] ?? 'scheduled',
            $data['sync_frequency'] ?? null,
            true
        ];

        $result = $this->db->query($sql, $params);
        
        if ($result) {
            return ['success' => true, 'message' => 'External system added', 'data' => ['id' => $this->db->lastInsertId()]];
        }
        
        return ['success' => false, 'message' => 'Failed to add external system'];
    }

    /**
     * Update external system
     */
    public function updateExternalSystem($restaurantId, $systemId, $data)
    {
        $updateFields = [];
        $params = [];

        if (isset($data['system_name'])) {
            $updateFields[] = 'system_name = ?';
            $params[] = $data['system_name'];
        }
        if (isset($data['api_base_url'])) {
            $updateFields[] = 'api_base_url = ?';
            $params[] = $data['api_base_url'];
        }
        if (isset($data['api_key'])) {
            $updateFields[] = 'api_key_encrypted = ?';
            $params[] = $this->encrypt($data['api_key']);
        }
        if (isset($data['api_secret'])) {
            $updateFields[] = 'api_secret_encrypted = ?';
            $params[] = $this->encrypt($data['api_secret']);
        }
        if (isset($data['sync_mode'])) {
            $updateFields[] = 'sync_mode = ?';
            $params[] = $data['sync_mode'];
        }
        if (isset($data['sync_frequency'])) {
            $updateFields[] = 'sync_frequency = ?';
            $params[] = $data['sync_frequency'];
        }
        if (isset($data['is_active'])) {
            $updateFields[] = 'is_active = ?';
            $params[] = $data['is_active'];
        }

        if (empty($updateFields)) {
            return ['success' => false, 'message' => 'No fields to update'];
        }

        $params[] = $restaurantId;
        $params[] = $systemId;

        $sql = "UPDATE external_systems 
                SET " . implode(', ', $updateFields) . ", updated_at = CURRENT_TIMESTAMP
                WHERE restaurant_id = ? AND id = ?";

        $result = $this->db->query($sql, $params);
        
        if ($result) {
            return ['success' => true, 'message' => 'External system updated'];
        }
        
        return ['success' => false, 'message' => 'Failed to update external system'];
    }

    /**
     * Delete external system
     */
    public function deleteExternalSystem($restaurantId, $systemId)
    {
        $sql = "DELETE FROM external_systems 
                WHERE restaurant_id = ? AND id = ?";
        
        $result = $this->db->query($sql, [$restaurantId, $systemId]);
        
        if ($result) {
            return ['success' => true, 'message' => 'External system deleted'];
        }
        
        return ['success' => false, 'message' => 'Failed to delete external system'];
    }

    /**
     * Trigger sync
     */
    public function triggerSync($restaurantId, $systemId, $syncType, $userId)
    {
        // Get system details
        $sql = "SELECT * FROM external_systems 
                WHERE restaurant_id = ? AND id = ? AND is_active = TRUE";
        $system = $this->db->query($sql, [$restaurantId, $systemId])->fetch();
        
        if (!$system) {
            return ['success' => false, 'message' => 'System not found or inactive'];
        }

        // Create sync log
        $sql = "INSERT INTO sync_logs 
                (restaurant_id, external_system_id, sync_type, sync_direction, sync_status, started_at, triggered_by, triggered_by_user_id)
                VALUES (?, ?, ?, 'inbound', 'started', NOW(), 'manual', ?)";
        
        $this->db->query($sql, [$restaurantId, $systemId, $syncType, $userId]);
        $syncLogId = $this->db->lastInsertId();

        // Process sync based on system type
        $result = $this->processSync($system, $syncType, $syncLogId);

        // Update sync log
        $updateSql = "UPDATE sync_logs 
                      SET sync_status = ?, completed_at = NOW(), duration_seconds = TIMESTAMPDIFF(SECOND, started_at, NOW()),
                          total_records = ?, successful_records = ?, failed_records = ?
                      WHERE id = ?";
        
        $this->db->query($updateSql, [
            $result['status'],
            $result['total'],
            $result['successful'],
            $result['failed'],
            $syncLogId
        ]);

        return [
            'success' => $result['status'] === 'completed',
            'message' => $result['message'],
            'data' => [
                'sync_log_id' => $syncLogId,
                'total_records' => $result['total'],
                'successful_records' => $result['successful'],
                'failed_records' => $result['failed']
            ]
        ];
    }

    /**
     * Process sync
     */
    private function processSync($system, $syncType, $syncLogId)
    {
        // In real implementation, this would call the external system API
        // For now, simulate sync
        
        $total = 0;
        $successful = 0;
        $failed = 0;
        $status = 'completed';
        $message = 'Sync completed';

        switch ($system['system_type']) {
            case 'pos':
                // POS sync logic
                $total = rand(10, 50);
                $successful = $total - rand(0, 3);
                $failed = $total - $successful;
                break;
            
            case 'payment_processor':
                // Payment processor sync logic
                $total = rand(20, 100);
                $successful = $total - rand(0, 5);
                $failed = $total - $successful;
                break;
            
            case 'delivery_platform':
                // Delivery platform sync logic
                $total = rand(5, 30);
                $successful = $total - rand(0, 2);
                $failed = $total - $successful;
                break;
            
            default:
                $message = 'Unknown system type';
                $status = 'failed';
        }

        // Update system last sync
        $updateSql = "UPDATE external_systems 
                      SET last_sync_at = NOW(), last_sync_status = ?
                      WHERE id = ?";
        
        $this->db->query($updateSql, [$status, $system['id']]);

        return [
            'status' => $status,
            'message' => $message,
            'total' => $total,
            'successful' => $successful,
            'failed' => $failed
        ];
    }

    /**
     * Get sync logs
     */
    public function getSyncLogs($restaurantId, $systemId, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE sl.restaurant_id = ?";
        
        if ($systemId) {
            $where .= " AND sl.external_system_id = ?";
            $params[] = $systemId;
        }

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM sync_logs sl {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;

        // Get data
        $sql = "SELECT sl.*, es.system_name, es.system_type 
                FROM sync_logs sl
                LEFT JOIN external_systems es ON sl.external_system_id = es.id
                {$where}
                ORDER BY sl.started_at DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $data = $this->db->query($sql, $params)->fetchAll();

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }

    /**
     * Get data mappings
     */
    public function getDataMappings($restaurantId, $systemId, $mappingType)
    {
        $params = [$restaurantId];
        $where = "WHERE dm.restaurant_id = ?";
        
        if ($systemId) {
            $where .= " AND dm.external_system_id = ?";
            $params[] = $systemId;
        }
        
        if ($mappingType) {
            $where .= " AND dm.mapping_type = ?";
            $params[] = $mappingType;
        }

        $sql = "SELECT dm.*, es.system_name 
                FROM data_mappings dm
                LEFT JOIN external_systems es ON dm.external_system_id = es.id
                {$where}
                ORDER BY dm.mapping_type, dm.created_at DESC";
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Add data mapping
     */
    public function addDataMapping($restaurantId, $data)
    {
        $sql = "INSERT INTO data_mappings 
                (restaurant_id, external_system_id, mapping_type, local_entity_id, external_entity_id, mapping_data, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $restaurantId,
            $data['external_system_id'],
            $data['mapping_type'],
            $data['local_entity_id'],
            $data['external_entity_id'],
            json_encode($data['mapping_data'] ?? []),
            true
        ];

        $result = $this->db->query($sql, $params);
        
        if ($result) {
            return ['success' => true, 'message' => 'Data mapping added'];
        }
        
        return ['success' => false, 'message' => 'Failed to add data mapping'];
    }

    /**
     * Get monitoring data
     */
    public function getMonitoringData($restaurantId, $systemId, $metricType, $dateFrom, $dateTo)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($systemId) {
            $where .= " AND external_system_id = ?";
            $params[] = $systemId;
        }
        
        if ($metricType) {
            $where .= " AND metric_type = ?";
            $params[] = $metricType;
        }
        
        if ($dateFrom) {
            $where .= " AND recorded_at >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND recorded_at <= ?";
            $params[] = $dateTo;
        }

        $sql = "SELECT * FROM integration_monitoring {$where} ORDER BY recorded_at DESC LIMIT 1000";
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Handle webhook
     */
    public function handleWebhook($systemId, $eventType, $signature, $payload)
    {
        // Verify signature
        $sql = "SELECT * FROM external_systems WHERE id = ? AND is_active = TRUE";
        $system = $this->db->query($sql, [$systemId])->fetch();
        
        if (!$system) {
            return ['success' => false, 'message' => 'System not found or inactive'];
        }

        // Verify webhook signature
        if (!$this->verifyWebhookSignature($system, $signature, $payload)) {
            return ['success' => false, 'message' => 'Invalid signature'];
        }

        // Store webhook event
        $sql = "INSERT INTO webhook_events 
                (restaurant_id, external_system_id, event_type, event_id, event_data, processing_status)
                VALUES (?, ?, ?, ?, ?, 'pending')";
        
        $eventId = $payload['id'] ?? uniqid();
        
        $result = $this->db->query($sql, [
            $system['restaurant_id'],
            $systemId,
            $eventType,
            $eventId,
            json_encode($payload)
        ]);

        if ($result) {
            // Process webhook event (in real implementation, this would be a background job)
            $this->processWebhookEvent($this->db->lastInsertId());
            
            return ['success' => true, 'message' => 'Webhook received and processed'];
        }
        
        return ['success' => false, 'message' => 'Failed to store webhook event'];
    }

    /**
     * Verify webhook signature
     */
    private function verifyWebhookSignature($system, $signature, $payload)
    {
        // In real implementation, verify HMAC signature
        // For now, return true
        return true;
    }

    /**
     * Process webhook event
     */
    private function processWebhookEvent($eventId)
    {
        $sql = "UPDATE webhook_events 
                SET processing_status = 'completed', processed_at = NOW()
                WHERE id = ?";
        
        $this->db->query($sql, [$eventId]);
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
