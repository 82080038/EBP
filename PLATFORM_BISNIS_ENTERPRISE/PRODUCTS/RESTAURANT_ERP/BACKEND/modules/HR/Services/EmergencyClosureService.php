<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

use PDO;

class EmergencyClosureService
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
     * Create emergency closure
     */
    public function createClosure($data, $tenantId, $branchId, $createdBy)
    {
        $sql = "INSERT INTO emergency_closures (tenant_id, branch_id, closure_type, start_time, end_time, is_active, severity, description, impact_assessment, recovery_plan, created_by)
                VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $tenantId,
            $branchId,
            $data['closure_type'],
            $data['start_time'],
            $data['end_time'] ?? null,
            $data['severity'] ?? 'MEDIUM',
            $data['description'] ?? null,
            $data['impact_assessment'] ?? null,
            $data['recovery_plan'] ?? null,
            $createdBy
        ]);

        return ['success' => true, 'message' => 'Emergency closure created successfully', 'closure_id' => $this->db->lastInsertId()];
    }

    /**
     * Get active closures
     */
    public function getActiveClosures($tenantId, $branchId = null)
    {
        $sql = "SELECT * FROM emergency_closures WHERE tenant_id = ? AND is_active = 1";
        $params = [$tenantId];

        if ($branchId) {
            $sql .= " AND branch_id = ?";
            $params[] = $branchId;
        }

        $sql .= " ORDER BY start_time DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all closures
     */
    public function getAllClosures($tenantId, $branchId = null, $startDate = null, $endDate = null)
    {
        $sql = "SELECT * FROM emergency_closures WHERE tenant_id = ?";
        $params = [$tenantId];

        if ($branchId) {
            $sql .= " AND branch_id = ?";
            $params[] = $branchId;
        }

        if ($startDate) {
            $sql .= " AND start_time >= ?";
            $params[] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND start_time <= ?";
            $params[] = $endDate;
        }

        $sql .= " ORDER BY start_time DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update closure
     */
    public function updateClosure($closureId, $data)
    {
        $sql = "UPDATE emergency_closures 
                SET end_time = ?, severity = ?, description = ?, impact_assessment = ?, recovery_plan = ?, updated_at = CURRENT_TIMESTAMP
                WHERE closure_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['end_time'] ?? null,
            $data['severity'],
            $data['description'],
            $data['impact_assessment'],
            $data['recovery_plan'],
            $closureId
        ]);

        return ['success' => true, 'message' => 'Emergency closure updated successfully'];
    }

    /**
     * Close emergency closure
     */
    public function closeClosure($closureId, $endTime = null)
    {
        $endTime = $endTime ?? date('Y-m-d H:i:s');
        
        $sql = "UPDATE emergency_closures 
                SET end_time = ?, is_active = 0, updated_at = CURRENT_TIMESTAMP
                WHERE closure_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$endTime, $closureId]);

        return ['success' => true, 'message' => 'Emergency closure closed successfully'];
    }

    /**
     * Update notification status
     */
    public function updateNotificationStatus($closureId, $notifiedEmployees, $notifiedCustomers)
    {
        $sql = "UPDATE emergency_closures 
                SET notified_employees = ?, notified_customers = ?, updated_at = CURRENT_TIMESTAMP
                WHERE closure_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$notifiedEmployees ? 1 : 0, $notifiedCustomers ? 1 : 0, $closureId]);

        return ['success' => true, 'message' => 'Notification status updated successfully'];
    }

    /**
     * Check if business is closed due to emergency
     */
    public function isEmergencyClosed($tenantId, $branchId, $dateTime = null)
    {
        $dateTime = $dateTime ?? date('Y-m-d H:i:s');

        $sql = "SELECT closure_id FROM emergency_closures 
                WHERE tenant_id = ? AND branch_id = ? AND is_active = 1
                AND start_time <= ? AND (end_time IS NULL OR end_time >= ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $dateTime, $dateTime]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }
}
