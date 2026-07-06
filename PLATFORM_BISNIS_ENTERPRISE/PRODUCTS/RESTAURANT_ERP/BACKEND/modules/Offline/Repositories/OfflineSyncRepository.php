<?php



class OfflineSyncRepository
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

    public function createSyncQueue($data)
    {
        $sql = "INSERT INTO offline_sync_queue (tenant_id, branch_id, user_id, operation_type, entity_type, entity_data, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['user_id'],
            $data['operation_type'],
            $data['entity_type'],
            $data['entity_data'],
            $data['status']
        ]);
        return $this->db->lastInsertId();
    }

    public function getPendingOperations($tenantId, $branchId)
    {
        $sql = "SELECT * FROM offline_sync_queue WHERE tenant_id = ? AND branch_id = ? AND status = 'PENDING' ORDER BY created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateSyncStatus($syncId, $status, $errorMessage)
    {
        $sql = "UPDATE offline_sync_queue SET status = ?, error_message = ?, synced_at = CURRENT_TIMESTAMP WHERE sync_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$status, $errorMessage, $syncId]);
    }

    public function getSyncOperation($syncId)
    {
        $sql = "SELECT * FROM offline_sync_queue WHERE sync_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$syncId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getSyncStatus($tenantId, $branchId)
    {
        $sql = "SELECT 
                    COUNT(CASE WHEN status = 'PENDING' THEN 1 END) as pending_count,
                    COUNT(CASE WHEN status = 'SYNCED' THEN 1 END) as synced_count,
                    COUNT(CASE WHEN status = 'FAILED' THEN 1 END) as failed_count,
                    COUNT(CASE WHEN status = 'CONFLICT' THEN 1 END) as conflict_count
                FROM offline_sync_queue 
                WHERE tenant_id = ? AND branch_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getConflicts($tenantId, $branchId)
    {
        $sql = "SELECT * FROM offline_sync_queue WHERE tenant_id = ? AND branch_id = ? AND status = 'CONFLICT' ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
