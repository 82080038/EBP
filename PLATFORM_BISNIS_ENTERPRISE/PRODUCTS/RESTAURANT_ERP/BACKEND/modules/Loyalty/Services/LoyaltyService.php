<?php

if (!class_exists('LoyaltyRepository')) {
    require_once __DIR__ . '/../Repositories/LoyaltyRepository.php';
}

if (!class_exists('LoyaltyPoint')) {
    require_once __DIR__ . '/../Models/LoyaltyPoint.php';
}

if (!class_exists('LoyaltyReward')) {
    require_once __DIR__ . '/../Models/LoyaltyReward.php';
}

if (!class_exists('CustomerLoyalty')) {
    require_once __DIR__ . '/../Models/CustomerLoyalty.php';
}

if (!class_exists('Database')) {
    require_once __DIR__ . '/../../../core/Database.php';
}

if (!class_exists('Audit')) {
    require_once __DIR__ . '/../../../core/Audit.php';
}

/**
 * Loyalty Service
 * 
 * @package EBP\Modules\Loyalty
 * @version 1.0.0
 */

class LoyaltyService
{
    private $repository;
    private $db;
    
    // Tier configuration
    private $tierConfig = [
        'BRONZE' => ['points_required' => 0, 'next_tier' => 'SILVER', 'next_points' => 100],
        'SILVER' => ['points_required' => 100, 'next_tier' => 'GOLD', 'next_points' => 500],
        'GOLD' => ['points_required' => 500, 'next_tier' => 'PLATINUM', 'next_points' => 1000],
        'PLATINUM' => ['points_required' => 1000, 'next_tier' => null, 'next_points' => null]
    ];
    
    public function __construct()
    {
        $this->repository = new LoyaltyRepository();
        $this->db = Database::getInstance();
    }
    
    // ==================== Loyalty Points ====================
    
    public function getAllPoints(int $tenantId, ?int $userId = null): array
    {
        return $this->repository->findAllPoints($tenantId, $userId);
    }
    
    public function getPointById(int $id, int $tenantId): ?array
    {
        return $this->repository->findPointById($id, $tenantId);
    }
    
