<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

if (!class_exists('EmployeeRepository')) {
    require_once __DIR__ . '/../Repositories/EmployeeRepository.php';
}

use PDO;

class AttendanceService
{
    private $db;

    public function __construct()
    {
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Check in employee
     */
    public function checkIn($employeeId, $tenantId, $branchId, $checkInTime = null)
    {
        $attendanceDate = date('Y-m-d');
        $checkInTime = $checkInTime ?? date('H:i:s');

        // Check if already checked in today
        $sql = "SELECT attendance_id FROM attendance 
                WHERE employee_id = ? AND attendance_date = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$employeeId, $attendanceDate]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            return ['success' => false, 'message' => 'Already checked in today'];
        }

        $sql = "INSERT INTO attendance (tenant_id, branch_id, employee_id, attendance_date, check_in_time, status)
                VALUES (?, ?, ?, ?, ?, 'PRESENT')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $employeeId, $attendanceDate, $checkInTime]);

        return ['success' => true, 'message' => 'Check in successful', 'attendance_id' => $this->db->lastInsertId()];
    }

    /**
     * Check out employee
     */
    public function checkOut($employeeId, $checkOutTime = null)
    {
        $attendanceDate = date('Y-m-d');
        $checkOutTime = $checkOutTime ?? date('H:i:s');

        $sql = "SELECT attendance_id, check_in_time FROM attendance 
                WHERE employee_id = ? AND attendance_date = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$employeeId, $attendanceDate]);
        $attendance = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$attendance) {
            return ['success' => false, 'message' => 'No check-in record found for today'];
        }

        if ($attendance['check_out_time']) {
            return ['success' => false, 'message' => 'Already checked out today'];
        }

        // Calculate work hours
        $checkIn = strtotime($attendance['check_in_time']);
        $checkOut = strtotime($checkOutTime);
        $workMinutes = ($checkOut - $checkIn) / 60;
        $workHours = round($workMinutes / 60, 2);

        $sql = "UPDATE attendance 
                SET check_out_time = ?, work_hours = ?
                WHERE attendance_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$checkOutTime, $workHours, $attendance['attendance_id']]);

        return ['success' => true, 'message' => 'Check out successful', 'work_hours' => $workHours];
    }

    /**
     * Get attendance records
     */
    public function getAttendance($tenantId, $branchId, $employeeId = null, $startDate = null, $endDate = null)
    {
        $sql = "SELECT a.*, e.employee_name, e.employee_code 
                FROM attendance a
                JOIN employees e ON a.employee_id = e.employee_id
                WHERE a.tenant_id = ? AND a.branch_id = ?";
        $params = [$tenantId, $branchId];

        if ($employeeId) {
            $sql .= " AND a.employee_id = ?";
            $params[] = $employeeId;
        }

        if ($startDate) {
            $sql .= " AND a.attendance_date >= ?";
            $params[] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND a.attendance_date <= ?";
            $params[] = $endDate;
        }

        $sql .= " ORDER BY a.attendance_date DESC, a.check_in_time DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Start break
     */
    public function startBreak($employeeId)
    {
        $attendanceDate = date('Y-m-d');
        $breakStartTime = date('H:i:s');

        $sql = "UPDATE attendance 
                SET break_start_time = ?
                WHERE employee_id = ? AND attendance_date = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$breakStartTime, $employeeId, $attendanceDate]);

        return ['success' => true, 'message' => 'Break started'];
    }

    /**
     * End break
     */
    public function endBreak($employeeId)
    {
        $attendanceDate = date('Y-m-d');
        $breakEndTime = date('H:i:s');

        $sql = "UPDATE attendance 
                SET break_end_time = ?
                WHERE employee_id = ? AND attendance_date = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$breakEndTime, $employeeId, $attendanceDate]);

        return ['success' => true, 'message' => 'Break ended'];
    }

    /**
     * Get attendance summary
     */
    public function getAttendanceSummary($tenantId, $branchId, $startDate, $endDate)
    {
        $sql = "SELECT 
                    COUNT(*) as total_days,
                    SUM(CASE WHEN status = 'PRESENT' THEN 1 ELSE 0 END) as present_days,
                    SUM(CASE WHEN status = 'ABSENT' THEN 1 ELSE 0 END) as absent_days,
                    SUM(CASE WHEN status = 'LATE' THEN 1 ELSE 0 END) as late_days,
                    SUM(CASE WHEN status = 'ON_LEAVE' THEN 1 ELSE 0 END) as leave_days,
                    SUM(work_hours) as total_work_hours,
                    SUM(overtime_hours) as total_overtime_hours
                FROM attendance
                WHERE tenant_id = ? AND branch_id = ?
                AND attendance_date BETWEEN ? AND ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $startDate, $endDate]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
