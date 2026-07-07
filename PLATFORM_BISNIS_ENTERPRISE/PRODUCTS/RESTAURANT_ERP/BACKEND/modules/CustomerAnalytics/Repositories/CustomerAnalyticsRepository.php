<?php

namespace Modules\CustomerAnalytics\Repositories;

use Core\Database;

class CustomerAnalyticsRepository
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->connect();
    }
    
    /**
     * Get customer behavior analytics
     */
    public function getCustomerBehavior($tenantId, $customerId, $startDate, $endDate)
    {
        $sql = "SELECT 
                    COUNT(DISTINCT o.order_id) as total_orders,
                    SUM(o.total_amount) as total_spent,
                    AVG(o.total_amount) as average_order_value,
                    COUNT(DISTINCT DATE(o.created_at)) as visit_frequency,
                    COUNT(DISTINCT o.table_id) as table_variety,
                    COUNT(DISTINCT o.order_type) as order_type_variety
                FROM orders o
                WHERE o.customer_id = :customer_id
                AND o.tenant_id = :tenant_id
                AND o.created_at BETWEEN :start_date AND :end_date
                AND o.deleted_at IS NULL
                AND o.status IN ('COMPLETED', 'PAID')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customer_id' => $customerId, 'tenant_id' => $tenantId, 'start_date' => $startDate, 'end_date' => $endDate]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get customer cohort analysis
     */
    public function getCohortAnalysis($tenantId, $startDate, $endDate)
    {
        $sql = "SELECT 
                    DATE_FORMAT(MIN(o.created_at), '%Y-%m') as cohort_month,
                    c.customer_id,
                    COUNT(DISTINCT o.order_id) as orders_in_month,
                    SUM(o.total_amount) as spent_in_month
                FROM customers c
                INNER JOIN orders o ON c.customer_id = o.customer_id
                WHERE c.tenant_id = :tenant_id
                AND c.created_at BETWEEN :start_date AND :end_date
                AND o.deleted_at IS NULL
                AND o.status IN ('COMPLETED', 'PAID')
                GROUP BY c.customer_id, DATE_FORMAT(MIN(o.created_at), '%Y-%m'), DATE_FORMAT(o.created_at, '%Y-%m')
                ORDER BY cohort_month, c.customer_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'start_date' => $startDate, 'end_date' => $endDate]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get customer journey mapping
     */
    public function getCustomerJourney($tenantId, $customerId)
    {
        $sql = "SELECT 
                    o.order_id,
                    o.order_number,
                    o.order_type,
                    o.created_at,
                    o.total_amount,
                    t.table_number,
                    p.product_name,
                    oi.quantity
                FROM orders o
                LEFT JOIN tables t ON o.table_id = t.table_id
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                LEFT JOIN products p ON oi.product_id = p.product_id
                WHERE o.customer_id = :customer_id
                AND o.tenant_id = :tenant_id
                AND o.deleted_at IS NULL
                ORDER BY o.created_at ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customer_id' => $customerId, 'tenant_id' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get customer segment
     */
    public function getCustomerSegment($tenantId, $customerId)
    {
        $sql = "SELECT cs.*, s.segment_name, s.segment_description
                FROM customer_segments cs
                INNER JOIN segments s ON cs.segment_id = s.segment_id
                WHERE cs.customer_id = :customer_id
                AND cs.tenant_id = :tenantId
                AND cs.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customer_id' => $customerId, 'tenantId' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get customer lifetime value
     */
    public function getCustomerLifetimeValue($tenantId, $customerId)
    {
        $sql = "SELECT 
                    c.customer_id,
                    c.name,
                    c.created_at as customer_since,
                    COUNT(DISTINCT o.order_id) as total_orders,
                    SUM(o.total_amount) as total_lifetime_value,
                    AVG(o.total_amount) as average_order_value,
                    DATEDIFF(NOW(), c.created_at) as days_as_customer,
                    CASE 
                        WHEN DATEDIFF(NOW(), c.created_at) > 0 
                        THEN SUM(o.total_amount) / DATEDIFF(NOW(), c.created_at) 
                        ELSE 0 
                    END as daily_value
                FROM customers c
                LEFT JOIN orders o ON c.customer_id = o.customer_id 
                    AND o.status IN ('COMPLETED', 'PAID')
                    AND o.deleted_at IS NULL
                WHERE c.customer_id = :customer_id
                AND c.tenant_id = :tenant_id
                AND c.deleted_at IS NULL
                GROUP BY c.customer_id, c.name, c.created_at";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customer_id' => $customerId, 'tenant_id' => $tenantId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get customer retention rate
     */
    public function getRetentionRate($tenantId, $startDate, $endDate)
    {
        $sql = "SELECT 
                    COUNT(DISTINCT c.customer_id) as total_customers,
                    COUNT(DISTINCT CASE 
                        WHEN o.order_id IS NOT NULL THEN c.customer_id 
                    END) as returning_customers,
                    (COUNT(DISTINCT CASE 
                        WHEN o.order_id IS NOT NULL THEN c.customer_id 
                    END) * 100.0 / COUNT(DISTINCT c.customer_id)) as retention_rate
                FROM customers c
                LEFT JOIN orders o ON c.customer_id = o.customer_id 
                    AND o.created_at BETWEEN :start_date AND :end_date
                    AND o.status IN ('COMPLETED', 'PAID')
                    AND o.deleted_at IS NULL
                WHERE c.tenant_id = :tenant_id
                AND c.created_at < :start_date
                AND c.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'start_date' => $startDate, 'end_date' => $endDate]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get customer churn analysis
     */
    public function getChurnAnalysis($tenantId, $daysInactive = 90)
    {
        $sql = "SELECT 
                    c.customer_id,
                    c.name,
                    c.phone,
                    c.email,
                    MAX(o.created_at) as last_order_date,
                    DATEDIFF(NOW(), MAX(o.created_at)) as days_since_last_order,
                    COUNT(DISTINCT o.order_id) as total_orders,
                    SUM(o.total_amount) as total_spent
                FROM customers c
                LEFT JOIN orders o ON c.customer_id = o.customer_id 
                    AND o.status IN ('COMPLETED', 'PAID')
                    AND o.deleted_at IS NULL
                WHERE c.tenant_id = :tenant_id
                AND c.deleted_at IS NULL
                GROUP BY c.customer_id, c.name, c.phone, c.email
                HAVING days_since_last_order >= :days_inactive
                ORDER BY days_since_last_order DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'days_inactive' => $daysInactive]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get customer preferences analytics
     */
    public function getPreferenceAnalytics($tenantId, $customerId)
    {
        $sql = "SELECT 
                    p.category_id,
                    c.category_name,
                    COUNT(DISTINCT oi.order_id) as order_count,
                    SUM(oi.quantity) as total_quantity,
                    SUM(oi.subtotal) as total_spent
                FROM order_items oi
                INNER JOIN orders o ON oi.order_id = o.order_id
                INNER JOIN products p ON oi.product_id = p.product_id
                INNER JOIN categories c ON p.category_id = c.category_id
                WHERE o.customer_id = :customer_id
                AND o.tenant_id = :tenant_id
                AND o.deleted_at IS NULL
                AND o.status IN ('COMPLETED', 'PAID')
                GROUP BY p.category_id, c.category_name
                ORDER BY total_spent DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customer_id' => $customerId, 'tenant_id' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get customer peak hours
     */
    public function getPeakHours($tenantId, $customerId)
    {
        $sql = "SELECT 
                    HOUR(o.created_at) as hour,
                    COUNT(DISTINCT o.order_id) as order_count,
                    SUM(o.total_amount) as total_spent
                FROM orders o
                WHERE o.customer_id = :customer_id
                AND o.tenant_id = :tenant_id
                AND o.deleted_at IS NULL
                AND o.status IN ('COMPLETED', 'PAID')
                GROUP BY HOUR(o.created_at)
                ORDER BY order_count DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customer_id' => $customerId, 'tenant_id' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
