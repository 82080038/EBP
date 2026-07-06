<?php

use PDO;
use PDOException;

global $pdo;

/**
 * Food Waste Tracking Service
 * 
 * Tracks and analyzes food waste for cost reduction
 */
class FoodWasteService
{
    private $db;
    private $tenantId;
    private $branchId;

    public function __construct($tenantId = null, $branchId = null)
    {
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
    }

    /**
     * Record food waste
     */
    public function recordWaste($data)
    {
        try {
            $sql = "INSERT INTO food_waste (tenant_id, branch_id, waste_date, waste_type, 
                    inventory_item_id, quantity, unit, reason, cost_per_unit, 
                    total_cost, recorded_by, notes) 
                    VALUES (:tenant_id, :branch_id, :waste_date, :waste_type, 
                    :inventory_item_id, :quantity, :unit, :reason, :cost_per_unit, 
                    :total_cost, :recorded_by, :notes)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':tenant_id' => $this->tenantId,
                ':branch_id' => $this->branchId,
                ':waste_date' => $data['waste_date'],
                ':waste_type' => $data['waste_type'],
                ':inventory_item_id' => $data['inventory_item_id'] ?? null,
                ':quantity' => $data['quantity'],
                ':unit' => $data['unit'],
                ':reason' => $data['reason'],
                ':cost_per_unit' => $data['cost_per_unit'] ?? 0,
                ':total_cost' => $data['total_cost'] ?? 0,
                ':recorded_by' => $data['recorded_by'] ?? null,
                ':notes' => $data['notes'] ?? null
            ]);

            $wasteId = $this->db->lastInsertId();

            return [
                'success' => true,
                'message' => 'Waste recorded successfully',
                'data' => ['waste_id' => $wasteId]
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to record waste: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get waste records
     */
    public function getWasteRecords($filters = [])
    {
        try {
            $sql = "SELECT fw.*, ii.name as item_name, u.username as recorded_by_name 
                    FROM food_waste fw 
                    LEFT JOIN inventory_items ii ON fw.inventory_item_id = ii.inventory_item_id 
                    LEFT JOIN users u ON fw.recorded_by = u.user_id 
                    WHERE fw.tenant_id = :tenant_id";
            
            $params = [':tenant_id' => $this->tenantId];

            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                $sql .= " AND fw.waste_date BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $filters['start_date'];
                $params[':end_date'] = $filters['end_date'];
            }

            if (!empty($filters['waste_type'])) {
                $sql .= " AND fw.waste_type = :waste_type";
                $params[':waste_type'] = $filters['waste_type'];
            }

            $sql .= " ORDER BY fw.waste_date DESC";

            if (!empty($filters['limit'])) {
                $sql .= " LIMIT :limit";
                $params[':limit'] = (int)$filters['limit'];
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $records
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to get waste records: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get waste analysis
     */
    public function getWasteAnalysis($startDate, $endDate)
    {
        try {
            $sql = "SELECT 
                    waste_type,
                    COUNT(*) as record_count,
                    SUM(quantity) as total_quantity,
                    SUM(total_cost) as total_cost
                    FROM food_waste
                    WHERE tenant_id = :tenant_id 
                    AND waste_date BETWEEN :start_date AND :end_date
                    GROUP BY waste_type
                    ORDER BY total_cost DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':tenant_id' => $this->tenantId,
                ':start_date' => $startDate,
                ':end_date' => $endDate
            ]);
            $byType = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get top waste items
            $sql = "SELECT 
                    ii.name as item_name,
                    SUM(fw.quantity) as total_quantity,
                    SUM(fw.total_cost) as total_cost,
                    COUNT(*) as occurrence_count
                    FROM food_waste fw
                    LEFT JOIN inventory_items ii ON fw.inventory_item_id = ii.inventory_item_id
                    WHERE fw.tenant_id = :tenant_id 
                    AND fw.waste_date BETWEEN :start_date AND :end_date
                    GROUP BY ii.name
                    ORDER BY total_cost DESC
                    LIMIT 10";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':tenant_id' => $this->tenantId,
                ':start_date' => $startDate,
                ':end_date' => $endDate
            ]);
            $topItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get waste by reason
            $sql = "SELECT 
                    reason,
                    COUNT(*) as record_count,
                    SUM(total_cost) as total_cost
                    FROM food_waste
                    WHERE tenant_id = :tenant_id 
                    AND waste_date BETWEEN :start_date AND :end_date
                    GROUP BY reason
                    ORDER BY total_cost DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':tenant_id' => $this->tenantId,
                ':start_date' => $startDate,
                ':end_date' => $endDate
            ]);
            $byReason = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $totalCost = array_sum(array_column($byType, 'total_cost'));

            return [
                'success' => true,
                'data' => [
                    'by_type' => $byType,
                    'top_items' => $topItems,
                    'by_reason' => $byReason,
                    'total_cost' => $totalCost
                ]
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to get waste analysis: ' . $e->getMessage()
            ];
        }
    }
}
