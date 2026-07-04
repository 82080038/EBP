<?php

namespace App\Modules\Inventory\Models;

use App\Core\BaseModel;

class StockMovement extends BaseModel
{
    protected $table = 'stock_movements';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'inventory_item_id',
        'movement_type',
        'movement_direction',
        'quantity',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'reference_number',
        'unit_cost',
        'total_cost',
        'location_id',
        'performed_by',
        'performed_at',
        'notes'
    ];

    /**
     * Get paginated movements
     */
    public function getPaginated($restaurantId, $itemId, $movementType, $dateFrom, $dateTo, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE sm.restaurant_id = ?";
        
        if ($itemId) {
            $where .= " AND sm.inventory_item_id = ?";
            $params[] = $itemId;
        }
        
        if ($movementType) {
            $where .= " AND sm.movement_type = ?";
            $params[] = $movementType;
        }
        
        if ($dateFrom) {
            $where .= " AND sm.performed_at >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND sm.performed_at <= ?";
            $params[] = $dateTo;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} sm {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT sm.*, ii.item_name, ii.item_code, u.username as performed_by_name 
                FROM {$this->table} sm
                LEFT JOIN inventory_items ii ON sm.inventory_item_id = ii.id
                LEFT JOIN users u ON sm.performed_by = u.id
                {$where}
                ORDER BY sm.performed_at DESC
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
     * Get by item ID
     */
    public function getByItemId($itemId, $limit = 50)
    {
        $sql = "SELECT sm.*, u.username as performed_by_name 
                FROM {$this->table} sm
                LEFT JOIN users u ON sm.performed_by = u.id
                WHERE sm.inventory_item_id = ? 
                ORDER BY sm.performed_at DESC 
                LIMIT ?";
        return $this->db->query($sql, [$itemId, $limit])->fetchAll();
    }

    /**
     * Find by ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $result = $this->db->query($sql, [$id])->fetch();
        return $result ?: null;
    }

    /**
     * Get by reference
     */
    public function getByReference($referenceType, $referenceId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE reference_type = ? AND reference_id = ?";
        return $this->db->query($sql, [$referenceType, $referenceId])->fetchAll();
    }
}
