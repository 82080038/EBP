<?php

if (!class_exists('database')) {
    require_once __DIR__ . '/../../../config/database.php';
}

class CreditRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createCredit($data)
    {
        $sql = "INSERT INTO customer_credits (tenant_id, branch_id, customer_id, credit_amount, credit_type, reference_type, reference_id, due_date, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['customer_id'],
            $data['credit_amount'],
            $data['credit_type'],
            $data['reference_type'] ?? null,
            $data['reference_id'] ?? null,
            $data['due_date'] ?? null,
            $data['status'] ?? 'ACTIVE',
            $data['notes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function getCredit($creditId, $tenantId)
    {
        $sql = "SELECT * FROM customer_credits WHERE credit_id = ? AND tenant_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$creditId, $tenantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateCreditStatus($creditId, $status)
    {
        $sql = "UPDATE customer_credits SET status = ? WHERE credit_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$status, $creditId]);
    }

    public function getCustomerCredits($tenantId, $branchId, $customerId)
    {
        $sql = "SELECT * FROM customer_credits WHERE tenant_id = ? AND branch_id = ? AND customer_id = ? AND deleted_at IS NULL ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOverdueCredits($tenantId, $branchId)
    {
        $sql = "SELECT cc.*, c.customer_name FROM customer_credits cc JOIN customers c ON cc.customer_id = c.customer_id WHERE cc.tenant_id = ? AND cc.branch_id = ? AND cc.status = 'ACTIVE' AND cc.due_date < CURDATE() AND cc.deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
