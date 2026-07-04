<?php

namespace App\Modules\Sales\Models;

use App\Core\BaseModel;

class ProductSales extends BaseModel
{
    protected $table = 'product_sales';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'menu_item_id',
        'aggregate_type',
        'period_start',
        'period_end',
        'quantity_sold',
        'total_revenue',
        'total_cost',
        'gross_profit',
        'order_count',
        'sales_rank'
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
        
        $sql = "SELECT ps.*, mi.item_name, mi.item_code 
                FROM {$this->table} ps
                LEFT JOIN menu_items mi ON ps.menu_item_id = mi.id
                {$where}
                ORDER BY ps.total_revenue DESC
                LIMIT ?";
        $params[] = $limit;
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get top products
     */
    public function getTopProducts($restaurantId, $dateFrom, $dateTo, $limit)
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
        
        $sql = "SELECT ps.*, mi.item_name, mi.item_code 
                FROM {$this->table} ps
                LEFT JOIN menu_items mi ON ps.menu_item_id = mi.id
                {$where}
                ORDER BY ps.total_revenue DESC
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
     * Get by menu item
     */
    public function getByMenuItem($menuItemId, $restaurantId, $limit = 30)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND menu_item_id = ? ORDER BY period_start DESC LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $menuItemId, $limit])->fetchAll();
    }
}
