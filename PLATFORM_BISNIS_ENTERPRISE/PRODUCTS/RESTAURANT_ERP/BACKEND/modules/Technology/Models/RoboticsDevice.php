<?php

namespace App\Modules\Technology\Models;

use App\Core\BaseModel;

class RoboticsDevice extends BaseModel
{
    protected $table = 'robotics_devices';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'integration_id',
        'device_name',
        'device_type',
        'device_model',
        'location',
        'capabilities',
        'device_status',
        'battery_level',
        'total_operations',
        'successful_operations',
        'success_rate'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $integrationId, $status)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($integrationId) {
            $where .= " AND integration_id = ?";
            $params[] = $integrationId;
        }
        
        if ($status) {
            $where .= " AND device_status = ?";
            $params[] = $status;
        }
        
        $sql = "SELECT rd.*, ti.integration_name 
                FROM {$this->table} rd
                LEFT JOIN technology_integrations ti ON rd.integration_id = ti.id
                {$where}
                ORDER BY rd.device_name ASC";
        
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
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND device_status = ?";
        $result = $this->db->query($sql, [$restaurantId, $status])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Update operation stats
     */
    public function updateOperationStats($id, $success)
    {
        $device = $this->findById($id, 0);
        
        if (!$device) {
            return false;
        }
        
        $totalOps = $device['total_operations'] + 1;
        $successfulOps = $device['successful_operations'] + ($success ? 1 : 0);
        $successRate = ($successfulOps / $totalOps) * 100;
        
        return $this->update($id, [
            'total_operations' => $totalOps,
            'successful_operations' => $successfulOps,
            'success_rate' => $successRate
        ]);
    }

    /**
     * Get by type
     */
    public function getByType($restaurantId, $deviceType)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND device_type = ? ORDER BY device_name ASC";
        return $this->db->query($sql, [$restaurantId, $deviceType])->fetchAll();
    }
}
