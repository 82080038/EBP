<?php



class SustainabilityRepository
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

    public function createWaste($data)
    {
        $sql = "INSERT INTO waste_tracking (tenant_id, branch_id, waste_date, waste_type, quantity, unit, estimated_cost, reason) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['waste_date'],
            $data['waste_type'],
            $data['quantity'],
            $data['unit'],
            $data['estimated_cost'],
            $data['reason']
        ]);
        return $this->db->lastInsertId();
    }

    public function createMetric($data)
    {
        $sql = "INSERT INTO sustainability_metrics (tenant_id, branch_id, metric_date, carbon_footprint_kg, energy_kwh, water_liters, waste_kg) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['tenant_id'],
            $data['branch_id'],
            $data['metric_date'],
            $data['carbon_footprint_kg'],
            $data['energy_kwh'],
            $data['water_liters'],
            $data['waste_kg']
        ]);
        return $this->db->lastInsertId();
    }

    public function getWasteByTenant($tenantId, $branchId = null, $startDate = null, $endDate = null)
    {
        $sql = "SELECT * FROM waste_tracking WHERE tenant_id = ?";
        $params = [$tenantId];
        
        if ($branchId) {
            $sql .= " AND branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($startDate) {
            $sql .= " AND waste_date >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND waste_date <= ?";
            $params[] = $endDate;
        }
        
        $sql .= " ORDER BY waste_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMetricsByTenant($tenantId, $branchId = null, $startDate = null, $endDate = null)
    {
        $sql = "SELECT * FROM sustainability_metrics WHERE tenant_id = ?";
        $params = [$tenantId];
        
        if ($branchId) {
            $sql .= " AND branch_id = ?";
            $params[] = $branchId;
        }
        
        if ($startDate) {
            $sql .= " AND metric_date >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND metric_date <= ?";
            $params[] = $endDate;
        }
        
        $sql .= " ORDER BY metric_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
