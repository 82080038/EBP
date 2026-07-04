<?php

namespace App\Modules\Inventory\Models;

use App\Core\BaseModel;

class StockAlert extends BaseModel
{
    protected $table = 'stock_alerts';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'inventory_item_id',
        'alert_type',
        'alert_severity',
        'alert_message',
        'current_stock',
        'threshold_value',
        'is_resolved',
        'resolved_at',
        'resolved_by',
        'notification_sent',
        'notification_sent_at'
    ];

    /**
     * Get paginated alerts
     */
    public function getPaginated($restaurantId, $alertType, $isResolved, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($alertType) {
            $where .= " AND alert_type = ?";
            $params[] = $alertType;
        }
        
        if ($isResolved !== null) {
            $where .= " AND is_resolved = ?";
            $params[] = $isResolved;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT sa.*, ii.item_name, ii.item_code, u.username as resolved_by_name 
                FROM {$this->table} sa
                LEFT JOIN inventory_items ii ON sa.inventory_item_id = ii.id
                LEFT JOIN users u ON sa.resolved_by = u.id
                {$where}
                ORDER BY sa.created_at DESC
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
        $sql = "SELECT * FROM {$this->table} WHERE id = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$id, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Get by item ID
     */
    public function getByItemId($itemId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE inventory_item_id = ? ORDER BY created_at DESC";
        return $this->db->query($sql, [$itemId])->fetchAll();
    }

    /**
     * Count unresolved
     */
    public function countUnresolved($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND is_resolved = FALSE";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get by alert type
     */
    public function getByAlertType($restaurantId, $alertType)
    {
        $sql = "SELECT sa.*, ii.item_name, ii.item_code 
                FROM {$this->table} sa
                LEFT JOIN inventory_items ii ON sa.inventory_item_id = ii.id
                WHERE sa.restaurant_id = ? AND sa.alert_type = ? AND sa.is_resolved = FALSE
                ORDER BY sa.created_at DESC";
        return $this->db->query($sql, [$restaurantId, $alertType])->fetchAll();
    }

    /**
     * Get critical alerts
     */
    public function getCritical($restaurantId)
    {
        $sql = "SELECT sa.*, ii.item_name, ii.item_code 
                FROM {$this->table} sa
                LEFT JOIN inventory_items ii ON sa.inventory_item_id = ii.id
                WHERE sa.restaurant_id = ? AND sa.alert_severity = 'critical' AND sa.is_resolved = FALSE
                ORDER BY sa.created_at DESC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }
}
