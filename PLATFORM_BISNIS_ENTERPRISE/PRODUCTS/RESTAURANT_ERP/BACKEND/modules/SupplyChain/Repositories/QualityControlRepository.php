<?php



class QualityControlRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createQualityCheck($data)
    {
        $sql = "INSERT INTO quality_checks (tenant_id, branch_id, check_type, product_id, batch_number, check_date, checked_by, quality_score, check_status, issues, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['check_type'],
            $data['product_id'] ?? null,
            $data['batch_number'] ?? null,
            $data['check_date'],
            $data['checked_by'],
            $data['quality_score'] ?? null,
            $data['status'] ?? 'PENDING',
            $data['issues'] ?? null,
            $data['notes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function updateQualityCheckResult($checkId, $data, $tenantId)
    {
        $sql = "UPDATE quality_checks SET check_status = ?, quality_score = ?, issues = ?, notes = ? WHERE check_id = ? AND tenant_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['status'],
            $data['quality_score'] ?? null,
            $data['issues'] ?? null,
            $data['notes'] ?? null,
            $checkId,
            $tenantId
        ]);
    }

    public function getQualityChecks($tenantId, $branchId, $checkType = null, $status = null)
    {
        $sql = "SELECT qc.*, p.product_name, e.employee_name as checked_by_name FROM quality_checks qc LEFT JOIN products p ON qc.product_id = p.product_id LEFT JOIN employees e ON qc.checked_by = e.employee_id WHERE qc.tenant_id = ? AND qc.branch_id = ?";
        $params = [$tenantId, $branchId];
        
        if ($checkType) {
            $sql .= " AND qc.check_type = ?";
            $params[] = $checkType;
        }
        
        if ($status) {
            $sql .= " AND qc.check_status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY qc.check_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getQualityReport($tenantId, $branchId, $dateFrom, $dateTo)
    {
        $sql = "
            SELECT 
                check_type,
                COUNT(*) as total_checks,
                SUM(CASE WHEN check_status = 'PASSED' THEN 1 ELSE 0 END) as passed,
                SUM(CASE WHEN check_status = 'FAILED' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN check_status = 'PENDING' THEN 1 ELSE 0 END) as pending,
                AVG(quality_score) as avg_quality_score
            FROM quality_checks
            WHERE tenant_id = ? 
            AND branch_id = ?
            AND check_date BETWEEN ? AND ?
            GROUP BY check_type
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $dateFrom, $dateTo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
