<?php



class TaxCalculationRepository
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

    public function getOrder($orderId, $tenantId)
    {
        $sql = "SELECT * FROM orders WHERE order_id = ? AND tenant_id = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId, $tenantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTaxRate($tenantId, $branchId)
    {
        $sql = "SELECT * FROM tax_rates WHERE tenant_id = ? AND branch_id = ? AND is_active = TRUE ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createTaxRate($data)
    {
        $sql = "INSERT INTO tax_rates (tenant_id, branch_id, ppn_rate, pb1_rate, effective_date, is_active) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['ppn_rate'],
            $data['pb1_rate'],
            $data['effective_date'] ?? date('Y-m-d'),
            $data['is_active'] ?? true
        ]);
        return $this->db->lastInsertId();
    }

    public function updateTaxRate($taxRateId, $data)
    {
        $sql = "UPDATE tax_rates SET ppn_rate = ?, pb1_rate = ?, effective_date = ?, is_active = ? WHERE tax_rate_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['ppn_rate'],
            $data['pb1_rate'],
            $data['effective_date'] ?? date('Y-m-d'),
            $data['is_active'] ?? true,
            $taxRateId
        ]);
    }

    public function getOrdersForTax($tenantId, $branchId, $startDate, $endDate)
    {
        $sql = "SELECT * FROM orders WHERE tenant_id = ? AND branch_id = ? AND created_at BETWEEN ? AND ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
