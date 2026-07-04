<?php

namespace App\Modules\Offline\Models;

use App\Core\BaseModel;

class OfflineConflict extends BaseModel
{
    protected $table = 'offline_conflicts';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'offline_transaction_id',
        'conflict_type',
        'conflict_description',
        'local_data',
        'remote_data',
        'resolution_action',
        'resolved_data',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
        'is_resolved'
    ];

    /**
     * Get paginated conflicts
     */
    public function getPaginated($restaurantId, $isResolved, $page, $limit)
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
    public function findById($id, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$id, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Count unresolved
     */
    public function countUnresolved($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND is_resolved = FALSE";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }
}
