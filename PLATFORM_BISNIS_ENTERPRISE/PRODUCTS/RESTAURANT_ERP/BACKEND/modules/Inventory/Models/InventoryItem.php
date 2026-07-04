<?php

namespace App\Modules\Inventory\Models;

use App\Core\BaseModel;

class InventoryItem extends BaseModel
{
    protected $table = 'inventory_items';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'item_code',
        'item_name',
        'item_description',
        'category_id',
        'unit_id',
        'current_stock',
        'minimum_stock',
        'maximum_stock',
        'reorder_point',
        'reorder_quantity',
        'cost_per_unit',
        'average_cost',
        'last_purchase_price',
        'supplier_id',
        'supplier_item_code',
        'is_active',
        'is_perishable',
        'shelf_life_days'
    ];

    /**
     * Get paginated items
     */
    public function getPaginated($restaurantId, $categoryId, $lowStock, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ? AND is_active = TRUE";
        
        if ($categoryId) {
            $where .= " AND category_id = ?";
            $params[] = $categoryId;
        }
        
        if ($lowStock) {
            $where .= " AND current_stock <= minimum_stock";
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT ii.*, ic.category_name, iu.unit_abbreviation, s.supplier_name 
                FROM {$this->table} ii
                LEFT JOIN inventory_categories ic ON ii.category_id = ic.id
                LEFT JOIN inventory_units iu ON ii.unit_id = iu.id
                LEFT JOIN suppliers s ON ii.supplier_id = s.id
                {$where}
                ORDER BY ii.item_name ASC
                LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $data = $this->db->query($sql, $params)->fetchAll();
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }

    /**
     * Find by ID
     */
    public function findById($id, $restaurantId)
    {
        $sql = "SELECT ii.*, ic.category_name, iu.unit_name, iu.unit_abbreviation, s.supplier_name 
                FROM {$this->table} ii
                LEFT JOIN inventory_categories ic ON ii.category_id = ic.id
                LEFT JOIN inventory_units iu ON ii.unit_id = iu.id
                LEFT JOIN suppliers s ON ii.supplier_id = s.id
                WHERE ii.id = ? AND ii.restaurant_id = ?";
        $result = $this->db->query($sql, [$id, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Find by code
     */
    public function findByCode($itemCode, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE item_code = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$itemCode, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Count by restaurant
     */
    public function countByRestaurant($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Count low stock
     */
    public function countLowStock($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE restaurant_id = ? AND is_active = TRUE AND current_stock <= minimum_stock";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get low stock items
     */
    public function getLowStock($restaurantId)
    {
        $sql = "SELECT ii.*, ic.category_name, iu.unit_abbreviation 
                FROM {$this->table} ii
                LEFT JOIN inventory_categories ic ON ii.category_id = ic.id
                LEFT JOIN inventory_units iu ON ii.unit_id = iu.id
                WHERE ii.restaurant_id = ? AND ii.is_active = TRUE AND ii.current_stock <= ii.minimum_stock
                ORDER BY ii.current_stock ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }
}
