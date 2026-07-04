<?php

namespace App\Modules\Offline\Models;

use App\Core\BaseModel;

class OfflineDataSnapshot extends BaseModel
{
    protected $table = 'offline_data_snapshots';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'device_id',
        'user_id',
        'data_type',
        'snapshot_data',
        'snapshot_version',
        'last_synced_at',
        'sync_status'
    ];

    /**
     * Get latest snapshot
     */
    public function getLatest($restaurantId, $deviceId, $dataType)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($deviceId) {
            $where .= " AND device_id = ?";
            $params[] = $deviceId;
        }
        
        if ($dataType) {
            $where .= " AND data_type = ?";
            $params[] = $dataType;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY created_at DESC LIMIT 1";
        $result = $this->db->query($sql, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Find by ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $result = $this->db->query($sql, [$id])->fetch();
        return $result ?: null;
    }

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $limit = 50)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? ORDER BY created_at DESC LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $limit])->fetchAll();
    }
}
