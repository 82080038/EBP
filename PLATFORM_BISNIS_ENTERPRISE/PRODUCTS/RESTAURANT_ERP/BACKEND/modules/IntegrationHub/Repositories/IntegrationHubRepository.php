<?php

namespace Modules\IntegrationHub\Repositories;

use Core\Database;

class IntegrationHubRepository
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->connect();
    }
    
    /**
     * Find all external integrations for tenant
     */
    public function findAll($tenantId, $limit = 100, $offset = 0)
    {
        $sql = "SELECT ei.*, u.username as created_by_name
                FROM external_integrations ei
                LEFT JOIN users u ON ei.created_by = u.user_id
                WHERE ei.tenant_id = :tenant_id 
                AND ei.deleted_at IS NULL
                ORDER BY ei.integration_name ASC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'limit' => $limit, 'offset' => $offset]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Find external integration by ID
     */
    public function findById($integrationId, $tenantId)
    {
        $sql = "SELECT ei.*, u.username as created_by_name
                FROM external_integrations ei
                LEFT JOIN users u ON ei.created_by = u.user_id
                WHERE ei.id = :integration_id 
                AND ei.tenant_id = :tenant_id 
                AND ei.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['integration_id' => $integrationId, 'tenant_id' => $tenantId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Create external integration
     */
    public function create($data)
    {
        $sql = "INSERT INTO external_integrations (tenant_id, integration_name, integration_type, provider_name,
                                                  api_endpoint, api_key, api_secret, webhook_url,
                                                  integration_config, sync_frequency, integration_status,
                                                  created_by, created_at)
                VALUES (:tenant_id, :integration_name, :integration_type, :provider_name,
                        :api_endpoint, :api_key, :api_secret, :webhook_url,
                        :integration_config, :sync_frequency, :integration_status,
                        :created_by, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update external integration
     */
    public function update($integrationId, $data, $tenantId)
    {
        $setClause = [];
        foreach (array_keys($data) as $key) {
            if ($key !== 'id') {
                $setClause[] = "$key = :$key";
            }
        }
        $setClause[] = "updated_at = NOW()";
        
        $sql = "UPDATE external_integrations SET " . implode(', ', $setClause) . " 
                WHERE id = :integration_id AND tenant_id = :tenant_id";
        
        $params = array_merge($data, ['integration_id' => $integrationId, 'tenant_id' => $tenantId]);
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Update sync status
     */
    public function updateSyncStatus($integrationId, $lastSyncAt, $nextSyncAt, $tenantId)
    {
        $sql = "UPDATE external_integrations 
                SET last_sync_at = :last_sync_at, 
                    next_sync_at = :next_sync_at, 
                    updated_at = NOW() 
                WHERE id = :integration_id AND tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'integration_id' => $integrationId,
            'last_sync_at' => $lastSyncAt,
            'next_sync_at' => $nextSyncAt,
            'tenant_id' => $tenantId
        ]);
    }
    
    /**
     * Update health status
     */
    public function updateHealthStatus($integrationId, $healthStatus, $tenantId)
    {
        $sql = "UPDATE external_integrations 
                SET health_status = :health_status, 
                    last_health_check = NOW(), 
                    updated_at = NOW() 
                WHERE id = :integration_id AND tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'integration_id' => $integrationId,
            'health_status' => $healthStatus,
            'tenant_id' => $tenantId
        ]);
    }
    
    /**
     * Soft delete external integration
     */
    public function delete($integrationId, $tenantId)
    {
        $sql = "UPDATE external_integrations 
                SET deleted_at = NOW() 
                WHERE id = :integration_id AND tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['integration_id' => $integrationId, 'tenant_id' => $tenantId]);
    }
    
    /**
     * Get integrations by type
     */
    public function getByType($tenantId, $type, $limit = 100)
    {
        $sql = "SELECT ei.*, u.username as created_by_name
                FROM external_integrations ei
                LEFT JOIN users u ON ei.created_by = u.user_id
                WHERE ei.tenant_id = :tenant_id 
                AND ei.integration_type = :type
                AND ei.deleted_at IS NULL
                ORDER BY ei.integration_name ASC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'type' => $type, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get integrations by status
     */
    public function getByStatus($tenantId, $status, $limit = 100)
    {
        $sql = "SELECT ei.*, u.username as created_by_name
                FROM external_integrations ei
                LEFT JOIN users u ON ei.created_by = u.user_id
                WHERE ei.tenant_id = :tenant_id 
                AND ei.integration_status = :status
                AND ei.deleted_at IS NULL
                ORDER BY ei.integration_name ASC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'status' => $status, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get active integrations
     */
    public function getActive($tenantId)
    {
        $sql = "SELECT ei.*, u.username as created_by_name
                FROM external_integrations ei
                LEFT JOIN users u ON ei.created_by = u.user_id
                WHERE ei.tenant_id = :tenant_id 
                AND ei.integration_status = 'active'
                AND ei.deleted_at IS NULL
                ORDER BY ei.integration_name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Count integrations by status
     */
    public function countByStatus($tenantId, $status = null)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM external_integrations 
                WHERE tenant_id = :tenant_id 
                AND deleted_at IS NULL";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($status !== null) {
            $sql .= " AND integration_status = :status";
            $params['status'] = $status;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    /**
     * Get integration mappings
     */
    public function getMappings($integrationId, $tenantId)
    {
        $sql = "SELECT im.* 
                FROM integration_mappings im
                WHERE im.integration_id = :integration_id
                AND im.tenant_id = :tenantId
                AND im.deleted_at IS NULL
                ORDER BY im.mapping_name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['integration_id' => $integrationId, 'tenant_id' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get sync logs
     */
    public function getSyncLogs($integrationId, $tenantId, $limit = 50)
    {
        $sql = "SELECT isl.* 
                FROM integration_sync_logs isl
                WHERE isl.integration_id = :integration_id
                AND isl.tenant_id = :tenantId
                ORDER BY isl.sync_start DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['integration_id' => $integrationId, 'tenant_id' => $tenantId, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
