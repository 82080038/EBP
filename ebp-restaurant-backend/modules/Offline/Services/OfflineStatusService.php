<?php

if (!class_exists('database')) {
    require_once __DIR__ . '/../../../config/database.php';
}

class OfflineStatusService
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getOfflineStatus($tenantId, $branchId)
    {
        try {
            // Simplified offline status - check if database is accessible
            $status = 'ONLINE';
            $pendingCount = 0;
            $lastSync = date('Y-m-d H:i:s');
            $offlineDuration = 0;

            // Try to check orders table for recent activity
            $stmt = $this->db->prepare("SELECT COUNT(*) as order_count FROM orders WHERE tenant_id = ? AND branch_id = ? AND DATE(created_at) = CURDATE()");
            $stmt->execute([$tenantId, $branchId]);
            $orderCount = $stmt->fetch(PDO::FETCH_ASSOC)['order_count'];

            return [
                'success' => true,
                'message' => 'Offline status retrieved successfully',
                'data' => [
                    'status' => $status,
                    'pending_sync_count' => $pendingCount,
                    'last_sync_time' => $lastSync,
                    'offline_duration_seconds' => $offlineDuration,
                    'is_offline' => false,
                    'today_orders' => $orderCount
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get offline status: ' . $e->getMessage()
            ];
        }
    }
}
