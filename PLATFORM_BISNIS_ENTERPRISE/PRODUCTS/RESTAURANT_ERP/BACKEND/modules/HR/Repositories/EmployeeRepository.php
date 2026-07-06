<?php



class EmployeeRepository
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
        $sql = "INSERT INTO employees (tenant_id, branch_id, employee_code, employee_name, position, department, hire_date, status, base_salary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['employee_code'],
            $data['employee_name'],
            $data['position'] ?? null,
            $data['department'] ?? null,
            $data['hire_date'] ?? null,
            $data['status'] ?? 'ACTIVE',
            $data['base_salary'] ?? 0
        ]);
        return $this->db->lastInsertId();
    }

    public function createAttendance($data)
    {
        $sql = "INSERT INTO attendance (tenant_id, branch_id, employee_id, attendance_date, check_in_time, check_out_time, work_hours, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'] ?? null,
            $data['employee_id'],
            $data['attendance_date'],
            $data['check_in_time'],
            $data['check_out_time'],
            $data['work_hours'] ?? null,
            $data['status'],
            $data['notes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function createPayroll($data)
    {
        $sql = "INSERT INTO payroll (tenant_id, branch_id, payroll_number, period_start, period_end, total_gross_pay, total_deductions, total_net_pay, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['payroll_number'],
            $data['period_start'],
            $data['period_end'],
            $data['total_gross_pay'] ?? 0,
            $data['total_deductions'] ?? 0,
            $data['total_net_pay'] ?? 0,
            $data['status']
        ]);
        return $this->db->lastInsertId();
    }

    public function createPayrollItem($data)
    {
        $sql = "INSERT INTO payroll_items (payroll_id, employee_id, base_salary, overtime_hours, overtime_pay, bonus, commission, deductions, net_pay) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['payroll_id'],
            $data['employee_id'],
            $data['base_salary'],
            $data['overtime_hours'],
            $data['overtime_pay'],
            $data['bonus'],
            $data['commission'],
            $data['deductions'],
            $data['net_pay']
        ]);
        return $this->db->lastInsertId();
    }

    public function getByTenant($tenantId, $branchId = null)
    {
        if ($branchId) {
            $sql = "SELECT * FROM employees WHERE tenant_id = ? AND branch_id = ? AND deleted_at IS NULL ORDER BY employee_name ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId, $branchId]);
        } else {
            $sql = "SELECT * FROM employees WHERE tenant_id = ? AND deleted_at IS NULL ORDER BY employee_name ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAttendanceByEmployee($employeeId, $periodStart, $periodEnd)
    {
        $sql = "SELECT * FROM attendance WHERE employee_id = ? AND attendance_date BETWEEN ? AND ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$employeeId, $periodStart, $periodEnd]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updatePayroll($payrollId, $data)
    {
        $setClauses = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $setClauses[] = "$field = ?";
            $values[] = $value;
        }
        
        $values[] = $payrollId;
        
        $sql = "UPDATE payroll SET " . implode(', ', $setClauses) . " WHERE payroll_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
    }
}
