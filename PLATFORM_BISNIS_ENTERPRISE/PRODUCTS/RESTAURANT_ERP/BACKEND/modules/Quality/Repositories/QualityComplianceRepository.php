<?php



class QualityComplianceRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createComplianceCheck($data)
    {
        $sql = "INSERT INTO quality_compliance_checks (tenant_id, branch_id, check_type, check_date, area, compliance_score, status, issues, corrective_actions, checked_by, next_check_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['check_type'],
            $data['check_date'],
            $data['area'] ?? null,
            $data['compliance_score'] ?? null,
            $data['status'] ?? 'PENDING',
            $data['issues'] ?? null,
            $data['corrective_actions'] ?? null,
            $data['checked_by'],
            $data['next_check_date'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function getComplianceReport($tenantId, $branchId, $dateFrom, $dateTo)
    {
        $sql = "
            SELECT 
                check_type,
                COUNT(*) as total_checks,
                SUM(CASE WHEN status = 'COMPLIANT' THEN 1 ELSE 0 END) as compliant,
                SUM(CASE WHEN status = 'NON_COMPLIANT' THEN 1 ELSE 0 END) as non_compliant,
                SUM(CASE WHEN status = 'PARTIALLY_COMPLIANT' THEN 1 ELSE 0 END) as partially_compliant,
                AVG(compliance_score) as avg_compliance_score
            FROM quality_compliance_checks
            WHERE tenant_id = ? 
            AND branch_id = ?
            AND check_date BETWEEN ? AND ?
            GROUP BY check_type
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $dateFrom, $dateTo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createFoodSafetyProtocol($data)
    {
        $sql = "INSERT INTO food_safety_protocols (tenant_id, branch_id, protocol_name, protocol_type, description, critical_control_points, monitoring_frequency, responsible_person, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['protocol_name'],
            $data['protocol_type'],
            $data['description'] ?? null,
            $data['critical_control_points'] ? json_encode($data['critical_control_points']) : null,
            $data['monitoring_frequency'] ?? null,
            $data['responsible_person'] ?? null,
            $data['is_active'] ?? true
        ]);
        return $this->db->lastInsertId();
    }

    public function getFoodSafetyProtocols($tenantId, $branchId)
    {
        $sql = "SELECT * FROM food_safety_protocols WHERE tenant_id = ? AND branch_id = ? AND is_active = TRUE ORDER BY protocol_type, protocol_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
