<?php

namespace App\Modules\Segment\Models;

use App\Core\BaseModel;

class SegmentWorkflow extends BaseModel
{
    protected $table = 'segment_workflows';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'segment_configuration_id',
        'workflow_name',
        'workflow_type',
        'workflow_steps',
        'conditions',
        'is_active',
        'created_by'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $type)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($type) {
            $where .= " AND workflow_type = ?";
            $params[] = $type;
        }
        
        $sql = "SELECT sw.*, sc.segment_name, u.username as created_by_name 
                FROM {$this->table} sw
                LEFT JOIN segment_configurations sc ON sw.segment_configuration_id = sc.id
                LEFT JOIN users u ON sw.created_by = u.id
                {$where}
                ORDER BY sw.workflow_name ASC";
        
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
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE ORDER BY workflow_name ASC";
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
     * Get by type
     */
    public function getByType($restaurantId, $workflowType)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND workflow_type = ? AND is_active = TRUE ORDER BY workflow_name ASC";
        return $this->db->query($sql, [$restaurantId, $workflowType])->fetchAll();
    }

    /**
     * Get by segment configuration
     */
    public function getBySegmentConfiguration($segmentConfigurationId, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE segment_configuration_id = ? AND restaurant_id = ? ORDER BY workflow_name ASC";
        return $this->db->query($sql, [$segmentConfigurationId, $restaurantId])->fetchAll();
    }
}
