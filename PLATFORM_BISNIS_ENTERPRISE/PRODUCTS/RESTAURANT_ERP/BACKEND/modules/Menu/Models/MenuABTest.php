<?php

declare(strict_types=1);

namespace Modules\Menu\Models;

class MenuABTest
{
    private int $id;
    private int $tenantId;
    private ?int $branchId;
    private string $name;
    private ?string $description;
    private string $status;
    private ?string $startDate;
    private ?string $endDate;
    private float $trafficSplit;
    private ?array $targetAudience;
    private ?string $successMetric;
    private int $createdBy;
    private string $createdAt;
    private ?int $updatedBy;
    private ?string $updatedAt;
    private ?string $deletedAt;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? 0;
        $this->tenantId = $data['tenant_id'] ?? 0;
        $this->branchId = $data['branch_id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->description = $data['description'] ?? null;
        $this->status = $data['status'] ?? 'DRAFT';
        $this->startDate = $data['start_date'] ?? null;
        $this->endDate = $data['end_date'] ?? null;
        $this->trafficSplit = (float)($data['traffic_split'] ?? 50.00);
        $this->targetAudience = isset($data['target_audience']) ? json_decode($data['target_audience'], true) : null;
        $this->successMetric = $data['success_metric'] ?? null;
        $this->createdBy = $data['created_by'] ?? 0;
        $this->createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->updatedBy = $data['updated_by'] ?? null;
        $this->updatedAt = $data['updated_at'] ?? null;
        $this->deletedAt = $data['deleted_at'] ?? null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTenantId(): int
    {
        return $this->tenantId;
    }

    public function getBranchId(): ?int
    {
        return $this->branchId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    public function getTrafficSplit(): float
    {
        return $this->trafficSplit;
    }

    public function getTargetAudience(): ?array
    {
        return $this->targetAudience;
    }

    public function getSuccessMetric(): ?string
    {
        return $this->successMetric;
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

    public function getDeletedAt(): ?string
    {
        return $this->deletedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenantId,
            'branch_id' => $this->branchId,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'traffic_split' => $this->trafficSplit,
            'target_audience' => $this->targetAudience,
            'success_metric' => $this->successMetric,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt,
            'updated_by' => $this->updatedBy,
            'updated_at' => $this->updatedAt,
            'deleted_at' => $this->deletedAt
        ];
    }
}
