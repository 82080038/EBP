<?php

namespace App\Modules\CustomerAnalytics\Services;

use App\Modules\CustomerAnalytics\Models\CustomerSegment;
use App\Modules\CustomerAnalytics\Models\CustomerBehavior;
use App\Modules\CustomerAnalytics\Models\CustomerJourney;
use App\Modules\CustomerAnalytics\Models\CustomerCohort;
use App\Core\Database;

class CustomerAnalyticsService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get customer segments
     */
    public function getSegments($restaurantId)
    {
        $segmentModel = new CustomerSegment();
        return $segmentModel->getByRestaurant($restaurantId);
    }

    /**
     * Create customer segment
     */
    public function createSegment($restaurantId, $data)
    {
        $segmentModel = new CustomerSegment();
        
        $segmentData = [
            'restaurant_id' => $restaurantId,
            'segment_name' => $data->segment_name,
            'segment_description' => $data->segment_description ?? null,
            'segment_criteria' => json_encode($data->segment_criteria ?? []),
            'segment_color' => $data->segment_color ?? null,
            'sort_order' => $data->sort_order ?? 0,
            'is_active' => true
        ];
        
        $segmentId = $segmentModel->create($segmentData);
        
        if (!$segmentId) {
            return ['success' => false, 'message' => 'Failed to create segment'];
        }
        
        return ['success' => true, 'message' => 'Segment created', 'segment_id' => $segmentId];
    }

    /**
     * Get customer behavior
     */
    public function getCustomerBehavior($customerId, $restaurantId)
    {
        $behaviorModel = new CustomerBehavior();
        return $behaviorModel->getByCustomer($customerId, $restaurantId);
    }

    /**
     * Get customer journey
     */
    public function getCustomerJourney($customerId, $restaurantId)
    {
        $journeyModel = new CustomerJourney();
        return $journeyModel->getByCustomer($customerId, $restaurantId);
    }

    /**
     * Get customer cohorts
     */
    public function getCohorts($restaurantId)
    {
        $cohortModel = new CustomerCohort();
        return $cohortModel->getByRestaurant($restaurantId);
    }

    /**
     * Create customer cohort
     */
    public function createCohort($restaurantId, $data)
    {
        $cohortModel = new CustomerCohort();
        
        $cohortData = [
            'restaurant_id' => $restaurantId,
            'cohort_name' => $data->cohort_name,
            'cohort_description' => $data->cohort_description ?? null,
            'cohort_type' => $data->cohort_type,
            'cohort_criteria' => json_encode($data->cohort_criteria ?? []),
            'cohort_start_date' => $data->cohort_start_date,
            'cohort_end_date' => $data->cohort_end_date ?? null,
            'is_active' => true
        ];
        
        $cohortId = $cohortModel->create($cohortData);
        
        if (!$cohortId) {
            return ['success' => false, 'message' => 'Failed to create cohort'];
        }
        
        return ['success' => true, 'message' => 'Cohort created', 'cohort_id' => $cohortId];
    }

    /**
     * Get cohort data
     */
    public function getCohortData($cohortId, $restaurantId)
    {
        $sql = "SELECT * FROM customer_cohort_data WHERE cohort_id = ? AND restaurant_id = ? ORDER BY period_number ASC";
        return $this->db->query($sql, [$cohortId, $restaurantId])->fetchAll();
    }

    /**
     * Get summary
     */
    public function getSummary($restaurantId)
    {
        $segmentModel = new CustomerSegment();
        $behaviorModel = new CustomerBehavior();
        
        // Get segment counts
        $segments = $segmentModel->getByRestaurant($restaurantId);
        
        // Get total customers
        $totalCustomers = $behaviorModel->countByRestaurant($restaurantId);
        
        // Get high-value customers
        $highValueCustomers = $behaviorModel->countByLifetimeValue($restaurantId, 1000000);
        
        // Get at-risk customers
        $atRiskCustomers = $behaviorModel->countByChurnRisk($restaurantId, 'high');
        
        // Average lifetime value
        $avgLifetimeValue = $behaviorModel->getAverageLifetimeValue($restaurantId);
        
        return [
            'total_customers' => $totalCustomers,
            'high_value_customers' => $highValueCustomers,
            'at_risk_customers' => $atRiskCustomers,
            'average_lifetime_value' => $avgLifetimeValue,
            'segments' => $segments
        ];
    }
}
