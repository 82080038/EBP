<?php

namespace App\Modules\Technology\Models;

use App\Core\BaseModel;

class AutomationWorkflow extends BaseModel
{
    protected $table = 'automation_workflows';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'workflow_name',
        'workflow_description',
        'workflow_type',
        'trigger_type',
        'trigger_config',
        'actions',
        'conditions',
        'workflow_status',
        'last_executed_at',
        'execution_count',
        'success_count',
        'failure_count',
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
            $where .= " AND workflow_type = ?";
            $params[] = $type;
        }
        
        if ($status) {
            $where .= " AND workflow_status = ?";
            $params[] = $status;
        }
        
        $sql = "SELECT aw.*, u.username as created_by_name 
                FROM {$this->table} aw
                LEFT JOIN users u ON aw.created_by = u.id
                {$where}
                ORDER BY aw.workflow_name ASC";
        
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
     * Count by status
     */
    public function countByStatus($restaurantId, $status)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND workflow_status = ?";
        $result = $this->db->query($sql, [$restaurantId, $status])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get active
     */
    public function getActive($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND workflow_status = 'active' ORDER BY workflow_name ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Update execution stats
     */
    public function updateExecutionStats($id, $success)
    {
        $workflow = $this->findById($id, 0);
        
        if (!$workflow) {
            return false;
        }
        
        $updateData = [
            'last_executed_at' => date('Y-m-d H:i:s'),
            'execution_count' => $workflow['execution_count'] + 1
        ];
        
        if ($success) {
            $updateData['success_count'] = $workflow['success_count'] + 1;
        } else {
            $updateData['failure_count'] = $workflow['failure_count'] + 1;
        }
        
        return $this->update($id, $updateData);
    }

    /**
     * Get by trigger type
     */
    public function getByTriggerType($restaurantId, $triggerType)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND trigger_type = ? AND workflow_status = 'active' ORDER BY workflow_name ASC";
        return $this->db->query($sql, [$restaurantId, $triggerType])->fetchAll();
    }
}
