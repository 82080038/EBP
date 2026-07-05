<?php

declare(strict_types=1);

namespace Modules\Menu\Models;

class MenuSeason
{
    private int $id;
    private int $tenantId;
    private string $seasonName;
    private string $seasonType;
    private int $year;
    private string $startDate;
    private string $endDate;
    private ?string $description;
    private ?string $theme;
    private string $status;
    private int $createdBy;
    private string $createdAt;
    private ?int $updatedBy;
    private ?string $updatedAt;
    private ?string $deletedAt;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? 0;
        $this->tenantId = $data['tenant_id'] ?? 0;
        $this->seasonName = $data['season_name'] ?? '';
        $this->seasonType = $data['season_type'] ?? 'CUSTOM';
        $this->year = $data['year'] ?? (int)date('Y');
        $this->startDate = $data['start_date'] ?? '';
        $this->endDate = $data['end_date'] ?? '';
        $this->description = $data['description'] ?? null;
        $this->theme = $data['theme'] ?? null;
        $this->status = $data['status'] ?? 'DRAFT';
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

    public function getSeasonName(): string
    {
        return $this->seasonName;
    }

    public function getSeasonType(): string
    {
        return $this->seasonType;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getStartDate(): string
    {
        return $this->startDate;
    }

    public function getEndDate(): string
    {
        return $this->endDate;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function getStatus(): string
    {
        return $this->status;
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
            'season_name' => $this->seasonName,
            'season_type' => $this->seasonType,
            'year' => $this->year,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'description' => $this->description,
            'theme' => $this->theme,
            'status' => $this->status,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt,
            'updated_by' => $this->updatedBy,
            'updated_at' => $this->updatedAt,
            'deleted_at' => $this->deletedAt
        ];
    }
}
