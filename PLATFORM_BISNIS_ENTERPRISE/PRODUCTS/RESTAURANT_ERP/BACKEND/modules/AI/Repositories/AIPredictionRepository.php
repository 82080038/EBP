<?php



class AIPredictionRepository
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

    public function create($data)
    {
        $sql = "INSERT INTO ai_predictions (tenant_id, branch_id, prediction_type, model_version, prediction_date, prediction_data, confidence_score) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['prediction_type'],
            $data['model_version'] ?? null,
            $data['prediction_date'],
            $data['prediction_data'],
            $data['confidence_score']
        ]);
        return $this->db->lastInsertId();
    }

    public function getByTenant($tenantId, $branchId = null, $predictionType = null)
    {
        $sql = "SELECT * FROM ai_predictions WHERE tenant_id = ?";
        $params = [$tenantId];
        
        if ($branchId) {
            $sql .= " AND branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($predictionType) {
            $sql .= " AND prediction_type = ?";
            $params[] = $predictionType;
        }
        
        $sql .= " ORDER BY prediction_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
