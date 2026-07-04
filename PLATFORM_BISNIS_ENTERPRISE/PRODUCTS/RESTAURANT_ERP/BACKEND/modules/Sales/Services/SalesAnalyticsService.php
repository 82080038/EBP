<?php

namespace App\Modules\Sales\Services;

use App\Modules\Sales\Models\SalesAggregate;
use App\Modules\Sales\Models\ProductSales;
use App\Modules\Sales\Models\CategorySales;
use App\Modules\Sales\Models\HourlySales;
use App\Modules\Sales\Models\SalesTarget;
use App\Core\Database;

class SalesAnalyticsService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get sales aggregates
     */
    public function getAggregates($restaurantId, $aggregateType, $dateFrom, $dateTo, $limit)
    {
        $aggregateModel = new SalesAggregate();
        return $aggregateModel->getByRestaurant($restaurantId, $aggregateType, $dateFrom, $dateTo, $limit);
    }

    /**
     * Get product sales
     */
    public function getProductSales($restaurantId, $aggregateType, $dateFrom, $dateTo, $limit)
    {
        $productSalesModel = new ProductSales();
        return $productSalesModel->getByRestaurant($restaurantId, $aggregateType, $dateFrom, $dateTo, $limit);
    }

    /**
     * Get category sales
     */
    public function getCategorySales($restaurantId, $aggregateType, $dateFrom, $dateTo, $limit)
    {
        $categorySalesModel = new CategorySales();
        return $categorySalesModel->getByRestaurant($restaurantId, $aggregateType, $dateFrom, $dateTo, $limit);
    }

    /**
     * Get hourly sales
     */
    public function getHourlySales($restaurantId, $date)
    {
        $hourlySalesModel = new HourlySales();
        return $hourlySalesModel->getByDate($restaurantId, $date);
    }

    /**
     * Get sales targets
     */
    public function getSalesTargets($restaurantId, $status)
    {
        $targetModel = new SalesTarget();
        return $targetModel->getByRestaurant($restaurantId, $status);
    }

    /**
     * Create sales target
     */
    public function createSalesTarget($restaurantId, $userId, $data)
    {
        $targetModel = new SalesTarget();
        
        $targetData = [
            'restaurant_id' => $restaurantId,
            'target_name' => $data->target_name,
            'target_description' => $data->target_description ?? null,
            'target_type' => $data->target_type,
            'target_period_start' => $data->target_period_start,
            'target_period_end' => $data->target_period_end,
            'revenue_target' => $data->revenue_target,
            'order_target' => $data->order_target ?? null,
            'profit_target' => $data->profit_target ?? null,
            'target_status' => 'active',
            'created_by' => $userId
        ];
        
        $targetId = $targetModel->create($targetData);
        
        if (!$targetId) {
            return ['success' => false, 'message' => 'Failed to create sales target'];
        }
        
        return ['success' => true, 'message' => 'Sales target created', 'target_id' => $targetId];
    }

    /**
     * Get sales trends
     */
    public function getSalesTrends($restaurantId, $trendType, $trendPeriod, $limit)
    {
        $sql = "SELECT * FROM sales_trends 
                WHERE restaurant_id = ? AND trend_type = ? AND trend_period = ?
                ORDER BY period_start DESC
                LIMIT ?";
        
        return $this->db->query($sql, [$restaurantId, $trendType, $trendPeriod, $limit])->fetchAll();
    }

    /**
     * Get summary
     */
    public function getSummary($restaurantId, $dateFrom, $dateTo)
    {
        $aggregateModel = new SalesAggregate();
        
        // Get daily aggregates for the period
        $aggregates = $aggregateModel->getByRestaurant($restaurantId, 'daily', $dateFrom, $dateTo, 365);
        
        $totalRevenue = 0;
        $totalOrders = 0;
        $totalProfit = 0;
        
        foreach ($aggregates as $agg) {
            $totalRevenue += $agg['total_revenue'];
            $totalOrders += $agg['total_orders'];
            $totalProfit += $agg['gross_profit'];
        }
        
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        
        // Get top products
        $productSalesModel = new ProductSales();
        $topProducts = $productSalesModel->getTopProducts($restaurantId, $dateFrom, $dateTo, 5);
        
        // Get top categories
        $categorySalesModel = new CategorySales();
        $topCategories = $categorySalesModel->getTopCategories($restaurantId, $dateFrom, $dateTo, 5);
        
        return [
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'total_profit' => $totalProfit,
            'average_order_value' => $avgOrderValue,
            'top_products' => $topProducts,
            'top_categories' => $topCategories
        ];
    }
}
