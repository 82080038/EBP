<?php

namespace App\Modules\Loyalty\Services;

use App\Modules\Loyalty\Models\LoyaltyProgram;
use App\Modules\Loyalty\Models\LoyaltyTier;
use App\Modules\Loyalty\Models\CustomerLoyalty;
use App\Modules\Loyalty\Models\PointsTransaction;
use App\Modules\Loyalty\Models\Reward;
use App\Modules\Loyalty\Models\RewardRedemption;
use App\Core\Database;

class LoyaltyService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get loyalty programs
     */
    public function getPrograms($restaurantId)
    {
        $programModel = new LoyaltyProgram();
        return $programModel->getByRestaurant($restaurantId);
    }

    /**
     * Create loyalty program
     */
    public function createProgram($restaurantId, $data)
    {
        $programModel = new LoyaltyProgram();
        
        $programData = [
            'restaurant_id' => $restaurantId,
            'program_name' => $data->program_name,
            'program_description' => $data->program_description ?? null,
            'points_per_currency' => $data->points_per_currency ?? 1.00,
            'points_per_visit' => $data->points_per_visit ?? 10,
            'minimum_spend_for_points' => $data->minimum_spend_for_points ?? 0.00,
            'points_to_currency_ratio' => $data->points_to_currency_ratio ?? 0.01,
            'minimum_points_to_redeem' => $data->minimum_points_to_redeem ?? 100,
            'points_expiration_days' => $data->points_expiration_days ?? null,
            'is_active' => true
        ];
        
        $programId = $programModel->create($programData);
        
        if (!$programId) {
            return ['success' => false, 'message' => 'Failed to create program'];
        }
        
        return ['success' => true, 'message' => 'Program created', 'program_id' => $programId];
    }

    /**
     * Get loyalty tiers
     */
    public function getTiers($restaurantId, $programId)
    {
        $tierModel = new LoyaltyTier();
        return $tierModel->getByRestaurant($restaurantId, $programId);
    }

    /**
     * Create loyalty tier
     */
    public function createTier($restaurantId, $data)
    {
        $tierModel = new LoyaltyTier();
        
        $tierData = [
            'restaurant_id' => $restaurantId,
            'program_id' => $data->program_id,
            'tier_name' => $data->tier_name,
            'tier_description' => $data->tier_description ?? null,
            'minimum_points' => $data->minimum_points,
            'minimum_spend' => $data->minimum_spend,
            'minimum_visits' => $data->minimum_visits,
            'points_multiplier' => $data->points_multiplier ?? 1.00,
            'discount_percentage' => $data->discount_percentage ?? 0.00,
            'free_delivery' => $data->free_delivery ?? false,
            'priority_seating' => $data->priority_seating ?? false,
            'special_offers' => $data->special_offers ?? false,
            'tier_color' => $data->tier_color ?? null,
            'tier_icon' => $data->tier_icon ?? null,
            'sort_order' => $data->sort_order ?? 0,
            'is_active' => true
        ];
        
        $tierId = $tierModel->create($tierData);
        
        if (!$tierId) {
            return ['success' => false, 'message' => 'Failed to create tier'];
        }
        
        return ['success' => true, 'message' => 'Tier created', 'tier_id' => $tierId];
    }

    /**
     * Get customer loyalty
     */
    public function getCustomerLoyalty($customerId, $restaurantId)
    {
        $loyaltyModel = new CustomerLoyalty();
        $loyalty = $loyaltyModel->getByCustomer($customerId, $restaurantId);
        
        if ($loyalty) {
            // Get tier details
            $tierModel = new LoyaltyTier();
            if ($loyalty['current_tier_id']) {
                $loyalty['tier'] = $tierModel->findById($loyalty['current_tier_id']);
            }
            
            // Get recent transactions
            $transactionModel = new PointsTransaction();
            $loyalty['recent_transactions'] = $transactionModel->getByCustomer($customerId, 10);
        }
        
        return $loyalty;
    }

    /**
     * Enroll customer in loyalty program
     */
    public function enrollCustomer($customerId, $restaurantId, $data)
    {
        $loyaltyModel = new CustomerLoyalty();
        
        // Check if already enrolled
        $existing = $loyaltyModel->getByCustomer($customerId, $restaurantId);
        if ($existing) {
            return ['success' => false, 'message' => 'Customer already enrolled'];
        }
        
        $loyaltyData = [
            'restaurant_id' => $restaurantId,
            'customer_id' => $customerId,
            'program_id' => $data->program_id,
            'current_points' => 0,
            'total_points_earned' => 0,
            'total_points_redeemed' => 0,
            'current_tier_id' => null,
            'total_visits' => 0,
            'total_spend' => 0.00,
            'is_active' => true,
            'enrolled_at' => date('Y-m-d H:i:s')
        ];
        
        $loyaltyId = $loyaltyModel->create($loyaltyData);
        
        if (!$loyaltyId) {
            return ['success' => false, 'message' => 'Failed to enroll customer'];
        }
        
        return ['success' => true, 'message' => 'Customer enrolled', 'loyalty_id' => $loyaltyId];
    }

    /**
     * Award points to customer
     */
    public function awardPoints($customerId, $restaurantId, $userId, $data)
    {
        $loyaltyModel = new CustomerLoyalty();
        $loyalty = $loyaltyModel->getByCustomer($customerId, $restaurantId);
        
        if (!$loyalty) {
            return ['success' => false, 'message' => 'Customer not enrolled in loyalty program'];
        }
        
        $points = $data->points;
        $transactionType = $data->transaction_type ?? 'bonus';
        
        $balanceBefore = $loyalty['current_points'];
        $balanceAfter = $balanceBefore + $points;
        
        // Update customer loyalty
        $loyaltyModel->update($loyalty['id'], [
            'current_points' => $balanceAfter,
            'total_points_earned' => $loyalty['total_points_earned'] + $points
        ]);
        
        // Log transaction
        $transactionModel = new PointsTransaction();
        $transactionData = [
            'restaurant_id' => $restaurantId,
            'customer_loyalty_id' => $loyalty['id'],
            'customer_id' => $customerId,
            'transaction_type' => $transactionType,
            'points_amount' => $points,
            'reference_type' => $data->reference_type ?? null,
            'reference_id' => $data->reference_id ?? null,
            'reference_number' => $data->reference_number ?? null,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'expires_at' => $data->expires_at ?? null,
            'created_by' => $userId,
            'notes' => $data->notes ?? null
        ];
        
        $transactionModel->create($transactionData);
        
        // Check for tier upgrade
        $this->checkTierUpgrade($loyalty['id'], $restaurantId);
        
        return ['success' => true, 'message' => 'Points awarded', 'new_balance' => $balanceAfter];
    }

    /**
     * Check tier upgrade
     */
    private function checkTierUpgrade($loyaltyId, $restaurantId)
    {
        $loyaltyModel = new CustomerLoyalty();
        $tierModel = new LoyaltyTier();
        
        $loyalty = $loyaltyModel->findById($loyaltyId);
        if (!$loyalty) {
            return;
        }
        
        // Get eligible tiers
        $tiers = $tierModel->getByRestaurant($restaurantId, $loyalty['program_id']);
        
        foreach ($tiers as $tier) {
            if ($loyalty['current_points'] >= $tier['minimum_points'] &&
                $loyalty['total_spend'] >= $tier['minimum_spend'] &&
                $loyalty['total_visits'] >= $tier['minimum_visits']) {
                
                // Update to this tier if it's higher than current
                if (!$loyalty['current_tier_id'] || $tier['sort_order'] > $loyalty['current_tier_id']) {
                    $loyaltyModel->update($loyaltyId, ['current_tier_id' => $tier['id']]);
                    break;
                }
            }
        }
    }

    /**
     * Get points transactions
     */
    public function getTransactions($restaurantId, $customerId, $transactionType, $page, $limit)
    {
        $transactionModel = new PointsTransaction();
        return $transactionModel->getPaginated($restaurantId, $customerId, $transactionType, $page, $limit);
    }

    /**
     * Get rewards
     */
    public function getRewards($restaurantId, $programId)
    {
        $rewardModel = new Reward();
        return $rewardModel->getByRestaurant($restaurantId, $programId);
    }

    /**
     * Create reward
     */
    public function createReward($restaurantId, $data)
    {
        $rewardModel = new Reward();
        
        $rewardData = [
            'restaurant_id' => $restaurantId,
            'program_id' => $data->program_id,
            'reward_name' => $data->reward_name,
            'reward_description' => $data->reward_description ?? null,
            'reward_type' => $data->reward_type,
            'points_required' => $data->points_required,
            'discount_percentage' => $data->discount_percentage ?? null,
            'discount_amount' => $data->discount_amount ?? null,
            'free_item_id' => $data->free_item_id ?? null,
            'is_available' => true,
            'available_from' => $data->available_from ?? null,
            'available_until' => $data->available_until ?? null,
            'total_quantity' => $data->total_quantity ?? null,
            'remaining_quantity' => $data->total_quantity ?? null,
            'max_redemptions_per_customer' => $data->max_redemptions_per_customer ?? null,
            'max_redemptions_total' => $data->max_redemptions_total ?? null,
            'image_url' => $data->image_url ?? null,
            'sort_order' => $data->sort_order ?? 0,
            'is_active' => true
        ];
        
        $rewardId = $rewardModel->create($rewardData);
        
        if (!$rewardId) {
            return ['success' => false, 'message' => 'Failed to create reward'];
        }
        
        return ['success' => true, 'message' => 'Reward created', 'reward_id' => $rewardId];
    }

    /**
     * Redeem reward
     */
    public function redeemReward($rewardId, $restaurantId, $userId, $data)
    {
        $rewardModel = new Reward();
        $reward = $rewardModel->findById($rewardId, $restaurantId);
        
        if (!$reward) {
            return ['success' => false, 'message' => 'Reward not found'];
        }
        
        if (!$reward['is_available'] || !$reward['is_active']) {
            return ['success' => false, 'message' => 'Reward not available'];
        }
        
        $loyaltyModel = new CustomerLoyalty();
        $loyalty = $loyaltyModel->getByCustomer($data->customer_id, $restaurantId);
        
        if (!$loyalty) {
            return ['success' => false, 'message' => 'Customer not enrolled'];
        }
        
        if ($loyalty['current_points'] < $reward['points_required']) {
            return ['success' => false, 'message' => 'Insufficient points'];
        }
        
        // Deduct points
        $balanceBefore = $loyalty['current_points'];
        $balanceAfter = $balanceBefore - $reward['points_required'];
        
        $loyaltyModel->update($loyalty['id'], [
            'current_points' => $balanceAfter,
            'total_points_redeemed' => $loyalty['total_points_redeemed'] + $reward['points_required']
        ]);
        
        // Log points transaction
        $transactionModel = new PointsTransaction();
        $transactionData = [
            'restaurant_id' => $restaurantId,
            'customer_loyalty_id' => $loyalty['id'],
            'customer_id' => $data->customer_id,
            'transaction_type' => 'redeemed',
            'points_amount' => -$reward['points_required'],
            'reference_type' => 'reward',
            'reference_id' => $rewardId,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'created_by' => $userId
        ];
        
        $transactionId = $transactionModel->create($transactionData);
        
        // Create redemption record
        $redemptionModel = new RewardRedemption();
        $redemptionData = [
            'restaurant_id' => $restaurantId,
            'customer_id' => $data->customer_id,
            'reward_id' => $rewardId,
            'points_transaction_id' => $transactionId,
            'points_used' => $reward['points_required'],
            'redemption_status' => 'pending',
            'redeemed_at' => date('Y-m-d H:i:s'),
            'redeemed_by' => $userId,
            'notes' => $data->notes ?? null
        ];
        
        $redemptionId = $redemptionModel->create($redemptionData);
        
        if (!$redemptionId) {
            return ['success' => false, 'message' => 'Failed to create redemption'];
        }
        
        // Update reward quantity if limited
        if ($reward['total_quantity'] !== null) {
            $rewardModel->update($rewardId, [
                'remaining_quantity' => $reward['remaining_quantity'] - 1
            ]);
        }
        
        return ['success' => true, 'message' => 'Reward redeemed', 'redemption_id' => $redemptionId, 'redemption_code' => $redemptionModel->findById($redemptionId)['redemption_code']];
    }

    /**
     * Get reward redemptions
     */
    public function getRedemptions($restaurantId, $customerId, $status, $page, $limit)
    {
        $redemptionModel = new RewardRedemption();
        return $redemptionModel->getPaginated($restaurantId, $customerId, $status, $page, $limit);
    }

    /**
     * Apply reward redemption
     */
    public function applyRedemption($redemptionId, $restaurantId, $userId)
    {
        $redemptionModel = new RewardRedemption();
        $redemption = $redemptionModel->findById($redemptionId, $restaurantId);
        
        if (!$redemption) {
            return ['success' => false, 'message' => 'Redemption not found'];
        }
        
        if ($redemption['redemption_status'] !== 'pending') {
            return ['success' => false, 'message' => 'Redemption already processed'];
        }
        
        $updated = $redemptionModel->update($redemptionId, [
            'redemption_status' => 'applied',
            'applied_at' => date('Y-m-d H:i:s'),
            'applied_by' => $userId
        ]);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to apply redemption'];
        }
        
        return ['success' => true, 'message' => 'Redemption applied'];
    }

    /**
     * Get statistics
     */
    public function getStatistics($restaurantId)
    {
        $loyaltyModel = new CustomerLoyalty();
        $transactionModel = new PointsTransaction();
        $redemptionModel = new RewardRedemption();
        
        // Total enrolled customers
        $totalEnrolled = $loyaltyModel->countByRestaurant($restaurantId);
        
        // Total points issued
        $totalPointsIssued = $transactionModel->sumByType($restaurantId, 'earned');
        
        // Total points redeemed
        $totalPointsRedeemed = $transactionModel->sumByType($restaurantId, 'redeemed');
        
        // Total redemptions
        $totalRedemptions = $redemptionModel->countByRestaurant($restaurantId);
        
        return [
            'total_enrolled' => $totalEnrolled,
            'total_points_issued' => $totalPointsIssued,
            'total_points_redeemed' => $totalPointsRedeemed,
            'total_redemptions' => $totalRedemptions
        ];
    }
}
