<?php

/**
 * LoyaltyPoint Model
 * 
 * @package EBP\Modules\Loyalty
 * @version 1.0.0
 */

class LoyaltyPoint
{
    private $loyalty_point_id;
    private $tenant_id;
    private $user_id;
    private $points_earned;
    private $points_redeemed;
    private $transaction_type;
    private $reference_id;
    private $reference_type;
    private $notes;
    private $created_by;
    private $created_at;
    
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fromArray($data);
        }
    }
    
    // Getters
    public function getLoyaltyPointId(): ?int { return $this->loyalty_point_id; }
    public function getTenantId(): int { return $this->tenant_id; }
    public function getUserId(): int { return $this->user_id; }
    public function getPointsEarned(): int { return $this->points_earned; }
    public function getPointsRedeemed(): int { return $this->points_redeemed; }
    public function getTransactionType(): string { return $this->transaction_type; }
    public function getReferenceId(): ?int { return $this->reference_id; }
    public function getReferenceType(): ?string { return $this->reference_type; }
    public function getNotes(): ?string { return $this->notes; }
    public function getCreatedBy(): ?int { return $this->created_by; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    
    // Setters
    public function setLoyaltyPointId(int $id): void { $this->loyalty_point_id = $id; }
    public function setTenantId(int $tenantId): void { $this->tenant_id = $tenantId; }
    public function setUserId(int $userId): void { $this->user_id = $userId; }
    public function setPointsEarned(int $points): void { $this->points_earned = $points; }
    public function setPointsRedeemed(int $points): void { $this->points_redeemed = $points; }
    public function setTransactionType(string $type): void { $this->transaction_type = $type; }
    public function setReferenceId(?int $id): void { $this->reference_id = $id; }
    public function setReferenceType(?string $type): void { $this->reference_type = $type; }
    public function setNotes(?string $notes): void { $this->notes = $notes; }
    public function setCreatedBy(?int $userId): void { $this->created_by = $userId; }
    public function setCreatedAt(?string $date): void { $this->created_at = $date; }
    
    public function fromArray(array $data): void
    {
        $this->loyalty_point_id = $data['loyalty_point_id'] ?? null;
        $this->tenant_id = $data['tenant_id'] ?? 0;
        $this->user_id = $data['user_id'] ?? 0;
        $this->points_earned = $data['points_earned'] ?? 0;
        $this->points_redeemed = $data['points_redeemed'] ?? 0;
        $this->transaction_type = $data['transaction_type'] ?? 'EARNED';
        $this->reference_id = $data['reference_id'] ?? null;
        $this->reference_type = $data['reference_type'] ?? null;
        $this->notes = $data['notes'] ?? null;
        $this->created_by = $data['created_by'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
    }
    
    public function toArray(): array
    {
        return [
            'loyalty_point_id' => $this->loyalty_point_id,
            'tenant_id' => $this->tenant_id,
            'user_id' => $this->user_id,
            'points_earned' => $this->points_earned,
            'points_redeemed' => $this->points_redeemed,
            'transaction_type' => $this->transaction_type,
            'reference_id' => $this->reference_id,
            'reference_type' => $this->reference_type,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at
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
        
        $validTypes = ['EARNED', 'REDEEMED', 'ADJUSTED'];
        if (!in_array($this->transaction_type, $validTypes)) {
            $errors['transaction_type'] = 'Invalid transaction type';
        }
        
        if ($this->points_earned < 0) {
            $errors['points_earned'] = 'Points earned cannot be negative';
        }
        
        if ($this->points_redeemed < 0) {
            $errors['points_redeemed'] = 'Points redeemed cannot be negative';
        }
        
        return $errors;
    }
}
