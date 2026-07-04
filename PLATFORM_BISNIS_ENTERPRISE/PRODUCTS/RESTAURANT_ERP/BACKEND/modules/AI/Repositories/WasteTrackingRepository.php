<?php



class WasteTrackingRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createWaste($data)
    {
        $sql = "INSERT INTO waste_tracking (tenant_id, branch_id, product_id, waste_date, waste_quantity, waste_unit, waste_reason, estimated_cost, recorded_by, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'] ?? null,
            $data['product_id'],
            $data['waste_date'],
            $data['waste_quantity'],
            $data['waste_unit'] ?? null,
            $data['waste_reason'],
            $data['estimated_cost'],
            $data['recorded_by'],
            $data['notes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function getWasteReport($tenantId, $branchId, $dateFrom, $dateTo)
    {
        $sql = "SELECT wt.*, p.product_name, u.username as recorded_by_name FROM waste_tracking wt LEFT JOIN products p ON wt.product_id = p.product_id LEFT JOIN users u ON wt.recorded_by = u.user_id WHERE wt.tenant_id = ? AND wt.branch_id = ? AND wt.waste_date BETWEEN ? AND ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $dateFrom, $dateTo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
