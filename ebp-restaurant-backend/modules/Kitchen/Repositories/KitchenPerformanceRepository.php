<?php

if (!class_exists('database')) {
    require_once __DIR__ . '/../../../config/database.php';
}

class KitchenPerformanceRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createChefPerformance($data)
    {
        $sql = "INSERT INTO chef_performance (tenant_id, branch_id, employee_id, performance_date, orders_prepared, orders_on_time, average_preparation_time, quality_score, customer_rating, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['employee_id'],
            $data['performance_date'],
            $data['orders_prepared'] ?? 0,
            $data['orders_on_time'] ?? 0,
            $data['average_preparation_time'] ?? 0,
            $data['quality_score'] ?? 0,
            $data['customer_rating'] ?? 0,
            $data['notes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function getKitchenMetrics($tenantId, $branchId, $dateFrom, $dateTo)
    {
        $sql = "
            SELECT 
                DATE(ko.created_at) as date,
                COUNT(ko.kitchen_order_id) as total_orders,
                SUM(CASE WHEN ko.status = 'READY' THEN 1 ELSE 0 END) as completed_orders,
                AVG(TIMESTAMPDIFF(MINUTE, ko.created_at, ko.completed_at)) as avg_preparation_time,
                SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, ko.created_at, ko.completed_at) <= 15 THEN 1 ELSE 0 END) as on_time_orders,
                SUM(CASE WHEN ko.priority = 'URGENT' THEN 1 ELSE 0 END) as urgent_orders
            FROM kitchen_orders ko
            WHERE ko.tenant_id = ? 
            AND ko.branch_id = ?
            AND ko.created_at BETWEEN ? AND ?
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getChefPerformance($tenantId, $branchId, $employeeId, $dateFrom, $dateTo)
    {
        $sql = "SELECT * FROM chef_performance WHERE tenant_id = ? AND branch_id = ? AND employee_id = ? AND performance_date BETWEEN ? AND ? ORDER BY performance_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $employeeId, $dateFrom, $dateTo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBottleneckAnalysis($tenantId, $branchId, $dateFrom, $dateTo)
    {
        $sql = "
            SELECT 
                DATE(ko.created_at) as date,
                HOUR(ko.created_at) as hour,
                COUNT(ko.kitchen_order_id) as order_count,
                AVG(TIMESTAMPDIFF(MINUTE, ko.created_at, ko.completed_at)) as avg_preparation_time,
                SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, ko.created_at, ko.completed_at) > 20 THEN 1 ELSE 0 END) as delayed_orders
            FROM kitchen_orders ko
            WHERE ko.tenant_id = ? 
            AND ko.branch_id = ?
            AND ko.created_at BETWEEN ? AND ?
            AND ko.status = 'READY'
            GROUP BY DATE(ko.created_at), HOUR(ko.created_at)
            HAVING delayed_orders > 0
            ORDER BY date DESC, hour ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
