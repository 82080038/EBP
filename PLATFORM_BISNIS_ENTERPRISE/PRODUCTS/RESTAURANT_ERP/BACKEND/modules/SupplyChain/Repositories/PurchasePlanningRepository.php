<?php



class PurchasePlanningRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getLowStockItems($tenantId, $branchId)
    {
        $sql = "SELECT i.*, p.product_name, 10 as min_stock, 100 as max_stock FROM inventory i JOIN products p ON i.product_id = p.product_id WHERE i.tenant_id = ? AND i.branch_id = ? AND i.quantity <= 10 AND i.deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalesHistory($tenantId, $branchId, $startDate, $endDate)
    {
        $sql = "SELECT oi.product_id, SUM(oi.quantity) as quantity FROM order_items oi JOIN orders o ON oi.order_id = o.order_id WHERE o.tenant_id = ? AND o.branch_id = ? AND o.created_at BETWEEN ? AND ? AND o.deleted_at IS NULL GROUP BY oi.product_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createPurchasePlan($data)
    {
        $sql = "INSERT INTO purchase_plans (tenant_id, branch_id, planning_date, plan_status) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['planning_date'],
            $data['plan_status']
        ]);
        return $this->db->lastInsertId();
    }

    public function addPlanItem($data)
    {
        $sql = "INSERT INTO purchase_plan_items (plan_id, product_id, suggested_quantity, current_stock, priority) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['plan_id'],
            $data['product_id'],
            $data['suggested_quantity'],
            $data['current_stock'],
            $data['priority']
        ]);
        return $this->db->lastInsertId();
    }

    public function getPurchasePlan($planId, $tenantId)
    {
        $sql = "SELECT * FROM purchase_plans WHERE plan_id = ? AND tenant_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$planId, $tenantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePlanStatus($planId, $status, $approvedBy)
    {
        $sql = "UPDATE purchase_plans SET plan_status = ?, approved_by = ?, approved_at = CURRENT_TIMESTAMP WHERE plan_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$status, $approvedBy, $planId]);
    }

    public function getPlanItems($planId)
    {
        $sql = "SELECT ppi.*, p.product_name FROM purchase_plan_items ppi JOIN products p ON ppi.product_id = p.product_id WHERE ppi.plan_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$planId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPurchasePlans($tenantId, $branchId, $status = null)
    {
        $sql = "SELECT pp.*, u.username as approved_by_name FROM purchase_plans pp LEFT JOIN users u ON pp.approved_by = u.user_id WHERE pp.tenant_id = ? AND pp.branch_id = ?";
        $params = [$tenantId, $branchId];
        
        if ($status) {
            $sql .= " AND pp.plan_status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY pp.planning_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createPurchaseRequisition($tenantId, $branchId, $data)
    {
        $sql = "INSERT INTO purchase_requisitions (tenant_id, branch_id, requisition_number, requisition_date, requested_by, notes) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $tenantId,
            $branchId,
            $data['requisition_number'],
            $data['requisition_date'],
            $data['requested_by'],
            $data['notes']
        ]);
        return $this->db->lastInsertId();
    }

    public function addRequisitionItem($data)
    {
        $sql = "INSERT INTO purchase_requisition_items (requisition_id, product_id, requested_quantity, notes) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['requisition_id'],
            $data['product_id'],
            $data['requested_quantity'],
            $data['notes']
        ]);
        return $this->db->lastInsertId();
    }
}
