<?php

namespace App\Modules\IntegrationHub\Models;

use App\Core\BaseModel;

class IntegrationSyncLog extends BaseModel
{
    protected $table = 'integration_sync_logs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'integration_id',
        'sync_type',
        'sync_direction',
        'records_processed',
        'records_success',
        'records_failed',
        'started_at',
        'completed_at',
        'duration_seconds',
        'sync_status',
        'error_message',
        'sync_details'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $integrationId, $status, $limit)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($integrationId) {
            $where .= " AND integration_id = ?";
            $params[] = $integrationId;
        }
        
        if ($status) {
            $where .= " AND sync_status = ?";
            $params[] = $status;
        }
        
        $sql = "SELECT isl.*, ei.integration_name 
                FROM {$this->table} isl
                LEFT JOIN external_integrations ei ON isl.integration_id = ei.id
                {$where}
                ORDER BY isl.started_at DESC LIMIT ?";
        $params[] = $limit;
        
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
     * Get recent
     */
    public function getRecent($restaurantId, $limit = 10)
    {
        $sql = "SELECT isl.*, ei.integration_name 
                FROM {$this->table} isl
                LEFT JOIN external_integrations ei ON isl.integration_id = ei.id
                WHERE isl.restaurant_id = ? 
                ORDER BY isl.started_at DESC LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $limit])->fetchAll();
    }

    /**
     * Get by integration
     */
    public function getByIntegration($integrationId, $restaurantId, $limit = 20)
    {
        $sql = "SELECT * FROM {$this->table} WHERE integration_id = ? AND restaurant_id = ? ORDER BY started_at DESC LIMIT ?";
        return $this->db->query($sql, [$integrationId, $restaurantId, $limit])->fetchAll();
    }

    /**
     * Get by status
     */
    public function getByStatus($restaurantId, $syncStatus, $limit = 20)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND sync_status = ? ORDER BY started_at DESC LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $syncStatus, $limit])->fetchAll();
    }

    /**
     * Update completion
     */
    public function updateCompletion($id, $recordsProcessed, $recordsSuccess, $recordsFailed, $errorMessage = null)
    {
        $log = $this->findById($id, 0);
        
        if (!$log) {
            return false;
        }
        
        $duration = time() - strtotime($log['started_at']);
        $syncStatus = $errorMessage ? 'failed' : 'completed';
        
        return $this->update($id, [
            'records_processed' => $recordsProcessed,
            'records_success' => $recordsSuccess,
            'records_failed' => $recordsFailed,
            'completed_at' => date('Y-m-d H:i:s'),
            'duration_seconds' => $duration,
            'sync_status' => $syncStatus,
            'error_message' => $errorMessage
        ]);
    }

    /**
     * Count by status
     */
    public function countByStatus($restaurantId, $syncStatus)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND sync_status = ?";
        $result = $this->db->query($sql, [$restaurantId, $syncStatus])->fetch();
        return $result['count'] ?? 0;
    }
}
