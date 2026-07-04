<?php

namespace App\Modules\IoT\Models;

use App\Core\BaseModel;

class SmartAutomation extends BaseModel
{
    protected $table = 'smart_automations';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'automation_name',
        'automation_description',
        'trigger_type',
        'trigger_config',
        'action_type',
        'action_config',
        'is_active',
        'last_executed_at',
        'execution_count',
        'success_count',
        'failure_count',
        'created_by'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $triggerType, $isActive)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($triggerType) {
            $where .= " AND trigger_type = ?";
            $params[] = $triggerType;
        }
        
        if ($isActive !== null) {
            $where .= " AND is_active = ?";
            $params[] = $isActive;
        }
        
        $sql = "SELECT sa.*, u.username as created_by_name 
                FROM {$this->table} sa
                LEFT JOIN users u ON sa.created_by = u.id
                {$where}
                ORDER BY sa.created_at DESC";
        
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
     * Get active
     */
    public function getActive($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE ORDER BY created_at DESC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Count active
     */
    public function countActive($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Update execution stats
     */
    public function updateExecutionStats($id, $status)
    {
        $automation = $this->findById($id, 0);
        
        if (!$automation) {
            return false;
        }
        
        $updateData = [
            'last_executed_at' => date('Y-m-d H:i:s'),
            'execution_count' => $automation['execution_count'] + 1
        ];
        
        if ($status === 'success') {
            $updateData['success_count'] = $automation['success_count'] + 1;
        } else {
            $updateData['failure_count'] = $automation['failure_count'] + 1;
        }
        
        return $this->update($id, $updateData);
    }

    /**
     * Get execution history
     */
    public function getExecutionHistory($automationId, $restaurantId, $limit = 20)
    {
        $sql = "SELECT * FROM automation_execution_history WHERE automation_id = ? AND restaurant_id = ? ORDER BY executed_at DESC LIMIT ?";
        return $this->db->query($sql, [$automationId, $restaurantId, $limit])->fetchAll();
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
}
