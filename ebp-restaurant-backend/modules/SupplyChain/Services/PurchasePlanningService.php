<?php

if (!class_exists('PurchasePlanningRepository')) {
    require_once __DIR__ . '/../Repositories/PurchasePlanningRepository.php';
}
if (!class_exists('database')) {
    require_once __DIR__ . '/../../../config/database.php';
}

class PurchasePlanningService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new PurchasePlanningRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function generatePurchasePlan($tenantId, $branchId, $planningDate)
    {
        try {
            // Get inventory items with low stock
            $lowStockItems = $this->repository->getLowStockItems($tenantId, $branchId);
            
            // Get sales history for demand forecasting
            $salesHistory = $this->repository->getSalesHistory($tenantId, $branchId, date('Y-m-d', strtotime('-30 days')), date('Y-m-d'));
            
            $planItems = [];
            
            foreach ($lowStockItems as $item) {
                // Calculate suggested order quantity based on usage rate
                $avgDailyUsage = $this->calculateAvgDailyUsage($item['product_id'], $salesHistory);
                $daysOfStock = $item['quantity'] / ($avgDailyUsage > 0 ? $avgDailyUsage : 1);
                
                if ($daysOfStock < 7) { // Less than 7 days of stock
                    $suggestedQty = max($item['max_stock'] - $item['quantity'], ceil($avgDailyUsage * 14)); // Order for 14 days or to max stock
                    
                    $planItems[] = [
                        'product_id' => $item['product_id'],
                        'product_name' => $item['product_name'],
                        'current_stock' => $item['quantity'],
                        'min_stock' => $item['min_stock'],
                        'max_stock' => $item['max_stock'],
                        'avg_daily_usage' => $avgDailyUsage,
                        'days_of_stock' => $daysOfStock,
                        'suggested_quantity' => $suggestedQty,
                        'priority' => $daysOfStock < 3 ? 'URGENT' : 'NORMAL'
                    ];
                }
            }

            // Save the plan
            $planId = $this->repository->createPurchasePlan([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'planning_date' => $planningDate,
                'plan_status' => 'DRAFT'
            ]);

            foreach ($planItems as $item) {
                $this->repository->addPlanItem([
                    'plan_id' => $planId,
                    'product_id' => $item['product_id'],
                    'suggested_quantity' => $item['suggested_quantity'],
                    'current_stock' => $item['current_stock'],
                    'priority' => $item['priority']
                ]);
            }

            return [
                'success' => true,
                'message' => 'Purchase plan generated successfully',
                'plan_id' => $planId,
                'items' => $planItems
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to generate purchase plan: ' . $e->getMessage()
            ];
        }
    }

    public function approvePurchasePlan($planId, $tenantId, $userId)
    {
        try {
            $plan = $this->repository->getPurchasePlan($planId, $tenantId);
            
            if (!$plan) {
                return [
                    'success' => false,
                    'message' => 'Purchase plan not found'
                ];
            }

            if ($plan['plan_status'] !== 'DRAFT') {
                return [
                    'success' => false,
                    'message' => 'Plan can only be approved from DRAFT status'
                ];
            }

            $this->repository->updatePlanStatus($planId, 'APPROVED', $userId);

            // Create purchase requisition from plan
            $planItems = $this->repository->getPlanItems($planId);
            
            $requisitionData = [
                'requisition_number' => 'PR-' . date('Ymd') . '-' . rand(1000, 9999),
                'requisition_date' => date('Y-m-d'),
                'requested_by' => $userId,
                'notes' => 'Generated from purchase plan #' . $planId
            ];

            $requisitionId = $this->repository->createPurchaseRequisition($tenantId, $plan['branch_id'], $requisitionData);

            foreach ($planItems as $item) {
                $this->repository->addRequisitionItem([
                    'requisition_id' => $requisitionId,
                    'product_id' => $item['product_id'],
                    'requested_quantity' => $item['suggested_quantity'],
                    'notes' => 'Priority: ' . $item['priority']
                ]);
            }

            return [
                'success' => true,
                'message' => 'Purchase plan approved and requisition created',
                'requisition_id' => $requisitionId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to approve plan: ' . $e->getMessage()
            ];
        }
    }

    public function getPurchasePlans($tenantId, $branchId, $status = null)
    {
        try {
            $plans = $this->repository->getPurchasePlans($tenantId, $branchId, $status);
            
            return [
                'success' => true,
                'message' => 'Purchase plans retrieved successfully',
                'data' => $plans
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get plans: ' . $e->getMessage()
            ];
        }
    }

    private function calculateAvgDailyUsage($productId, $salesHistory)
    {
        $totalUsage = 0;
        $days = 30;

        foreach ($salesHistory as $sale) {
            if ($sale['product_id'] == $productId) {
                $totalUsage += $sale['quantity'];
            }
        }

        return $totalUsage / $days;
    }
}
