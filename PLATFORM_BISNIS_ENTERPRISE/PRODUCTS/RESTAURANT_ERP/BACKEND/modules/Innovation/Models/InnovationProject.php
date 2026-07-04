<?php

namespace App\Modules\Innovation\Models;

use App\Core\BaseModel;

class InnovationProject extends BaseModel
{
    protected $table = 'innovation_projects';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'project_name',
        'project_description',
        'project_type',
        'start_date',
        'target_end_date',
        'actual_end_date',
        'budget_amount',
        'actual_spent',
        'project_lead',
        'team_members',
        'project_status',
        'completion_percentage',
        'project_outcome',
        'lessons_learned',
        'created_by'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $status, $type)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($status) {
            $where .= " AND project_status = ?";
            $params[] = $status;
        }
        
        if ($type) {
            $where .= " AND project_type = ?";
            $params[] = $type;
        }
        
        $sql = "SELECT ip.*, u1.username as project_lead_name, u2.username as created_by_name 
                FROM {$this->table} ip
                LEFT JOIN users u1 ON ip.project_lead = u1.id
                LEFT JOIN users u2 ON ip.created_by = u2.id
                {$where}
                ORDER BY ip.start_date DESC";
        
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
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND project_status = ?";
        $result = $this->db->query($sql, [$restaurantId, $status])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get active projects
     */
    public function getActive($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND project_status = 'in_progress' ORDER BY start_date ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Update progress
     */
    public function updateProgress($id, $completionPercentage)
    {
        $status = 'in_progress';
        if ($completionPercentage >= 100) {
            $status = 'completed';
        }
        
        return $this->update($id, [
            'completion_percentage' => $completionPercentage,
            'project_status' => $status
        ]);
    }

    /**
     * Get by project lead
     */
    public function getByProjectLead($projectLead, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND project_lead = ? ORDER BY start_date DESC";
        return $this->db->query($sql, [$restaurantId, $projectLead])->fetchAll();
    }
}
