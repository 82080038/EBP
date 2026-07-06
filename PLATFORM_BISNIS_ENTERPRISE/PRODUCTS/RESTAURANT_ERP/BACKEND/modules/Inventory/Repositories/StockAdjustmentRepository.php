<?php



class StockAdjustmentRepository
{
    private $db;

    public function __construct($db = null)
    {
        if ($db) {
            $this->db = $db;
        } else {
            $host = 'localhost';
            $dbname = 'ebp_restaurant_db';
            $username = 'ebp_app';
            $password = 'ebp_secure_password_2026';
            $socket = '/opt/lampp/var/mysql/mysql.sock';

            $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
            $this->db = new PDO($dsn, $username, $password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    public function create($data)
    {
        $sql = "INSERT INTO stock_adjustments (tenant_id, branch_id, adjustment_number, adjustment_type, adjustment_date, reason, reference_number, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['adjustment_number'],
            $data['adjustment_type'],
            $data['adjustment_date'],
            $data['reason'],
            $data['reference_number'],
            $data['status'],
            $data['notes']
        ]);
        return $this->db->lastInsertId();
    }

    public function createItem($data)
    {
        $sql = "INSERT INTO stock_adjustment_items (adjustment_id, inventory_id, batch_number, quantity, unit_cost, total_cost, expiry_date, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['adjustment_id'],
            $data['inventory_id'],
            $data['batch_number'],
            $data['quantity'],
            $data['unit_cost'],
            $data['total_cost'],
            $data['expiry_date'],
            $data['notes']
        ]);
        return $this->db->lastInsertId();
    }

    public function getByTenant($tenantId, $branchId = null)
    {
        if ($branchId) {
            $sql = "SELECT * FROM stock_adjustments WHERE tenant_id = ? AND branch_id = ? AND deleted_at IS NULL ORDER BY adjustment_date DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId, $branchId]);
        } else {
            $sql = "SELECT * FROM stock_adjustments WHERE tenant_id = ? AND deleted_at IS NULL ORDER BY adjustment_date DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($adjustmentId)
    {
        $sql = "SELECT * FROM stock_adjustments WHERE adjustment_id = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$adjustmentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getItems($adjustmentId)
    {
        $sql = "SELECT sai.*, i.product_id, p.product_name 
                 FROM stock_adjustment_items sai
                 LEFT JOIN inventory i ON sai.inventory_id = i.inventory_id
                 LEFT JOIN products p ON i.product_id = p.product_id
                 WHERE sai.adjustment_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$adjustmentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function approve($adjustmentId, $userId)
    {
        $sql = "UPDATE stock_adjustments SET status = 'APPROVED', approved_by = ?, approved_at = NOW() WHERE adjustment_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $adjustmentId]);
    }

    public function update($adjustmentId, $data)
    {
        $setClauses = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $setClauses[] = "$field = ?";
            $values[] = $value;
        }
        
        $values[] = $adjustmentId;
        
        $sql = "UPDATE stock_adjustments SET " . implode(', ', $setClauses) . " WHERE adjustment_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
    }

    public function delete($adjustmentId)
    {
        $sql = "UPDATE stock_adjustments SET deleted_at = NOW() WHERE adjustment_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$adjustmentId]);
    }
}