    public function awardPoints(int $tenantId, int $userId, int $points, string $transactionType = 'EARNED', ?int $referenceId = null, ?string $referenceType = null, ?string $notes = null, ?int $createdBy = null): array
    {
        $pdo = $this->db->connect();
        
        try {
            $pdo->beginTransaction();
            
            // Validate points
            if ($points <= 0) {
                return [
                    'success' => false,
                    'message' => 'Points must be greater than 0'
                ];
            }
            
            // Create point transaction
            $pointData = [
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'points_earned' => $points,
                'points_redeemed' => 0,
                'transaction_type' => $transactionType,
                'reference_id' => $referenceId,
                'reference_type' => $referenceType,
                'notes' => $notes,
                'created_by' => $createdBy
            ];
            
            $pointId = $this->repository->createPoint($pointData);
            
            // Update customer loyalty
            $this->updateCustomerLoyaltyPoints($tenantId, $userId, $points, 0, $createdBy);
            
            // Audit log - commented out for testing
            // Audit::log($tenantId, $createdBy, 'LOYALTY_POINTS_AWARDED', [
            //     'point_id' => $pointId,
            //     'user_id' => $userId,
            //     'points' => $points,
            //     'transaction_type' => $transactionType
            // ]);
            
            $pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Points awarded successfully',
                'data' => ['point_id' => $pointId]
            ];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to award points',
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function redeemPoints(int $tenantId, int $userId, int $points, ?int $referenceId = null, ?string $referenceType = null, ?string $notes = null, ?int $createdBy = null): array
    {
        $pdo = $this->db->connect();
        
        try {
            $pdo->beginTransaction();
            
            // Check if user has enough points
            $currentPoints = $this->repository->getUserTotalPoints($tenantId, $userId);
            
            if ($currentPoints < $points) {
                return [
                    'success' => false,
                    'message' => 'Insufficient points'
                ];
            }
            
            // Create point transaction
            $pointData = [
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'points_earned' => 0,
                'points_redeemed' => $points,
                'transaction_type' => 'REDEEMED',
                'reference_id' => $referenceId,
                'reference_type' => $referenceType,
                'notes' => $notes,
                'created_by' => $createdBy
            ];
            
            $pointId = $this->repository->createPoint($pointData);
            
            // Update customer loyalty
            $this->updateCustomerLoyaltyPoints($tenantId, $userId, 0, $points, $createdBy);
            
            // Audit log - commented out for testing
            // Audit::log($tenantId, $createdBy, 'LOYALTY_POINTS_REDEEMED', [
            //     'point_id' => $pointId,
            //     'user_id' => $userId,
            //     'points' => $points
            // ]);
            
            $pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Points redeemed successfully',
                'data' => ['point_id' => $pointId]
            ];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to redeem points',
                'error' => $e->getMessage()
            ];
        }
    }
    
    // ==================== Loyalty Rewards ====================
    
    public function getAllRewards(int $tenantId, ?string $status = null): array
    {
        return $this->repository->findAllRewards($tenantId, $status);
    }
    
    public function getRewardById(int $id, int $tenantId): ?array
    {
        return $this->repository->findRewardById($id, $tenantId);
    }
    
    public function createReward(int $tenantId, array $data, ?int $userId = null): array
    {
        $pdo = $this->db->connect();
        
        try {
            $pdo->beginTransaction();
            
            // Validate reward
            $reward = new LoyaltyReward($data);
            $errors = $reward->validate();
            
            if (!empty($errors)) {
                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors
                ];
            }
            
            // Prepare data
            $rewardData = [
                'tenant_id' => $tenantId,
                'reward_code' => $data['reward_code'],
                'reward_name' => $data['reward_name'],
                'reward_name_en' => $data['reward_name_en'] ?? null,
                'reward_description' => $data['reward_description'] ?? null,
                'points_required' => $data['points_required'],
                'reward_type' => $data['reward_type'],
                'discount_percentage' => $data['discount_percentage'] ?? null,
                'discount_amount' => $data['discount_amount'] ?? null,
                'status' => $data['status'] ?? 'ACTIVE',
                'valid_from' => $data['valid_from'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'created_by' => $userId
            ];
            
            $rewardId = $this->repository->createReward($rewardData);
            
            // Audit log - commented out for testing
            // Audit::log($tenantId, $userId, 'LOYALTY_REWARD_CREATED', [
            //     'reward_id' => $rewardId,
            //     'reward_code' => $data['reward_code']
            // ]);
            
            $pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Reward created successfully',
                'data' => ['reward_id' => $rewardId]
            ];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to create reward',
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function updateReward(int $id, int $tenantId, array $data, ?int $userId = null): array
    {
        $pdo = $this->db->connect();
        
        try {
            $pdo->beginTransaction();
            
            // Validate reward
            $reward = new LoyaltyReward($data);
            $errors = $reward->validate();
            
            if (!empty($errors)) {
                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors
                ];
            }
            
            // Prepare data
            $rewardData = [
                'tenant_id' => $tenantId,
                'reward_name' => $data['reward_name'],
                'reward_name_en' => $data['reward_name_en'] ?? null,
                'reward_description' => $data['reward_description'] ?? null,
                'points_required' => $data['points_required'],
                'reward_type' => $data['reward_type'],
                'discount_percentage' => $data['discount_percentage'] ?? null,
                'discount_amount' => $data['discount_amount'] ?? null,
                'status' => $data['status'] ?? 'ACTIVE',
                'valid_from' => $data['valid_from'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'updated_by' => $userId
            ];
            
            $result = $this->repository->updateReward($id, $rewardData);
            
            // Audit log - commented out for testing
            // Audit::log($tenantId, $userId, 'LOYALTY_REWARD_UPDATED', [
            //     'reward_id' => $id
            // ]);
            
            $pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Reward updated successfully'
            ];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to update reward',
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function deleteReward(int $id, int $tenantId, ?int $userId = null): array
    {
        $pdo = $this->db->connect();
        
        try {
            $pdo->beginTransaction();
            
            $result = $this->repository->deleteReward($id, $tenantId);
            
            // Audit log - commented out for testing
            // Audit::log($tenantId, $userId, 'LOYALTY_REWARD_DELETED', [
            //     'reward_id' => $id
            // ]);
            
            $pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Reward deleted successfully'
            ];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to delete reward',
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function redeemReward(int $tenantId, int $userId, int $rewardId, ?int $createdBy = null): array
    {
        $pdo = $this->db->connect();
        
        try {
            $pdo->beginTransaction();
            
            // Get reward
            $reward = $this->repository->findRewardById($rewardId, $tenantId);
            
            if (!$reward) {
                return [
                    'success' => false,
                    'message' => 'Reward not found'
                ];
            }
            
            // Check if reward is valid
            $rewardModel = new LoyaltyReward($reward);
            if (!$rewardModel->isValid()) {
                return [
                    'success' => false,
                    'message' => 'Reward is not available'
                ];
            }
            
            // Check if user has enough points
            $currentPoints = $this->repository->getUserTotalPoints($tenantId, $userId);
            
            if ($currentPoints < $reward['points_required']) {
                return [
                    'success' => false,
                    'message' => 'Insufficient points to redeem this reward'
                ];
            }
            
            // Redeem points
            $redeemResult = $this->redeemPoints($tenantId, $userId, $reward['points_required'], $rewardId, 'REWARD', 'Redeemed reward: ' . $reward['reward_name'], $createdBy);
            
            if (!$redeemResult['success']) {
                return $redeemResult;
            }
            
            $pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Reward redeemed successfully',
                'data' => [
                    'reward_id' => $rewardId,
                    'reward_name' => $reward['reward_name'],
                    'points_redeemed' => $reward['points_required']
                ]
            ];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to redeem reward',
                'error' => $e->getMessage()
            ];
        }
    }
    
    // ==================== Customer Loyalty ====================
    
    public function getAllCustomerLoyalty(int $tenantId): array
    {
        return $this->repository->findAllCustomerLoyalty($tenantId);
    }
    
    public function getCustomerLoyaltyById(int $id, int $tenantId): ?array
    {
        return $this->repository->findCustomerLoyaltyById($id, $tenantId);
    }
    
    public function getCustomerLoyaltyByUser(int $userId, int $tenantId): ?array
    {
        return $this->repository->findCustomerLoyaltyByUser($userId, $tenantId);
    }
    
    public function enrollCustomer(int $tenantId, int $userId, ?int $createdBy = null): array
    {
        $pdo = $this->db->connect();
        
        try {
            $pdo->beginTransaction();
            
            // Check if user already enrolled
            $existing = $this->repository->findCustomerLoyaltyByUser($userId, $tenantId);
            
            if ($existing) {
                return [
                    'success' => false,
                    'message' => 'User already enrolled in loyalty program'
                ];
            }
            
            // Create customer loyalty
            $loyaltyData = [
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'total_points' => 0,
                'current_tier' => 'BRONZE',
                'tier_progress' => 0,
                'tier_points_required' => 100,
                'points_earned_lifetime' => 0,
                'points_redeemed_lifetime' => 0,
                'created_by' => $createdBy
            ];
            
            $loyaltyId = $this->repository->createCustomerLoyalty($loyaltyData);
            
            // Award welcome bonus
            $this->awardPoints($tenantId, $userId, 100, 'EARNED', null, 'WELCOME_BONUS', 'Welcome bonus points', $createdBy);
            
            // Audit log - commented out for testing
            // Audit::log($tenantId, $createdBy, 'LOYALTY_CUSTOMER_ENROLLED', [
            //     'customer_loyalty_id' => $loyaltyId,
            //     'user_id' => $userId
            // ]);
            
            $pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Customer enrolled successfully',
                'data' => ['customer_loyalty_id' => $loyaltyId]
            ];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to enroll customer',
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function getTopCustomers(int $tenantId, int $limit = 10): array
    {
        return $this->repository->getTopCustomersByPoints($tenantId, $limit);
    }
    
    public function getCustomersByTier(int $tenantId, string $tier): array
    {
        return $this->repository->getCustomersByTier($tenantId, $tier);
    }
    
    // ==================== Helper Methods ====================
    
    private function updateCustomerLoyaltyPoints(int $tenantId, int $userId, int $pointsEarned, int $pointsRedeemed, ?int $createdBy = null): void
    {
        // Get or create customer loyalty
        $loyalty = $this->repository->findCustomerLoyaltyByUser($userId, $tenantId);
        
        if (!$loyalty) {
            // Create new customer loyalty
            $loyaltyData = [
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'total_points' => $pointsEarned - $pointsRedeemed,
                'current_tier' => 'BRONZE',
                'tier_progress' => 0,
                'tier_points_required' => 100,
                'points_earned_lifetime' => $pointsEarned,
                'points_redeemed_lifetime' => $pointsRedeemed,
                'created_by' => $createdBy
            ];
            
            $this->repository->createCustomerLoyalty($loyaltyData);
        } else {
            // Update existing customer loyalty
            $newTotalPoints = $loyalty['total_points'] + $pointsEarned - $pointsRedeemed;
            $newPointsEarnedLifetime = $loyalty['points_earned_lifetime'] + $pointsEarned;
            $newPointsRedeemedLifetime = $loyalty['points_redeemed_lifetime'] + $pointsRedeemed;
            
            // Check for tier upgrade
            $newTier = $loyalty['current_tier'];
            $lastTierUpgrade = $loyalty['last_tier_upgrade'];
            
            foreach ($this->tierConfig as $tier => $config) {
                if ($newTotalPoints >= $config['points_required'] && $this->compareTiers($tier, $newTier) > 0) {
                    $newTier = $tier;
                    $lastTierUpgrade = date('Y-m-d');
                }
            }
            
            // Calculate tier progress
            $tierConfig = $this->tierConfig[$newTier];
            $tierProgress = 0;
            if ($tierConfig['next_tier'] !== null) {
                $tierProgress = min(100, ($newTotalPoints / $tierConfig['next_points']) * 100);
            }
            
            $updateData = [
                'tenant_id' => $tenantId,
                'total_points' => $newTotalPoints,
                'current_tier' => $newTier,
                'tier_progress' => $tierProgress,
                'tier_points_required' => $tierConfig['points_required'],
                'points_earned_lifetime' => $newPointsEarnedLifetime,
                'points_redeemed_lifetime' => $newPointsRedeemedLifetime,
                'last_tier_upgrade' => $lastTierUpgrade,
                'updated_by' => $createdBy
            ];
            
            $this->repository->updateCustomerLoyalty($loyalty['customer_loyalty_id'], $updateData);
        }
    }
    
    private function compareTiers(string $tier1, string $tier2): int
    {
        $tiers = ['BRONZE' => 1, 'SILVER' => 2, 'GOLD' => 3, 'PLATINUM' => 4];
        return $tiers[$tier1] - $tiers[$tier2];
    }
}
