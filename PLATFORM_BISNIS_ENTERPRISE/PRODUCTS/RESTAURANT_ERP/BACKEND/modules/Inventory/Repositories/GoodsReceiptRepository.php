<?php



class GoodsReceiptRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function create($data)
    {
        $sql = "INSERT INTO goods_receipt (tenant_id, branch_id, receipt_number, purchase_order_id, supplier_id, receipt_date, status, notes, received_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['receipt_number'],
            $data['purchase_order_id'],
            $data['supplier_id'],
            $data['receipt_date'],
            $data['status'],
            $data['notes'],
            $data['received_by']
        ]);
        return $this->db->lastInsertId();
    }

    public function createItem($data)
    {
        $sql = "INSERT INTO goods_receipt_items (receipt_id, inventory_id, quantity, unit_cost, batch_number, expiry_date, manufacturing_date, total_cost) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['receipt_id'],
            $data['inventory_id'],
            $data['quantity'],
            $data['unit_cost'],
            $data['batch_number'],
            $data['expiry_date'],
            $data['manufacturing_date'],
            $data['total_cost']
        ]);
        return $this->db->lastInsertId();
    }

    public function getByTenant($tenantId, $branchId = null)
    {
        if ($branchId) {
            $sql = "SELECT gr.*, s.supplier_name FROM goods_receipt gr LEFT JOIN suppliers s ON gr.supplier_id = s.supplier_id WHERE gr.tenant_id = ? AND gr.branch_id = ? AND gr.deleted_at IS NULL ORDER BY gr.receipt_date DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId, $branchId]);
        } else {
            $sql = "SELECT gr.*, s.supplier_name FROM goods_receipt gr LEFT JOIN suppliers s ON gr.supplier_id = s.supplier_id WHERE gr.tenant_id = ? AND gr.deleted_at IS NULL ORDER BY gr.receipt_date DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getItems($receiptId)
    {
        $sql = "SELECT gri.*, i.product_id, p.product_name 
                 FROM goods_receipt_items gri
                 LEFT JOIN inventory i ON gri.inventory_id = i.inventory_id
                 LEFT JOIN products p ON i.product_id = p.product_id
                 WHERE gri.receipt_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$receiptId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($receiptId, $data)
    {
        $setClauses = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $setClauses[] = "$field = ?";
            $values[] = $value;
        }
        
        $values[] = $receiptId;
        
        $sql = "UPDATE goods_receipt SET " . implode(', ', $setClauses) . " WHERE receipt_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
    }
}
