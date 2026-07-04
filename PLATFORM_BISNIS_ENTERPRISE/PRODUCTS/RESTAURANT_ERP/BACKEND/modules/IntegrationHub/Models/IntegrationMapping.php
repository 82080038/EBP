<?php

namespace App\Modules\IntegrationHub\Models;

use App\Core\BaseModel;

class IntegrationMapping extends BaseModel
{
    protected $table = 'integration_mappings';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'integration_id',
        'mapping_name',
        'mapping_type',
        'source_system',
        'source_entity',
        'source_field',
        'target_system',
        'target_entity',
        'target_field',
        'transformation_rules',
        'is_active'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $integrationId)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($integrationId) {
            $where .= " AND integration_id = ?";
            $params[] = $integrationId;
        }
        
        $sql = "SELECT im.*, ei.integration_name 
                FROM {$this->table} im
                LEFT JOIN external_integrations ei ON im.integration_id = ei.id
                {$where}
                ORDER BY im.mapping_name ASC";
        
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
     * Get active
     */
    public function getActive($restaurantId, $integrationId)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ? AND is_active = TRUE";
        
        if ($integrationId) {
            $where .= " AND integration_id = ?";
            $params[] = $integrationId;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY mapping_name ASC";
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get by type
     */
    public function getByType($restaurantId, $mappingType)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND mapping_type = ? AND is_active = TRUE ORDER BY mapping_name ASC";
        return $this->db->query($sql, [$restaurantId, $mappingType])->fetchAll();
    }

    /**
     * Get by integration
     */
    public function getByIntegration($integrationId, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE integration_id = ? AND restaurant_id = ? ORDER BY mapping_name ASC";
        return $this->db->query($sql, [$integrationId, $restaurantId])->fetchAll();
    }
}
