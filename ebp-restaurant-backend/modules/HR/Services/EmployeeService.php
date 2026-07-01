<?php

require_once __DIR__ . '/../Repositories/EmployeeRepository.php';
require_once __DIR__ . '/../../../config/database.php';

class EmployeeService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new EmployeeRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createEmployee($data, $tenantId, $branchId)
    {
        try {
            if (empty($data['employee_code']) || empty($data['employee_name'])) {
                return [
                    'success' => false,
                    'message' => 'Employee code and name are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $branchId;
            $employeeId = $this->repository->create($data);

            return [
                'success' => true,
                'message' => 'Employee created successfully',
                'employee_id' => $employeeId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create employee: ' . $e->getMessage()
            ];
        }
    }

    public function recordAttendance($employeeId, $date, $checkInTime, $checkOutTime, $tenantId)
    {
        try {
            $attendanceData = [
                'tenant_id' => $tenantId,
                'employee_id' => $employeeId,
                'attendance_date' => $date,
                'check_in_time' => $checkInTime,
                'check_out_time' => $checkOutTime,
                'status' => 'PRESENT'
            ];

            if ($checkInTime && $checkOutTime) {
                $checkIn = strtotime($checkInTime);
                $checkOut = strtotime($checkOutTime);
                $workHours = ($checkOut - $checkIn) / 3600;
                $attendanceData['work_hours'] = $workHours;
            }

            $this->repository->createAttendance($attendanceData);

            return [
                'success' => true,
                'message' => 'Attendance recorded successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to record attendance: ' . $e->getMessage()
            ];
        }
    }

    public function calculatePayroll($tenantId, $branchId, $periodStart, $periodEnd)
    {
        try {
            $employees = $this->repository->getByTenant($tenantId, $branchId);
            
            $payrollNumber = 'PAY-' . date('Ymd', strtotime($periodEnd));
            $payrollId = $this->repository->createPayroll([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'payroll_number' => $payrollNumber,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'status' => 'CALCULATED'
            ]);

            $totalGrossPay = 0;
            $totalDeductions = 0;

            foreach ($employees as $employee) {
                $attendance = $this->repository->getAttendanceByEmployee($employee['employee_id'], $periodStart, $periodEnd);
                $totalWorkHours = array_sum(array_column($attendance, 'work_hours'));
                
                $baseSalary = $employee['base_salary'] ?? 0;
                $overtimeHours = max(0, $totalWorkHours - 160); // Assuming 160 hours/month
                $overtimePay = $overtimeHours * ($baseSalary / 160) * 1.5;
                
                $grossPay = $baseSalary + $overtimePay;
                $deductions = $grossPay * 0.1; // 10% deductions
                $netPay = $grossPay - $deductions;

                $this->repository->createPayrollItem([
                    'payroll_id' => $payrollId,
                    'employee_id' => $employee['employee_id'],
                    'base_salary' => $baseSalary,
                    'overtime_hours' => $overtimeHours,
                    'overtime_pay' => $overtimePay,
                    'bonus' => 0,
                    'commission' => 0,
                    'deductions' => $deductions,
                    'net_pay' => $netPay
                ]);

                $totalGrossPay += $grossPay;
                $totalDeductions += $deductions;
            }

            $this->repository->updatePayroll($payrollId, [
                'total_gross_pay' => $totalGrossPay,
                'total_deductions' => $totalDeductions,
                'total_net_pay' => $totalGrossPay - $totalDeductions
            ]);

            return [
                'success' => true,
                'message' => 'Payroll calculated successfully',
                'payroll_id' => $payrollId,
                'total_net_pay' => $totalGrossPay - $totalDeductions
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to calculate payroll: ' . $e->getMessage()
            ];
        }
    }

    public function getEmployees($tenantId, $branchId = null)
    {
        try {
            $employees = $this->repository->getByTenant($tenantId, $branchId);
            
            return [
                'success' => true,
                'message' => 'Employees retrieved successfully',
                'data' => $employees
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get employees: ' . $e->getMessage()
            ];
        }
    }
}
