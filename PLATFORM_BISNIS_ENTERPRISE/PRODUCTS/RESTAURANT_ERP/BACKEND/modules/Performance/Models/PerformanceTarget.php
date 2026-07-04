<?php

namespace App\Modules\Performance\Models;

use App\Core\BaseModel;

class PerformanceTarget extends BaseModel
{
    protected $table = 'performance_targets';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'target_name',
        'target_description',
        'target_category',
        'target_type',
        'target_period_start',
        'target_period_end',
        'target_value',
        'target_comparison',
        'actual_value',
        'achievement_percentage',
        'target_status',
        'assigned_to',
        'created_by'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $category, $status)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($category) {
            $where .= " AND target_category = ?";
            $params[] = $category;
        }
        
        if ($status) {
            $where .= " AND target_status = ?";
            $params[] = $status;
        }
        
        $sql = "SELECT pt.*, u1.username as assigned_to_name, u2.username as created_by_name 
                FROM {$this->table} pt
                LEFT JOIN users u1 ON pt.assigned_to = u1.id
                LEFT JOIN users u2 ON pt.created_by = u2.id
                {$where}
                ORDER BY pt.target_period_start DESC";
        
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
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND target_status = 'active' ORDER BY target_period_start ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Update actuals
     */
    public function updateActuals($id, $actualValue)
    {
        $target = $this->findById($id, 0);
        
        if (!$target) {
            return false;
        }
        
        $achievementPercentage = $target['target_value'] > 0 ? ($actualValue / $target['target_value']) * 100 : 0;
        
        // Determine status
        $status = 'active';
        if ($achievementPercentage >= 100) {
            $status = 'exceeded';
        } elseif ($achievementPercentage >= 95) {
            $status = 'achieved';
        } elseif ($target['target_period_end'] < date('Y-m-d')) {
            $status = 'missed';
        }
        
        return $this->update($id, [
            'actual_value' => $actualValue,
            'achievement_percentage' => $achievementPercentage,
            'target_status' => $status
        ]);
    }

    /**
     * Get by category
     */
    public function getByCategory($restaurantId, $category)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND target_category = ? ORDER BY target_period_start DESC";
        return $this->db->query($sql, [$restaurantId, $category])->fetchAll();
    }
}
