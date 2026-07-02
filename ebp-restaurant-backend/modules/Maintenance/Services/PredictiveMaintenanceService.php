<?php

if (!class_exists('WorkOrderRepository')) {
    require_once __DIR__ . '/../Repositories/WorkOrderRepository.php';
}
if (!class_exists('database')) {
    require_once __DIR__ . '/../../../config/database.php';
}

class PredictiveMaintenanceService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new WorkOrderRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function predictMaintenanceNeeds($tenantId, $branchId)
    {
        try {
            // Get assets
            $assets = $this->repository->getAssets($tenantId, $branchId);
            
            // Get maintenance history
            $maintenanceHistory = $this->repository->getEquipmentHistory($tenantId, $branchId);
            
            $predictions = [];
            
            foreach ($assets as $asset) {
                // Calculate days since last maintenance
                $lastMaintenance = $this->getLastMaintenanceDate($asset['asset_id'], $maintenanceHistory);
                $daysSinceLastMaintenance = $lastMaintenance ? (strtotime(date('Y-m-d')) - strtotime($lastMaintenance)) / 86400 : 365;
                
                // Calculate maintenance frequency
                $avgMaintenanceInterval = $this->calculateAvgMaintenanceInterval($asset['asset_id'], $maintenanceHistory);
                
                // Predict next maintenance date
                $nextMaintenanceDate = $this->predictNextMaintenance($lastMaintenance, $avgMaintenanceInterval);
                
                // Calculate risk score
                $riskScore = $this->calculateRiskScore($daysSinceLastMaintenance, $avgMaintenanceInterval);
                
                if ($riskScore > 50) {
                    $predictions[] = [
                        'asset_id' => $asset['asset_id'],
                        'asset_name' => $asset['asset_name'],
                        'asset_type' => $asset['asset_type'],
                        'last_maintenance' => $lastMaintenance,
                        'days_since_last_maintenance' => $daysSinceLastMaintenance,
                        'avg_maintenance_interval' => $avgMaintenanceInterval,
                        'predicted_next_maintenance' => $nextMaintenanceDate,
                        'risk_score' => $riskScore,
                        'priority' => $riskScore > 80 ? 'URGENT' : ($riskScore > 60 ? 'HIGH' : 'MEDIUM'),
                        'recommendation' => $this->getMaintenanceRecommendation($riskScore, $daysSinceLastMaintenance)
                    ];
                }
            }

            return [
                'success' => true,
                'message' => 'Predictive maintenance analysis completed',
                'data' => $predictions
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to predict maintenance: ' . $e->getMessage()
            ];
        }
    }

    private function getLastMaintenanceDate($assetId, $maintenanceHistory)
    {
        $lastDate = null;
        foreach ($maintenanceHistory as $history) {
            if ($history['asset_id'] == $assetId && $history['event_type'] == 'MAINTENANCE') {
                if (!$lastDate || $history['event_date'] > $lastDate) {
                    $lastDate = $history['event_date'];
                }
            }
        }
        return $lastDate;
    }

    private function calculateAvgMaintenanceInterval($assetId, $maintenanceHistory)
    {
        $intervals = [];
        $dates = [];
        
        foreach ($maintenanceHistory as $history) {
            if ($history['asset_id'] == $assetId && $history['event_type'] == 'MAINTENANCE') {
                $dates[] = $history['event_date'];
            }
        }
        
        sort($dates);
        
        for ($i = 1; $i < count($dates); $i++) {
            $interval = (strtotime($dates[$i]) - strtotime($dates[$i-1])) / 86400;
            $intervals[] = $interval;
        }
        
        if (empty($intervals)) {
            return 90; // Default 90 days
        }
        
        return array_sum($intervals) / count($intervals);
    }

    private function predictNextMaintenance($lastMaintenance, $avgInterval)
    {
        if (!$lastMaintenance) {
            return date('Y-m-d', strtotime('+' . $avgInterval . ' days'));
        }
        
        return date('Y-m-d', strtotime($lastMaintenance . ' +' . $avgInterval . ' days'));
    }

    private function calculateRiskScore($daysSinceLast, $avgInterval)
    {
        if (!$avgInterval) return 50;
        
        $percentage = ($daysSinceLast / $avgInterval) * 100;
        
        return min(100, $percentage);
    }

    private function getMaintenanceRecommendation($riskScore, $daysSinceLast)
    {
        if ($riskScore > 80) {
            return 'Urgent maintenance required. Schedule immediately to prevent failure.';
        } elseif ($riskScore > 60) {
            return 'Maintenance due soon. Schedule within next 7 days.';
        } else {
            return 'Maintenance recommended within next 30 days.';
        }
    }
}
