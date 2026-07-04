<?php

namespace App\Modules\Inventory\Models;

use App\Core\BaseModel;

class WasteLog extends BaseModel
{
    protected $table = 'waste_logs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'inventory_item_id',
        'waste_quantity',
        'waste_reason',
        'waste_reason_description',
        'unit_cost',
        'total_cost',
        'location_id',
        'reported_by',
        'approved_by',
        'waste_date',
        'approved_at',
        'is_approved',
        'notes'
    ];

    /**
     * Get paginated waste logs
     */
    public function getPaginated($restaurantId, $itemId, $reason, $dateFrom, $dateTo, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE wl.restaurant_id = ?";
        
        if ($itemId) {
            $where .= " AND wl.inventory_item_id = ?";
            $params[] = $itemId;
        }
        
        if ($reason) {
            $where .= " AND wl.waste_reason = ?";
            $params[] = $reason;
        }
        
        if ($dateFrom) {
            $where .= " AND wl.waste_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND wl.waste_date <= ?";
            $params[] = $dateTo;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} wl {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT wl.*, ii.item_name, ii.item_code, u1.username as reported_by_name, u2.username as approved_by_name 
                FROM {$this->table} wl
                LEFT JOIN inventory_items ii ON wl.inventory_item_id = ii.id
                LEFT JOIN users u1 ON wl.reported_by = u1.id
                LEFT JOIN users u2 ON wl.approved_by = u2.id
                {$where}
                ORDER BY wl.waste_date DESC
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
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $result = $this->db->query($sql, [$id])->fetch();
        return $result ?: null;
    }

    /**
     * Get by item ID
     */
    public function getByItemId($itemId, $limit = 50)
    {
        $sql = "SELECT * FROM {$this->table} WHERE inventory_item_id = ? ORDER BY waste_date DESC LIMIT ?";
        return $this->db->query($sql, [$itemId, $limit])->fetchAll();
    }

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $dateFrom = null, $dateTo = null)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($dateFrom) {
            $where .= " AND waste_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND waste_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY waste_date DESC";
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Sum waste by reason
     */
    public function sumByReason($restaurantId, $dateFrom = null, $dateTo = null)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($dateFrom) {
            $where .= " AND waste_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND waste_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT waste_reason, COUNT(*) as count, SUM(total_cost) as total_cost 
                FROM {$this->table} {$where}
                GROUP BY waste_reason
                ORDER BY total_cost DESC";
        
        return $this->db->query($sql, $params)->fetchAll();
    }
}
