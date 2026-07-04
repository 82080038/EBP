<?php

namespace App\Modules\Sales\Models;

use App\Core\BaseModel;

class SalesAggregate extends BaseModel
{
    protected $table = 'sales_aggregates';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'aggregate_type',
        'period_start',
        'period_end',
        'total_orders',
        'total_revenue',
        'total_cost',
        'gross_profit',
        'net_profit',
        'dine_in_orders',
        'dine_in_revenue',
        'takeaway_orders',
        'takeaway_revenue',
        'delivery_orders',
        'delivery_revenue',
        'cash_payments',
        'cash_amount',
        'card_payments',
        'card_amount',
        'digital_payments',
        'digital_amount',
        'average_order_value',
        'average_items_per_order',
        'unique_customers',
        'returning_customers'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $aggregateType, $dateFrom, $dateTo, $limit)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($aggregateType) {
            $where .= " AND aggregate_type = ?";
            $params[] = $aggregateType;
        }
        
        if ($dateFrom) {
            $where .= " AND period_start >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND period_end <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY period_start DESC LIMIT ?";
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
    public function getLatest($restaurantId, $aggregateType)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND aggregate_type = ? ORDER BY period_start DESC LIMIT 1";
        $result = $this->db->query($sql, [$restaurantId, $aggregateType])->fetch();
        return $result ?: null;
    }

    /**
     * Get total for period
     */
    public function getTotalForPeriod($restaurantId, $dateFrom, $dateTo)
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
        
        $sql = "SELECT 
                    SUM(total_orders) as total_orders,
                    SUM(total_revenue) as total_revenue,
                    SUM(gross_profit) as gross_profit,
                    SUM(unique_customers) as unique_customers
                FROM {$this->table} {$where}";
        
        $result = $this->db->query($sql, $params)->fetch();
        return $result ?: ['total_orders' => 0, 'total_revenue' => 0, 'gross_profit' => 0, 'unique_customers' => 0];
    }
}
