<?php

namespace App\Modules\Security\Models;

use App\Core\BaseModel;

class SecurityAuditLog extends BaseModel
{
    protected $table = 'security_audit_logs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'user_id',
        'action_type',
        'action_category',
        'action_description',
        'ip_address',
        'user_agent',
        'request_method',
        'request_url',
        'resource_type',
        'resource_id',
        'action_status',
        'failure_reason',
        'action_data'
    ];

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
     * Get recent logs
     */
    public function getRecent($restaurantId, $limit = 10)
    {
        $sql = "SELECT sal.*, u.username, u.full_name 
                FROM {$this->table} sal
                LEFT JOIN users u ON sal.user_id = u.id
                WHERE sal.restaurant_id = ? 
                ORDER BY sal.created_at DESC 
                LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $limit])->fetchAll();
    }

    /**
     * Get paginated logs
     */
    public function getPaginated($restaurantId, $actionType, $actionCategory, $actionStatus, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($actionType) {
            $where .= " AND action_type = ?";
            $params[] = $actionType;
        }
        
        if ($actionCategory) {
            $where .= " AND action_category = ?";
            $params[] = $actionCategory;
        }
        
        if ($actionStatus) {
            $where .= " AND action_status = ?";
            $params[] = $actionStatus;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT sal.*, u.username, u.full_name 
                FROM {$this->table} sal
                LEFT JOIN users u ON sal.user_id = u.id
                {$where}
                ORDER BY sal.created_at DESC
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
}
