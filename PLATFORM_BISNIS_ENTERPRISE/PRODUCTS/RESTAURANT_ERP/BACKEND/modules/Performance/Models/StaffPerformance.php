<?php

namespace App\Modules\Performance\Models;

use App\Core\BaseModel;

class StaffPerformance extends BaseModel
{
    protected $table = 'staff_performance';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'staff_id',
        'period_type',
        'period_start',
        'period_end',
        'orders_processed',
        'orders_per_hour',
        'revenue_generated',
        'average_order_value',
        'average_order_time',
        'average_service_time',
        'order_accuracy_rate',
        'customer_rating',
        'total_tips',
        'tip_percentage'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $staffId, $periodType, $dateFrom, $dateTo, $limit)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($staffId) {
            $where .= " AND staff_id = ?";
            $params[] = $staffId;
        }
        
        if ($periodType) {
            $where .= " AND period_type = ?";
            $params[] = $periodType;
        }
        
        if ($dateFrom) {
            $where .= " AND period_start >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND period_end <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT sp.*, u.username as staff_name, u.first_name, u.last_name 
                FROM {$this->table} sp
                LEFT JOIN users u ON sp.staff_id = u.id
                {$where}
                ORDER BY sp.period_start DESC
                LIMIT ?";
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
     * Get top performers
     */
    public function getTopPerformers($restaurantId, $dateFrom, $dateTo, $limit)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($dateFrom) {
            $where .= " AND period_start >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND period_end <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT sp.staff_id, u.username, u.first_name, u.last_name,
                    SUM(sp.orders_processed) as total_orders,
                    SUM(sp.revenue_generated) as total_revenue,
                    AVG(sp.customer_rating) as avg_rating,
                    AVG(sp.orders_per_hour) as avg_orders_per_hour
                FROM {$this->table} sp
                LEFT JOIN users u ON sp.staff_id = u.id
                {$where}
                GROUP BY sp.staff_id, u.username, u.first_name, u.last_name
                ORDER BY total_revenue DESC
                LIMIT ?";
        $params[] = $limit;
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get by staff
     */
    public function getByStaff($staffId, $restaurantId, $limit = 30)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND staff_id = ? ORDER BY period_start DESC LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $staffId, $limit])->fetchAll();
    }
}
