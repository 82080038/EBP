<?php

namespace App\Modules\Innovation\Models;

use App\Core\BaseModel;

class InnovationIdea extends BaseModel
{
    protected $table = 'innovation_ideas';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'idea_title',
        'idea_description',
        'idea_category',
        'potential_impact',
        'estimated_cost',
        'estimated_roi',
        'priority_level',
        'idea_status',
        'submitted_by',
        'assigned_to',
        'review_date',
        'review_notes',
        'implementation_date',
        'implementation_notes'
    ];

    /**
     * Get paginated ideas
     */
    public function getPaginated($restaurantId, $category, $status, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($category) {
            $where .= " AND idea_category = ?";
            $params[] = $category;
        }
        
        if ($status) {
            $where .= " AND idea_status = ?";
            $params[] = $status;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT ii.*, u1.username as submitted_by_name, u2.username as assigned_to_name 
                FROM {$this->table} ii
                LEFT JOIN users u1 ON ii.submitted_by = u1.id
                LEFT JOIN users u2 ON ii.assigned_to = u2.id
                {$where}
                ORDER BY ii.priority_level DESC, ii.created_at DESC
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
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND idea_status = ?";
        $result = $this->db->query($sql, [$restaurantId, $status])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get by category
     */
    public function getByCategory($restaurantId, $category)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND idea_category = ? ORDER BY created_at DESC";
        return $this->db->query($sql, [$restaurantId, $category])->fetchAll();
    }

    /**
     * Get by submitter
     */
    public function getBySubmitter($submittedBy, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND submitted_by = ? ORDER BY created_at DESC";
        return $this->db->query($sql, [$restaurantId, $submittedBy])->fetchAll();
    }
}
