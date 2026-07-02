<?php

if (!class_exists('database')) {
    require_once __DIR__ . '/../../../config/database.php';
}

class StockOpnameRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function create($data)
    {
        $sql = "INSERT INTO stock_opname (tenant_id, branch_id, opname_number, opname_date, status, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['opname_number'],
            $data['opname_date'],
            $data['status'],
            $data['notes'],
            $data['created_by']
        ]);
        return $this->db->lastInsertId();
    }

    public function createItem($data)
    {
        $sql = "INSERT INTO stock_opname_items (opname_id, inventory_id, system_quantity, physical_quantity, difference, unit_cost, difference_value, reason) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['opname_id'],
            $data['inventory_id'],
            $data['system_quantity'],
            $data['physical_quantity'],
            $data['difference'],
            $data['unit_cost'],
            $data['difference_value'],
            $data['reason']
        ]);
        return $this->db->lastInsertId();
    }

    public function getByTenant($tenantId, $branchId = null)
    {
        if ($branchId) {
            $sql = "SELECT * FROM stock_opname WHERE tenant_id = ? AND branch_id = ? AND deleted_at IS NULL ORDER BY opname_date DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId, $branchId]);
        } else {
            $sql = "SELECT * FROM stock_opname WHERE tenant_id = ? AND deleted_at IS NULL ORDER BY opname_date DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getItems($opnameId)
    {
        $sql = "SELECT soi.*, i.product_id, p.product_name 
                 FROM stock_opname_items soi
                 LEFT JOIN inventory i ON soi.inventory_id = i.inventory_id
                 LEFT JOIN products p ON i.product_id = p.product_id
                 WHERE soi.opname_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$opnameId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($opnameId, $data)
    {
        $setClauses = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $setClauses[] = "$field = ?";
            $values[] = $value;
        }
        
        $values[] = $opnameId;
        
        $sql = "UPDATE stock_opname SET " . implode(', ', $setClauses) . " WHERE opname_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
    }
}
