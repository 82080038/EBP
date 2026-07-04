<?php

namespace App\Modules\Offline\Models;

use App\Core\BaseModel;

class SyncQueue extends BaseModel
{
    protected $table = 'sync_queue';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'device_id',
        'queue_type',
        'priority',
        'payload',
        'status',
        'processing_attempts',
        'max_attempts',
        'started_at',
        'completed_at',
        'error_message',
        'error_details',
        'depends_on_id'
    ];

    /**
     * Get paginated queue items
     */
    public function getPaginated($restaurantId, $deviceId, $status, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($deviceId) {
            $where .= " AND device_id = ?";
            $params[] = $deviceId;
        }
        
        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT * FROM {$this->table} {$where} 
                ORDER BY priority DESC, created_at ASC 
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
     * Get pending items
     */
    public function getPending($restaurantId, $deviceId = null)
    {
        $params = [$restaurantId, 'pending'];
        $where = "WHERE restaurant_id = ? AND status = ?";
        
        if ($deviceId) {
            $where .= " AND device_id = ?";
            $params[] = $deviceId;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} 
                ORDER BY priority DESC, created_at ASC 
                LIMIT 50";
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Find by ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $result = $this->db->query($sql, [$id])->fetch();
        return $result ?: null;
    }

    /**
     * Update status
     */
    public function updateStatus($id, $status, $error = null)
    {
        $params = [$status, $id];
        $sql = "UPDATE {$this->table} SET status = ?";
        
        if ($status === 'processing') {
            $sql .= ", started_at = NOW()";
        } elseif ($status === 'completed') {
            $sql .= ", completed_at = NOW()";
        } elseif ($status === 'failed') {
            $sql .= ", error_message = ?, processing_attempts = processing_attempts + 1";
            $params = [$status, $error, $id];
        }
        
        $sql .= " WHERE id = ?";
        
        return $this->db->query($sql, $params);
    }
}
