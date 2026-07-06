<?php

class AccountingPeriodRepository
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

    public function createPeriod($data)
    {
        $sql = "INSERT INTO accounting_periods (tenant_id, branch_id, fiscal_year, period_number, period_name, start_date, end_date, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['fiscal_year'],
            $data['period_number'],
            $data['period_name'],
            $data['start_date'],
            $data['end_date'],
            $data['status'],
            $data['notes']
        ]);
        return $this->db->lastInsertId();
    }

    public function getPeriods($tenantId, $branchId, $fiscalYear = null, $status = null)
    {
        $sql = "SELECT * FROM accounting_periods WHERE tenant_id = ? AND branch_id = ? AND deleted_at IS NULL";
        $params = [$tenantId, $branchId];

        if ($fiscalYear) {
            $sql .= " AND fiscal_year = ?";
            $params[] = $fiscalYear;
        }

        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY fiscal_year DESC, period_number DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPeriod($tenantId, $branchId, $periodId)
    {
        $sql = "SELECT * FROM accounting_periods WHERE tenant_id = ? AND branch_id = ? AND period_id = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $periodId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPeriodByNumber($tenantId, $branchId, $fiscalYear, $periodNumber)
    {
        $sql = "SELECT * FROM accounting_periods WHERE tenant_id = ? AND branch_id = ? AND fiscal_year = ? AND period_number = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $fiscalYear, $periodNumber]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCurrentPeriod($tenantId, $branchId)
    {
        $currentDate = date('Y-m-d');
        $sql = "SELECT * FROM accounting_periods WHERE tenant_id = ? AND branch_id = ? AND start_date <= ? AND end_date >= ? AND status = 'OPEN' AND deleted_at IS NULL ORDER BY start_date DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $currentDate, $currentDate]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getFuturePeriods($tenantId, $branchId, $fiscalYear, $periodNumber)
    {
        $sql = "SELECT * FROM accounting_periods WHERE tenant_id = ? AND branch_id = ? AND fiscal_year = ? AND period_number > ? AND status = 'OPEN' AND deleted_at IS NULL ORDER BY period_number ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $fiscalYear, $periodNumber]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updatePeriod($periodId, $data)
    {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $params[] = $value;
        }

        $params[] = $periodId;
        $sql = "UPDATE accounting_periods SET " . implode(', ', $fields) . " WHERE period_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function getUnpostedJournalEntries($tenantId, $branchId, $startDate, $endDate)
    {
        $sql = "SELECT journal_id, journal_number, journal_date FROM journal_entries 
                WHERE tenant_id = ? AND branch_id = ? 
                AND journal_date BETWEEN ? AND ? 
                AND status != 'POSTED'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $startDate, $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTrialBalance($tenantId, $branchId, $asOfDate)
    {
        $sql = "
            SELECT 
                coa.account_code,
                coa.account_name,
                coa.account_type,
                SUM(CASE WHEN jl.debit > 0 THEN jl.debit ELSE 0 END) as total_debit,
                SUM(CASE WHEN jl.credit > 0 THEN jl.credit ELSE 0 END) as total_credit
            FROM chart_of_accounts coa
            LEFT JOIN journal_lines jl ON coa.account_id = jl.account_id
            LEFT JOIN journal_entries je ON jl.journal_entry_id = je.journal_entry_id
            WHERE coa.tenant_id = ?
            AND (je.tenant_id IS NULL OR je.tenant_id = ?)
            AND (je.journal_date <= ? OR je.journal_date IS NULL)
            AND coa.is_active = TRUE
            AND coa.deleted_at IS NULL
            GROUP BY coa.account_id, coa.account_code, coa.account_name, coa.account_type
            ORDER BY coa.account_code
        ";
        
        $params = [$tenantId, $tenantId, $asOfDate];
        if ($branchId) {
            $sql = str_replace('AND (je.journal_date <= ? OR je.journal_date IS NULL)', 'AND (je.journal_date <= ? OR je.journal_date IS NULL) AND (je.branch_id IS NULL OR je.branch_id = ?)', $sql);
            $params[] = $branchId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
