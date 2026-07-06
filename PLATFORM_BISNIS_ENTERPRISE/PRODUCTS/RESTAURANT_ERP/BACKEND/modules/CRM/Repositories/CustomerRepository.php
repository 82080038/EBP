<?php



class CustomerRepository
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
        $sql = "INSERT INTO customers (tenant_id, customer_code, customer_name, phone, email, date_of_birth, gender, address, city, province, postal_code, country, preferred_branch_id, loyalty_points, loyalty_tier, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['customer_code'],
            $data['customer_name'],
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['date_of_birth'] ?? null,
            $data['gender'] ?? null,
            $data['address'] ?? null,
            $data['city'] ?? null,
            $data['province'] ?? null,
            $data['postal_code'] ?? null,
            $data['country'] ?? 'Indonesia',
            $data['preferred_branch_id'] ?? null,
            $data['loyalty_points'] ?? 0,
            $data['loyalty_tier'] ?? 'BRONZE',
            $data['status'] ?? 'ACTIVE',
            $data['notes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function getByTenant($tenantId, $filters = [])
    {
        $sql = "SELECT * FROM customers WHERE tenant_id = ? AND deleted_at IS NULL";
        $params = [$tenantId];

        if (!empty($filters['loyalty_tier'])) {
            $sql .= " AND loyalty_tier = ?";
            $params[] = $filters['loyalty_tier'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        $sql .= " ORDER BY customer_name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($customerId)
    {
        $sql = "SELECT * FROM customers WHERE customer_id = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($customerId, $data)
    {
        $setClauses = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $setClauses[] = "$field = ?";
            $values[] = $value;
        }
        
        $values[] = $customerId;
        
        $sql = "UPDATE customers SET " . implode(', ', $setClauses) . " WHERE customer_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
    }

    public function updateLoyaltyPoints($customerId, $points)
    {
        $sql = "UPDATE customers SET loyalty_points = ? WHERE customer_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$points, $customerId]);
    }

    public function createLoyaltyTransaction($data)
    {
        $sql = "INSERT INTO loyalty_transactions (tenant_id, customer_id, transaction_type, points, order_id, reference_number, description) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['customer_id'],
            $data['transaction_type'],
            $data['points'],
            $data['order_id'] ?? null,
            $data['reference_number'] ?? null,
            $data['description'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function createVisit($data)
    {
        $sql = "INSERT INTO customer_visits (customer_id, branch_id, visit_date, order_count, total_spent) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['customer_id'],
            $data['branch_id'],
            $data['visit_date'],
            $data['order_count'],
            $data['total_spent']
        ]);
        return $this->db->lastInsertId();
    }

    public function delete($customerId)
    {
        $sql = "UPDATE customers SET deleted_at = NOW() WHERE customer_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
    }
}
