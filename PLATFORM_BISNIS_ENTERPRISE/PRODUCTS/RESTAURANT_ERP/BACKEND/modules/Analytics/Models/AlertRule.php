<?php

namespace App\Modules\Analytics\Models;

use App\Core\BaseModel;

class AlertRule extends BaseModel
{
    protected $table = 'alert_rules';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'alert_name',
        'alert_description',
        'kpi_id',
        'condition_type',
        'threshold_value',
        'notification_channels',
        'notification_recipients',
        'is_active'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId)
    {
        $sql = "SELECT ar.*, kd.kpi_name, kd.kpi_code 
                FROM {$this->table} ar
                LEFT JOIN kpi_definitions kd ON ar.kpi_id = kd.id
                WHERE ar.restaurant_id = ? 
                ORDER BY ar.is_active DESC, ar.created_at DESC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
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
     * Get active rules
     */
    public function getActive($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Get history
     */
    public function getHistory($restaurantId, $status, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $params = [$restaurantId];
        $where = "WHERE ah.restaurant_id = ?";
        
        if ($status) {
            $where .= " AND ah.alert_status = ?";
            $params[] = $status;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM alert_history ah {$where}";
        $totalResult = $this->db->query($countSql, $params)->fetch();
        $total = $totalResult['total'] ?? 0;
        
        // Get data
        $sql = "SELECT ah.*, ar.alert_name, kd.kpi_name, u.username as acknowledged_by_name 
                FROM alert_history ah
                LEFT JOIN alert_rules ar ON ah.alert_rule_id = ar.id
                LEFT JOIN kpi_definitions kd ON ah.kpi_id = kd.id
                LEFT JOIN users u ON ah.acknowledged_by = u.id
                {$where}
                ORDER BY ah.triggered_at DESC
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
     * Count by restaurant
     */
    public function countByRestaurant($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ?";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Count active
     */
    public function countActive($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND is_active = TRUE";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }
}
