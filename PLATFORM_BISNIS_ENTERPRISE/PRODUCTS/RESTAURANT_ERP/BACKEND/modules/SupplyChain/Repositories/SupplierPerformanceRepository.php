<?php



class SupplierPerformanceRepository
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

    public function createPerformance($data)
    {
        $sql = "INSERT INTO supplier_performance (tenant_id, supplier_id, evaluation_date, on_time_delivery_rate, quality_score, price_competitiveness, overall_rating, notes, evaluated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['supplier_id'],
            $data['evaluation_date'],
            $data['on_time_delivery_rate'] ?? null,
            $data['quality_score'] ?? null,
            $data['price_competitiveness'] ?? null,
            $data['overall_rating'],
            $data['notes'] ?? null,
            $data['evaluated_by']
        ]);
        return $this->db->lastInsertId();
    }

    public function getSupplierPerformance($tenantId, $supplierId, $dateFrom = null, $dateTo = null)
    {
        $sql = "SELECT sp.*, s.supplier_name, u.username as evaluated_by_name FROM supplier_performance sp JOIN suppliers s ON sp.supplier_id = s.supplier_id LEFT JOIN users u ON sp.evaluated_by = u.user_id WHERE sp.tenant_id = ? AND sp.supplier_id = ?";
        $params = [$tenantId, $supplierId];
        
        if ($dateFrom) {
            $sql .= " AND sp.evaluation_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND sp.evaluation_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql .= " ORDER BY sp.evaluation_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSupplierRanking($tenantId, $branchId, $dateFrom = null, $dateTo = null)
    {
        $sql = "
            SELECT 
                s.supplier_id,
                s.supplier_name,
                AVG(sp.overall_rating) as avg_rating,
                AVG(sp.on_time_delivery_rate) as avg_delivery_rate,
                AVG(sp.quality_score) as avg_quality_score,
                AVG(sp.price_competitiveness) as avg_price_score,
                COUNT(sp.performance_id) as evaluation_count
            FROM suppliers s
            LEFT JOIN supplier_performance sp ON s.supplier_id = sp.supplier_id AND sp.tenant_id = s.tenant_id
            WHERE s.tenant_id = ?
        ";
        $params = [$tenantId];
        
        if ($dateFrom && $dateTo) {
            $sql .= " AND (sp.evaluation_date IS NULL OR (sp.evaluation_date BETWEEN ? AND ?))";
            $params[] = $dateFrom;
            $params[] = $dateTo;
        }
        
        $sql .= " GROUP BY s.supplier_id ORDER BY avg_rating DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
