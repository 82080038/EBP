<?php



class SupplyChainRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createRequisition($data)
    {
        $sql = "INSERT INTO purchase_requisitions (tenant_id, branch_id, requisition_number, requisition_date, requested_by, status, approved_by, approved_at, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['requisition_number'],
            $data['requisition_date'],
            $data['requested_by'],
            $data['status'],
            $data['approved_by'] ?? null,
            $data['approved_at'] ?? null,
            $data['notes']
        ]);
        return $this->db->lastInsertId();
    }

    public function getByTenant($tenantId, $branchId = null)
    {
        if ($branchId) {
            $sql = "SELECT pr.*, u.username as requested_by_name FROM purchase_requisitions pr LEFT JOIN users u ON pr.requested_by = u.user_id WHERE pr.tenant_id = ? AND pr.branch_id = ? AND pr.deleted_at IS NULL ORDER BY pr.requisition_date DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId, $branchId]);
        } else {
            $sql = "SELECT pr.*, u.username as requested_by_name FROM purchase_requisitions pr LEFT JOIN users u ON pr.requested_by = u.user_id WHERE pr.tenant_id = ? AND pr.deleted_at IS NULL ORDER BY pr.requisition_date DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateRequisition($reqId, $data)
    {
        $setClauses = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $setClauses[] = "$field = ?";
            $values[] = $value;
        }
        
        $values[] = $reqId;
        
        $sql = "UPDATE purchase_requisitions SET " . implode(', ', $setClauses) . " WHERE requisition_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
    }
}
