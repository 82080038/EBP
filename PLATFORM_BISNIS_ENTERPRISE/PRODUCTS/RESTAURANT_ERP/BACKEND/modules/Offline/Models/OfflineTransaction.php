<?php

namespace App\Modules\Offline\Models;

use App\Core\BaseModel;

class OfflineTransaction extends BaseModel
{
    protected $table = 'offline_transactions';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'user_id',
        'device_id',
        'transaction_type',
        'transaction_data',
        'sync_status',
        'sync_attempts',
        'last_sync_attempt_at',
        'conflict_data',
        'conflict_resolved',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
        'synced_at'
    ];

    /**
     * Get pending transactions
     */
    public function getPending($restaurantId, $deviceId = null)
    {
        $params = [$restaurantId, 'pending'];
        $where = "WHERE restaurant_id = ? AND sync_status = ?";
        
        if ($deviceId) {
            $where .= " AND device_id = ?";
            $params[] = $deviceId;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY created_at ASC LIMIT 100";
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Count by status
     */
    public function countByStatus($restaurantId, $deviceId = null, $status)
    {
        $params = [$restaurantId, $status];
        $where = "WHERE restaurant_id = ? AND sync_status = ?";
        
        if ($deviceId) {
            $where .= " AND device_id = ?";
            $params[] = $deviceId;
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table} {$where}";
        $result = $this->db->query($sql, $params)->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get paginated transactions
     */
    public function getPaginated($restaurantId, $deviceId, $syncStatus, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($deviceId) {
            $where .= " AND device_id = ?";
            $params[] = $deviceId;
        }
        
        if ($syncStatus) {
            $where .= " AND sync_status = ?";
            $params[] = $syncStatus;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY created_at DESC LIMIT ? OFFSET ?";
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
     * Find by ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $result = $this->db->query($sql, [$id])->fetch();
        return $result ?: null;
    }
}
