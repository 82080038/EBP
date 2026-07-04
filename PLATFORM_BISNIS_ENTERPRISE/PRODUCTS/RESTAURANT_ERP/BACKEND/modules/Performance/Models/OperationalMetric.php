<?php

namespace App\Modules\Performance\Models;

use App\Core\BaseModel;

class OperationalMetric extends BaseModel
{
    protected $table = 'operational_metrics';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'metric_type',
        'period_start',
        'period_end',
        'table_turnover_rate',
        'average_table_occupancy',
        'peak_occupancy_time',
        'average_prep_time',
        'average_delivery_time',
        'kitchen_efficiency',
        'average_seating_time',
        'average_billing_time',
        'service_efficiency',
        'average_wait_time',
        'maximum_wait_time'
    ];

    /**
     * Get by restaurant
     */
    public function getByRestaurant($restaurantId, $metricType, $dateFrom, $dateTo, $limit)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($metricType) {
            $where .= " AND metric_type = ?";
            $params[] = $metricType;
        }
        
        if ($dateFrom) {
            $where .= " AND period_start >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND period_end <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY period_start DESC LIMIT ?";
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
     * Get latest
     */
    public function getLatest($restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? ORDER BY period_start DESC LIMIT 1";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Get by type
     */
    public function getByType($restaurantId, $metricType, $limit = 30)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND metric_type = ? ORDER BY period_start DESC LIMIT ?";
        return $this->db->query($sql, [$restaurantId, $metricType, $limit])->fetchAll();
    }
}
