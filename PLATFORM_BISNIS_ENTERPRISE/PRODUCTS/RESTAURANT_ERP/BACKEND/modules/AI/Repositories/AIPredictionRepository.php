<?php



class AIPredictionRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
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
