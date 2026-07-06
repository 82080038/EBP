<?php



class IntegrationRepository
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

    public function upsertSetting($data)
    {
        $sql = "INSERT INTO integration_settings (tenant_id, branch_id, integration_type, setting_key, setting_value, is_encrypted) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), is_encrypted = VALUES(is_encrypted), updated_at = CURRENT_TIMESTAMP";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['integration_type'],
            $data['setting_key'],
            $data['setting_value'],
            $data['is_encrypted']
        ]);
    }

    public function getSettings($tenantId, $branchId, $integrationType)
    {
        $sql = "SELECT * FROM integration_settings WHERE tenant_id = ? AND branch_id = ? AND integration_type = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $integrationType]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function logIntegration($data)
    {
        $sql = "INSERT INTO integration_logs (tenant_id, branch_id, integration_type, action, request_payload, response_payload, status, error_message, external_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['integration_type'],
            $data['action'],
            $data['request_payload'],
            $data['response_payload'],
            $data['status'],
            $data['error_message'] ?? null,
            $data['external_id'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function getLogs($tenantId, $branchId = null, $integrationType = null, $limit = 100)
    {
        $sql = "SELECT * FROM integration_logs WHERE tenant_id = ?";
        $params = [$tenantId];
        
        if ($branchId) {
            $sql .= " AND branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($integrationType) {
            $sql .= " AND integration_type = ?";
            $params[] = $integrationType;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT " . intval($limit);
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
