<?php

if (!class_exists('Database')) {
    require_once __DIR__ . '/../../../core/Database.php';
}

/**
 * Loyalty Repository
 * 
 * @package EBP\Modules\Loyalty
 * @version 1.0.0
 */

class LoyaltyRepository
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->connect();
    }
    
    // ==================== Loyalty Points ====================
    
    public function findAllPoints(int $tenantId, ?int $userId = null): array
    {
        $sql = "SELECT * FROM loyalty_points 
                WHERE tenant_id = :tenant_id";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($userId !== null) {
            $sql .= " AND user_id = :user_id";
            $params['user_id'] = $userId;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function findPointById(int $id, int $tenantId): ?array
    {
        $sql = "SELECT * FROM loyalty_points 
                WHERE loyalty_point_id = :id 
                AND tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'tenant_id' => $tenantId]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function createPoint(array $data): int
    {
        $sql = "INSERT INTO loyalty_points 
                (tenant_id, user_id, points_earned, points_redeemed, 
                 transaction_type, reference_id, reference_type, notes, created_by) 
                VALUES 
                (:tenant_id, :user_id, :points_earned, :points_redeemed,
                 :transaction_type, :reference_id, :reference_type, :notes, :created_by)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        
        return (int) $this->db->lastInsertId();
    }
    
    public function getUserTotalPoints(int $tenantId, int $userId): int
    {
        $sql = "SELECT 
                COALESCE(SUM(points_earned - points_redeemed), 0) as total_points
                FROM loyalty_points 
                WHERE tenant_id = :tenant_id 
                AND user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'user_id' => $userId]);
        
        $result = $stmt->fetch();
        return (int) ($result['total_points'] ?? 0);
    }
    
    // ==================== Loyalty Rewards ====================
    
    public function findAllRewards(int $tenantId, ?string $status = null): array
    {
        $sql = "SELECT * FROM loyalty_rewards 
                WHERE tenant_id = :tenant_id 
                AND deleted_at IS NULL";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($status !== null) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function findRewardById(int $id, int $tenantId): ?array
    {
        $sql = "SELECT * FROM loyalty_rewards 
                WHERE reward_id = :id 
                AND tenant_id = :tenant_id 
                AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'tenant_id' => $tenantId]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function findRewardByCode(string $code, int $tenantId): ?array
    {
        $sql = "SELECT * FROM loyalty_rewards 
                WHERE reward_code = :code 
                AND tenant_id = :tenant_id 
                AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['code' => $code, 'tenant_id' => $tenantId]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function createReward(array $data): int
    {
        $sql = "INSERT INTO loyalty_rewards 
                (tenant_id, reward_code, reward_name, reward_name_en, reward_description,
                 points_required, reward_type, discount_percentage, discount_amount,
                 status, valid_from, valid_until, created_by) 
                VALUES 
                (:tenant_id, :reward_code, :reward_name, :reward_name_en, :reward_description,
                 :points_required, :reward_type, :discount_percentage, :discount_amount,
                 :status, :valid_from, :valid_until, :created_by)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        
        return (int) $this->db->lastInsertId();
    }
    
    public function updateReward(int $id, array $data): bool
    {
        $sql = "UPDATE loyalty_rewards 
                SET reward_name = :reward_name,
                    reward_name_en = :reward_name_en,
                    reward_description = :reward_description,
                    points_required = :points_required,
                    reward_type = :reward_type,
                    discount_percentage = :discount_percentage,
                    discount_amount = :discount_amount,
                    status = :status,
                    valid_from = :valid_from,
                    valid_until = :valid_until,
                    updated_by = :updated_by,
                    updated_at = CURRENT_TIMESTAMP
                WHERE reward_id = :id 
                AND tenant_id = :tenant_id";
        
        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($data);
    }
    
    public function deleteReward(int $id, int $tenantId): bool
    {
        $sql = "UPDATE loyalty_rewards 
                SET deleted_at = CURRENT_TIMESTAMP 
                WHERE reward_id = :id 
                AND tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute(['id' => $id, 'tenant_id' => $tenantId]);
    }
    
    // ==================== Customer Loyalty ====================
    
    public function findAllCustomerLoyalty(int $tenantId): array
    {
        $sql = "SELECT cl.*, u.username, u.email 
                FROM customer_loyalty cl
                LEFT JOIN users u ON cl.user_id = u.user_id
                WHERE cl.tenant_id = :tenant_id 
                AND cl.deleted_at IS NULL
                ORDER BY cl.total_points DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId]);
        
        return $stmt->fetchAll();
    }
    
    public function findCustomerLoyaltyById(int $id, int $tenantId): ?array
    {
        $sql = "SELECT * FROM customer_loyalty 
                WHERE customer_loyalty_id = :id 
                AND tenant_id = :tenant_id 
                AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'tenant_id' => $tenantId]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function findCustomerLoyaltyByUser(int $userId, int $tenantId): ?array
    {
        $sql = "SELECT * FROM customer_loyalty 
                WHERE user_id = :user_id 
                AND tenant_id = :tenant_id 
                AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'tenant_id' => $tenantId]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function createCustomerLoyalty(array $data): int
    {
        $sql = "INSERT INTO customer_loyalty 
                (tenant_id, user_id, total_points, current_tier, tier_progress,
                 tier_points_required, points_earned_lifetime, points_redeemed_lifetime,
                 created_by) 
                VALUES 
                (:tenant_id, :user_id, :total_points, :current_tier, :tier_progress,
                 :tier_points_required, :points_earned_lifetime, :points_redeemed_lifetime,
                 :created_by)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        
        return (int) $this->db->lastInsertId();
    }
    
    public function updateCustomerLoyalty(int $id, array $data): bool
    {
        $sql = "UPDATE customer_loyalty 
                SET total_points = :total_points,
                    current_tier = :current_tier,
                    tier_progress = :tier_progress,
                    tier_points_required = :tier_points_required,
                    points_earned_lifetime = :points_earned_lifetime,
                    points_redeemed_lifetime = :points_redeemed_lifetime,
                    last_tier_upgrade = :last_tier_upgrade,
                    updated_by = :updated_by,
                    updated_at = CURRENT_TIMESTAMP
                WHERE customer_loyalty_id = :id 
                AND tenant_id = :tenant_id";
        
        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($data);
    }
    
    public function deleteCustomerLoyalty(int $id, int $tenantId): bool
    {
        $sql = "UPDATE customer_loyalty 
                SET deleted_at = CURRENT_TIMESTAMP 
                WHERE customer_loyalty_id = :id 
                AND tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute(['id' => $id, 'tenant_id' => $tenantId]);
    }
    
    public function getTopCustomersByPoints(int $tenantId, int $limit = 10): array
    {
        $sql = "SELECT cl.*, u.username, u.email 
                FROM customer_loyalty cl
                LEFT JOIN users u ON cl.user_id = u.user_id
                WHERE cl.tenant_id = :tenant_id 
                AND cl.deleted_at IS NULL
                ORDER BY cl.total_points DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute(['tenant_id' => $tenantId]);
        
        return $stmt->fetchAll();
    }
    
    public function getCustomersByTier(int $tenantId, string $tier): array
    {
        $sql = "SELECT cl.*, u.username, u.email 
                FROM customer_loyalty cl
                LEFT JOIN users u ON cl.user_id = u.user_id
                WHERE cl.tenant_id = :tenant_id 
                AND cl.current_tier = :tier 
                AND cl.deleted_at IS NULL
                ORDER BY cl.total_points DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'tier' => $tier]);
        
        return $stmt->fetchAll();
    }
}
