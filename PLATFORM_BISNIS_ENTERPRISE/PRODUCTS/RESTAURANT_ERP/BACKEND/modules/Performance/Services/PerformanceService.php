<?php

namespace App\Modules\Performance\Services;

use App\Modules\Performance\Models\StaffPerformance;
use App\Modules\Performance\Models\OperationalMetric;
use App\Modules\Performance\Models\PerformanceTarget;
use App\Modules\Performance\Models\EfficiencyMetric;
use App\Core\Database;

class PerformanceService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get staff performance
     */
    public function getStaffPerformance($restaurantId, $staffId, $periodType, $dateFrom, $dateTo, $limit)
    {
        $staffPerformanceModel = new StaffPerformance();
        return $staffPerformanceModel->getByRestaurant($restaurantId, $staffId, $periodType, $dateFrom, $dateTo, $limit);
    }

    /**
     * Get operational metrics
     */
    public function getOperationalMetrics($restaurantId, $metricType, $dateFrom, $dateTo, $limit)
    {
        $operationalMetricModel = new OperationalMetric();
        return $operationalMetricModel->getByRestaurant($restaurantId, $metricType, $dateFrom, $dateTo, $limit);
    }

    /**
     * Get performance targets
     */
    public function getPerformanceTargets($restaurantId, $category, $status)
    {
        $targetModel = new PerformanceTarget();
        return $targetModel->getByRestaurant($restaurantId, $category, $status);
    }

    /**
     * Create performance target
     */
    public function createPerformanceTarget($restaurantId, $userId, $data)
    {
        $targetModel = new PerformanceTarget();
        
        $targetData = [
            'restaurant_id' => $restaurantId,
            'target_name' => $data->target_name,
            'target_description' => $data->target_description ?? null,
            'target_category' => $data->target_category,
            'target_type' => $data->target_type,
            'target_period_start' => $data->target_period_start,
            'target_period_end' => $data->target_period_end,
            'target_value' => $data->target_value,
            'target_comparison' => $data->target_comparison,
            'assigned_to' => $data->assigned_to ?? null,
            'created_by' => $userId
        ];
        
        $targetId = $targetModel->create($targetData);
        
        if (!$targetId) {
            return ['success' => false, 'message' => 'Failed to create performance target'];
        }
        
        return ['success' => true, 'message' => 'Performance target created', 'target_id' => $targetId];
    }

    /**
     * Get efficiency metrics
     */
    public function getEfficiencyMetrics($restaurantId, $dateFrom, $dateTo, $limit)
    {
        $efficiencyModel = new EfficiencyMetric();
        return $efficiencyModel->getByRestaurant($restaurantId, $dateFrom, $dateTo, $limit);
    }

    /**
     * Get summary
     */
    public function getSummary($restaurantId, $dateFrom, $dateTo)
    {
        $staffPerformanceModel = new StaffPerformance();
        $operationalMetricModel = new OperationalMetric();
        $efficiencyModel = new EfficiencyMetric();
        
        // Get latest operational metrics
        $latestOperational = $operationalMetricModel->getLatest($restaurantId);
        
        // Get latest efficiency metrics
        $latestEfficiency = $efficiencyModel->getLatest($restaurantId);
        
        // Get top performing staff
        $topStaff = $staffPerformanceModel->getTopPerformers($restaurantId, $dateFrom, $dateTo, 5);
        
        // Get active targets
        $targetModel = new PerformanceTarget();
        $activeTargets = $targetModel->getActive($restaurantId);
        
        return [
            'operational_metrics' => $latestOperational,
            'efficiency_metrics' => $latestEfficiency,
            'top_performers' => $topStaff,
            'active_targets' => $activeTargets
        ];
    }
}
