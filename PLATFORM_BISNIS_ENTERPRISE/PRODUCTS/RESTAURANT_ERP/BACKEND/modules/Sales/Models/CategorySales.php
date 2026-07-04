<?php

namespace App\Modules\Sales\Models;

use App\Core\BaseModel;

class CategorySales extends BaseModel
{
    protected $table = 'category_sales';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'category_id',
        'aggregate_type',
        'period_start',
        'period_end',
        'total_quantity',
        'total_revenue',
        'total_cost',
        'gross_profit',
        'order_count',
        'revenue_share'
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
        
        $sql = "SELECT cs.*, mc.category_name 
                FROM {$this->table} cs
                LEFT JOIN menu_categories mc ON cs.category_id = mc.id
                {$where}
                ORDER BY cs.total_revenue DESC
                LIMIT ?";
        $params[] = $limit;
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get top categories
     */
    public function getTopCategories($restaurantId, $dateFrom, $dateTo, $limit)
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
        
        $sql = "SELECT cs.*, mc.category_name 
                FROM {$this->table} cs
                LEFT JOIN menu_categories mc ON cs.category_id = mc.id
                {$where}
                ORDER BY cs.total_revenue DESC
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
     * Get by category
     */
    public function getByCategory($categoryId, $restaurantId, $limit = 30)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND category_id = ? ORDER BY period_start DESC LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $categoryId, $limit])->fetchAll();
    }
}
