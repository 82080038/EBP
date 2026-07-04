<?php

namespace App\Modules\IntegrationHub\Models;

use App\Core\BaseModel;

class ExternalIntegration extends BaseModel
{
    protected $table = 'external_integrations';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'integration_name',
        'integration_type',
        'provider_name',
        'api_endpoint',
        'api_key',
        'api_secret',
        'webhook_url',
        'integration_config',
        'sync_frequency',
        'last_sync_at',
        'next_sync_at',
        'integration_status',
        'last_health_check',
        'health_status',
        'created_by'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $type, $status)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($type) {
            $where .= " AND integration_type = ?";
            $params[] = $type;
        }
        
        if ($status) {
            $where .= " AND integration_status = ?";
            $params[] = $status;
        }
        
        $sql = "SELECT ei.*, u.username as created_by_name 
                FROM {$this->table} ei
                LEFT JOIN users u ON ei.created_by = u.id
                {$where}
                ORDER BY ei.integration_name ASC";
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Find by ID
     */
    public function findById($id, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$id, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Count by restaurant
     */
    public function countByRestaurant($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ?";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Count by status
     */
    public function countByStatus($restaurantId, $status)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND integration_status = ?";
        $result = $this->db->query($sql, [$restaurantId, $status])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get active
     */
    public function getActive($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND integration_status = 'active' ORDER BY integration_name ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Update sync status
     */
    public function updateSyncStatus($id, $lastSyncAt, $nextSyncAt)
    {
        return $this->update($id, [
            'last_sync_at' => $lastSyncAt,
            'next_sync_at' => $nextSyncAt
        ]);
    }

    /**
     * Update health status
     */
    public function updateHealthStatus($id, $healthStatus)
    {
        return $this->update($id, [
            'health_status' => $healthStatus,
            'last_health_check' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get by type
     */
    public function getByType($restaurantId, $integrationType)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND integration_type = ? ORDER BY integration_name ASC";
        return $this->db->query($sql, [$restaurantId, $integrationType])->fetchAll();
    }
}
