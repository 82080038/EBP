<?php

namespace App\Modules\Sustainability\Models;

use App\Core\BaseModel;

class SustainabilityMetric extends BaseModel
{
    protected $table = 'sustainability_metrics';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'metric_date',
        'metric_type',
        'carbon_footprint_kg',
        'carbon_footprint_per_revenue',
        'energy_consumption_kwh',
        'energy_cost',
        'renewable_energy_percentage',
        'water_consumption_liters',
        'water_cost',
        'water_recycled_percentage',
        'total_waste_kg',
        'food_waste_kg',
        'recycled_waste_kg',
        'composted_waste_kg',
        'recycling_rate',
        'packaging_waste_kg',
        'sustainable_packaging_percentage',
        'delivery_distance_km',
        'transport_emissions_kg',
        'sustainability_score',
        'sustainability_rating'
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
            $where .= " AND metric_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND metric_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT * FROM {$this->table} {$where} ORDER BY metric_date DESC LIMIT ?";
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
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? ORDER BY metric_date DESC LIMIT 1";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Get by date
     */
    public function getByDate($restaurantId, $date)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND metric_date = ?";
        $result = $this->db->query($sql, [$restaurantId, $date])->fetch();
        return $result ?: null;
    }

    /**
     * Calculate sustainability score
     */
    public function calculateScore($restaurantId, $date)
    {
        // This would calculate a comprehensive sustainability score
        // based on various metrics
        $score = 0;
        
        // Carbon footprint (20 points)
        // Energy efficiency (20 points)
        // Water conservation (15 points)
        // Waste management (20 points)
        // Sustainable packaging (15 points)
        // Transport efficiency (10 points)
        
        return $score;
    }
}
