<?php

if (!class_exists('AIRepository')) {
    require_once __DIR__ . '/../Repositories/AIRepository.php';
}


class DynamicPricingService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new AIRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function generateDynamicPricing($tenantId, $branchId, $productId = null)
    {
        try {
            // Get sales data for demand analysis
            $salesData = $this->repository->getSalesData($tenantId, $branchId, date('Y-m-d', strtotime('-30 days')), date('Y-m-d'));
            
            // Get current inventory
            $inventory = $this->repository->getInventory($tenantId, $branchId);
            
            $pricingRecommendations = [];
            
            foreach ($inventory as $item) {
                if ($productId && $item['product_id'] != $productId) continue;
                
                // Calculate demand score
                $demandScore = $this->calculateDemandScore($item['product_id'], $salesData);
                
                // Calculate inventory pressure
                $inventoryPressure = $this->calculateInventoryPressure($item['quantity'], $item['min_stock'] ?? 10);
                
                // Calculate recommended price adjustment
                $priceAdjustment = $this->calculatePriceAdjustment($demandScore, $inventoryPressure);
                
                $currentPrice = $item['unit_cost'] * 1.5; // Default markup
                $recommendedPrice = $currentPrice * (1 + $priceAdjustment);
                
                $pricingRecommendations[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'current_price' => $currentPrice,
                    'recommended_price' => $recommendedPrice,
                    'price_adjustment_percent' => $priceAdjustment * 100,
                    'demand_score' => $demandScore,
                    'inventory_pressure' => $inventoryPressure,
                    'reason' => $this->getPricingReason($demandScore, $inventoryPressure)
                ];
            }

            // Save prediction
            $predictionData = [
                'recommendations' => $pricingRecommendations,
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->repository->savePrediction($tenantId, $branchId, 'DYNAMIC_PRICING', $predictionData, 75.0);

            return [
                'success' => true,
                'message' => 'Dynamic pricing recommendations generated',
                'data' => $pricingRecommendations
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to generate pricing: ' . $e->getMessage()
            ];
        }
    }

    private function calculateDemandScore($productId, $salesData)
    {
        $totalSales = 0;
        foreach ($salesData as $sale) {
            if ($sale['product_id'] == $productId) {
                $totalSales += $sale['quantity'];
            }
        }
        
        // Normalize to 0-100 scale
        return min(100, ($totalSales / 100) * 100);
    }

    private function calculateInventoryPressure($currentStock, $minStock)
    {
        if ($currentStock <= $minStock) {
            return 100; // High pressure - increase price
        } elseif ($currentStock <= $minStock * 2) {
            return 50; // Medium pressure
        } else {
            return 0; // Low pressure - could decrease price
        }
    }

    private function calculatePriceAdjustment($demandScore, $inventoryPressure)
    {
        // High demand + low inventory = increase price
        // Low demand + high inventory = decrease price
        $adjustment = ($demandScore - 50) * 0.002 + ($inventoryPressure - 50) * 0.003;
        
        // Cap adjustment at +/- 20%
        return max(-0.20, min(0.20, $adjustment));
    }

    private function getPricingReason($demandScore, $inventoryPressure)
    {
        if ($demandScore > 70 && $inventoryPressure > 70) {
            return 'High demand and low inventory - recommended price increase';
        } elseif ($demandScore < 30 && $inventoryPressure < 30) {
            return 'Low demand and high inventory - recommended price decrease';
        } elseif ($inventoryPressure > 70) {
            return 'Low inventory - recommended price increase';
        } elseif ($demandScore > 70) {
            return 'High demand - recommended price increase';
        } else {
            return 'Normal market conditions - maintain current price';
        }
    }
}
