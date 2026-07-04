<?php

namespace App\Modules\Procurement\Models;

use App\Core\BaseModel;

class SupplierSpend extends BaseModel
{
    protected $table = 'supplier_spend';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'supplier_id',
        'period_type',
        'period_start',
        'period_end',
        'total_orders',
        'total_spend',
        'average_order_value',
        'on_time_delivery_rate',
        'quality_score',
        'spend_share',
        'spend_rank'
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
        
        $sql = "SELECT ss.*, s.supplier_name 
                FROM {$this->table} ss
                LEFT JOIN suppliers s ON ss.supplier_id = s.id
                {$where}
                ORDER BY ss.total_spend DESC
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
     * Get top spenders
     */
    public function getTopSpenders($restaurantId, $dateFrom, $dateTo, $limit)
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
        
        $sql = "SELECT ss.*, s.supplier_name 
                FROM {$this->table} ss
                LEFT JOIN suppliers s ON ss.supplier_id = s.id
                {$where}
                ORDER BY ss.total_spend DESC
                LIMIT ?";
        $params[] = $limit;
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get by supplier
     */
    public function getBySupplier($supplierId, $restaurantId, $limit = 12)
    {
        $sql = "SELECT * FROM {$this->table} WHERE supplier_id = ? AND restaurant_id = ? ORDER BY period_start DESC LIMIT ?";
        return $this->db->query($sql, [$supplierId, $restaurantId, $limit])->fetchAll();
    }
}
