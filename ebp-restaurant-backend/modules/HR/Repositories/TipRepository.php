<?php

if (!class_exists('database')) {
    require_once __DIR__ . '/../../../config/database.php';
}

class TipRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createTipDistribution($data)
    {
        $sql = "INSERT INTO tip_distributions (tenant_id, branch_id, order_id, total_tip_amount, distribution_date, notes) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['order_id'],
            $data['total_tip_amount'],
            $data['distribution_date'],
            $data['notes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function addTipRecipient($data)
    {
        $sql = "INSERT INTO tip_recipients (tip_id, employee_id, tip_amount, percentage) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tip_id'],
            $data['employee_id'],
            $data['tip_amount'],
            $data['percentage']
        ]);
        return $this->db->lastInsertId();
    }

    public function getTipDistributions($tenantId, $branchId, $date = null)
    {
        $sql = "SELECT td.*, o.order_number FROM tip_distributions td LEFT JOIN orders o ON td.order_id = o.order_id WHERE td.tenant_id = ? AND td.branch_id = ?";
        $params = [$tenantId, $branchId];
        
        if ($date) {
            $sql .= " AND td.distribution_date = ?";
            $params[] = $date;
        }
        
        $sql .= " ORDER BY td.distribution_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEmployeeTips($tenantId, $branchId, $employeeId, $startDate = null, $endDate = null)
    {
        $sql = "SELECT tr.*, td.distribution_date, td.total_tip_amount FROM tip_recipients tr JOIN tip_distributions td ON tr.tip_id = td.tip_id WHERE td.tenant_id = ? AND td.branch_id = ? AND tr.employee_id = ?";
        $params = [$tenantId, $branchId, $employeeId];
        
        if ($startDate) {
            $sql .= " AND td.distribution_date >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND td.distribution_date <= ?";
            $params[] = $endDate;
        }
        
        $sql .= " ORDER BY td.distribution_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
