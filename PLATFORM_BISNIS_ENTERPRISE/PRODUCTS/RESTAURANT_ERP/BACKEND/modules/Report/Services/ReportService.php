<?php



class ReportService
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    // Sales Reports
    public function getSalesReport(int $tenantId, ?int $branchId = null, string $dateFrom, string $dateTo): array
    {
        $sql = "
            SELECT 
                DATE(o.created_at) as date,
                COUNT(o.order_id) as total_orders,
                SUM(o.total_amount) as total_revenue,
                SUM(o.paid_amount) as total_paid,
                AVG(o.total_amount) as average_order_value,
                COUNT(DISTINCT o.user_id) as unique_customers
            FROM orders o
            WHERE o.tenant_id = :tenant_id
            AND o.created_at BETWEEN :date_from AND :date_to
            AND o.deleted_at IS NULL
        ";
        
        $params = [
            'tenant_id' => $tenantId,
            'date_from' => $dateFrom . ' 00:00:00',
            'date_to' => $dateTo . ' 23:59:59'
        ];
        
        if ($branchId !== null) {
            $sql .= " AND o.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " GROUP BY DATE(o.created_at) ORDER BY date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopSellingProducts(int $tenantId, ?int $branchId = null, string $dateFrom, string $dateTo, int $limit = 10): array
    {
        $sql = "
            SELECT 
                p.product_id,
                p.product_name,
                p.product_code,
                SUM(oi.quantity) as total_quantity,
                SUM(oi.subtotal) as total_revenue,
                COUNT(DISTINCT oi.order_id) as order_count
            FROM order_items oi
            JOIN products p ON oi.product_id = p.product_id
            JOIN orders o ON oi.order_id = o.order_id
            WHERE o.tenant_id = :tenant_id
            AND o.created_at BETWEEN :date_from AND :date_to
            AND o.deleted_at IS NULL
        ";
        
        $params = [
            'tenant_id' => $tenantId,
            'date_from' => $dateFrom . ' 00:00:00',
            'date_to' => $dateTo . ' 23:59:59'
        ];
        
        if ($branchId !== null) {
            $sql .= " AND o.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " GROUP BY p.product_id, p.product_name, p.product_code 
                  ORDER BY total_quantity DESC 
                  LIMIT " . (int)$limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Inventory Reports
    public function getInventoryReport(int $tenantId, ?int $branchId = null): array
    {
        $sql = "
            SELECT 
                i.inventory_id,
                p.product_name,
                p.product_code,
                i.quantity,
                i.unit,
                i.minimum_stock,
                i.maximum_stock,
                (i.quantity - i.minimum_stock) as buffer,
                CASE 
                    WHEN i.quantity <= i.minimum_stock THEN 'LOW'
                    WHEN i.quantity >= i.maximum_stock THEN 'HIGH'
                    ELSE 'NORMAL'
                END as stock_status
            FROM inventory i
            JOIN products p ON i.product_id = p.product_id
            WHERE i.tenant_id = :tenant_id
            AND i.deleted_at IS NULL
        ";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($branchId !== null) {
            $sql .= " AND i.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " ORDER BY stock_status ASC, p.product_name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStockMovementReport(int $tenantId, ?int $branchId = null, string $dateFrom, string $dateTo): array
    {
        $sql = "
            SELECT 
                st.transaction_type,
                p.product_name,
                p.product_code,
                SUM(st.quantity) as total_quantity,
                COUNT(st.stock_transaction_id) as transaction_count
            FROM stock_transactions st
            JOIN products p ON st.product_id = p.product_id
            WHERE st.tenant_id = :tenant_id
            AND st.created_at BETWEEN :date_from AND :date_to
        ";
        
        $params = [
            'tenant_id' => $tenantId,
            'date_from' => $dateFrom . ' 00:00:00',
            'date_to' => $dateTo . ' 23:59:59'
        ];
        
        if ($branchId !== null) {
            $sql .= " AND st.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " GROUP BY st.transaction_type, p.product_id, p.product_name, p.product_code
                  ORDER BY st.transaction_type, total_quantity DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Kitchen Reports
    public function getKitchenPerformanceReport(int $tenantId, ?int $branchId = null, string $dateFrom, string $dateTo): array
    {
        $sql = "
            SELECT 
                DATE(ko.created_at) as date,
                COUNT(ko.kitchen_order_id) as total_orders,
                SUM(CASE WHEN ko.status = 'READY' THEN 1 ELSE 0 END) as completed_orders,
                AVG(TIMESTAMPDIFF(MINUTE, ko.created_at, ko.completed_at)) as avg_preparation_time,
                SUM(CASE WHEN ko.priority = 'URGENT' THEN 1 ELSE 0 END) as urgent_orders
            FROM kitchen_orders ko
            WHERE ko.tenant_id = :tenant_id
            AND ko.created_at BETWEEN :date_from AND :date_to
        ";
        
        $params = [
            'tenant_id' => $tenantId,
            'date_from' => $dateFrom . ' 00:00:00',
            'date_to' => $dateTo . ' 23:59:59'
        ];
        
        if ($branchId !== null) {
            $sql .= " AND ko.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " GROUP BY DATE(ko.created_at) ORDER BY date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Reservation Reports
    public function getReservationReport(int $tenantId, ?int $branchId = null, string $dateFrom, string $dateTo): array
    {
        $sql = "
            SELECT 
                DATE(r.reservation_date) as date,
                COUNT(r.reservation_id) as total_reservations,
                SUM(r.party_size) as total_guests,
                SUM(CASE WHEN r.status = 'COMPLETED' THEN 1 ELSE 0 END) as completed_reservations,
                SUM(CASE WHEN r.status = 'CANCELLED' THEN 1 ELSE 0 END) as cancelled_reservations,
                SUM(CASE WHEN r.status = 'NO_SHOW' THEN 1 ELSE 0 END) as no_shows,
                AVG(r.party_size) as avg_party_size
            FROM reservations r
            WHERE r.tenant_id = :tenant_id
            AND r.reservation_date BETWEEN :date_from AND :date_to
            AND r.deleted_at IS NULL
        ";
        
        $params = [
            'tenant_id' => $tenantId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];
        
        if ($branchId !== null) {
            $sql .= " AND r.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " GROUP BY DATE(r.reservation_date) ORDER BY date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Financial Reports
    public function getFinancialReport(int $tenantId, ?int $branchId = null, string $dateFrom, string $dateTo): array
    {
        $sql = "
            SELECT 
                DATE(o.created_at) as date,
                SUM(o.total_amount) as gross_revenue,
                SUM(o.tax) as total_tax,
                SUM(o.discount) as total_discount,
                SUM(o.total_amount - o.tax + o.discount) as net_revenue,
                SUM(o.paid_amount) as total_collected,
                SUM(CASE WHEN o.payment_status = 'PAID' THEN o.total_amount ELSE 0 END) as paid_revenue,
                SUM(CASE WHEN o.payment_status = 'UNPAID' THEN o.total_amount ELSE 0 END) as outstanding_revenue
            FROM orders o
            WHERE o.tenant_id = :tenant_id
            AND o.created_at BETWEEN :date_from AND :date_to
            AND o.deleted_at IS NULL
        ";
        
        $params = [
            'tenant_id' => $tenantId,
            'date_from' => $dateFrom . ' 00:00:00',
            'date_to' => $dateTo . ' 23:59:59'
        ];
        
        if ($branchId !== null) {
            $sql .= " AND o.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " GROUP BY DATE(o.created_at) ORDER BY date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Dashboard Summary
    public function getDashboardSummary(int $tenantId, ?int $branchId = null): array
    {
        $summary = [];
        
        // Today's sales
        $sql = "
            SELECT 
                COUNT(o.order_id) as total_orders,
                SUM(o.total_amount) as total_revenue,
                SUM(o.paid_amount) as total_paid
            FROM orders o
            WHERE o.tenant_id = :tenant_id
            AND DATE(o.created_at) = CURDATE()
            AND o.deleted_at IS NULL
        ";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($branchId !== null) {
            $sql .= " AND o.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $summary['today'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Pending kitchen orders
        $sql = "
            SELECT COUNT(*) as count
            FROM kitchen_orders
            WHERE tenant_id = :tenant_id
            AND status = 'PENDING'
        ";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($branchId !== null) {
            $sql .= " AND branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $summary['pending_kitchen'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Low stock items
        $sql = "
            SELECT COUNT(*) as count
            FROM inventory i
            WHERE i.tenant_id = :tenant_id
            AND i.quantity <= i.minimum_stock
            AND i.deleted_at IS NULL
        ";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($branchId !== null) {
            $sql .= " AND i.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $summary['low_stock'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Today's reservations
        $sql = "
            SELECT COUNT(*) as count
            FROM reservations
            WHERE tenant_id = :tenant_id
            AND reservation_date = CURDATE()
            AND status IN ('PENDING', 'CONFIRMED')
            AND deleted_at IS NULL
        ";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($branchId !== null) {
            $sql .= " AND branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $summary['today_reservations'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $summary;
    }

    // Profit & Loss Statement
    public function getProfitLossReport(int $tenantId, ?int $branchId = null, string $dateFrom, string $dateTo): array
    {
        $sql = "
            SELECT 
                DATE(o.created_at) as date,
                SUM(o.total_amount) as gross_revenue,
                SUM(o.tax) as total_tax,
                SUM(o.discount) as total_discount,
                SUM(o.total_amount - o.tax + o.discount) as net_revenue,
                0 as cost_of_goods_sold,
                SUM(o.total_amount - o.tax + o.discount) as gross_profit,
                100 as gross_profit_margin
            FROM orders o
            WHERE o.tenant_id = :tenant_id
            AND o.created_at BETWEEN :date_from AND :date_to
            AND o.deleted_at IS NULL
        ";
        
        $params = [
            'tenant_id' => $tenantId,
            'date_from' => $dateFrom . ' 00:00:00',
            'date_to' => $dateTo . ' 23:59:59'
        ];
        
        if ($branchId !== null) {
            $sql .= " AND o.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " GROUP BY DATE(o.created_at) ORDER BY date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Cost Analysis
    public function getCostAnalysisReport(int $tenantId, ?int $branchId = null, string $dateFrom, string $dateTo): array
    {
        $sql = "
            SELECT 
                p.product_name,
                p.product_code,
                SUM(oi.quantity) as total_quantity,
                0 as total_cost,
                SUM(oi.subtotal) as total_revenue,
                0 as cost_percentage,
                100 as profit_margin
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.product_id
            LEFT JOIN orders o ON oi.order_id = o.order_id
            WHERE o.tenant_id = :tenant_id
            AND o.created_at BETWEEN :date_from AND :date_to
            AND o.deleted_at IS NULL
        ";
        
        $params = [
            'tenant_id' => $tenantId,
            'date_from' => $dateFrom . ' 00:00:00',
            'date_to' => $dateTo . ' 23:59:59'
        ];
        
        if ($branchId !== null) {
            $sql .= " AND o.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " GROUP BY p.product_id, p.product_name, p.product_code ORDER BY total_revenue DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Food Cost Percentage
    public function getFoodCostPercentage(int $tenantId, ?int $branchId = null, string $dateFrom, string $dateTo): array
    {
        $sql = "
            SELECT 
                DATE(o.created_at) as date,
                0 as total_food_cost,
                SUM(o.total_amount) as total_sales,
                0 as food_cost_percentage
            FROM orders o
            WHERE o.tenant_id = :tenant_id
            AND o.created_at BETWEEN :date_from AND :date_to
            AND o.deleted_at IS NULL
        ";
        
        $params = [
            'tenant_id' => $tenantId,
            'date_from' => $dateFrom . ' 00:00:00',
            'date_to' => $dateTo . ' 23:59:59'
        ];
        
        if ($branchId !== null) {
            $sql .= " AND o.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " GROUP BY DATE(o.created_at) ORDER BY date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Sales by Hour Heatmap
    public function getSalesByHour(int $tenantId, ?int $branchId = null, string $dateFrom, string $dateTo): array
    {
        $sql = "
            SELECT 
                HOUR(o.created_at) as hour,
                COUNT(o.order_id) as total_orders,
                SUM(o.total_amount) as total_revenue,
                AVG(o.total_amount) as average_order_value,
                COUNT(DISTINCT o.table_id) as active_tables
            FROM orders o
            WHERE o.tenant_id = :tenant_id
            AND o.created_at BETWEEN :date_from AND :date_to
            AND o.deleted_at IS NULL
        ";
        
        $params = [
            'tenant_id' => $tenantId,
            'date_from' => $dateFrom . ' 00:00:00',
            'date_to' => $dateTo . ' 23:59:59'
        ];
        
        if ($branchId !== null) {
            $sql .= " AND o.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " GROUP BY HOUR(o.created_at) ORDER BY hour ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Payment Method Breakdown
    public function getPaymentMethodBreakdown(int $tenantId, ?int $branchId = null, string $dateFrom, string $dateTo): array
    {
        $sql = "
            SELECT 
                pm.payment_method,
                COUNT(op.payment_id) as transaction_count,
                SUM(op.amount) as total_amount,
                (SUM(op.amount) / (SELECT SUM(amount) FROM order_payments op2 
                                    JOIN orders o2 ON op2.order_id = o2.order_id 
                                    WHERE o2.tenant_id = :tenant_id 
                                    AND o2.created_at BETWEEN :date_from AND :date_to 
                                    AND o2.deleted_at IS NULL) * 100) as percentage
            FROM order_payments op
            JOIN orders o ON op.order_id = o.order_id
            JOIN payment_methods pm ON op.payment_method_id = pm.payment_method_id
            WHERE o.tenant_id = :tenant_id
            AND o.created_at BETWEEN :date_from AND :date_to
            AND o.deleted_at IS NULL
        ";
        
        $params = [
            'tenant_id' => $tenantId,
            'date_from' => $dateFrom . ' 00:00:00',
            'date_to' => $dateTo . ' 23:59:59'
        ];
        
        if ($branchId !== null) {
            $sql .= " AND o.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " GROUP BY pm.payment_method ORDER BY total_amount DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Inventory Usage Report
    public function getInventoryUsageReport(int $tenantId, ?int $branchId = null, string $dateFrom, string $dateTo): array
    {
        $sql = "
            SELECT 
                p.product_name,
                p.product_code,
                i.unit,
                SUM(oi.quantity) as total_used,
                i.quantity as current_stock,
                i.average_cost,
                SUM(oi.quantity) * i.average_cost as total_cost
            FROM order_items oi
            JOIN products p ON oi.product_id = p.product_id
            JOIN inventory i ON p.product_id = i.product_id
            JOIN orders o ON oi.order_id = o.order_id
            WHERE o.tenant_id = :tenant_id
            AND o.created_at BETWEEN :date_from AND :date_to
            AND o.deleted_at IS NULL
        ";
        
        $params = [
            'tenant_id' => $tenantId,
            'date_from' => $dateFrom . ' 00:00:00',
            'date_to' => $dateTo . ' 23:59:59'
        ];
        
        if ($branchId !== null) {
            $sql .= " AND o.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " GROUP BY p.product_id, p.product_name, p.product_code, i.unit, i.quantity, i.average_cost ORDER BY total_used DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Labor Cost Analysis
    public function getLaborCostAnalysis(int $tenantId, ?int $branchId = null, string $dateFrom, string $dateTo): array
    {
        $sql = "
            SELECT 
                DATE(a.attendance_date) as date,
                COUNT(a.attendance_id) as total_staff,
                SUM(a.work_hours) as total_work_hours,
                AVG(a.work_hours) as avg_work_hours,
                SUM(e.base_salary / 30 * a.work_hours / 8) as estimated_labor_cost,
                (SELECT SUM(total_amount) FROM orders o2 
                 WHERE o2.tenant_id = :tenant_id 
                 AND o2.created_at BETWEEN :date_from AND :date_to 
                 AND DATE(o2.created_at) = DATE(a.attendance_date)
                 AND o2.deleted_at IS NULL) as daily_revenue
            FROM attendance a
            JOIN employees e ON a.employee_id = e.employee_id
            WHERE e.tenant_id = :tenant_id
            AND a.attendance_date BETWEEN :date_from AND :date_to
        ";
        
        $params = [
            'tenant_id' => $tenantId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];
        
        if ($branchId !== null) {
            $sql .= " AND e.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " GROUP BY DATE(a.attendance_date) ORDER BY date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tax Reports (PB1, PPN)
    public function getTaxReport(int $tenantId, ?int $branchId = null, string $dateFrom, string $dateTo): array
    {
        $sql = "
            SELECT 
                DATE(o.created_at) as date,
                SUM(o.total_amount) as gross_sales,
                SUM(o.tax) as total_tax,
                SUM(o.total_amount - o.tax) as net_sales,
                SUM(o.discount) as total_discount,
                SUM(CASE WHEN o.tax > 0 THEN o.tax ELSE 0 END) as ppn_tax,
                SUM(CASE WHEN o.tax = 0 THEN o.total_amount ELSE 0 END) as non_taxable_sales
            FROM orders o
            WHERE o.tenant_id = :tenant_id
            AND o.created_at BETWEEN :date_from AND :date_to
            AND o.deleted_at IS NULL
        ";
        
        $params = [
            'tenant_id' => $tenantId,
            'date_from' => $dateFrom . ' 00:00:00',
            'date_to' => $dateTo . ' 23:59:59'
        ];
        
        if ($branchId !== null) {
            $sql .= " AND o.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $sql .= " GROUP BY DATE(o.created_at) ORDER BY date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Export to CSV
    public function exportToCSV(array $data, string $filename): string
    {
        if (empty($data)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');
        
        // Get headers from first row
        $headers = array_keys($data[0]);
        fputcsv($output, $headers);
        
        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}
