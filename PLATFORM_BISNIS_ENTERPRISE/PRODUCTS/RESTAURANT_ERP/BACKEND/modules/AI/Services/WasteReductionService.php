<?php

if (!class_exists('WasteTrackingRepository')) {
    require_once __DIR__ . '/../Repositories/WasteTrackingRepository.php';
}


class WasteReductionService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new WasteTrackingRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function recordWaste($data, $tenantId, $userId)
    {
        try {
            if (empty($data['product_id']) || empty($data['waste_date']) || empty($data['waste_quantity'])) {
                return [
                    'success' => false,
                    'message' => 'Product ID, waste date, and quantity are required'
                ];
            }

            // Calculate estimated cost
            $estimatedCost = $this->calculateWasteCost($data['product_id'], $data['waste_quantity'], $tenantId);
            $data['estimated_cost'] = $estimatedCost;
            $data['tenant_id'] = $tenantId;
            $data['recorded_by'] = $userId;
            
            $wasteId = $this->repository->createWaste($data);

            return [
                'success' => true,
                'message' => 'Waste recorded successfully',
                'waste_id' => $wasteId,
                'estimated_cost' => $estimatedCost
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to record waste: ' . $e->getMessage()
            ];
        }
    }

    public function getWasteReport($tenantId, $branchId, $dateFrom, $dateTo)
    {
        try {
            $report = $this->repository->getWasteReport($tenantId, $branchId, $dateFrom, $dateTo);
            
            // Generate reduction recommendations
            $recommendations = $this->generateReductionRecommendations($report);
            
            return [
                'success' => true,
                'message' => 'Waste report retrieved successfully',
                'data' => [
                    'report' => $report,
                    'recommendations' => $recommendations
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get report: ' . $e->getMessage()
            ];
        }
    }

    private function calculateWasteCost($productId, $quantity, $tenantId)
    {
        // Try to get cost from products table
        $stmt = $this->db->prepare("SELECT AVG(p.cost) as avg_cost FROM products p WHERE p.product_id = ? AND p.tenant_id = ? LIMIT 1");
        $stmt->execute([$productId, $tenantId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $unitCost = $result['avg_cost'] ?? 0;
        return $unitCost * $quantity;
    }

    private function generateReductionRecommendations($report)
    {
        $recommendations = [];
        
        // Analyze by reason
        $reasonAnalysis = [];
        foreach ($report as $item) {
            $reason = $item['waste_reason'];
            if (!isset($reasonAnalysis[$reason])) {
                $reasonAnalysis[$reason] = ['count' => 0, 'total_cost' => 0];
            }
            $reasonAnalysis[$reason]['count']++;
            $reasonAnalysis[$reason]['total_cost'] += $item['estimated_cost'];
        }
        
        // Generate recommendations based on top waste reasons
        arsort($reasonAnalysis);
        $topReasons = array_slice($reasonAnalysis, 0, 3, true);
        
        foreach ($topReasons as $reason => $data) {
            if ($reason == 'EXPIRED') {
                $recommendations[] = [
                    'type' => 'INVENTORY',
                    'priority' => 'HIGH',
                    'message' => 'High expired waste (' . $data['count'] . ' items). Implement FIFO inventory management and reduce stock levels.'
                ];
            } elseif ($reason == 'SPOILED') {
                $recommendations[] = [
                    'type' => 'STORAGE',
                    'priority' => 'MEDIUM',
                    'message' => 'High spoilage rate. Review storage conditions and temperature controls.'
                ];
            } elseif ($reason == 'PREPARATION_ERROR') {
                $recommendations[] = [
                    'type' => 'TRAINING',
                    'priority' => 'MEDIUM',
                    'message' => 'High preparation error waste. Provide additional staff training.'
                ];
            }
        }
        
        return $recommendations;
    }
}
