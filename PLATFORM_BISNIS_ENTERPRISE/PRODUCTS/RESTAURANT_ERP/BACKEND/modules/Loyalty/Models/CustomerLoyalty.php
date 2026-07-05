<?php

/**
 * CustomerLoyalty Model
 * 
 * @package EBP\Modules\Loyalty
 * @version 1.0.0
 */

class CustomerLoyalty
{
    private $customer_loyalty_id;
    private $tenant_id;
    private $user_id;
    private $total_points;
    private $current_tier;
    private $tier_progress;
    private $tier_points_required;
    private $points_earned_lifetime;
    private $points_redeemed_lifetime;
    private $last_tier_upgrade;
    private $created_by;
    private $created_at;
    private $updated_by;
    private $updated_at;
    private $deleted_at;
    
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fromArray($data);
        }
    }
    
    // Getters
    public function getCustomerLoyaltyId(): ?int { return $this->customer_loyalty_id; }
    public function getTenantId(): int { return $this->tenant_id; }
    public function getUserId(): int { return $this->user_id; }
    public function getTotalPoints(): int { return $this->total_points; }
    public function getCurrentTier(): string { return $this->current_tier; }
    public function getTierProgress(): int { return $this->tier_progress; }
    public function getTierPointsRequired(): int { return $this->tier_points_required; }
    public function getPointsEarnedLifetime(): int { return $this->points_earned_lifetime; }
    public function getPointsRedeemedLifetime(): int { return $this->points_redeemed_lifetime; }
    public function getLastTierUpgrade(): ?string { return $this->last_tier_upgrade; }
    public function getCreatedBy(): ?int { return $this->created_by; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getUpdatedBy(): ?int { return $this->updated_by; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }
    public function getDeletedAt(): ?string { return $this->deleted_at; }
    
    // Setters
    public function setCustomerLoyaltyId(int $id): void { $this->customer_loyalty_id = $id; }
    public function setTenantId(int $tenantId): void { $this->tenant_id = $tenantId; }
    public function setUserId(int $userId): void { $this->user_id = $userId; }
    public function setTotalPoints(int $points): void { $this->total_points = $points; }
    public function setCurrentTier(string $tier): void { $this->current_tier = $tier; }
    public function setTierProgress(int $progress): void { $this->tier_progress = $progress; }
    public function setTierPointsRequired(int $points): void { $this->tier_points_required = $points; }
    public function setPointsEarnedLifetime(int $points): void { $this->points_earned_lifetime = $points; }
    public function setPointsRedeemedLifetime(int $points): void { $this->points_redeemed_lifetime = $points; }
    public function setLastTierUpgrade(?string $date): void { $this->last_tier_upgrade = $date; }
    public function setCreatedBy(?int $userId): void { $this->created_by = $userId; }
    public function setCreatedAt(?string $date): void { $this->created_at = $date; }
    public function setUpdatedBy(?int $userId): void { $this->updated_by = $userId; }
    public function setUpdatedAt(?string $date): void { $this->updated_at = $date; }
    public function setDeletedAt(?string $date): void { $this->deleted_at = $date; }
    
    public function fromArray(array $data): void
    {
        $this->customer_loyalty_id = $data['customer_loyalty_id'] ?? null;
        $this->tenant_id = $data['tenant_id'] ?? 0;
        $this->user_id = $data['user_id'] ?? 0;
        $this->total_points = $data['total_points'] ?? 0;
        $this->current_tier = $data['current_tier'] ?? 'BRONZE';
        $this->tier_progress = $data['tier_progress'] ?? 0;
        $this->tier_points_required = $data['tier_points_required'] ?? 100;
        $this->points_earned_lifetime = $data['points_earned_lifetime'] ?? 0;
        $this->points_redeemed_lifetime = $data['points_redeemed_lifetime'] ?? 0;
        $this->last_tier_upgrade = $data['last_tier_upgrade'] ?? null;
        $this->created_by = $data['created_by'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_by = $data['updated_by'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
        $this->deleted_at = $data['deleted_at'] ?? null;
    }
    
    public function toArray(): array
    {
        return [
            'customer_loyalty_id' => $this->customer_loyalty_id,
            'tenant_id' => $this->tenant_id,
            'user_id' => $this->user_id,
            'total_points' => $this->total_points,
            'current_tier' => $this->current_tier,
            'tier_progress' => $this->tier_progress,
            'tier_points_required' => $this->tier_points_required,
            'points_earned_lifetime' => $this->points_earned_lifetime,
            'points_redeemed_lifetime' => $this->points_redeemed_lifetime,
            'last_tier_upgrade' => $this->last_tier_upgrade,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_by' => $this->updated_by,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }
    
    public function validate(): array
    {
        $errors = [];
        
        if (empty($this->tenant_id)) {
            $errors['tenant_id'] = 'Tenant ID is required';
        }
        
        if (empty($this->user_id)) {
            $errors['user_id'] = 'User ID is required';
        }
        
        if ($this->total_points < 0) {
            $errors['total_points'] = 'Total points cannot be negative';
        }
        
        $validTiers = ['BRONZE', 'SILVER', 'GOLD', 'PLATINUM'];
        if (!in_array($this->current_tier, $validTiers)) {
            $errors['current_tier'] = 'Invalid tier';
        }
        
        if ($this->tier_progress < 0) {
            $errors['tier_progress'] = 'Tier progress cannot be negative';
        }
        
        if ($this->tier_points_required <= 0) {
            $errors['tier_points_required'] = 'Tier points required must be greater than 0';
        }
        
        return $errors;
    }
    
    public function canUpgradeTier(): bool
    {
        return $this->total_points >= $this->tier_points_required;
    }
    
    public function getNextTier(): ?string
    {
        $tiers = ['BRONZE', 'SILVER', 'GOLD', 'PLATINUM'];
        $currentIndex = array_search($this->current_tier, $tiers);
        
        if ($currentIndex !== false && $currentIndex < count($tiers) - 1) {
            return $tiers[$currentIndex + 1];
        }
        
        return null;
    }
    
    public function calculateTierProgress(): int
    {
        if ($this->tier_points_required > 0) {
            return min(100, ($this->total_points / $this->tier_points_required) * 100);
        }
        return 0;
    }
}
