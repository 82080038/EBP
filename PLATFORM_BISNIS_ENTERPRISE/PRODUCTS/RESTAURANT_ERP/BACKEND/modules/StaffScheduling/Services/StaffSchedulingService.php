<?php

use PDO;
use PDOException;

global $pdo;

/**
 * Staff Scheduling Service
 * 
 * Manages staff shifts, scheduling, and availability
 */
class StaffSchedulingService
{
    private $db;
    private $tenantId;
    private $branchId;

    public function __construct($tenantId = null, $branchId = null)
    {
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
    }

    /**
     * Create shift
     */
    public function createShift($data)
    {
        try {
            $sql = "INSERT INTO shifts (tenant_id, branch_id, shift_code, shift_name, start_time, end_time, 
                    break_duration_minutes, is_active) 
                    VALUES (:tenant_id, :branch_id, :shift_code, :shift_name, :start_time, :end_time, 
                    :break_duration_minutes, 1)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':tenant_id' => $this->tenantId,
                ':branch_id' => $this->branchId,
                ':shift_code' => $data['shift_code'],
                ':shift_name' => $data['shift_name'],
                ':start_time' => $data['start_time'],
                ':end_time' => $data['end_time'],
                ':break_duration_minutes' => $data['break_duration'] ?? 0
            ]);

            $shiftId = $this->db->lastInsertId();

            return [
                'success' => true,
                'message' => 'Shift created successfully',
                'data' => ['shift_id' => $shiftId]
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to create shift: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create schedule
     */
    public function createSchedule($data)
    {
        try {
            $sql = "INSERT INTO schedules (tenant_id, branch_id, user_id, shift_id, 
                    schedule_date, status, notes) 
                    VALUES (:tenant_id, :branch_id, :user_id, :shift_id, 
                    :schedule_date, :status, :notes)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':tenant_id' => $this->tenantId,
                ':branch_id' => $this->branchId,
                ':user_id' => $data['user_id'],
                ':shift_id' => $data['shift_id'],
                ':schedule_date' => $data['schedule_date'],
                ':status' => $data['status'] ?? 'scheduled',
                ':notes' => $data['notes'] ?? null
            ]);

            $scheduleId = $this->db->lastInsertId();

            return [
                'success' => true,
                'message' => 'Schedule created successfully',
                'data' => ['schedule_id' => $scheduleId]
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to create schedule: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get schedules by date range
     */
    public function getSchedules($startDate, $endDate)
    {
        try {
            $sql = "SELECT s.*, u.full_name, u.username, sh.shift_name, sh.start_time, sh.end_time 
                    FROM schedules s 
                    LEFT JOIN users u ON s.user_id = u.user_id 
                    LEFT JOIN shifts sh ON s.shift_id = sh.shift_id 
                    WHERE s.tenant_id = :tenant_id 
                    AND s.schedule_date BETWEEN :start_date AND :end_date
                    ORDER BY s.schedule_date, sh.start_time";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':tenant_id' => $this->tenantId,
                ':start_date' => $startDate,
                ':end_date' => $endDate
            ]);
            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $schedules
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to get schedules: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get shifts
     */
    public function getShifts()
    {
        try {
            $sql = "SELECT * FROM shifts WHERE tenant_id = :tenant_id AND is_active = 1 ORDER BY start_time";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':tenant_id' => $this->tenantId]);
            $shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $shifts
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to get shifts: ' . $e->getMessage()
            ];
        }
    }
}
