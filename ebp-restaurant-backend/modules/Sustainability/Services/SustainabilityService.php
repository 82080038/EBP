<?php

require_once __DIR__ . '/../Repositories/SustainabilityRepository.php';
require_once __DIR__ . '/../../../config/database.php';

class SustainabilityService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new SustainabilityRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function recordWaste($data, $tenantId, $branchId)
    {
        try {
            if (empty($data['waste_type']) || empty($data['waste_date'])) {
                return [
                    'success' => false,
                    'message' => 'Waste type and date are required'
                ];
            }

            $wasteData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'waste_date' => $data['waste_date'],
                'waste_type' => $data['waste_type'],
                'quantity' => $data['quantity'] ?? 0,
                'unit' => $data['unit'] ?? null,
                'estimated_cost' => $data['estimated_cost'] ?? 0,
                'reason' => $data['reason'] ?? null
            ];

            $wasteId = $this->repository->createWaste($wasteData);

            return [
                'success' => true,
                'message' => 'Waste recorded successfully',
                'waste_id' => $wasteId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to record waste: ' . $e->getMessage()
            ];
        }
    }

    public function recordSustainabilityMetrics($data, $tenantId, $branchId)
    {
        try {
            if (empty($data['metric_date'])) {
                return [
                    'success' => false,
                    'message' => 'Metric date is required'
                ];
            }

            $metricData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'metric_date' => $data['metric_date'],
                'carbon_footprint_kg' => $data['carbon_footprint_kg'] ?? 0,
                'energy_kwh' => $data['energy_kwh'] ?? 0,
                'water_liters' => $data['water_liters'] ?? 0,
                'waste_kg' => $data['waste_kg'] ?? 0
            ];

            $metricId = $this->repository->createMetric($metricData);

            return [
                'success' => true,
                'message' => 'Sustainability metrics recorded successfully',
                'metric_id' => $metricId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to record metrics: ' . $e->getMessage()
            ];
        }
    }

    public function getWasteTracking($tenantId, $branchId = null, $startDate = null, $endDate = null)
    {
        try {
            $waste = $this->repository->getWasteByTenant($tenantId, $branchId, $startDate, $endDate);
            
            return [
                'success' => true,
                'message' => 'Waste tracking retrieved successfully',
                'data' => $waste
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get waste tracking: ' . $e->getMessage()
            ];
        }
    }

    public function getSustainabilityMetrics($tenantId, $branchId = null, $startDate = null, $endDate = null)
    {
        try {
            $metrics = $this->repository->getMetricsByTenant($tenantId, $branchId, $startDate, $endDate);
            
            return [
                'success' => true,
                'message' => 'Sustainability metrics retrieved successfully',
                'data' => $metrics
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get sustainability metrics: ' . $e->getMessage()
            ];
        }
    }
}
