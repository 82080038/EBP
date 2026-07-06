<?php

use PDO;
use PDOException;

global $pdo;

/**
 * Tip Management Service
 * 
 * Manages tip recording, distribution, and reporting
 */
class TipManagementService
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
     * Record tip
     */
    public function recordTip($data)
    {
        try {
            $sql = "INSERT INTO tips (tenant_id, branch_id, user_id, order_id, tip_date, 
                    tip_amount, tip_type, payment_method, recorded_by, notes) 
                    VALUES (:tenant_id, :branch_id, :user_id, :order_id, :tip_date, 
                    :tip_amount, :tip_type, :payment_method, :recorded_by, :notes)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':tenant_id' => $this->tenantId,
                ':branch_id' => $this->branchId,
                ':user_id' => $data['user_id'],
                ':order_id' => $data['order_id'] ?? null,
                ':tip_date' => $data['tip_date'],
                ':tip_amount' => $data['tip_amount'],
                ':tip_type' => $data['tip_type'] ?? 'cash',
                ':payment_method' => $data['payment_method'] ?? 'cash',
                ':recorded_by' => $data['recorded_by'] ?? null,
                ':notes' => $data['notes'] ?? null
            ]);

            $tipId = $this->db->lastInsertId();

            return [
                'success' => true,
                'message' => 'Tip recorded successfully',
                'data' => ['tip_id' => $tipId]
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to record tip: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get tips by date range
     */
    public function getTips($startDate, $endDate, $userId = null)
    {
        try {
            $sql = "SELECT t.*, u.full_name, u.username, o.order_number 
                    FROM tips t 
                    LEFT JOIN users u ON t.user_id = u.user_id 
                    LEFT JOIN orders o ON t.order_id = o.order_id 
                    WHERE t.tenant_id = :tenant_id 
                    AND t.tip_date BETWEEN :start_date AND :end_date";
            
            $params = [
                ':tenant_id' => $this->tenantId,
                ':start_date' => $startDate,
                ':end_date' => $endDate
            ];

            if ($userId) {
                $sql .= " AND t.user_id = :user_id";
                $params[':user_id'] = $userId;
            }

            $sql .= " ORDER BY t.tip_date DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $tips = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $tips
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to get tips: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get tip summary by user
     */
    public function getTipSummary($startDate, $endDate)
    {
        try {
            $sql = "SELECT 
                    t.user_id,
                    u.full_name,
                    u.username,
                    COUNT(*) as tip_count,
                    SUM(t.tip_amount) as total_tips,
                    AVG(t.tip_amount) as avg_tip
                    FROM tips t
                    LEFT JOIN users u ON t.user_id = u.user_id
                    WHERE t.tenant_id = :tenant_id 
                    AND t.tip_date BETWEEN :start_date AND :end_date
                    GROUP BY t.user_id, u.full_name, u.username
                    ORDER BY total_tips DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':tenant_id' => $this->tenantId,
                ':start_date' => $startDate,
                ':end_date' => $endDate
            ]);
            $summary = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $totalTips = array_sum(array_column($summary, 'total_tips'));

            return [
                'success' => true,
                'data' => [
                    'by_user' => $summary,
                    'total_tips' => $totalTips
                ]
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to get tip summary: ' . $e->getMessage()
            ];
        }
    }
}
