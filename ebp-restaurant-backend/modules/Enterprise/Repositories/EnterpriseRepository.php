<?php

if (!class_exists('database')) {
    require_once __DIR__ . '/../../../config/database.php';
}

class EnterpriseRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createShiftSchedule($data)
    {
        $sql = "INSERT INTO shift_schedules (tenant_id, branch_id, employee_id, shift_date, shift_type, start_time, end_time, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['employee_id'] ?? null,
            $data['shift_date'],
            $data['shift_type'],
            $data['start_time'],
            $data['end_time'],
            $data['status'] ?? 'SCHEDULED',
            $data['notes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function createPerformanceEvaluation($data)
    {
        $sql = "INSERT INTO performance_evaluations (tenant_id, branch_id, employee_id, evaluation_period_start, evaluation_period_end, overall_score, attendance_score, productivity_score, quality_score, customer_service_score, teamwork_score, comments, evaluator_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['employee_id'],
            $data['evaluation_period_start'],
            $data['evaluation_period_end'],
            $data['overall_score'] ?? null,
            $data['attendance_score'] ?? null,
            $data['productivity_score'] ?? null,
            $data['quality_score'] ?? null,
            $data['customer_service_score'] ?? null,
            $data['teamwork_score'] ?? null,
            $data['comments'] ?? null,
            $data['evaluator_id'],
            $data['status'] ?? 'DRAFT'
        ]);
        return $this->db->lastInsertId();
    }

    public function createCashFlow($data)
    {
        $sql = "INSERT INTO cash_flow (tenant_id, branch_id, flow_date, flow_type, category, amount, description, reference_type, reference_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['flow_date'],
            $data['flow_type'],
            $data['category'] ?? null,
            $data['amount'],
            $data['description'] ?? null,
            $data['reference_type'] ?? null,
            $data['reference_id'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function createBudget($data)
    {
        $sql = "INSERT INTO budgets (tenant_id, branch_id, budget_name, budget_type, period_start, period_end, budgeted_amount, actual_amount, variance, variance_percentage, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['budget_name'],
            $data['budget_type'],
            $data['period_start'],
            $data['period_end'],
            $data['budgeted_amount'],
            $data['actual_amount'] ?? 0,
            $data['variance'] ?? 0,
            $data['variance_percentage'] ?? 0,
            $data['status'] ?? 'ACTIVE'
        ]);
        return $this->db->lastInsertId();
    }

    public function updateBudget($budgetId, $data)
    {
        $setClauses = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $setClauses[] = "$field = ?";
            $values[] = $value;
        }
        
        $values[] = $budgetId;
        
        $sql = "UPDATE budgets SET " . implode(', ', $setClauses) . " WHERE budget_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
    }

    public function getShiftSchedules($tenantId, $branchId, $date = null)
    {
        $sql = "SELECT ss.*, e.employee_name FROM shift_schedules ss LEFT JOIN employees e ON ss.employee_id = e.employee_id WHERE ss.tenant_id = ? AND ss.branch_id = ?";
        $params = [$tenantId, $branchId];
        
        if ($date) {
            $sql .= " AND ss.shift_date = ?";
            $params[] = $date;
        }
        
        $sql .= " ORDER BY ss.shift_date, ss.start_time";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPerformanceEvaluations($tenantId, $branchId, $employeeId = null)
    {
        $sql = "SELECT pe.*, e.employee_name, u.username as evaluator_name FROM performance_evaluations pe LEFT JOIN employees e ON pe.employee_id = e.employee_id LEFT JOIN users u ON pe.evaluator_id = u.user_id WHERE pe.tenant_id = ? AND pe.branch_id = ?";
        $params = [$tenantId, $branchId];
        
        if ($employeeId) {
            $sql .= " AND pe.employee_id = ?";
            $params[] = $employeeId;
        }
        
        $sql .= " ORDER BY pe.evaluation_period_start DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCashFlow($tenantId, $branchId, $startDate = null, $endDate = null)
    {
        $sql = "SELECT * FROM cash_flow WHERE tenant_id = ? AND branch_id = ?";
        $params = [$tenantId, $branchId];
        
        if ($startDate) {
            $sql .= " AND flow_date >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND flow_date <= ?";
            $params[] = $endDate;
        }
        
        $sql .= " ORDER BY flow_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBudgets($tenantId, $branchId, $periodStart = null, $periodEnd = null)
    {
        $sql = "SELECT * FROM budgets WHERE tenant_id = ? AND branch_id = ?";
        $params = [$tenantId, $branchId];
        
        if ($periodStart) {
            $sql .= " AND period_start >= ?";
            $params[] = $periodStart;
        }
        
        if ($periodEnd) {
            $sql .= " AND period_end <= ?";
            $params[] = $periodEnd;
        }
        
        $sql .= " ORDER BY period_start DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
