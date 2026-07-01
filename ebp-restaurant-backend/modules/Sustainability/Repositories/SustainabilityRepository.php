<?php

require_once __DIR__ . '/../../../config/database.php';

class SustainabilityRepository
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
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
