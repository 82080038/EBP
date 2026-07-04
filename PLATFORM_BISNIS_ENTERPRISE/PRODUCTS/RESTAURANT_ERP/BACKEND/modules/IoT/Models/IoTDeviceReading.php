<?php

namespace App\Modules\IoT\Models;

use App\Core\BaseModel;

class IoTDeviceReading extends BaseModel
{
    protected $table = 'iot_device_readings';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'device_id',
        'reading_type',
        'reading_value',
        'reading_unit',
        'reading_quality',
        'reading_timestamp',
        'additional_data'
    ];

    /**
     * Get by device
     */
    public function getByDevice($deviceId, $restaurantId, $readingType, $dateFrom, $dateTo, $limit)
    {
        $params = [$restaurantId, $deviceId];
        $where = "WHERE restaurant_id = ? AND device_id = ?";
        
        if ($readingType) {
            $where .= " AND reading_type = ?";
            $params[] = $readingType;
        }
        
        if ($dateFrom) {
            $where .= " AND reading_timestamp >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND reading_timestamp <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY reading_timestamp DESC LIMIT ?";
        $params[] = $limit;
        
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
     * Get latest by device
     */
    public function getLatestByDevice($deviceId, $restaurantId, $limit = 10)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND device_id = ? ORDER BY reading_timestamp DESC LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $deviceId, $limit])->fetchAll();
    }

    /**
     * Get by type
     */
    public function getByType($restaurantId, $readingType, $limit = 100)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND reading_type = ? ORDER BY reading_timestamp DESC LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $readingType, $limit])->fetchAll();
    }

    /**
     * Get average for period
     */
    public function getAverageForPeriod($deviceId, $restaurantId, $readingType, $dateFrom, $dateTo)
    {
        $params = [$restaurantId, $deviceId, $readingType];
        $where = "WHERE restaurant_id = ? AND device_id = ? AND reading_type = ?";
        
        if ($dateFrom) {
            $where .= " AND reading_timestamp >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND reading_timestamp <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT AVG(reading_value) as avg_value, MIN(reading_value) as min_value, MAX(reading_value) as max_value, COUNT(*) as count
                FROM {$this->table} {$where}";
        
        $result = $this->db->query($sql, $params)->fetch();
        return $result ?: ['avg_value' => 0, 'min_value' => 0, 'max_value' => 0, 'count' => 0];
    }
}
