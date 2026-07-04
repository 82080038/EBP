<?php

namespace App\Modules\Sustainability\Models;

use App\Core\BaseModel;

class SustainabilityGoal extends BaseModel
{
    protected $table = 'sustainability_goals';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'goal_name',
        'goal_description',
        'goal_category',
        'goal_type',
        'goal_period_start',
        'goal_period_end',
        'target_value',
        'target_unit',
        'target_comparison',
        'actual_value',
        'achievement_percentage',
        'goal_status',
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
            $where .= " AND goal_category = ?";
            $params[] = $category;
        }
        
        if ($status) {
            $where .= " AND goal_status = ?";
            $params[] = $status;
        }
        
        $sql = "SELECT sg.*, u.username as created_by_name 
                FROM {$this->table} sg
                LEFT JOIN users u ON sg.created_by = u.id
                {$where}
                ORDER BY sg.goal_period_start DESC";
        
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
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND goal_status = 'active' ORDER BY goal_period_start ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Update actuals
     */
    public function updateActuals($id, $actualValue)
    {
        $goal = $this->findById($id, 0);
        
        if (!$goal) {
            return false;
        }
        
        $achievementPercentage = $goal['target_value'] > 0 ? ($actualValue / $goal['target_value']) * 100 : 0;
        
        // Determine status
        $status = 'active';
        if ($goal['target_comparison'] === 'less_than') {
            if ($achievementPercentage <= 100) {
                $status = 'achieved';
            } elseif ($achievementPercentage < 95) {
                $status = 'exceeded';
            }
        } else {
            if ($achievementPercentage >= 100) {
                $status = 'exceeded';
            } elseif ($achievementPercentage >= 95) {
                $status = 'achieved';
            }
        }
        
        if ($goal['goal_period_end'] < date('Y-m-d') && $status === 'active') {
            $status = 'missed';
        }
        
        return $this->update($id, [
            'actual_value' => $actualValue,
            'achievement_percentage' => $achievementPercentage,
            'goal_status' => $status
        ]);
    }

    /**
     * Get by category
     */
    public function getByCategory($restaurantId, $category)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND goal_category = ? ORDER BY goal_period_start DESC";
        return $this->db->query($sql, [$restaurantId, $category])->fetchAll();
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
