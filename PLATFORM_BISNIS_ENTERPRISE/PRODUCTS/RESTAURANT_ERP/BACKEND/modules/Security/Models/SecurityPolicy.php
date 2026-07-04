<?php

namespace App\Modules\Security\Models;

use App\Core\BaseModel;

class SecurityPolicy extends BaseModel
{
    protected $table = 'security_policies';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'policy_name',
        'policy_type',
        'policy_description',
        'policy_config',
        'is_active',
        'is_enforced',
        'priority'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $policyType = null)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($policyType) {
            $where .= " AND policy_type = ?";
            $params[] = $policyType;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY priority DESC, created_at ASC";
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
     * Get active policies
     */
    public function getActive($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? AND is_active = TRUE AND is_enforced = TRUE 
                ORDER BY priority DESC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }
}
