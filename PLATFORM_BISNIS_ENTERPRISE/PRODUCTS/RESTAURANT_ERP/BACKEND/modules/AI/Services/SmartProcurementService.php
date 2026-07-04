<?php

if (!class_exists('AIRepository')) {
    require_once __DIR__ . '/../Repositories/AIRepository.php';
}


class SmartProcurementService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new AIRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function generateProcurementRecommendation($tenantId, $branchId, $forecastDays = 30)
    {
        try {
            // Get sales history for demand forecasting
            $salesHistory = $this->repository->getSalesHistory($tenantId, $branchId, date('Y-m-d', strtotime('-90 days')), date('Y-m-d'));
            
            // Get current inventory
            $inventory = $this->repository->getInventory($tenantId, $branchId);
            
            // Get supplier performance
            $supplierPerformance = $this->repository->getSupplierPerformance($tenantId);
            
            $recommendations = [];
            
            foreach ($inventory as $item) {
                // Calculate average daily usage
                $avgDailyUsage = $this->calculateAvgDailyUsage($item['product_id'], $salesHistory);
                
                // Calculate days of stock remaining
                $daysOfStock = $item['quantity'] / ($avgDailyUsage > 0 ? $avgDailyUsage : 1);
                
                // Generate recommendation if stock is low
                if ($daysOfStock < 14) {
                    $suggestedQty = ceil($avgDailyUsage * $forecastDays);
                    
                    // Find best supplier for this product
                    $bestSupplier = $this->findBestSupplier($item['product_id'], $supplierPerformance);
                    
                    $recommendations[] = [
                        'product_id' => $item['product_id'],
                        'product_name' => $item['product_name'],
                        'current_stock' => $item['quantity'],
                        'avg_daily_usage' => $avgDailyUsage,
                        'days_of_stock' => $daysOfStock,
                        'suggested_quantity' => $suggestedQty,
                        'priority' => $daysOfStock < 7 ? 'URGENT' : 'NORMAL',
                        'recommended_supplier' => $bestSupplier,
                        'estimated_cost' => $this->estimateCost($item['product_id'], $suggestedQty, $tenantId),
                        'confidence_score' => $this->calculateConfidence($avgDailyUsage, count($salesHistory))
                    ];
                }
            }

            // Save prediction
            $predictionData = [
                'recommendations' => $recommendations,
                'forecast_days' => $forecastDays,
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->repository->savePrediction($tenantId, $branchId, 'PROCUREMENT_RECOMMENDATION', $predictionData, 85.0);

            return [
                'success' => true,
                'message' => 'Procurement recommendations generated successfully',
                'data' => $recommendations
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to generate recommendations: ' . $e->getMessage()
            ];
        }
    }

    private function calculateAvgDailyUsage($productId, $salesHistory)
    {
        $totalUsage = 0;
        foreach ($salesHistory as $sale) {
            if ($sale['product_id'] == $productId) {
                $totalUsage += $sale['quantity'];
            }
        }
        return $totalUsage / 90; // 90 days history
    }

    private function findBestSupplier($productId, $supplierPerformance)
    {
        // Simple logic: return supplier with highest overall rating
        if (empty($supplierPerformance)) {
            return null;
        }
        
        usort($supplierPerformance, function($a, $b) {
            return ($b['avg_rating'] ?? 0) - ($a['avg_rating'] ?? 0);
        });
        
        return $supplierPerformance[0]['supplier_name'] ?? null;
    }

    private function estimateCost($productId, $quantity, $tenantId)
    {
        // Get average cost from inventory
        $stmt = $this->db->prepare("SELECT AVG(unit_cost) as avg_cost FROM inventory WHERE product_id = ? AND tenant_id = ?");
        $stmt->execute([$productId, $tenantId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $avgCost = $result['avg_cost'] ?? 0;
        return $avgCost * $quantity;
    }

    private function calculateConfidence($avgDailyUsage, $dataPoints)
    {
        // Higher confidence with more data points and consistent usage
        if ($dataPoints < 10) return 50;
        if ($avgDailyUsage < 1) return 60;
        return min(95, 60 + ($dataPoints / 10) * 5);
    }
}
