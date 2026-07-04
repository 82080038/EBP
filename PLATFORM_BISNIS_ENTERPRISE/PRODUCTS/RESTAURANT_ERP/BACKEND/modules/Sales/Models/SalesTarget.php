<?php

namespace App\Modules\Sales\Models;

use App\Core\BaseModel;

class SalesTarget extends BaseModel
{
    protected $table = 'sales_targets';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'target_name',
        'target_description',
        'target_type',
        'target_period_start',
        'target_period_end',
        'revenue_target',
        'order_target',
        'profit_target',
        'actual_revenue',
        'actual_orders',
        'actual_profit',
        'revenue_achievement',
        'order_achievement',
        'profit_achievement',
        'target_status',
        'created_by'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $status)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($status) {
            $where .= " AND target_status = ?";
            $params[] = $status;
        }
        
        $sql = "SELECT st.*, u.username as created_by_name 
                FROM {$this->table} st
                LEFT JOIN users u ON st.created_by = u.id
                {$where}
                ORDER BY st.target_period_start DESC";
        
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
     * Get active targets
     */
    public function getActive($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND target_status = 'active' ORDER BY target_period_start ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Update actuals
     */
    public function updateActuals($id, $actualRevenue, $actualOrders, $actualProfit)
    {
        $target = $this->findById($id, 0);
        
        if (!$target) {
            return false;
        }
        
        $revenueAchievement = $target['revenue_target'] > 0 ? ($actualRevenue / $target['revenue_target']) * 100 : 0;
        $orderAchievement = $target['order_target'] > 0 ? ($actualOrders / $target['order_target']) * 100 : 0;
        $profitAchievement = $target['profit_target'] > 0 ? ($actualProfit / $target['profit_target']) * 100 : 0;
        
        // Determine status
        $status = 'active';
        if ($revenueAchievement >= 100) {
            $status = 'exceeded';
        } elseif ($revenueAchievement >= 95) {
            $status = 'completed';
        } elseif ($target['target_period_end'] < date('Y-m-d')) {
            $status = 'missed';
        }
        
        return $this->update($id, [
            'actual_revenue' => $actualRevenue,
            'actual_orders' => $actualOrders,
            'actual_profit' => $actualProfit,
            'revenue_achievement' => $revenueAchievement,
            'order_achievement' => $orderAchievement,
            'profit_achievement' => $profitAchievement,
            'target_status' => $status
        ]);
    }
}
