<?php

namespace App\Modules\Sales\Models;

use App\Core\BaseModel;

class HourlySales extends BaseModel
{
    protected $table = 'hourly_sales';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'sale_date',
        'sale_hour',
        'total_orders',
        'total_revenue',
        'average_order_value'
    ];

    /**
     * Get by date
     */
    public function getByDate($restaurantId, $date)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND sale_date = ? ORDER BY sale_hour ASC";
        return $this->db->query($sql, [$restaurantId, $date])->fetchAll();
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
     * Get by date range
     */
    public function getByDateRange($restaurantId, $dateFrom, $dateTo)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($dateFrom) {
            $where .= " AND sale_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND sale_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY sale_date ASC, sale_hour ASC";
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get peak hours
     */
    public function getPeakHours($restaurantId, $dateFrom, $dateTo, $limit = 5)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($dateFrom) {
            $where .= " AND sale_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND sale_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT sale_hour, SUM(total_orders) as total_orders, SUM(total_revenue) as total_revenue
                FROM {$this->table} {$where}
                GROUP BY sale_hour
                ORDER BY total_orders DESC
                LIMIT ?";
        $params[] = $limit;
        
        return $this->db->query($sql, $params)->fetchAll();
    }
}
