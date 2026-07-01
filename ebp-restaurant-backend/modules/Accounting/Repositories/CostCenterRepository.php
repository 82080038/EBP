<?php

require_once __DIR__ . '/../../../config/database.php';

class CostCenterRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createCostCenter($data)
    {
        $sql = "INSERT INTO cost_centers (tenant_id, branch_id, cost_center_code, cost_center_name, cost_center_type, parent_cost_center_id, budget_amount, manager_id, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['cost_center_code'],
            $data['cost_center_name'],
            $data['cost_center_type'],
            $data['parent_cost_center_id'] ?? null,
            $data['budget_amount'] ?? null,
            $data['manager_id'] ?? null,
            $data['is_active'] ?? true
        ]);
        return $this->db->lastInsertId();
    }

    public function getCostCenters($tenantId, $branchId)
    {
        $sql = "SELECT cc.*, e.employee_name as manager_name, pcc.cost_center_name as parent_name FROM cost_centers cc LEFT JOIN employees e ON cc.manager_id = e.employee_id LEFT JOIN cost_centers pcc ON cc.parent_cost_center_id = pcc.cost_center_id WHERE cc.tenant_id = ? AND cc.branch_id = ? AND cc.deleted_at IS NULL ORDER BY cc.cost_center_code ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCostCenterReport($tenantId, $branchId, $costCenterId, $dateFrom, $dateTo)
    {
        // Simplified report - aggregate expenses by cost center
        $sql = "
            SELECT 
                cc.cost_center_code,
                cc.cost_center_name,
                cc.budget_amount,
                COALESCE(SUM(jl.debit), 0) as total_expenses,
                COALESCE(cc.budget_amount - SUM(jl.debit), cc.budget_amount) as remaining_budget,
                CASE 
                    WHEN cc.budget_amount > 0 THEN (SUM(jl.debit) / cc.budget_amount * 100)
                    ELSE 0
                END as budget_usage_percentage
            FROM cost_centers cc
            LEFT JOIN journal_lines jl ON jl.cost_center_id = cc.cost_center_id
            LEFT JOIN journal_entries je ON jl.journal_entry_id = je.journal_entry_id
            WHERE cc.tenant_id = ? 
            AND cc.branch_id = ?
            AND cc.deleted_at IS NULL
        ";
        
        $params = [$tenantId, $branchId];
        
        if ($costCenterId) {
            $sql .= " AND cc.cost_center_id = ?";
            $params[] = $costCenterId;
        }
        
        if ($dateFrom && $dateTo) {
            $sql .= " AND je.entry_date BETWEEN ? AND ?";
            $params[] = $dateFrom;
            $params[] = $dateTo;
        }
        
        $sql .= " GROUP BY cc.cost_center_id ORDER BY cc.cost_center_code ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateCostCenter($costCenterId, $data, $tenantId)
    {
        $sql = "UPDATE cost_centers SET cost_center_name = ?, cost_center_type = ?, parent_cost_center_id = ?, budget_amount = ?, manager_id = ?, is_active = ? WHERE cost_center_id = ? AND tenant_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['cost_center_name'],
            $data['cost_center_type'],
            $data['parent_cost_center_id'] ?? null,
            $data['budget_amount'] ?? null,
            $data['manager_id'] ?? null,
            $data['is_active'] ?? true,
            $costCenterId,
            $tenantId
        ]);
    }
}
