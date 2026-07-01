<?php

require_once __DIR__ . '/../config/database.php';

class Audit
{

    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }



    public function log($tenantId, $userId, $module, $action, $recordId, $tableName, $oldValue = null, $newValue = null)
    {

        $sql = "
            INSERT INTO audit_logs
            (tenant_id, user_id, module, action, record_id, table_name, old_value, new_value, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ";



        $stmt = $this->db->prepare($sql);

        $stmt->execute([

            $tenantId,

            $userId,

            $module,

            $action,

            $recordId,

            $tableName,

            json_encode($oldValue),

            json_encode($newValue),

            $_SERVER['REMOTE_ADDR'] ?? 'CLI',

            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'

        ]);

    }

}
