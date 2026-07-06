<?php

class BudgetRepository
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

    public function createBudget($data)
    {
        $sql = "INSERT INTO budgets (tenant_id, branch_id, budget_name, fiscal_year, start_date, end_date, total_budget, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['budget_name'],
            $data['fiscal_year'],
            $data['start_date'],
            $data['end_date'],
            $data['total_budget'],
            $data['status'],
            $data['notes']
        ]);
        return $this->db->lastInsertId();
    }

    public function getBudgets($tenantId, $branchId, $fiscalYear = null, $status = null)
    {
        $sql = "SELECT * FROM budgets WHERE tenant_id = ? AND branch_id = ? AND deleted_at IS NULL";
        $params = [$tenantId, $branchId];

        if ($fiscalYear) {
            $sql .= " AND fiscal_year = ?";
            $params[] = $fiscalYear;
        }

        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY fiscal_year DESC, start_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBudget($tenantId, $branchId, $budgetId)
    {
        $sql = "SELECT * FROM budgets WHERE tenant_id = ? AND branch_id = ? AND budget_id = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $budgetId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateBudget($budgetId, $data)
    {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $params[] = $value;
        }

        $params[] = $budgetId;
        $sql = "UPDATE budgets SET " . implode(', ', $fields) . " WHERE budget_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function addBudgetItem($data)
    {
        $sql = "INSERT INTO budget_items (budget_id, account_id, budgeted_amount, actual_amount, variance, period_type) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['budget_id'],
            $data['account_id'],
            $data['budgeted_amount'],
            $data['actual_amount'],
            $data['variance'],
            $data['period_type']
        ]);
        return $this->db->lastInsertId();
    }

    public function getBudgetItems($budgetId)
    {
        $sql = "
            SELECT 
                bi.*,
                coa.account_code,
                coa.account_name,
                coa.account_type
            FROM budget_items bi
            JOIN chart_of_accounts coa ON bi.account_id = coa.account_id
            WHERE bi.budget_id = ?
            ORDER BY coa.account_type, coa.account_code
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$budgetId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActualAmount($tenantId, $branchId, $accountId, $startDate, $endDate)
    {
        $sql = "
            SELECT 
                SUM(CASE WHEN debit_amount > 0 THEN debit_amount ELSE 0 END) - 
                SUM(CASE WHEN credit_amount > 0 THEN credit_amount ELSE 0 END) as net_amount
            FROM general_ledger
            WHERE tenant_id = ?
            AND branch_id = ?
            AND account_id = ?
            AND transaction_date BETWEEN ? AND ?
            AND deleted_at IS NULL
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $accountId, $startDate, $endDate]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['net_amount'] : 0;
    }
}
