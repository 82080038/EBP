<?php

namespace App\Modules\Technology\Models;

use App\Core\BaseModel;

class TechnologyIntegration extends BaseModel
{
    protected $table = 'technology_integrations';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'integration_name',
        'integration_type',
        'integration_category',
        'provider_name',
        'provider_contact',
        'api_endpoint',
        'api_key',
        'configuration',
        'integration_status',
        'last_health_check',
        'health_status',
        'created_by'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $type, $status)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($type) {
            $where .= " AND integration_type = ?";
            $params[] = $type;
        }
        
        if ($status) {
            $where .= " AND integration_status = ?";
            $params[] = $status;
        }
        
        $sql = "SELECT ti.*, u.username as created_by_name 
                FROM {$this->table} ti
                LEFT JOIN users u ON ti.created_by = u.id
                {$where}
                ORDER BY ti.integration_name ASC";
        
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
     * Count by status
     */
    public function countByStatus($restaurantId, $status)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND integration_status = ?";
        $result = $this->db->query($sql, [$restaurantId, $status])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get active
     */
    public function getActive($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND integration_status = 'active' ORDER BY integration_name ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Update health status
     */
    public function updateHealthStatus($id, $healthStatus)
    {
        return $this->update($id, [
            'health_status' => $healthStatus,
            'last_health_check' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get by type
     */
    public function getByType($restaurantId, $integrationType)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND integration_type = ? ORDER BY integration_name ASC";
        return $this->db->query($sql, [$restaurantId, $integrationType])->fetchAll();
    }
}
