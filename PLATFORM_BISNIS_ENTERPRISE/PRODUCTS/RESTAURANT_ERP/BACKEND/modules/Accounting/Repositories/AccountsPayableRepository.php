<?php

class AccountsPayableRepository
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

    public function createBill($data)
    {
        $sql = "INSERT INTO accounts_payable (tenant_id, branch_id, supplier_id, bill_number, bill_date, due_date, amount, paid_amount, balance_amount, status, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['supplier_id'],
            $data['bill_number'],
            $data['bill_date'],
            $data['due_date'],
            $data['amount'],
            $data['paid_amount'],
            $data['balance_amount'],
            $data['status'],
            $data['description']
        ]);
        return $this->db->lastInsertId();
    }

    public function getBills($tenantId, $branchId, $status = null, $supplierId = null)
    {
        $sql = "
            SELECT 
                ap.*,
                s.supplier_name,
                s.email
            FROM accounts_payable ap
            LEFT JOIN suppliers s ON ap.supplier_id = s.supplier_id
            WHERE ap.tenant_id = ?
            AND ap.branch_id = ?
            AND ap.deleted_at IS NULL
        ";

        $params = [$tenantId, $branchId];

        if ($status) {
            $sql .= " AND ap.status = ?";
            $params[] = $status;
        }

        if ($supplierId) {
            $sql .= " AND ap.supplier_id = ?";
            $params[] = $supplierId;
        }

        $sql .= " ORDER BY ap.bill_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBill($tenantId, $branchId, $apId)
    {
        $sql = "
            SELECT 
                ap.*,
                s.supplier_name,
                s.email,
                s.phone
            FROM accounts_payable ap
            LEFT JOIN suppliers s ON ap.supplier_id = s.supplier_id
            WHERE ap.tenant_id = ?
            AND ap.branch_id = ?
            AND ap.ap_id = ?
            AND ap.deleted_at IS NULL
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $apId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addPayment($data)
    {
        $sql = "INSERT INTO ap_payments (tenant_id, branch_id, ap_id, payment_date, amount, payment_method, reference_number, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['ap_id'],
            $data['payment_date'],
            $data['amount'],
            $data['payment_method'],
            $data['reference_number'],
            $data['notes']
        ]);
        return $this->db->lastInsertId();
    }

    public function updateBill($apId, $data)
    {
        $sql = "UPDATE accounts_payable SET paid_amount = ?, balance_amount = ?, status = ? WHERE ap_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['paid_amount'],
            $data['balance_amount'],
            $data['status'],
            $apId
        ]);
    }

    public function getBillPayments($apId)
    {
        $sql = "SELECT * FROM ap_payments WHERE ap_id = ? AND deleted_at IS NULL ORDER BY payment_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$apId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAgingReport($tenantId, $branchId)
    {
        $currentDate = date('Y-m-d');
        
        $sql = "
            SELECT 
                ap.ap_id,
                ap.bill_number,
                ap.bill_date,
                ap.due_date,
                ap.amount,
                ap.paid_amount,
                ap.balance_amount,
                ap.status,
                s.supplier_name,
                CASE 
                    WHEN ap.due_date < DATE_SUB(?, INTERVAL 30 DAY) THEN '0-30'
                    WHEN ap.due_date < DATE_SUB(?, INTERVAL 60 DAY) THEN '31-60'
                    WHEN ap.due_date < DATE_SUB(?, INTERVAL 90 DAY) THEN '61-90'
                    ELSE '90+'
                END as aging_bucket,
                DATEDIFF(?, ap.due_date) as days_overdue
            FROM accounts_payable ap
            LEFT JOIN suppliers s ON ap.supplier_id = s.supplier_id
            WHERE ap.tenant_id = ?
            AND ap.branch_id = ?
            AND ap.status IN ('PENDING', 'PARTIAL', 'OVERDUE')
            AND ap.deleted_at IS NULL
            ORDER BY ap.due_date ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$currentDate, $currentDate, $currentDate, $currentDate, $tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
