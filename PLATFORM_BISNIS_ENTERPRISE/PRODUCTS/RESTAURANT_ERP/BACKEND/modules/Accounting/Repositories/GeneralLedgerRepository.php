<?php

class GeneralLedgerRepository
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

    public function getLedger($tenantId, $branchId, $startDate, $endDate, $accountId = null)
    {
        $sql = "
            SELECT 
                gl.ledger_id,
                gl.transaction_date,
                coa.account_code,
                coa.account_name,
                coa.account_type,
                gl.description,
                gl.debit_amount,
                gl.credit_amount,
                gl.balance,
                gl.reference_type,
                gl.reference_id
            FROM general_ledger gl
            JOIN chart_of_accounts coa ON gl.account_id = coa.account_id
            WHERE gl.tenant_id = ?
            AND gl.branch_id = ?
            AND gl.transaction_date BETWEEN ? AND ?
            AND gl.deleted_at IS NULL
        ";

        $params = [$tenantId, $branchId, $startDate, $endDate];

        if ($accountId) {
            $sql .= " AND gl.account_id = ?";
            $params[] = $accountId;
        }

        $sql .= " ORDER BY gl.transaction_date, gl.ledger_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAccountBalance($tenantId, $branchId, $accountId, $asOfDate)
    {
        $sql = "
            SELECT 
                coa.account_id,
                coa.account_code,
                coa.account_name,
                coa.account_type,
                SUM(gl.debit_amount) as total_debit,
                SUM(gl.credit_amount) as total_credit,
                SUM(gl.debit_amount) - SUM(gl.credit_amount) as balance
            FROM general_ledger gl
            JOIN chart_of_accounts coa ON gl.account_id = coa.account_id
            WHERE gl.tenant_id = ?
            AND gl.branch_id = ?
            AND gl.account_id = ?
            AND gl.transaction_date <= ?
            AND gl.deleted_at IS NULL
            GROUP BY coa.account_id, coa.account_code, coa.account_name, coa.account_type
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $accountId, $asOfDate]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCashFlowStatement($tenantId, $branchId, $startDate, $endDate)
    {
        $sql = "
            SELECT 
                cash_flow_type,
                sub_type,
                SUM(amount) as total_amount
            FROM cash_flow_items
            WHERE tenant_id = ?
            AND branch_id = ?
            AND transaction_date BETWEEN ? AND ?
            AND deleted_at IS NULL
            GROUP BY cash_flow_type, sub_type
            ORDER BY cash_flow_type, sub_type
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $startDate, $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createGeneralLedgerEntry($data)
    {
        $sql = "INSERT INTO general_ledger (tenant_id, branch_id, journal_entry_id, journal_line_id, account_id, transaction_date, reference_type, reference_id, description, debit_amount, credit_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['journal_entry_id'],
            $data['journal_line_id'],
            $data['account_id'],
            $data['transaction_date'],
            $data['reference_type'],
            $data['reference_id'],
            $data['description'],
            $data['debit_amount'],
            $data['credit_amount']
        ]);
        return $this->db->lastInsertId();
    }

    public function getJournalEntry($journalEntryId)
    {
        $sql = "SELECT * FROM journal_entries WHERE journal_entry_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$journalEntryId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getJournalLines($journalEntryId)
    {
        $sql = "SELECT * FROM journal_lines WHERE journal_entry_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$journalEntryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
