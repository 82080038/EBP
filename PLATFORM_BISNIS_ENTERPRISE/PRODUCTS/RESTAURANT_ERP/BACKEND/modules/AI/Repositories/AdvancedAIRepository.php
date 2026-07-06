<?php



class AdvancedAIRepository
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

    public function createMenuEngineering($data)
    {
        $sql = "INSERT INTO ai_menu_engineering (tenant_id, branch_id, analysis_date, product_id, product_name, current_price, cost, margin, sales_volume, popularity_score, profit_score, menu_mix_score, recommendation, recommended_price, reasoning) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['analysis_date'],
            $data['product_id'],
            $data['product_name'],
            $data['current_price'],
            $data['cost'],
            $data['margin'],
            $data['sales_volume'],
            $data['popularity_score'],
            $data['profit_score'],
            $data['menu_mix_score'],
            $data['recommendation'],
            $data['recommended_price'],
            $data['reasoning']
        ]);
        return $this->db->lastInsertId();
    }

    public function createStaffOptimization($data)
    {
        $sql = "INSERT INTO ai_staff_optimization (tenant_id, branch_id, date, hour, predicted_orders, required_staff, current_staff, overstaffed, understaffed, cost_savings, recommendation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['date'],
            $data['hour'],
            $data['predicted_orders'],
            $data['required_staff'],
            $data['current_staff'],
            $data['overstaffed'] ? 1 : 0,
            $data['understaffed'] ? 1 : 0,
            $data['cost_savings'],
            $data['recommendation']
        ]);
        return $this->db->lastInsertId();
    }

    public function createFraudAlert($data)
    {
        $sql = "INSERT INTO ai_fraud_detection (tenant_id, branch_id, alert_type, severity, description, related_order_id, related_user_id, risk_score) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['alert_type'],
            $data['severity'],
            $data['description'],
            $data['related_order_id'],
            $data['related_user_id'],
            $data['risk_score']
        ]);
        return $this->db->lastInsertId();
    }

    public function createExecutiveInsight($data)
    {
        $sql = "INSERT INTO ai_executive_intelligence (tenant_id, branch_id, insight_category, insight_title, insight_description, metrics, trend, impact_level, recommended_actions, priority) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['insight_category'],
            $data['insight_title'],
            $data['insight_description'],
            $data['metrics'],
            $data['trend'],
            $data['impact_level'],
            $data['recommended_actions'],
            $data['priority']
        ]);
        return $this->db->lastInsertId();
    }

    public function getMenuEngineering($tenantId, $branchId, $date)
    {
        $sql = "SELECT * FROM ai_menu_engineering WHERE tenant_id = ? AND branch_id = ? AND analysis_date = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $branchId, $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFraudAlerts($tenantId, $branchId, $status = null)
    {
        $sql = "SELECT * FROM ai_fraud_detection WHERE tenant_id = ?";
        $params = [$tenantId];
        
        if ($branchId) {
            $sql .= " AND branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getExecutiveInsights($tenantId, $branchId, $status = null)
    {
        $sql = "SELECT * FROM ai_executive_intelligence WHERE tenant_id = ?";
        $params = [$tenantId];
        
        if ($branchId) {
            $sql .= " AND branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY priority DESC, created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
