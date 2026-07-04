<?php

namespace App\Modules\Reconciliation\Models;

use App\Core\BaseModel;

class ReconciliationRule extends BaseModel
{
    protected $table = 'reconciliation_rules';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'rule_name',
        'rule_type',
        'rule_config',
        'priority',
        'is_active'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? 
                ORDER BY priority DESC, created_at ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Get active by restaurant
     */
    public function getActiveByRestaurant($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? AND is_active = TRUE 
                ORDER BY priority DESC, created_at ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Find by ID
     */
    public function findById($id, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE id = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$id, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Get by type
     */
    public function getByType($restaurantId, $ruleType)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? AND rule_type = ? AND is_active = TRUE 
                ORDER BY priority DESC";
        return $this->db->query($sql, [$restaurantId, $ruleType])->fetchAll();
    }
}
