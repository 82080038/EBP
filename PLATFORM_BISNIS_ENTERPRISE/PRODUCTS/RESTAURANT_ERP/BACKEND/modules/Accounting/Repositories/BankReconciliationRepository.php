<?php

class BankReconciliationRepository
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

    public function createReconciliation($data)
    {
        $sql = "INSERT INTO bank_reconciliations (tenant_id, branch_id, bank_account_id, reconciliation_date, statement_balance, book_balance, difference, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['bank_account_id'],
            $data['reconciliation_date'],
            $data['statement_balance'],
            $data['book_balance'],
            $data['difference'],
            $data['status'],
            $data['notes']
        ]);
        return $this->db->lastInsertId();
    }

    public function getReconciliations($tenantId, $branchId, $bankAccountId = null, $status = null)
    {
        $sql = "
            SELECT 
                br.*,
                ba.account_name,
                ba.account_number,
                ba.bank_name
            FROM bank_reconciliations br
            JOIN bank_accounts ba ON br.bank_account_id = ba.bank_account_id
            WHERE br.tenant_id = ?
            AND br.branch_id = ?
            AND br.deleted_at IS NULL
        ";

        $params = [$tenantId, $branchId];

        if ($bankAccountId) {
            $sql .= " AND br.bank_account_id = ?";
            $params[] = $bankAccountId;
        }

        if ($status) {
            $sql .= " AND br.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY br.reconciliation_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReconciliation($tenantId, $branchId, $reconciliationId)
    {
        $sql = "
            SELECT 
                br.*,
                ba.account_name,
                ba.account_number,
                ba.bank_name
            FROM bank_reconciliations br
            JOIN bank_accounts ba ON br.bank_account_id = ba.bank_account_id
            WHERE br.tenant_id = ?
            AND br.branch_id = ?
            AND br.reconciliation_id = ?
            AND br.deleted_at IS NULL
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $reconciliationId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getReconciliationById($reconciliationId)
    {
        $sql = "SELECT * FROM bank_reconciliations WHERE reconciliation_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$reconciliationId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addItem($data)
    {
        $sql = "INSERT INTO bank_reconciliation_items (reconciliation_id, item_type, amount, description, reference_type, reference_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['reconciliation_id'],
            $data['item_type'],
            $data['amount'],
            $data['description'],
            $data['reference_type'],
            $data['reference_id']
        ]);
        return $this->db->lastInsertId();
    }

    public function getReconciliationItems($reconciliationId)
    {
        $sql = "SELECT * FROM bank_reconciliation_items WHERE reconciliation_id = ? ORDER BY created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$reconciliationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateReconciliation($reconciliationId, $data)
    {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $params[] = $value;
        }

        $params[] = $reconciliationId;
        $sql = "UPDATE bank_reconciliations SET " . implode(', ', $fields) . " WHERE reconciliation_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function getBookBalance($tenantId, $branchId, $bankAccountId)
    {
        $sql = "SELECT balance FROM bank_accounts WHERE bank_account_id = ? AND tenant_id = ? AND branch_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$bankAccountId, $tenantId, $branchId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['balance'] : 0.00;
    }

    public function getBankAccounts($tenantId, $branchId)
    {
        $sql = "SELECT * FROM bank_accounts WHERE tenant_id = ? AND branch_id = ? AND is_active = 1 AND deleted_at IS NULL ORDER BY account_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createBankAccount($data)
    {
        $sql = "INSERT INTO bank_accounts (tenant_id, branch_id, account_name, account_number, bank_name, account_type, currency, balance, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['account_name'],
            $data['account_number'],
            $data['bank_name'],
            $data['account_type'],
            $data['currency'],
            $data['balance'],
            $data['is_active']
        ]);
        return $this->db->lastInsertId();
    }
}
