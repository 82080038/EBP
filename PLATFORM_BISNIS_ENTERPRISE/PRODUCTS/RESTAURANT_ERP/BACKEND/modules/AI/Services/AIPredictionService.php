<?php

if (!class_exists('AIPredictionRepository')) {
    require_once __DIR__ . '/../Repositories/AIPredictionRepository.php';
}


class AIPredictionService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new AIPredictionRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function generateSalesForecast($tenantId, $branchId, $days = 7)
    {
        try {
            $predictions = [];
            $historicalData = $this->getHistoricalSales($tenantId, $branchId, 30);
            
            if (count($historicalData) < 7) {
                // Not enough data, use simple average
                $avgDailySales = $this->getAverageDailySales($tenantId, $branchId);
                for ($i = 1; $i <= $days; $i++) {
                    $date = date('Y-m-d', strtotime("+$i days"));
                    $predictions[] = [
                        'date' => $date,
                        'predicted_revenue' => $avgDailySales,
                        'confidence_score' => 0.5
                    ];
                }
            } else {
                // Simple moving average forecast
                $avgSales = array_sum(array_column($historicalData, 'total_revenue')) / count($historicalData);
                $trend = $this->calculateTrend($historicalData);
                
                for ($i = 1; $i <= $days; $i++) {
                    $date = date('Y-m-d', strtotime("+$i days"));
                    $predictedRevenue = $avgSales + ($trend * $i);
                    $predictions[] = [
                        'date' => $date,
                        'predicted_revenue' => max(0, $predictedRevenue),
                        'confidence_score' => 0.7
                    ];
                }
            }

            // Save predictions
            foreach ($predictions as $pred) {
                $this->repository->create([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'prediction_type' => 'SALES_FORECAST',
                    'prediction_date' => $pred['date'],
                    'prediction_data' => json_encode(['predicted_revenue' => $pred['predicted_revenue']]),
                    'confidence_score' => $pred['confidence_score']
                ]);
            }

            return [
                'success' => true,
                'message' => 'Sales forecast generated successfully',
                'data' => $predictions
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to generate forecast: ' . $e->getMessage()
            ];
        }
    }

    public function generateInventoryPrediction($tenantId, $branchId, $inventoryId)
    {
        try {
            $currentStock = $this->getCurrentStock($tenantId, $branchId, $inventoryId);
            $dailyUsage = $this->getDailyUsage($tenantId, $branchId, $inventoryId, 30);
            
            if ($dailyUsage > 0) {
                $daysUntilEmpty = $currentStock / $dailyUsage;
                $reorderDate = date('Y-m-d', strtotime("-" . ($daysUntilEmpty * 0.7) . " days"));
                $recommendedQuantity = ceil($dailyUsage * 7); // 7 days supply
            } else {
                $daysUntilEmpty = 999;
                $reorderDate = null;
                $recommendedQuantity = 0;
            }

            $prediction = [
                'inventory_id' => $inventoryId,
                'current_stock' => $currentStock,
                'daily_usage' => $dailyUsage,
                'days_until_empty' => $daysUntilEmpty,
                'reorder_date' => $reorderDate,
                'recommended_quantity' => $recommendedQuantity
            ];

            return [
                'success' => true,
                'message' => 'Inventory prediction generated successfully',
                'data' => $prediction
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to generate prediction: ' . $e->getMessage()
            ];
        }
    }

    private function getHistoricalSales($tenantId, $branchId, $days)
    {
        $sql = "SELECT DATE(created_at) as date, SUM(total_amount) as total_revenue 
                FROM orders 
                WHERE tenant_id = ? AND branch_id = ? 
                AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                AND deleted_at IS NULL
                GROUP BY DATE(created_at) ORDER BY date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getAverageDailySales($tenantId, $branchId)
    {
        $sql = "SELECT AVG(total_amount) as avg_sales 
                FROM orders 
                WHERE tenant_id = ? AND branch_id = ? 
                AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['avg_sales'] ?? 0;
    }

    private function calculateTrend($data)
    {
        if (count($data) < 2) return 0;
        
        $first = $data[0]['total_revenue'];
        $last = $data[count($data) - 1]['total_revenue'];
        return ($last - $first) / count($data);
    }

    private function getCurrentStock($tenantId, $branchId, $inventoryId)
    {
        $sql = "SELECT quantity FROM inventory WHERE tenant_id = ? AND branch_id = ? AND inventory_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $inventoryId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['quantity'] ?? 0;
    }

    private function getDailyUsage($tenantId, $branchId, $inventoryId, $days)
    {
        // Simplified - would need recipe data for accurate calculation
        $sql = "SELECT AVG(quantity) as avg_usage 
                FROM stock_adjustments 
                WHERE tenant_id = ? AND branch_id = ? 
                AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                AND adjustment_type = 'OUT'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $days]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['avg_usage'] ?? 0;
    }
}
