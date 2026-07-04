<?php

namespace App\Modules\Feedback\Models;

use App\Core\BaseModel;

class Feedback extends BaseModel
{
    protected $table = 'feedback';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'customer_id',
        'feedback_type',
        'subject',
        'message',
        'contact_email',
        'contact_phone',
        'feedback_source',
        'feedback_status',
        'priority',
        'assigned_to',
        'resolved_at'
    ];

    /**
     * Get paginated feedback
     */
    public function getPaginated($restaurantId, $type, $status, $priority, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($type) {
            $where .= " AND feedback_type = ?";
            $params[] = $type;
        }
        
        if ($status) {
            $where .= " AND feedback_status = ?";
            $params[] = $status;
        }
        
        if ($priority) {
            $where .= " AND priority = ?";
            $params[] = $priority;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT f.*, c.first_name, c.last_name, u.username as assigned_to_name 
                FROM {$this->table} f
                LEFT JOIN customers c ON f.customer_id = c.id
                LEFT JOIN users u ON f.assigned_to = u.id
                {$where}
                ORDER BY 
                    CASE f.priority
                        WHEN 'urgent' THEN 1
                        WHEN 'high' THEN 2
                        WHEN 'medium' THEN 3
                        WHEN 'low' THEN 4
                    END,
                    f.created_at DESC
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
        $sql = "SELECT f.*, c.first_name, c.last_name, u.username as assigned_to_name 
                FROM {$this->table} f
                LEFT JOIN customers c ON f.customer_id = c.id
                LEFT JOIN users u ON f.assigned_to = u.id
                WHERE f.id = ? AND f.restaurant_id = ?";
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
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND feedback_status = ?";
        $result = $this->db->query($sql, [$restaurantId, $status])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Count by priority
     */
    public function countByPriority($restaurantId, $priority)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND priority = ? AND feedback_status != 'resolved'";
        $result = $this->db->query($sql, [$restaurantId, $priority])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get by type
     */
    public function getByType($restaurantId, $type)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND feedback_type = ? ORDER BY created_at DESC";
        return $this->db->query($sql, [$restaurantId, $type])->fetchAll();
    }
}
