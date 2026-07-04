<?php

namespace App\Modules\Offline\Models;

use App\Core\BaseModel;

class DeviceRegistration extends BaseModel
{
    protected $table = 'device_registrations';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'user_id',
        'device_id',
        'device_name',
        'device_type',
        'device_os',
        'device_os_version',
        'app_version',
        'storage_capacity_mb',
        'available_storage_mb',
        'is_active',
        'last_seen_at'
    ];

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
     * Find by device ID
     */
    public function findByDeviceId($deviceId, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE device_id = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$deviceId, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? ORDER BY last_seen_at DESC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Update last seen
     */
    public function updateLastSeen($deviceId, $restaurantId)
    {
        $sql = "UPDATE {$this->table} SET last_seen_at = NOW(), updated_at = NOW() 
                WHERE device_id = ? AND restaurant_id = ?";
        return $this->db->query($sql, [$deviceId, $restaurantId]);
    }

    /**
     * Get active devices
     */
    public function getActive($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? AND is_active = TRUE 
                ORDER BY last_seen_at DESC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }
}
