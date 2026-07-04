<?php



class InventoryAdvancedRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createRepurposing($data)
    {
        $sql = "INSERT INTO inventory_repurposing (tenant_id, branch_id, repurposing_date, from_product_id, to_product_id, quantity, unit, conversion_ratio, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['repurposing_date'],
            $data['from_product_id'],
            $data['to_product_id'],
            $data['quantity'],
            $data['unit'] ?? null,
            $data['conversion_ratio'] ?? 1,
            $data['notes'] ?? null,
            $data['created_by']
        ]);
        return $this->db->lastInsertId();
    }

    public function updateInventoryQuantity($productId, $quantityChange)
    {
        $sql = "UPDATE inventory SET quantity = quantity + ? WHERE product_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quantityChange, $productId]);
    }

    public function updateInventoryQuantityByBranch($productId, $branchId, $quantityChange)
    {
        $sql = "UPDATE inventory SET quantity = quantity + ? WHERE product_id = ? AND branch_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quantityChange, $productId, $branchId]);
    }

    public function addStockTransaction($data)
    {
        $sql = "INSERT INTO stock_transactions (tenant_id, branch_id, product_id, transaction_type, quantity, notes) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['product_id'],
            $data['transaction_type'],
            $data['quantity'],
            $data['notes']
        ]);
        return $this->db->lastInsertId();
    }

    public function createStockTransfer($data)
    {
        $sql = "INSERT INTO stock_transfers (tenant_id, from_branch_id, to_branch_id, transfer_date, transfer_number, status, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['from_branch_id'],
            $data['to_branch_id'],
            $data['transfer_date'],
            $data['transfer_number'],
            $data['status'] ?? 'PENDING',
            $data['notes'] ?? null,
            $data['created_by']
        ]);
        return $this->db->lastInsertId();
    }

    public function addStockTransferItem($data)
    {
        $sql = "INSERT INTO stock_transfer_items (transfer_id, product_id, quantity, unit, notes) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['transfer_id'],
            $data['product_id'],
            $data['quantity'],
            $data['unit'],
            $data['notes']
        ]);
        return $this->db->lastInsertId();
    }

    public function getStockTransfer($transferId, $tenantId)
    {
        $sql = "SELECT * FROM stock_transfers WHERE transfer_id = ? AND tenant_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$transferId, $tenantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getStockTransferItems($transferId)
    {
        $sql = "SELECT sti.*, p.product_name FROM stock_transfer_items sti JOIN products p ON sti.product_id = p.product_id WHERE sti.transfer_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$transferId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStockTransferStatus($transferId, $status, $receivedBy)
    {
        $sql = "UPDATE stock_transfers SET status = ?, received_by = ?, received_at = CURRENT_TIMESTAMP WHERE transfer_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$status, $receivedBy, $transferId]);
    }

    public function getStockTransfers($tenantId, $branchId, $status = null)
    {
        $sql = "SELECT st.*, b1.branch_name as from_branch_name, b2.branch_name as to_branch_name FROM stock_transfers st LEFT JOIN branches b1 ON st.from_branch_id = b1.branch_id LEFT JOIN branches b2 ON st.to_branch_id = b2.branch_id WHERE st.tenant_id = ? AND (st.from_branch_id = ? OR st.to_branch_id = ?)";
        $params = [$tenantId, $branchId, $branchId];
        
        if ($status) {
            $sql .= " AND st.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY st.transfer_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRepurposingHistory($tenantId, $branchId, $dateFrom = null, $dateTo = null)
    {
        $sql = "SELECT ir.*, p1.product_name as from_product_name, p2.product_name as to_product_name FROM inventory_repurposing ir LEFT JOIN products p1 ON ir.from_product_id = p1.product_id LEFT JOIN products p2 ON ir.to_product_id = p2.product_id WHERE ir.tenant_id = ? AND ir.branch_id = ?";
        $params = [$tenantId, $branchId];
        
        if ($dateFrom) {
            $sql .= " AND ir.repurposing_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND ir.repurposing_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql .= " ORDER BY ir.repurposing_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
