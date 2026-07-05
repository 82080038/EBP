<?php

declare(strict_types=1);

namespace Modules\Menu\Services;

use PDO;

class MenuEngineeringReportService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getMenuPerformanceReport(int $tenantId, ?int $branchId = null, string $startDate = null, string $endDate = null): array
    {
        $sql = "SELECT 
                    p.id,
                    p.name,
                    p.base_price,
                    p.cost_price,
                    COUNT(DISTINCT oi.order_id) as order_count,
                    SUM(oi.quantity) as total_quantity,
                    SUM(oi.quantity * oi.unit_price) as total_revenue,
                    SUM(oi.quantity * p.cost_price) as total_cost,
                    SUM(oi.quantity * (oi.unit_price - p.cost_price)) as total_profit,
                    AVG(oi.unit_price) as avg_selling_price,
                    (SUM(oi.quantity * (oi.unit_price - p.cost_price)) / NULLIF(SUM(oi.quantity * oi.unit_price), 0)) * 100 as profit_margin,
                    (SUM(oi.quantity) / NULLIF(SUM(DISTINCT oi.order_id), 0)) as avg_quantity_per_order
                FROM products p
                LEFT JOIN order_items oi ON p.id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.id
                WHERE p.tenant_id = :tenant_id
                AND p.deleted_at IS NULL";
        
        $params = [':tenant_id' => $tenantId];

        if ($branchId !== null) {
            $sql .= " AND (p.branch_id IS NULL OR p.branch_id = :branch_id)";
            $params[':branch_id'] = $branchId;
        }

        if ($startDate !== null) {
            $sql .= " AND o.created_at >= :start_date";
            $params[':start_date'] = $startDate;
        }

        if ($endDate !== null) {
            $sql .= " AND o.created_at <= :end_date";
            $params[':end_date'] = $endDate;
        }

        $sql .= " GROUP BY p.id, p.name, p.base_price, p.cost_price
                  ORDER BY total_revenue DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoryPerformanceReport(int $tenantId, ?int $branchId = null, string $startDate = null, string $endDate = null): array
    {
        $sql = "SELECT 
                    c.id,
                    c.name as category_name,
                    COUNT(DISTINCT p.id) as product_count,
                    COUNT(DISTINCT oi.order_id) as order_count,
                    SUM(oi.quantity) as total_quantity,
                    SUM(oi.quantity * oi.unit_price) as total_revenue,
                    SUM(oi.quantity * p.cost_price) as total_cost,
                    SUM(oi.quantity * (oi.unit_price - p.cost_price)) as total_profit,
                    (SUM(oi.quantity * (oi.unit_price - p.cost_price)) / NULLIF(SUM(oi.quantity * oi.unit_price), 0)) * 100 as profit_margin
                FROM categories c
                LEFT JOIN products p ON c.id = p.category_id AND p.tenant_id = :tenant_id AND p.deleted_at IS NULL
                LEFT JOIN order_items oi ON p.id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.id
                WHERE c.tenant_id = :tenant_id
                AND c.deleted_at IS NULL";
        
        $params = [':tenant_id' => $tenantId];

        if ($branchId !== null) {
            $sql .= " AND (p.branch_id IS NULL OR p.branch_id = :branch_id)";
            $params[':branch_id'] = $branchId;
        }

        if ($startDate !== null) {
            $sql .= " AND o.created_at >= :start_date";
            $params[':start_date'] = $startDate;
        }

        if ($endDate !== null) {
            $sql .= " AND o.created_at <= :end_date";
            $params[':end_date'] = $endDate;
        }

        $sql .= " GROUP BY c.id, c.name
                  ORDER BY total_revenue DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMenuMixReport(int $tenantId, ?int $branchId = null, string $startDate = null, string $endDate = null): array
    {
        $sql = "SELECT 
                    p.id,
                    p.name,
                    c.name as category_name,
                    p.base_price,
                    p.cost_price,
                    (p.base_price - p.cost_price) as contribution_margin,
                    COUNT(DISTINCT oi.order_id) as order_count,
                    SUM(oi.quantity) as total_quantity,
                    SUM(oi.quantity * oi.unit_price) as total_revenue,
                    (SUM(oi.quantity * oi.unit_price) / (SELECT SUM(oi2.quantity * oi2.unit_price) 
                     FROM order_items oi2 
                     JOIN orders o2 ON oi2.order_id = o2.id 
                     WHERE o2.tenant_id = :tenant_id)) * 100 as revenue_percentage
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN order_items oi ON p.id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.id
                WHERE p.tenant_id = :tenant_id
                AND p.deleted_at IS NULL";
        
        $params = [':tenant_id' => $tenantId];

        if ($branchId !== null) {
            $sql .= " AND (p.branch_id IS NULL OR p.branch_id = :branch_id)";
            $params[':branch_id'] = $branchId;
        }

        if ($startDate !== null) {
            $sql .= " AND o.created_at >= :start_date";
            $params[':start_date'] = $startDate;
        }

        if ($endDate !== null) {
            $sql .= " AND o.created_at <= :end_date";
            $params[':end_date'] = $endDate;
        }

        $sql .= " GROUP BY p.id, p.name, c.name, p.base_price, p.cost_price
                  HAVING total_revenue > 0
                  ORDER BY revenue_percentage DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMenuProfitabilityReport(int $tenantId, ?int $branchId = null, string $startDate = null, string $endDate = null): array
    {
        $sql = "SELECT 
                    p.id,
                    p.name,
                    c.name as category_name,
                    p.base_price,
                    p.cost_price,
                    (p.base_price - p.cost_price) as contribution_margin,
                    ((p.base_price - p.cost_price) / NULLIF(p.base_price, 0)) * 100 as contribution_margin_percentage,
                    COUNT(DISTINCT oi.order_id) as order_count,
                    SUM(oi.quantity) as total_quantity,
                    SUM(oi.quantity * oi.unit_price) as total_revenue,
                    SUM(oi.quantity * p.cost_price) as total_cost,
                    SUM(oi.quantity * (oi.unit_price - p.cost_price)) as total_profit,
                    (SUM(oi.quantity * (oi.unit_price - p.cost_price)) / NULLIF(SUM(oi.quantity * oi.unit_price), 0)) * 100 as profit_margin,
                    CASE 
                        WHEN (SUM(oi.quantity * (oi.unit_price - p.cost_price)) / NULLIF(SUM(oi.quantity * oi.unit_price), 0)) * 100 >= 70 THEN 'STAR'
                        WHEN (SUM(oi.quantity * (oi.unit_price - p.cost_price)) / NULLIF(SUM(oi.quantity * oi.unit_price), 0)) * 100 >= 50 THEN 'HIGH'
                        WHEN (SUM(oi.quantity * (oi.unit_price - p.cost_price)) / NULLIF(SUM(oi.quantity * oi.unit_price), 0)) * 100 >= 30 THEN 'MEDIUM'
                        ELSE 'LOW'
                    END as profitability_class
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN order_items oi ON p.id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.id
                WHERE p.tenant_id = :tenant_id
                AND p.deleted_at IS NULL";
        
        $params = [':tenant_id' => $tenantId];

        if ($branchId !== null) {
            $sql .= " AND (p.branch_id IS NULL OR p.branch_id = :branch_id)";
            $params[':branch_id'] = $branchId;
        }

        if ($startDate !== null) {
            $sql .= " AND o.created_at >= :start_date";
            $params[':start_date'] = $startDate;
        }

        if ($endDate !== null) {
            $sql .= " AND o.created_at <= :end_date";
            $params[':end_date'] = $endDate;
        }

        $sql .= " GROUP BY p.id, p.name, c.name, p.base_price, p.cost_price
                  HAVING total_revenue > 0
                  ORDER BY profit_margin DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMenuTrendReport(int $tenantId, ?int $branchId = null, string $startDate = null, string $endDate = null): array
    {
        $sql = "SELECT 
                    DATE(o.created_at) as date,
                    COUNT(DISTINCT oi.order_id) as order_count,
                    SUM(oi.quantity) as total_quantity,
                    SUM(oi.quantity * oi.unit_price) as total_revenue,
                    COUNT(DISTINCT p.id) as unique_products_sold
                FROM orders o
                JOIN order_items oi ON o.id = oi.order_id
                JOIN products p ON oi.product_id = p.id
                WHERE o.tenant_id = :tenant_id
                AND o.deleted_at IS NULL";
        
        $params = [':tenant_id' => $tenantId];

        if ($branchId !== null) {
            $sql .= " AND o.branch_id = :branch_id";
            $params[':branch_id'] = $branchId;
        }

        if ($startDate !== null) {
            $sql .= " AND o.created_at >= :start_date";
            $params[':start_date'] = $startDate;
        }

        if ($endDate !== null) {
            $sql .= " AND o.created_at <= :end_date";
            $params[':end_date'] = $endDate;
        }

        $sql .= " GROUP BY DATE(o.created_at)
                  ORDER BY date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMenuOptimizationRecommendations(int $tenantId, ?int $branchId = null): array
    {
        $performance = $this->getMenuPerformanceReport($tenantId, $branchId);
        $recommendations = [];

        foreach ($performance as $item) {
            $profitMargin = (float)$item['profit_margin'];
            $orderCount = (int)$item['order_count'];
            $totalRevenue = (float)$item['total_revenue'];

            // High margin, low volume - promote more
            if ($profitMargin > 60 && $orderCount < 10) {
                $recommendations[] = [
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'recommendation' => 'PROMOTE',
                    'reason' => 'High profit margin (' . number_format($profitMargin, 1) . '%) but low order count',
                    'action' => 'Increase visibility through promotions or featured placement'
                ];
            }

            // Low margin, high volume - consider price increase or cost reduction
            if ($profitMargin < 30 && $orderCount > 50) {
                $recommendations[] = [
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'recommendation' => 'OPTIMIZE',
                    'reason' => 'Low profit margin (' . number_format($profitMargin, 1) . '%) despite high order count',
                    'action' => 'Consider price increase or cost reduction through supplier negotiation'
                ];
            }

            // Low margin, low volume - consider discontinuation
            if ($profitMargin < 20 && $orderCount < 5) {
                $recommendations[] = [
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'recommendation' => 'REVIEW',
                    'reason' => 'Low profit margin (' . number_format($profitMargin, 1) . '%) and low order count',
                    'action' => 'Consider discontinuation or menu repositioning'
                ];
            }

            // High margin, high volume - star item
            if ($profitMargin > 50 && $orderCount > 30) {
                $recommendations[] = [
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'recommendation' => 'MAINTAIN',
                    'reason' => 'Star item with high profit margin (' . number_format($profitMargin, 1) . '%) and high order count',
                    'action' => 'Maintain current positioning and ensure consistent quality'
                ];
            }
        }

        return $recommendations;
    }
}
