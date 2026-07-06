<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

use PDO;

class HolidayService
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
     * Create holiday
     */
    public function createHoliday($data, $tenantId, $branchId = null)
    {
        $sql = "INSERT INTO holidays (tenant_id, branch_id, holiday_name, holiday_date, holiday_type, is_recurring, recurring_month, recurring_day, description, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $tenantId,
            $branchId,
            $data['holiday_name'],
            $data['holiday_date'],
            $data['holiday_type'] ?? 'PUBLIC',
            $data['is_recurring'] ?? 0,
            $data['recurring_month'] ?? null,
            $data['recurring_day'] ?? null,
            $data['description'] ?? null
        ]);

        return ['success' => true, 'message' => 'Holiday created successfully', 'holiday_id' => $this->db->lastInsertId()];
    }

    /**
     * Get holidays
     */
    public function getHolidays($tenantId, $branchId = null, $startDate = null, $endDate = null)
    {
        $sql = "SELECT * FROM holidays WHERE tenant_id = ? AND is_active = 1";
        $params = [$tenantId];

        if ($branchId) {
            $sql .= " AND (branch_id IS NULL OR branch_id = ?)";
            $params[] = $branchId;
        } else {
            $sql .= " AND branch_id IS NULL";
        }

        if ($startDate) {
            $sql .= " AND holiday_date >= ?";
            $params[] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND holiday_date <= ?";
            $params[] = $endDate;
        }

        $sql .= " ORDER BY holiday_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update holiday
     */
    public function updateHoliday($holidayId, $data)
    {
        $sql = "UPDATE holidays SET holiday_name = ?, holiday_date = ?, holiday_type = ?, is_recurring = ?, description = ?, updated_at = CURRENT_TIMESTAMP
                WHERE holiday_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['holiday_name'],
            $data['holiday_date'],
            $data['holiday_type'],
            $data['is_recurring'],
            $data['description'],
            $holidayId
        ]);

        return ['success' => true, 'message' => 'Holiday updated successfully'];
    }

    /**
     * Delete holiday
     */
    public function deleteHoliday($holidayId)
    {
        $sql = "UPDATE holidays SET is_active = 0, updated_at = CURRENT_TIMESTAMP WHERE holiday_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$holidayId]);

        return ['success' => true, 'message' => 'Holiday deleted successfully'];
    }

    /**
     * Check if date is a holiday
     */
    public function isHoliday($tenantId, $branchId, $date)
    {
        $sql = "SELECT holiday_id FROM holidays 
                WHERE tenant_id = ? AND holiday_date = ? AND is_active = 1
                AND (branch_id IS NULL OR branch_id = ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $date, $branchId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }
}
