<?php

declare(strict_types=1);

namespace Modules\Menu\Services;

use Modules\Menu\Models\MenuABTest;
use PDO;

class MenuABTestService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function createABTest(array $data): MenuABTest
    {
        $sql = "INSERT INTO menu_ab_tests 
                (tenant_id, branch_id, name, description, status, start_date, end_date, 
                 traffic_split, target_audience, success_metric, created_by) 
                VALUES 
                (:tenant_id, :branch_id, :name, :description, :status, :start_date, :end_date,
                 :traffic_split, :target_audience, :success_metric, :created_by)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':branch_id' => $data['branch_id'] ?? null,
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':status' => $data['status'] ?? 'DRAFT',
            ':start_date' => $data['start_date'] ?? null,
            ':end_date' => $data['end_date'] ?? null,
            ':traffic_split' => $data['traffic_split'] ?? 50.00,
            ':target_audience' => isset($data['target_audience']) ? json_encode($data['target_audience']) : null,
            ':success_metric' => $data['success_metric'] ?? null,
            ':created_by' => $data['created_by']
        ]);

        $data['id'] = (int)$this->db->lastInsertId();
        return new MenuABTest($data);
    }

    public function getABTestById(int $id): ?MenuABTest
    {
        $sql = "SELECT * FROM menu_ab_tests WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? new MenuABTest($result) : null;
    }

    public function getABTestsByTenant(int $tenantId, ?int $branchId = null): array
    {
        $sql = "SELECT * FROM menu_ab_tests 
                WHERE tenant_id = :tenant_id AND deleted_at IS NULL";
        $params = [':tenant_id' => $tenantId];

        if ($branchId !== null) {
            $sql .= " AND (branch_id IS NULL OR branch_id = :branch_id)";
            $params[':branch_id'] = $branchId;
        }

        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($result) => new MenuABTest($result), $results);
    }

    public function getActiveABTests(int $tenantId): array
    {
        $sql = "SELECT * FROM menu_ab_tests 
                WHERE tenant_id = :tenant_id 
                AND status = 'ACTIVE' 
                AND start_date <= NOW() 
                AND (end_date IS NULL OR end_date >= NOW())
                AND deleted_at IS NULL
                ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($result) => new MenuABTest($result), $results);
    }

    public function updateABTest(int $id, array $data): ?MenuABTest
    {
        $sql = "UPDATE menu_ab_tests 
                SET name = :name, 
                    description = :description, 
                    status = :status, 
                    start_date = :start_date, 
                    end_date = :end_date, 
                    traffic_split = :traffic_split, 
                    target_audience = :target_audience, 
                    success_metric = :success_metric, 
                    updated_by = :updated_by,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':status' => $data['status'],
            ':start_date' => $data['start_date'] ?? null,
            ':end_date' => $data['end_date'] ?? null,
            ':traffic_split' => $data['traffic_split'] ?? 50.00,
            ':target_audience' => isset($data['target_audience']) ? json_encode($data['target_audience']) : null,
            ':success_metric' => $data['success_metric'] ?? null,
            ':updated_by' => $data['updated_by']
        ]);

        return $this->getABTestById($id);
    }

    public function deleteABTest(int $id, int $deletedBy): bool
    {
        $sql = "UPDATE menu_ab_tests 
                SET deleted_at = CURRENT_TIMESTAMP, 
                    updated_by = :deleted_by 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':deleted_by' => $deletedBy
        ]);
    }

    public function assignUserToVariant(int $abTestId, ?int $userId, ?string $sessionId): int
    {
        // Get active test
        $test = $this->getABTestById($abTestId);
        if (!$test || $test->getStatus() !== 'ACTIVE') {
            throw new \Exception('Test not found or not active');
        }

        // Check if user already assigned
        $sql = "SELECT id, variant_id FROM menu_ab_test_user_assignments 
                WHERE ab_test_id = :ab_test_id 
                AND (user_id = :user_id OR session_id = :session_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':ab_test_id' => $abTestId,
            ':user_id' => $userId,
            ':session_id' => $sessionId
        ]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            return (int)$existing['variant_id'];
        }

        // Assign variant based on traffic split
        $rand = mt_rand(1, 100);
        $variantType = $rand <= $test->getTrafficSplit() ? 'CONTROL' : 'VARIANT_A';

        // Get variant ID
        $sql = "SELECT id FROM menu_ab_test_variants 
                WHERE ab_test_id = :ab_test_id AND variant_type = :variant_type";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':ab_test_id' => $abTestId,
            ':variant_type' => $variantType
        ]);
        $variant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$variant) {
            throw new \Exception('Variant not found');
        }

        // Insert assignment
        $sql = "INSERT INTO menu_ab_test_user_assignments 
                (ab_test_id, variant_id, user_id, session_id) 
                VALUES (:ab_test_id, :variant_id, :user_id, :session_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':ab_test_id' => $abTestId,
            ':variant_id' => $variant['id'],
            ':user_id' => $userId,
            ':session_id' => $sessionId
        ]);

        return (int)$variant['id'];
    }

    public function recordTestResult(int $abTestId, int $variantId, string $metricName, float $metricValue, int $sampleSize = 1): bool
    {
        $sql = "INSERT INTO menu_ab_test_results 
                (ab_test_id, variant_id, metric_name, metric_value, sample_size) 
                VALUES (:ab_test_id, :variant_id, :metric_name, :metric_value, :sample_size)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':ab_test_id' => $abTestId,
            ':variant_id' => $variantId,
            ':metric_name' => $metricName,
            ':metric_value' => $metricValue,
            ':sample_size' => $sampleSize
        ]);
    }

    public function getTestResults(int $abTestId): array
    {
        $sql = "SELECT r.*, v.variant_type, v.variant_name 
                FROM menu_ab_test_results r
                JOIN menu_ab_test_variants v ON r.variant_id = v.id
                WHERE r.ab_test_id = :ab_test_id
                ORDER BY r.recorded_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':ab_test_id' => $abTestId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
