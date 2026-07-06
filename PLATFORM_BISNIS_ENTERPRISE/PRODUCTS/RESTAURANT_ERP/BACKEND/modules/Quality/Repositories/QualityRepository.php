<?php



class QualityRepository
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

    public function createCheck($data)
    {
        $sql = "INSERT INTO quality_checks (tenant_id, branch_id, check_type, check_date, checked_by, check_result, temperature, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['check_type'],
            $data['check_date'],
            $data['checked_by'],
            $data['check_result'],
            $data['temperature'],
            $data['notes']
        ]);
        return $this->db->lastInsertId();
    }

    public function createIncident($data)
    {
        $sql = "INSERT INTO incidents (tenant_id, branch_id, incident_type, incident_date, severity, description, reported_by, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['incident_type'],
            $data['incident_date'],
            $data['severity'],
            $data['description'],
            $data['reported_by'],
            $data['status']
        ]);
        return $this->db->lastInsertId();
    }

    public function getChecksByTenant($tenantId, $branchId = null)
    {
        if ($branchId) {
            $sql = "SELECT qc.*, u.username as checked_by_name FROM quality_checks qc LEFT JOIN users u ON qc.checked_by = u.user_id WHERE qc.tenant_id = ? AND qc.branch_id = ? ORDER BY qc.check_date DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId, $branchId]);
        } else {
            $sql = "SELECT qc.*, u.username as checked_by_name FROM quality_checks qc LEFT JOIN users u ON qc.checked_by = u.user_id WHERE qc.tenant_id = ? ORDER BY qc.check_date DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getIncidentsByTenant($tenantId, $branchId = null)
    {
        if ($branchId) {
            $sql = "SELECT i.*, u.username as reported_by_name FROM incidents i LEFT JOIN users u ON i.reported_by = u.user_id WHERE i.tenant_id = ? AND i.branch_id = ? AND i.deleted_at IS NULL ORDER BY i.incident_date DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId, $branchId]);
        } else {
            $sql = "SELECT i.*, u.username as reported_by_name FROM incidents i LEFT JOIN users u ON i.reported_by = u.user_id WHERE i.tenant_id = ? AND i.deleted_at IS NULL ORDER BY i.incident_date DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateIncident($incidentId, $data)
    {
        $setClauses = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $setClauses[] = "$field = ?";
            $values[] = $value;
        }
        
        $values[] = $incidentId;
        
        $sql = "UPDATE incidents SET " . implode(', ', $setClauses) . " WHERE incident_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
    }
}
