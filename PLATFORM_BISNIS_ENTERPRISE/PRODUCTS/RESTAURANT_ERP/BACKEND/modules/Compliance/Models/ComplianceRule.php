<?php

namespace App\Modules\Compliance\Models;

use App\Core\BaseModel;

class ComplianceRule extends BaseModel
{
    protected $table = 'compliance_rules';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'rule_type',
        'rule_name',
        'rule_description',
        'rule_config',
        'check_frequency',
        'next_check_date',
        'priority',
        'is_active'
    ];

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

    /**
     * Count pending checks
     */
    public function countPendingChecks($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE restaurant_id = ? AND is_active = TRUE AND next_check_date <= CURDATE()";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $ruleType = null)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($ruleType) {
            $where .= " AND rule_type = ?";
            $params[] = $ruleType;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY priority DESC, created_at ASC";
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get pending checks
     */
    public function getPendingChecks($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? AND is_active = TRUE AND next_check_date <= CURDATE()
                ORDER BY priority DESC, next_check_date ASC";
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
}
