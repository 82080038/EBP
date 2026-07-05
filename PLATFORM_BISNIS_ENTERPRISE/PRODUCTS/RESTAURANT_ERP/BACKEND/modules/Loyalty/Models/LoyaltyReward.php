<?php

/**
 * LoyaltyReward Model
 * 
 * @package EBP\Modules\Loyalty
 * @version 1.0.0
 */

class LoyaltyReward
{
    private $reward_id;
    private $tenant_id;
    private $reward_code;
    private $reward_name;
    private $reward_name_en;
    private $reward_description;
    private $points_required;
    private $reward_type;
    private $discount_percentage;
    private $discount_amount;
    private $status;
    private $valid_from;
    private $valid_until;
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
    public function getRewardId(): ?int { return $this->reward_id; }
    public function getTenantId(): int { return $this->tenant_id; }
    public function getRewardCode(): string { return $this->reward_code; }
    public function getRewardName(): string { return $this->reward_name; }
    public function getRewardNameEn(): ?string { return $this->reward_name_en; }
    public function getRewardDescription(): ?string { return $this->reward_description; }
    public function getPointsRequired(): int { return $this->points_required; }
    public function getRewardType(): string { return $this->reward_type; }
    public function getDiscountPercentage(): ?float { return $this->discount_percentage; }
    public function getDiscountAmount(): ?float { return $this->discount_amount; }
    public function getStatus(): string { return $this->status; }
    public function getValidFrom(): ?string { return $this->valid_from; }
    public function getValidUntil(): ?string { return $this->valid_until; }
    public function getCreatedBy(): ?int { return $this->created_by; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getUpdatedBy(): ?int { return $this->updated_by; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }
    public function getDeletedAt(): ?string { return $this->deleted_at; }
    
    // Setters
    public function setRewardId(int $id): void { $this->reward_id = $id; }
    public function setTenantId(int $tenantId): void { $this->tenant_id = $tenantId; }
    public function setRewardCode(string $code): void { $this->reward_code = $code; }
    public function setRewardName(string $name): void { $this->reward_name = $name; }
    public function setRewardNameEn(?string $name): void { $this->reward_name_en = $name; }
    public function setRewardDescription(?string $description): void { $this->reward_description = $description; }
    public function setPointsRequired(int $points): void { $this->points_required = $points; }
    public function setRewardType(string $type): void { $this->reward_type = $type; }
    public function setDiscountPercentage(?float $percentage): void { $this->discount_percentage = $percentage; }
    public function setDiscountAmount(?float $amount): void { $this->discount_amount = $amount; }
    public function setStatus(string $status): void { $this->status = $status; }
    public function setValidFrom(?string $date): void { $this->valid_from = $date; }
    public function setValidUntil(?string $date): void { $this->valid_until = $date; }
    public function setCreatedBy(?int $userId): void { $this->created_by = $userId; }
    public function setCreatedAt(?string $date): void { $this->created_at = $date; }
    public function setUpdatedBy(?int $userId): void { $this->updated_by = $userId; }
    public function setUpdatedAt(?string $date): void { $this->updated_at = $date; }
    public function setDeletedAt(?string $date): void { $this->deleted_at = $date; }
    
    public function fromArray(array $data): void
    {
        $this->reward_id = $data['reward_id'] ?? null;
        $this->tenant_id = $data['tenant_id'] ?? 0;
        $this->reward_code = $data['reward_code'] ?? '';
        $this->reward_name = $data['reward_name'] ?? '';
        $this->reward_name_en = $data['reward_name_en'] ?? null;
        $this->reward_description = $data['reward_description'] ?? null;
        $this->points_required = $data['points_required'] ?? 0;
        $this->reward_type = $data['reward_type'] ?? 'DISCOUNT';
        $this->discount_percentage = $data['discount_percentage'] ?? null;
        $this->discount_amount = $data['discount_amount'] ?? null;
        $this->status = $data['status'] ?? 'ACTIVE';
        $this->valid_from = $data['valid_from'] ?? null;
        $this->valid_until = $data['valid_until'] ?? null;
        $this->created_by = $data['created_by'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_by = $data['updated_by'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
        $this->deleted_at = $data['deleted_at'] ?? null;
    }
    
    public function toArray(): array
    {
        return [
            'reward_id' => $this->reward_id,
            'tenant_id' => $this->tenant_id,
            'reward_code' => $this->reward_code,
            'reward_name' => $this->reward_name,
            'reward_name_en' => $this->reward_name_en,
            'reward_description' => $this->reward_description,
            'points_required' => $this->points_required,
            'reward_type' => $this->reward_type,
            'discount_percentage' => $this->discount_percentage,
            'discount_amount' => $this->discount_amount,
            'status' => $this->status,
            'valid_from' => $this->valid_from,
            'valid_until' => $this->valid_until,
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
        
        if (empty($this->reward_code)) {
            $errors['reward_code'] = 'Reward code is required';
        }
        
        if (empty($this->reward_name)) {
            $errors['reward_name'] = 'Reward name is required';
        }
        
        if ($this->points_required <= 0) {
            $errors['points_required'] = 'Points required must be greater than 0';
        }
        
        $validTypes = ['DISCOUNT', 'FREE_ITEM', 'UPGRADE', 'EXPERIENCE'];
        if (!in_array($this->reward_type, $validTypes)) {
            $errors['reward_type'] = 'Invalid reward type';
        }
        
        $validStatuses = ['ACTIVE', 'INACTIVE', 'EXPIRED'];
        if (!in_array($this->status, $validStatuses)) {
            $errors['status'] = 'Invalid status';
        }
        
        if ($this->reward_type === 'DISCOUNT' && $this->discount_percentage === null && $this->discount_amount === null) {
            $errors['discount'] = 'Discount percentage or amount is required for DISCOUNT type';
        }
        
        return $errors;
    }
    
    public function isValid(): bool
    {
        if ($this->status !== 'ACTIVE') {
            return false;
        }
        
        $now = date('Y-m-d');
        
        if ($this->valid_from && $now < $this->valid_from) {
            return false;
        }
        
        if ($this->valid_until && $now > $this->valid_until) {
            return false;
        }
        
        return true;
    }
}
