<?php

namespace App\Modules\Procurement\Models;

use App\Core\BaseModel;

class ProcurementSpend extends BaseModel
{
    protected $table = 'procurement_spend';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'period_type',
        'period_start',
        'period_end',
        'total_orders',
        'total_spend',
        'average_order_value',
        'unique_suppliers',
        'top_supplier_id',
        'top_supplier_spend',
        'food_spend',
        'beverage_spend',
        'equipment_spend',
        'other_spend',
        'discount_savings',
        'negotiated_savings'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $periodType, $dateFrom, $dateTo, $limit)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
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
    public function getLatest($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? ORDER BY period_start DESC LIMIT 1";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
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
                    SUM(total_spend) as total_spend,
                    SUM(discount_savings) as total_savings,
                    SUM(negotiated_savings) as negotiated_savings
                FROM {$this->table} {$where}";
        
        $result = $this->db->query($sql, $params)->fetch();
        return $result ?: ['total_orders' => 0, 'total_spend' => 0, 'total_savings' => 0, 'negotiated_savings' => 0];
    }
}
