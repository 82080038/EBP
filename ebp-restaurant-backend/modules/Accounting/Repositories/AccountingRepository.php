<?php

require_once __DIR__ . '/../../../config/database.php';

class AccountingRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createJournal($data)
    {
        $sql = "INSERT INTO journal_entries (tenant_id, branch_id, journal_number, journal_date, description, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['journal_number'],
            $data['journal_date'],
            $data['description'],
            $data['status']
        ]);
        return $this->db->lastInsertId();
    }

    public function createJournalLine($data)
    {
        $sql = "INSERT INTO journal_lines (journal_id, account_id, debit, credit, description) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['journal_id'],
            $data['account_id'],
            $data['debit_amount'],
            $data['credit_amount'],
            $data['description']
        ]);
        return $this->db->lastInsertId();
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
            LEFT JOIN journal_entries je ON jl.journal_id = je.journal_id
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
            $sql = str_replace('(je.tenant_id IS NULL OR je.tenant_id = ?)', '(je.tenant_id IS NULL OR je.tenant_id = ?)', $sql);
            $sql = str_replace('AND (je.journal_date <= ? OR je.journal_date IS NULL)', 'AND (je.journal_date <= ? OR je.journal_date IS NULL) AND (je.branch_id IS NULL OR je.branch_id = ?)', $sql);
            $params[] = $branchId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBalanceSheet($tenantId, $branchId, $asOfDate)
    {
        $sql = "
            SELECT 
                coa.account_type,
                coa.account_name,
                SUM(CASE WHEN jl.debit > 0 THEN jl.debit ELSE 0 END) - 
                SUM(CASE WHEN jl.credit > 0 THEN jl.credit ELSE 0 END) as balance
            FROM chart_of_accounts coa
            LEFT JOIN journal_lines jl ON coa.account_id = jl.account_id
            LEFT JOIN journal_entries je ON jl.journal_id = je.journal_id
            WHERE coa.tenant_id = ?
            AND (je.tenant_id IS NULL OR je.tenant_id = ?)
            AND (je.journal_date <= ? OR je.journal_date IS NULL)
            AND coa.account_type IN ('ASSET', 'LIABILITY', 'EQUITY')
            AND coa.is_active = TRUE
            AND coa.deleted_at IS NULL
            GROUP BY coa.account_id, coa.account_type, coa.account_name
            ORDER BY coa.account_type, coa.account_code
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

    public function getProfitLoss($tenantId, $branchId, $periodStart, $periodEnd)
    {
        $sql = "
            SELECT 
                coa.account_type,
                coa.account_name,
                SUM(CASE WHEN jl.debit > 0 THEN jl.debit ELSE 0 END) - 
                SUM(CASE WHEN jl.credit > 0 THEN jl.credit ELSE 0 END) as balance
            FROM chart_of_accounts coa
            LEFT JOIN journal_lines jl ON coa.account_id = jl.account_id
            LEFT JOIN journal_entries je ON jl.journal_id = je.journal_id
            WHERE coa.tenant_id = ?
            AND (je.tenant_id IS NULL OR je.tenant_id = ?)
            AND je.journal_date BETWEEN ? AND ?
            AND coa.account_type IN ('REVENUE', 'EXPENSE')
            AND coa.is_active = TRUE
            AND coa.deleted_at IS NULL
            GROUP BY coa.account_id, coa.account_type, coa.account_name
            ORDER BY coa.account_type, coa.account_code
        ";
        
        $params = [$tenantId, $tenantId, $periodStart, $periodEnd];
        if ($branchId) {
            $sql = str_replace('AND je.journal_date BETWEEN ? AND ?', 'AND je.journal_date BETWEEN ? AND ? AND (je.branch_id IS NULL OR je.branch_id = ?)', $sql);
            $params[] = $branchId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
