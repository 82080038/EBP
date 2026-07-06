<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

use PDO;

class BusinessHoursService
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
     * Set business hours for a day
     */
    public function setBusinessHours($tenantId, $branchId, $dayOfWeek, $openTime, $closeTime, $isClosed = false, $breakStartTime = null, $breakEndTime = null)
    {
        $sql = "INSERT INTO business_hours (tenant_id, branch_id, day_of_week, open_time, close_time, is_closed, break_start_time, break_end_time)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                open_time = VALUES(open_time),
                close_time = VALUES(close_time),
                is_closed = VALUES(is_closed),
                break_start_time = VALUES(break_start_time),
                break_end_time = VALUES(break_end_time),
                updated_at = CURRENT_TIMESTAMP";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $dayOfWeek, $openTime, $closeTime, $isClosed ? 1 : 0, $breakStartTime, $breakEndTime]);

        return ['success' => true, 'message' => 'Business hours updated successfully'];
    }

    /**
     * Get business hours
     */
    public function getBusinessHours($tenantId, $branchId)
    {
        $sql = "SELECT * FROM business_hours WHERE tenant_id = ? AND branch_id = ? AND is_active = 1 ORDER BY day_of_week";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if business is open at specific time
     */
    public function isOpen($tenantId, $branchId, $dateTime = null)
    {
        $dateTime = $dateTime ?? new DateTime();
        $dayOfWeek = (int)$dateTime->format('N'); // 1=Monday, 7=Sunday
        $currentTime = $dateTime->format('H:i:s');
        $currentDate = $dateTime->format('Y-m-d');

        // Check special schedules first
        $sql = "SELECT * FROM special_schedules 
                WHERE tenant_id = ? AND branch_id = ? AND schedule_date = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $currentDate]);
        $specialSchedule = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($specialSchedule) {
            if ($specialSchedule['is_closed']) {
                return false;
            }
            if ($specialSchedule['open_time'] && $specialSchedule['close_time']) {
                return $currentTime >= $specialSchedule['open_time'] && $currentTime <= $specialSchedule['close_time'];
            }
        }

        // Check regular business hours
        $sql = "SELECT * FROM business_hours 
                WHERE tenant_id = ? AND branch_id = ? AND day_of_week = ? AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $dayOfWeek]);
        $businessHours = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$businessHours || $businessHours['is_closed']) {
            return false;
        }

        return $currentTime >= $businessHours['open_time'] && $currentTime <= $businessHours['close_time'];
    }

    /**
     * Create special schedule
     */
    public function createSpecialSchedule($data, $tenantId, $branchId)
    {
        $sql = "INSERT INTO special_schedules (tenant_id, branch_id, schedule_date, open_time, close_time, is_closed, reason, schedule_type)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                open_time = VALUES(open_time),
                close_time = VALUES(close_time),
                is_closed = VALUES(is_closed),
                reason = VALUES(reason),
                schedule_type = VALUES(schedule_type),
                updated_at = CURRENT_TIMESTAMP";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $tenantId,
            $branchId,
            $data['schedule_date'],
            $data['open_time'] ?? null,
            $data['close_time'] ?? null,
            $data['is_closed'] ?? 0,
            $data['reason'] ?? null,
            $data['schedule_type'] ?? 'SPECIAL'
        ]);

        return ['success' => true, 'message' => 'Special schedule created successfully'];
    }

    /**
     * Get special schedules
     */
    public function getSpecialSchedules($tenantId, $branchId, $startDate = null, $endDate = null)
    {
        $sql = "SELECT * FROM special_schedules WHERE tenant_id = ? AND branch_id = ?";
        $params = [$tenantId, $branchId];

        if ($startDate) {
            $sql .= " AND schedule_date >= ?";
            $params[] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND schedule_date <= ?";
            $params[] = $endDate;
        }

        $sql .= " ORDER BY schedule_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Delete special schedule
     */
    public function deleteSpecialSchedule($scheduleId)
    {
        $sql = "DELETE FROM special_schedules WHERE schedule_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$scheduleId]);

        return ['success' => true, 'message' => 'Special schedule deleted successfully'];
    }
}
