<?php

declare(strict_types=1);

namespace Modules\Menu\Models;

class ProductAllergen
{
    private int $id;
    private int $tenantId;
    private int $productId;
    private int $allergenId;
    private bool $contains;
    private bool $crossContaminationRisk;
    private ?string $notes;
    private int $createdBy;
    private string $createdAt;
    private ?int $updatedBy;
    private ?string $updatedAt;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? 0;
        $this->tenantId = $data['tenant_id'] ?? 0;
        $this->productId = $data['product_id'] ?? 0;
        $this->allergenId = $data['allergen_id'] ?? 0;
        $this->contains = (bool)($data['contains'] ?? true);
        $this->crossContaminationRisk = (bool)($data['cross_contamination_risk'] ?? false);
        $this->notes = $data['notes'] ?? null;
        $this->createdBy = $data['created_by'] ?? 0;
        $this->createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->updatedBy = $data['updated_by'] ?? null;
        $this->updatedAt = $data['updated_at'] ?? null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTenantId(): int
    {
        return $this->tenantId;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getAllergenId(): int
    {
        return $this->allergenId;
    }

    public function getContains(): bool
    {
        return $this->contains;
    }

    public function getCrossContaminationRisk(): bool
    {
        return $this->crossContaminationRisk;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getCreatedBy(): int
    {
        return $this->createdBy;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedBy(): ?int
    {
        return $this->updatedBy;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenantId,
            'product_id' => $this->productId,
            'allergen_id' => $this->allergenId,
            'contains' => $this->contains,
            'cross_contamination_risk' => $this->crossContaminationRisk,
            'notes' => $this->notes,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt,
            'updated_by' => $this->updatedBy,
            'updated_at' => $this->updatedAt
        ];
    }
}
