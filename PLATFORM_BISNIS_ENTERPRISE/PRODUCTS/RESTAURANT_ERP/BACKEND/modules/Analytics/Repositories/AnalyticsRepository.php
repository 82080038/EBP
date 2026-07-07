<?php

namespace Modules\Analytics\Repositories;

use Core\Database;

class AnalyticsRepository
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->connect();
    }
    
    /**
     * Get daily sales summary
     */
    public function getDailySalesSummary($tenantId, $startDate, $endDate)
    {
        $sql = "SELECT 
                    DATE(o.created_at) as date,
                    COUNT(DISTINCT o.order_id) as order_count,
                    SUM(o.total_amount) as total_sales,
                    SUM(o.subtotal) as subtotal,
                    SUM(o.tax_amount) as tax_amount,
                    SUM(o.discount_amount) as discount_amount,
                    AVG(o.total_amount) as average_order_value
                FROM orders o
                WHERE o.tenant_id = :tenant_id
                AND o.created_at BETWEEN :start_date AND :end_date
                AND o.deleted_at IS NULL
                AND o.status IN ('COMPLETED', 'PAID')
                GROUP BY DATE(o.created_at)
                ORDER BY date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'start_date' => $startDate, 'end_date' => $endDate]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get hourly sales summary
     */
    public function getHourlySalesSummary($tenantId, $date)
    {
        $sql = "SELECT 
                    HOUR(o.created_at) as hour,
                    COUNT(DISTINCT o.order_id) as order_count,
                    SUM(o.total_amount) as total_sales
                FROM orders o
                WHERE o.tenant_id = :tenant_id
                AND DATE(o.created_at) = :date
                AND o.deleted_at IS NULL
                AND o.status IN ('COMPLETED', 'PAID')
                GROUP BY HOUR(o.created_at)
                ORDER BY hour ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'date' => $date]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get top selling products
     */
    public function getTopSellingProducts($tenantId, $startDate, $endDate, $limit = 10)
    {
        $sql = "SELECT 
                    p.product_id,
                    p.product_name,
                    p.product_code,
                    SUM(oi.quantity) as total_quantity,
                    SUM(oi.subtotal) as total_revenue,
                    COUNT(DISTINCT oi.order_id) as order_count
                FROM order_items oi
                INNER JOIN orders o ON oi.order_id = o.order_id
                INNER JOIN products p ON oi.product_id = p.product_id
                WHERE o.tenant_id = :tenant_id
                AND o.created_at BETWEEN :start_date AND :end_date
                AND o.deleted_at IS NULL
                AND o.status IN ('COMPLETED', 'PAID')
                GROUP BY p.product_id, p.product_name, p.product_code
                ORDER BY total_quantity DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'start_date' => $startDate, 'end_date' => $endDate, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get category performance
     */
    public function getCategoryPerformance($tenantId, $startDate, $endDate)
    {
        $sql = "SELECT 
                    c.category_id,
                    c.category_name,
                    COUNT(DISTINCT oi.order_id) as order_count,
                    SUM(oi.quantity) as total_quantity,
                    SUM(oi.subtotal) as total_revenue
                FROM order_items oi
                INNER JOIN orders o ON oi.order_id = o.order_id
                INNER JOIN products p ON oi.product_id = p.product_id
                INNER JOIN categories c ON p.category_id = c.category_id
                WHERE o.tenant_id = :tenant_id
                AND o.created_at BETWEEN :start_date AND :end_date
                AND o.deleted_at IS NULL
                AND o.status IN ('COMPLETED', 'PAID')
                GROUP BY c.category_id, c.category_name
                ORDER BY total_revenue DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'start_date' => $startDate, 'end_date' => $endDate]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get payment method breakdown
     */
    public function getPaymentMethodBreakdown($tenantId, $startDate, $endDate)
    {
        $sql = "SELECT 
                    p.payment_method,
                    COUNT(*) as transaction_count,
                    SUM(p.amount) as total_amount,
                    AVG(p.amount) as average_amount
                FROM payments p
                INNER JOIN orders o ON p.order_id = o.order_id
                WHERE o.tenant_id = :tenant_id
                AND p.created_at BETWEEN :start_date AND :end_date
                AND p.deleted_at IS NULL
                AND p.payment_status = 'COMPLETED'
                GROUP BY p.payment_method
                ORDER BY total_amount DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'start_date' => $startDate, 'end_date' => $endDate]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get order type breakdown
     */
    public function getOrderTypeBreakdown($tenantId, $startDate, $endDate)
    {
        $sql = "SELECT 
                    o.order_type,
                    COUNT(*) as order_count,
                    SUM(o.total_amount) as total_revenue,
                    AVG(o.total_amount) as average_order_value
                FROM orders o
                WHERE o.tenant_id = :tenant_id
                AND o.created_at BETWEEN :start_date AND :end_date
                AND o.deleted_at IS NULL
                AND o.status IN ('COMPLETED', 'PAID')
                GROUP BY o.order_type
                ORDER BY total_revenue DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'start_date' => $startDate, 'end_date' => $endDate]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get customer analytics
     */
    public function getCustomerAnalytics($tenantId, $startDate, $endDate)
    {
        $sql = "SELECT 
                    COUNT(DISTINCT o.customer_id) as unique_customers,
                    COUNT(DISTINCT CASE WHEN o.customer_id IS NOT NULL THEN o.order_id END) as customer_orders,
                    COUNT(DISTINCT CASE WHEN o.customer_id IS NULL THEN o.order_id END) as guest_orders,
                    AVG(CASE WHEN o.customer_id IS NOT NULL THEN o.total_amount END) as avg_customer_order,
                    AVG(CASE WHEN o.customer_id IS NULL THEN o.total_amount END) as avg_guest_order
                FROM orders o
                WHERE o.tenant_id = :tenant_id
                AND o.created_at BETWEEN :start_date AND :end_date
                AND o.deleted_at IS NULL
                AND o.status IN ('COMPLETED', 'PAID')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'start_date' => $startDate, 'end_date' => $endDate]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get table performance
     */
    public function getTablePerformance($tenantId, $startDate, $endDate)
    {
        $sql = "SELECT 
                    t.table_id,
                    t.table_number,
                    COUNT(DISTINCT o.order_id) as order_count,
                    SUM(o.total_amount) as total_revenue,
                    AVG(o.total_amount) as average_order_value,
                    AVG(TIMESTAMPDIFF(MINUTE, o.created_at, o.updated_at)) as avg_duration_minutes
                FROM orders o
                INNER JOIN tables t ON o.table_id = t.table_id
                WHERE o.tenant_id = :tenant_id
                AND o.created_at BETWEEN :start_date AND :end_date
                AND o.deleted_at IS NULL
                AND o.status IN ('COMPLETED', 'PAID')
                GROUP BY t.table_id, t.table_number
                ORDER BY total_revenue DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'start_date' => $startDate, 'end_date' => $endDate]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get staff performance
     */
    public function getStaffPerformance($tenantId, $startDate, $endDate)
    {
        $sql = "SELECT 
                    u.user_id,
                    u.full_name,
                    COUNT(DISTINCT o.order_id) as order_count,
                    SUM(o.total_amount) as total_revenue,
                    AVG(o.total_amount) as average_order_value
                FROM orders o
                INNER JOIN users u ON o.created_by = u.user_id
                WHERE o.tenant_id = :tenant_id
                AND o.created_at BETWEEN :start_date AND :end_date
                AND o.deleted_at IS NULL
                AND o.status IN ('COMPLETED', 'PAID')
                GROUP BY u.user_id, u.full_name
                ORDER BY total_revenue DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'start_date' => $startDate, 'end_date' => $endDate]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get revenue trends
     */
    public function getRevenueTrends($tenantId, $months = 12)
    {
        $sql = "SELECT 
                    DATE_FORMAT(o.created_at, '%Y-%m') as month,
                    SUM(o.total_amount) as total_revenue,
                    COUNT(DISTINCT o.order_id) as order_count,
                    AVG(o.total_amount) as average_order_value
                FROM orders o
                WHERE o.tenant_id = :tenant_id
                AND o.created_at >= DATE_SUB(NOW(), INTERVAL :months MONTH)
                AND o.deleted_at IS NULL
                AND o.status IN ('COMPLETED', 'PAID')
                GROUP BY DATE_FORMAT(o.created_at, '%Y-%m')
                ORDER BY month ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'months' => $months]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get comparison with previous period
     */
    public function getComparisonWithPrevious($tenantId, $startDate, $endDate)
    {
        $currentSql = "SELECT 
                        SUM(o.total_amount) as current_revenue,
                        COUNT(DISTINCT o.order_id) as current_orders,
                        AVG(o.total_amount) as current_avg_order
                      FROM orders o
                      WHERE o.tenant_id = :tenant_id
                      AND o.created_at BETWEEN :start_date AND :end_date
                      AND o.deleted_at IS NULL
                      AND o.status IN ('COMPLETED', 'PAID')";
        
        $stmt = $this->db->prepare($currentSql);
        $stmt->execute(['tenant_id' => $tenantId, 'start_date' => $startDate, 'end_date' => $endDate]);
        $current = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Calculate previous period (same duration, before current period)
        $daysDiff = (strtotime($endDate) - strtotime($startDate)) / 86400 + 1;
        $prevStartDate = date('Y-m-d', strtotime($startDate) - ($daysDiff * 86400));
        $prevEndDate = date('Y-m-d', strtotime($startDate) - 86400);
        
        $previousSql = "SELECT 
                         SUM(o.total_amount) as previous_revenue,
                         COUNT(DISTINCT o.order_id) as previous_orders,
                         AVG(o.total_amount) as previous_avg_order
                       FROM orders o
                       WHERE o.tenant_id = :tenant_id
                       AND o.created_at BETWEEN :start_date AND :end_date
                       AND o.deleted_at IS NULL
                       AND o.status IN ('COMPLETED', 'PAID')";
        
        $stmt = $this->db->prepare($previousSql);
        $stmt->execute(['tenant_id' => $tenantId, 'start_date' => $prevStartDate, 'end_date' => $prevEndDate]);
        $previous = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return [
            'current' => $current,
            'previous' => $previous,
            'revenue_growth' => $previous['previous_revenue'] > 0 
                ? (($current['current_revenue'] - $previous['previous_revenue']) / $previous['previous_revenue']) * 100 
                : 0,
            'order_growth' => $previous['previous_orders'] > 0 
                ? (($current['current_orders'] - $previous['previous_orders']) / $previous['previous_orders']) * 100 
                : 0
        ];
    }
}
