<?php

namespace App\Modules\Sustainability\Services;

use App\Modules\Sustainability\Models\SustainabilityMetric;
use App\Modules\Sustainability\Models\WasteTracking;
use App\Modules\Sustainability\Models\SustainabilityGoal;
use App\Modules\Sustainability\Models\SustainabilityCertification;
use App\Core\Database;

class SustainabilityManagementService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get sustainability metrics
     */
    public function getMetrics($restaurantId, $metricType, $dateFrom, $dateTo, $limit)
    {
        $metricModel = new SustainabilityMetric();
        return $metricModel->getByRestaurant($restaurantId, $metricType, $dateFrom, $dateTo, $limit);
    }

    /**
     * Get waste tracking
     */
    public function getWasteTracking($restaurantId, $wasteType, $dateFrom, $dateTo, $page, $limit)
    {
        $wasteModel = new WasteTracking();
        return $wasteModel->getPaginated($restaurantId, $wasteType, $dateFrom, $dateTo, $page, $limit);
    }

    /**
     * Create waste record
     */
    public function createWasteRecord($restaurantId, $userId, $data)
    {
        $wasteModel = new WasteTracking();
        
        $wasteData = [
            'restaurant_id' => $restaurantId,
            'waste_date' => $data->waste_date,
            'waste_type' => $data->waste_type,
            'waste_category' => $data->waste_category ?? null,
            'waste_quantity' => $data->waste_quantity,
            'waste_unit' => $data->waste_unit ?? 'kg',
            'disposal_method' => $data->disposal_method,
            'disposal_cost' => $data->disposal_cost ?? 0.00,
            'waste_source' => $data->waste_source,
            'notes' => $data->notes ?? null,
            'recorded_by' => $userId
        ];
        
        $wasteId = $wasteModel->create($wasteData);
        
        if (!$wasteId) {
            return ['success' => false, 'message' => 'Failed to create waste record'];
        }
        
        return ['success' => true, 'message' => 'Waste record created', 'waste_id' => $wasteId];
    }

    /**
     * Get goals
     */
    public function getGoals($restaurantId, $category, $status)
    {
        $goalModel = new SustainabilityGoal();
        return $goalModel->getByRestaurant($restaurantId, $category, $status);
    }

    /**
     * Create goal
     */
    public function createGoal($restaurantId, $userId, $data)
    {
        $goalModel = new SustainabilityGoal();
        
        $goalData = [
            'restaurant_id' => $restaurantId,
            'goal_name' => $data->goal_name,
            'goal_description' => $data->goal_description ?? null,
            'goal_category' => $data->goal_category,
            'goal_type' => $data->goal_type,
            'goal_period_start' => $data->goal_period_start,
            'goal_period_end' => $data->goal_period_end,
            'target_value' => $data->target_value,
            'target_unit' => $data->target_unit,
            'target_comparison' => $data->target_comparison,
            'created_by' => $userId
        ];
        
        $goalId = $goalModel->create($goalData);
        
        if (!$goalId) {
            return ['success' => false, 'message' => 'Failed to create goal'];
        }
        
        return ['success' => true, 'message' => 'Goal created', 'goal_id' => $goalId];
    }

    /**
     * Get certifications
     */
    public function getCertifications($restaurantId, $certType, $status)
    {
        $certModel = new SustainabilityCertification();
        return $certModel->getByRestaurant($restaurantId, $certType, $status);
    }

    /**
     * Create certification
     */
    public function createCertification($restaurantId, $userId, $data)
    {
        $certModel = new SustainabilityCertification();
        
        $certData = [
            'restaurant_id' => $restaurantId,
            'certification_name' => $data->certification_name,
            'certification_type' => $data->certification_type,
            'issuing_organization' => $data->issuing_organization,
            'issue_date' => $data->issue_date,
            'expiry_date' => $data->expiry_date,
            'certificate_number' => $data->certificate_number ?? null,
            'certificate_document_url' => $data->certificate_document_url ?? null,
            'notes' => $data->notes ?? null,
            'created_by' => $userId
        ];
        
        $certId = $certModel->create($certData);
        
        if (!$certId) {
            return ['success' => false, 'message' => 'Failed to create certification'];
        }
        
        return ['success' => true, 'message' => 'Certification created', 'certification_id' => $certId];
    }

    /**
     * Get summary
     */
    public function getSummary($restaurantId)
    {
        $metricModel = new SustainabilityMetric();
        $wasteModel = new WasteTracking();
        $goalModel = new SustainabilityGoal();
        $certModel = new SustainabilityCertification();
        
        // Get latest metrics
        $latestMetrics = $metricModel->getLatest($restaurantId);
        
        // Get total waste this month
        $totalWaste = $wasteModel->getTotalForMonth($restaurantId);
        
        // Get active goals
        $activeGoals = $goalModel->getActive($restaurantId);
        
        // Get active certifications
        $activeCerts = $certModel->getActive($restaurantId);
        
        // Get expiring certifications
        $expiringCerts = $certModel->getExpiringSoon($restaurantId, 30);
        
        return [
            'latest_metrics' => $latestMetrics,
            'total_waste_month' => $totalWaste,
            'active_goals' => $activeGoals,
            'active_certifications' => $activeCerts,
            'expiring_certifications' => $expiringCerts
        ];
    }
}
