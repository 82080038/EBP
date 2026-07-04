<?php

namespace App\Modules\Procurement\Services;

use App\Modules\Procurement\Models\ProcurementSpend;
use App\Modules\Procurement\Models\SupplierSpend;
use App\Modules\Procurement\Models\CategorySpend;
use App\Modules\Procurement\Models\ProcurementTarget;
use App\Core\Database;

class ProcurementAnalyticsService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get procurement spend
     */
    public function getSpend($restaurantId, $periodType, $dateFrom, $dateTo, $limit)
    {
        $spendModel = new ProcurementSpend();
        return $spendModel->getByRestaurant($restaurantId, $periodType, $dateFrom, $dateTo, $limit);
    }

    /**
     * Get supplier spend
     */
    public function getSupplierSpend($restaurantId, $periodType, $dateFrom, $dateTo, $limit)
    {
        $supplierSpendModel = new SupplierSpend();
        return $supplierSpendModel->getByRestaurant($restaurantId, $periodType, $dateFrom, $dateTo, $limit);
    }

    /**
     * Get category spend
     */
    public function getCategorySpend($restaurantId, $periodType, $dateFrom, $dateTo, $limit)
    {
        $categorySpendModel = new CategorySpend();
        return $categorySpendModel->getByRestaurant($restaurantId, $periodType, $dateFrom, $dateTo, $limit);
    }

    /**
     * Get targets
     */
    public function getTargets($restaurantId, $category, $status)
    {
        $targetModel = new ProcurementTarget();
        return $targetModel->getByRestaurant($restaurantId, $category, $status);
    }

    /**
     * Create target
     */
    public function createTarget($restaurantId, $userId, $data)
    {
        $targetModel = new ProcurementTarget();
        
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
            'created_by' => $userId
        ];
        
        $targetId = $targetModel->create($targetData);
        
        if (!$targetId) {
            return ['success' => false, 'message' => 'Failed to create target'];
        }
        
        return ['success' => true, 'message' => 'Target created', 'target_id' => $targetId];
    }

    /**
     * Get cost variance
     */
    public function getCostVariance($restaurantId, $dateFrom, $dateTo, $limit)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($dateFrom) {
            $where .= " AND variance_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND variance_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT cv.*, ii.item_name, s.supplier_name 
                FROM cost_variance cv
                LEFT JOIN inventory_items ii ON cv.inventory_item_id = ii.id
                LEFT JOIN suppliers s ON cv.supplier_id = s.id
                {$where}
                ORDER BY cv.variance_date DESC
                LIMIT ?";
        $params[] = $limit;
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get summary
     */
    public function getSummary($restaurantId, $dateFrom, $dateTo)
    {
        $spendModel = new ProcurementSpend();
        $supplierSpendModel = new SupplierSpend();
        
        // Get latest spend
        $latestSpend = $spendModel->getLatest($restaurantId);
        
        // Get top suppliers
        $topSuppliers = $supplierSpendModel->getTopSpenders($restaurantId, $dateFrom, $dateTo, 5);
        
        // Get category breakdown
        $categorySpendModel = new CategorySpend();
        $categoryBreakdown = $categorySpendModel->getByRestaurant($restaurantId, 'monthly', $dateFrom, $dateTo, 10);
        
        // Get active targets
        $targetModel = new ProcurementTarget();
        $activeTargets = $targetModel->getActive($restaurantId);
        
        return [
            'latest_spend' => $latestSpend,
            'top_suppliers' => $topSuppliers,
            'category_breakdown' => $categoryBreakdown,
            'active_targets' => $activeTargets
        ];
    }
}
