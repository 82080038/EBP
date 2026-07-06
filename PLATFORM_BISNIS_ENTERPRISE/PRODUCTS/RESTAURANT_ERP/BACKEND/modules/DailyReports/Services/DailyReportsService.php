<?php

use PDO;
use PDOException;

global $pdo;

/**
 * Enhanced Daily Reports Service
 * 
 * Provides comprehensive daily operational reports
 */
class DailyReportsService
{
    private $db;
    private $tenantId;
    private $branchId;

    public function __construct($tenantId = null, $branchId = null)
    {
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
    }

    /**
     * Get daily sales report
     */
    public function getDailySalesReport($date)
    {
        try {
            $sql = "SELECT 
                    COUNT(DISTINCT o.order_id) as total_orders,
                    SUM(o.total_amount) as total_revenue,
                    AVG(o.total_amount) as avg_order_value,
                    COUNT(DISTINCT o.customer_id) as unique_customers,
                    SUM(CASE WHEN o.order_type = 'dine_in' THEN 1 ELSE 0 END) as dine_in_orders,
                    SUM(CASE WHEN o.order_type = 'takeaway' THEN 1 ELSE 0 END) as takeaway_orders,
                    SUM(CASE WHEN o.order_type = 'delivery' THEN 1 ELSE 0 END) as delivery_orders
                    FROM orders o
                    WHERE o.tenant_id = :tenant_id 
                    AND DATE(o.created_at) = :date
                    AND o.deleted_at IS NULL";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':tenant_id' => $this->tenantId,
                ':date' => $date
            ]);
            $report = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $report
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to get daily sales report: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get table turnover report
     */
    public function getTableTurnoverReport($date)
    {
        try {
            $sql = "SELECT 
                    t.table_id,
                    t.table_number,
                    t.capacity,
                    COUNT(DISTINCT o.order_id) as turnover_count,
                    SUM(o.total_amount) as revenue,
                    AVG(TIMESTAMPDIFF(MINUTE, o.created_at, o.updated_at)) as avg_turnover_time
                    FROM tables t
                    LEFT JOIN orders o ON t.table_id = o.table_id 
                    AND DATE(o.created_at) = :date
                    AND o.tenant_id = :tenant_id
                    WHERE t.tenant_id = :tenant_id
                    GROUP BY t.table_id, t.table_number, t.capacity
                    ORDER BY turnover_count DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':tenant_id' => $this->tenantId,
                ':date' => $date
            ]);
            $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $totalTurnover = array_sum(array_column($tables, 'turnover_count'));
            $totalRevenue = array_sum(array_column($tables, 'revenue'));

            return [
                'success' => true,
                'data' => [
                    'tables' => $tables,
                    'summary' => [
                        'total_turnover' => $totalTurnover,
                        'total_revenue' => $totalRevenue
                    ]
                ]
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to get table turnover report: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get server performance report
     */
    public function getServerPerformanceReport($date)
    {
        try {
            $sql = "SELECT 
                    u.user_id,
                    u.full_name,
                    u.username,
                    COUNT(DISTINCT o.order_id) as orders_served,
                    SUM(o.total_amount) as total_sales,
                    AVG(o.total_amount) as avg_order_value,
                    COUNT(DISTINCT o.customer_id) as customers_served
                    FROM users u
                    LEFT JOIN orders o ON u.user_id = o.user_id 
                    AND DATE(o.created_at) = :date
                    AND o.tenant_id = :tenant_id
                    AND o.deleted_at IS NULL
                    WHERE u.tenant_id = :tenant_id
                    GROUP BY u.user_id, u.full_name, u.username
                    ORDER BY total_sales DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':tenant_id' => $this->tenantId,
                ':date' => $date
            ]);
            $servers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $servers
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to get server performance report: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get peak hour analysis
     */
    public function getPeakHourAnalysis($date)
    {
        try {
            $sql = "SELECT 
                    HOUR(o.created_at) as hour,
                    COUNT(DISTINCT o.order_id) as order_count,
                    SUM(o.total_amount) as revenue,
                    COUNT(DISTINCT o.customer_id) as customer_count
                    FROM orders o
                    WHERE o.tenant_id = :tenant_id 
                    AND DATE(o.created_at) = :date
                    AND o.deleted_at IS NULL
                    GROUP BY HOUR(o.created_at)
                    ORDER BY hour";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':tenant_id' => $this->tenantId,
                ':date' => $date
            ]);
            $hours = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Find peak hour
            $peakHour = null;
            $maxOrders = 0;
            foreach ($hours as $hour) {
                if ($hour['order_count'] > $maxOrders) {
                    $maxOrders = $hour['order_count'];
                    $peakHour = $hour;
                }
            }

            return [
                'success' => true,
                'data' => [
                    'hourly_data' => $hours,
                    'peak_hour' => $peakHour
                ]
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to get peak hour analysis: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get comprehensive daily report
     */
    public function getComprehensiveDailyReport($date)
    {
        $salesReport = $this->getDailySalesReport($date);
        $tableTurnover = $this->getTableTurnoverReport($date);
        $serverPerformance = $this->getServerPerformanceReport($date);
        $peakHourAnalysis = $this->getPeakHourAnalysis($date);

        return [
            'success' => true,
            'data' => [
                'date' => $date,
                'sales_report' => $salesReport['success'] ? $salesReport['data'] : null,
                'table_turnover' => $tableTurnover['success'] ? $tableTurnover['data'] : null,
                'server_performance' => $serverPerformance['success'] ? $serverPerformance['data'] : null,
                'peak_hour_analysis' => $peakHourAnalysis['success'] ? $peakHourAnalysis['data'] : null
            ]
        ];
    }
}
