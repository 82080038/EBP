<?php



class AIRepository
{
    private $db;

    public function __construct($db = null)
    {
        if ($db) {
            $this->db = $db;
        } else {
            $host = 'localhost';
            $dbname = 'ebp_restaurant_db';
            $username = 'ebp_app';
            $password = 'ebp_secure_password_2026';
            $socket = '/opt/lampp/var/mysql/mysql.sock';

            $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
            $this->db = new PDO($dsn, $username, $password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    public function getSalesHistory($tenantId, $branchId, $startDate, $endDate)
    {
        $sql = "SELECT oi.product_id, SUM(oi.quantity) as quantity, DATE(o.created_at) as order_date FROM order_items oi JOIN orders o ON oi.order_id = o.order_id WHERE o.tenant_id = ? AND o.branch_id = ? AND o.created_at BETWEEN ? AND ? AND o.deleted_at IS NULL GROUP BY oi.product_id, DATE(o.created_at)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getInventory($tenantId, $branchId)
    {
        $sql = "SELECT i.*, p.product_name FROM inventory i JOIN products p ON i.product_id = p.product_id WHERE i.tenant_id = ? AND i.branch_id = ? AND i.deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSupplierPerformance($tenantId)
    {
        $sql = "SELECT s.supplier_id, s.supplier_name, AVG(sp.overall_rating) as avg_rating FROM suppliers s LEFT JOIN supplier_performance sp ON s.supplier_id = sp.supplier_id WHERE s.tenant_id = ? GROUP BY s.supplier_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function savePrediction($tenantId, $branchId, $predictionType, $predictionData, $confidenceScore)
    {
        $sql = "INSERT INTO ai_predictions (tenant_id, branch_id, prediction_type, prediction_date, prediction_data, confidence_score, model_version) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $tenantId,
            $branchId,
            $predictionType,
            date('Y-m-d'),
            json_encode($predictionData),
            $confidenceScore,
            '1.0'
        ]);
        return $this->db->lastInsertId();
    }

    public function getPredictions($tenantId, $branchId, $predictionType = null)
    {
        $sql = "SELECT * FROM ai_predictions WHERE tenant_id = ? AND branch_id = ?";
        $params = [$tenantId, $branchId];
        
        if ($predictionType) {
            $sql .= " AND prediction_type = ?";
            $params[] = $predictionType;
        }
        
        $sql .= " ORDER BY prediction_date DESC LIMIT 10";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getKitchenPerformanceData($tenantId, $branchId, $dateFrom, $dateTo)
    {
        $sql = "SELECT o.order_id, o.created_at, o.updated_at as completed_at, TIMESTAMPDIFF(MINUTE, o.created_at, o.updated_at) as preparation_time, CASE WHEN TIMESTAMPDIFF(MINUTE, o.created_at, o.updated_at) <= 15 THEN 1 ELSE 0 END as is_on_time, NULL as prepared_by FROM orders o WHERE o.tenant_id = ? AND o.branch_id = ? AND o.created_at BETWEEN ? AND ? AND o.status = 'COMPLETED' AND o.deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCustomerBehaviorData($tenantId, $branchId, $dateFrom, $dateTo)
    {
        $sql = "SELECT o.user_id, o.total_amount, DATE(o.created_at) as order_date, HOUR(o.created_at) as order_hour, COUNT(o.order_id) as order_count FROM orders o WHERE o.tenant_id = ? AND o.branch_id = ? AND o.created_at BETWEEN ? AND ? AND o.deleted_at IS NULL GROUP BY o.user_id, DATE(o.created_at), HOUR(o.created_at)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalesData($tenantId, $branchId, $dateFrom, $dateTo)
    {
        $sql = "SELECT oi.product_id, SUM(oi.quantity) as quantity, SUM(oi.quantity * oi.unit_price) as total_sales FROM order_items oi JOIN orders o ON oi.order_id = o.order_id WHERE o.tenant_id = ? AND o.branch_id = ? AND o.created_at BETWEEN ? AND ? AND o.deleted_at IS NULL GROUP BY oi.product_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
