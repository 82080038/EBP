<?php

namespace App\Modules\Procurement\Models;

use App\Core\BaseModel;

class CategorySpend extends BaseModel
{
    protected $table = 'category_spend';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'supplier_type',
        'period_type',
        'period_start',
        'period_end',
        'total_spend',
        'order_count',
        'previous_spend',
        'percentage_change'
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
     * Get by supplier type
     */
    public function getBySupplierType($restaurantId, $supplierType, $limit = 12)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND supplier_type = ? ORDER BY period_start DESC LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $supplierType, $limit])->fetchAll();
    }

    /**
     * Get latest by type
     */
    public function getLatestByType($restaurantId, $supplierType)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND supplier_type = ? ORDER BY period_start DESC LIMIT 1";
        $result = $this->db->query($sql, [$restaurantId, $supplierType])->fetch();
        return $result ?: null;
    }
}
