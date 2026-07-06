<?php

class AccountsReceivableRepository
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

    public function createInvoice($data)
    {
        $sql = "INSERT INTO accounts_receivable (tenant_id, branch_id, customer_id, invoice_number, invoice_date, due_date, amount, paid_amount, balance_amount, status, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['customer_id'],
            $data['invoice_number'],
            $data['invoice_date'],
            $data['due_date'],
            $data['amount'],
            $data['paid_amount'],
            $data['balance_amount'],
            $data['status'],
            $data['description']
        ]);
        return $this->db->lastInsertId();
    }

    public function getInvoices($tenantId, $branchId, $status = null, $customerId = null)
    {
        $sql = "
            SELECT 
                ar.*,
                c.customer_name,
                c.email
            FROM accounts_receivable ar
            LEFT JOIN customers c ON ar.customer_id = c.customer_id
            WHERE ar.tenant_id = ?
            AND ar.branch_id = ?
            AND ar.deleted_at IS NULL
        ";

        $params = [$tenantId, $branchId];

        if ($status) {
            $sql .= " AND ar.status = ?";
            $params[] = $status;
        }

        if ($customerId) {
            $sql .= " AND ar.customer_id = ?";
            $params[] = $customerId;
        }

        $sql .= " ORDER BY ar.invoice_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getInvoice($tenantId, $branchId, $arId)
    {
        $sql = "
            SELECT 
                ar.*,
                c.customer_name,
                c.email,
                c.phone
            FROM accounts_receivable ar
            LEFT JOIN customers c ON ar.customer_id = c.customer_id
            WHERE ar.tenant_id = ?
            AND ar.branch_id = ?
            AND ar.ar_id = ?
            AND ar.deleted_at IS NULL
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $arId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addPayment($data)
    {
        $sql = "INSERT INTO ar_payments (tenant_id, branch_id, ar_id, payment_date, amount, payment_method, reference_number, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['ar_id'],
            $data['payment_date'],
            $data['amount'],
            $data['payment_method'],
            $data['reference_number'],
            $data['notes']
        ]);
        return $this->db->lastInsertId();
    }

    public function updateInvoice($arId, $data)
    {
        $sql = "UPDATE accounts_receivable SET paid_amount = ?, balance_amount = ?, status = ? WHERE ar_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['paid_amount'],
            $data['balance_amount'],
            $data['status'],
            $arId
        ]);
    }

    public function getInvoicePayments($arId)
    {
        $sql = "SELECT * FROM ar_payments WHERE ar_id = ? AND deleted_at IS NULL ORDER BY payment_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$arId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAgingReport($tenantId, $branchId)
    {
        $currentDate = date('Y-m-d');
        
        $sql = "
            SELECT 
                ar.ar_id,
                ar.invoice_number,
                ar.invoice_date,
                ar.due_date,
                ar.amount,
                ar.paid_amount,
                ar.balance_amount,
                ar.status,
                c.customer_name,
                CASE 
                    WHEN ar.due_date < DATE_SUB(?, INTERVAL 30 DAY) THEN '0-30'
                    WHEN ar.due_date < DATE_SUB(?, INTERVAL 60 DAY) THEN '31-60'
                    WHEN ar.due_date < DATE_SUB(?, INTERVAL 90 DAY) THEN '61-90'
                    ELSE '90+'
                END as aging_bucket,
                DATEDIFF(?, ar.due_date) as days_overdue
            FROM accounts_receivable ar
            LEFT JOIN customers c ON ar.customer_id = c.customer_id
            WHERE ar.tenant_id = ?
            AND ar.branch_id = ?
            AND ar.status IN ('PENDING', 'PARTIAL', 'OVERDUE')
            AND ar.deleted_at IS NULL
            ORDER BY ar.due_date ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$currentDate, $currentDate, $currentDate, $currentDate, $tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
