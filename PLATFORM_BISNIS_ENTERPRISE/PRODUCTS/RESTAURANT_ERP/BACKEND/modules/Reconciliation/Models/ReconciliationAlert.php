<?php

namespace App\Modules\Reconciliation\Models;

use App\Core\BaseModel;

class ReconciliationAlert extends BaseModel
{
    protected $table = 'reconciliation_alerts';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'alert_type',
        'alert_severity',
        'alert_title',
        'alert_message',
        'alert_data',
        'reconciliation_transaction_id',
        'is_resolved',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
        'notification_sent',
        'notification_sent_at'
    ];

    /**
     * Count unresolved
     */
    public function countUnresolved($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE restaurant_id = ? AND is_resolved = FALSE";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get recent alerts
     */
    public function getRecent($restaurantId, $limit = 5)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $limit])->fetchAll();
    }

    /**
     * Get paginated alerts
     */
    public function getPaginated($restaurantId, $page, $limit, $isResolved = null)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($isResolved !== null) {
            $where .= " AND is_resolved = ?";
            $params[] = $isResolved;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT * FROM {$this->table} 
                {$where} 
                ORDER BY created_at DESC 
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
     * Find by ID
     */
    public function findById($id, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE id = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$id, $restaurantId])->fetch();
        return $result ?: null;
    }
}
