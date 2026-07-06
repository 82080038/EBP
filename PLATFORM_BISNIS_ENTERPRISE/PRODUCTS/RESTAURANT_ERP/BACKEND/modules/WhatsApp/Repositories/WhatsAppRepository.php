<?php



class WhatsAppRepository
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

    public function createSettings($data)
    {
        $sql = "INSERT INTO whatsapp_settings (tenant_id, branch_id, provider, api_token, api_url, sender_number, is_enabled) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['provider'],
            $data['api_token'],
            $data['api_url'] ?? null,
            $data['sender_number'] ?? null,
            $data['is_enabled'] ?? true
        ]);
        return $this->db->lastInsertId();
    }

    public function updateSettings($settingId, $data)
    {
        $sql = "UPDATE whatsapp_settings SET provider = ?, api_token = ?, api_url = ?, sender_number = ?, is_enabled = ? WHERE setting_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['provider'],
            $data['api_token'],
            $data['api_url'] ?? null,
            $data['sender_number'] ?? null,
            $data['is_enabled'] ?? true,
            $settingId
        ]);
    }

    public function getSettings($tenantId, $branchId)
    {
        $sql = "SELECT * FROM whatsapp_settings WHERE tenant_id = ? AND branch_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function logMessage($data)
    {
        $sql = "INSERT INTO whatsapp_message_logs (tenant_id, branch_id, recipient_number, message_type, message_content, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['recipient_number'],
            $data['message_type'],
            $data['message_content'],
            $data['status']
        ]);
        return $this->db->lastInsertId();
    }

    public function updateMessageLog($logId, $status, $response)
    {
        $sql = "UPDATE whatsapp_message_logs SET status = ?, provider_response = ?, sent_at = CURRENT_TIMESTAMP WHERE log_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$status, $response, $logId]);
    }

    public function createReportSchedule($data)
    {
        $sql = "INSERT INTO whatsapp_report_schedules (tenant_id, branch_id, report_type, recipient_numbers, schedule_time, schedule_day, is_enabled) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['report_type'],
            is_array($data['recipient_numbers']) ? json_encode($data['recipient_numbers']) : $data['recipient_numbers'],
            $data['schedule_time'],
            $data['schedule_day'] ?? null,
            $data['is_enabled'] ?? true
        ]);
        return $this->db->lastInsertId();
    }

    public function getReportSchedules($tenantId, $branchId, $reportType = null)
    {
        $sql = "SELECT * FROM whatsapp_report_schedules WHERE tenant_id = ? AND branch_id = ?";
        $params = [$tenantId, $branchId];
        
        if ($reportType) {
            $sql .= " AND report_type = ?";
            $params[] = $reportType;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateScheduleLastSent($scheduleId)
    {
        $sql = "UPDATE whatsapp_report_schedules SET last_sent_at = CURRENT_TIMESTAMP WHERE schedule_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$scheduleId]);
    }

    public function getMessageLogs($tenantId, $branchId, $limit = 50)
    {
        $sql = "SELECT * FROM whatsapp_message_logs WHERE tenant_id = ? AND branch_id = ? ORDER BY created_at DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
