<?php



class CustomerPricingRepository
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

    public function createCustomerPrice($data)
    {
        $sql = "INSERT INTO customer_pricing (tenant_id, branch_id, customer_id, product_id, special_price, discount_percentage, valid_from, valid_until, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['customer_id'],
            $data['product_id'],
            $data['special_price'] ?? null,
            $data['discount_percentage'] ?? null,
            $data['valid_from'] ?? null,
            $data['valid_until'] ?? null,
            $data['is_active'] ?? true
        ]);
        return $this->db->lastInsertId();
    }

    public function updateCustomerPrice($pricingId, $data)
    {
        $sql = "UPDATE customer_pricing SET special_price = ?, discount_percentage = ?, valid_from = ?, valid_until = ?, is_active = ? WHERE pricing_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['special_price'] ?? null,
            $data['discount_percentage'] ?? null,
            $data['valid_from'] ?? null,
            $data['valid_until'] ?? null,
            $data['is_active'] ?? true,
            $pricingId
        ]);
    }

    public function getCustomerProductPrice($tenantId, $branchId, $customerId, $productId)
    {
        $sql = "SELECT * FROM customer_pricing WHERE tenant_id = ? AND branch_id = ? AND customer_id = ? AND product_id = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $customerId, $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCustomerPricings($tenantId, $branchId, $customerId)
    {
        $sql = "SELECT cp.*, p.product_name FROM customer_pricing cp JOIN products p ON cp.product_id = p.product_id WHERE cp.tenant_id = ? AND cp.branch_id = ? AND cp.customer_id = ? AND cp.deleted_at IS NULL ORDER BY cp.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
