<?php

namespace App\Modules\Analytics\Models;

use App\Core\BaseModel;

class KpiValue extends BaseModel
{
    protected $table = 'kpi_values';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'kpi_id',
        'kpi_value',
        'period_type',
        'period_start',
        'period_end',
        'previous_value',
        'percentage_change'
    ];

    /**
     * Get by KPI
     */
    public function getByKpi($kpiId, $restaurantId, $periodType, $limit)
    {
        $params = [$restaurantId, $kpiId];
        $where = "WHERE restaurant_id = ? AND kpi_id = ?";
        
        if ($periodType) {
            $where .= " AND period_type = ?";
            $params[] = $periodType;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY period_start DESC LIMIT ?";
        $params[] = $limit;
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get latest value
     */
    public function getLatest($kpiId, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? AND kpi_id = ? 
                ORDER BY period_start DESC 
                LIMIT 1";
        $result = $this->db->query($sql, [$restaurantId, $kpiId])->fetch();
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
     * Get by period
     */
    public function getByPeriod($restaurantId, $periodType, $periodStart, $periodEnd)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE restaurant_id = ? AND period_type = ? 
                AND period_start >= ? AND period_end <= ?";
        return $this->db->query($sql, [$restaurantId, $periodType, $periodStart, $periodEnd])->fetchAll();
    }

    /**
     * Calculate percentage change
     */
    public function calculateChange($kpiId, $restaurantId, $currentValue, $periodType)
    {
        // Get previous period value
        $sql = "SELECT kpi_value FROM {$this->table} 
                WHERE restaurant_id = ? AND kpi_id = ? AND period_type = ?
                ORDER BY period_start DESC 
                LIMIT 1 OFFSET 1";
        $result = $this->db->query($sql, [$restaurantId, $kpiId, $periodType])->fetch();
        
        $previousValue = $result['kpi_value'] ?? 0;
        
        if ($previousValue == 0) {
            return 0;
        }
        
        return (($currentValue - $previousValue) / $previousValue) * 100;
    }
}
