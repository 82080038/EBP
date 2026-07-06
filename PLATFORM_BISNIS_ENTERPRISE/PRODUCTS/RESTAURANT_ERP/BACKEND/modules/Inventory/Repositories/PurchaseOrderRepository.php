<?php



class PurchaseOrderRepository
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
        $sql = "INSERT INTO purchase_orders (tenant_id, branch_id, po_number, supplier_id, order_date, expected_delivery_date, status, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['po_number'],
            $data['supplier_id'],
            $data['order_date'],
            $data['expected_delivery_date'],
            $data['status'],
            $data['notes'],
            $data['created_by']
        ]);
        return $this->db->lastInsertId();
    }

    public function createItem($data)
    {
        $sql = "INSERT INTO purchase_order_items (purchase_order_id, inventory_id, quantity, unit_price, discount_percentage, discount_amount, tax_percentage, tax_amount, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['purchase_order_id'],
            $data['inventory_id'],
            $data['quantity'],
            $data['unit_price'],
            $data['discount_percentage'],
            $data['discount_amount'],
            $data['tax_percentage'],
            $data['tax_amount'],
            $data['subtotal']
        ]);
        return $this->db->lastInsertId();
    }

    public function getByTenant($tenantId, $branchId = null)
    {
        if ($branchId) {
            $sql = "SELECT po.*, s.supplier_name FROM purchase_orders po LEFT JOIN suppliers s ON po.supplier_id = s.supplier_id WHERE po.tenant_id = ? AND po.branch_id = ? AND po.deleted_at IS NULL ORDER BY po.order_date DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId, $branchId]);
        } else {
            $sql = "SELECT po.*, s.supplier_name FROM purchase_orders po LEFT JOIN suppliers s ON po.supplier_id = s.supplier_id WHERE po.tenant_id = ? AND po.deleted_at IS NULL ORDER BY po.order_date DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getItems($poId)
    {
        $sql = "SELECT poi.*, i.product_id, p.product_name 
                 FROM purchase_order_items poi
                 LEFT JOIN inventory i ON poi.inventory_id = i.inventory_id
                 LEFT JOIN products p ON i.product_id = p.product_id
                 WHERE poi.purchase_order_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$poId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($poId, $data)
    {
        $setClauses = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $setClauses[] = "$field = ?";
            $values[] = $value;
        }
        
        $values[] = $poId;
        
        $sql = "UPDATE purchase_orders SET " . implode(', ', $setClauses) . " WHERE po_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
    }
}
