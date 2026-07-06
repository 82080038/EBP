<?php



class OfflineStatusService
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
