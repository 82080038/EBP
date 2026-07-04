<?php

namespace App\Modules\IoT\Models;

use App\Core\BaseModel;

class IoTDevice extends BaseModel
{
    protected $table = 'iot_devices';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'device_name',
        'device_type',
        'device_category',
        'device_serial',
        'device_model',
        'manufacturer',
        'connection_type',
        'ip_address',
        'mac_address',
        'location',
        'installation_date',
        'device_status',
        'last_heartbeat',
        'battery_level',
        'configuration',
        'notes'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $deviceType, $status)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($deviceType) {
            $where .= " AND device_type = ?";
            $params[] = $deviceType;
        }
        
        if ($status) {
            $where .= " AND device_status = ?";
            $params[] = $status;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY device_name ASC";
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
     * Count by restaurant
     */
    public function countByRestaurant($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ?";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
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
     * Update heartbeat
     */
    public function updateHeartbeat($id, $restaurantId, $batteryLevel = null)
    {
        $updateData = [
            'last_heartbeat' => date('Y-m-d H:i:s')
        ];
        
        if ($batteryLevel !== null) {
            $updateData['battery_level'] = $batteryLevel;
        }
        
        return $this->update($id, $updateData);
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
