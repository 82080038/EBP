<?php

namespace App\Modules\Performance\Models;

use App\Core\BaseModel;

class EfficiencyMetric extends BaseModel
{
    protected $table = 'efficiency_metrics';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'metric_date',
        'food_cost_percentage',
        'labor_cost_percentage',
        'overhead_cost_percentage',
        'total_cost_percentage',
        'revenue_per_seat',
        'revenue_per_hour',
        'revenue_per_staff',
        'table_turnover_rate',
        'staff_productivity',
        'inventory_turnover'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $dateFrom, $dateTo, $limit)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($dateFrom) {
            $where .= " AND metric_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND metric_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY metric_date DESC LIMIT ?";
        $params[] = $limit;
        
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
     * Get latest
     */
    public function getLatest($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? ORDER BY metric_date DESC LIMIT 1";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Get by date
     */
    public function getByDate($restaurantId, $date)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND metric_date = ?";
        $result = $this->db->query($sql, [$restaurantId, $date])->fetch();
        return $result ?: null;
    }

    /**
     * Get average for period
     */
    public function getAverageForPeriod($restaurantId, $dateFrom, $dateTo)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($dateFrom) {
            $where .= " AND metric_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND metric_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT 
                    AVG(food_cost_percentage) as avg_food_cost,
                    AVG(labor_cost_percentage) as avg_labor_cost,
                    AVG(overhead_cost_percentage) as avg_overhead_cost,
                    AVG(total_cost_percentage) as avg_total_cost,
                    AVG(revenue_per_seat) as avg_revenue_per_seat,
                    AVG(revenue_per_hour) as avg_revenue_per_hour,
                    AVG(table_turnover_rate) as avg_turnover_rate
                FROM {$this->table} {$where}";
        
        $result = $this->db->query($sql, $params)->fetch();
        return $result ?: [
            'avg_food_cost' => 0,
            'avg_labor_cost' => 0,
            'avg_overhead_cost' => 0,
            'avg_total_cost' => 0,
            'avg_revenue_per_seat' => 0,
            'avg_revenue_per_hour' => 0,
            'avg_turnover_rate' => 0
        ];
    }
}
