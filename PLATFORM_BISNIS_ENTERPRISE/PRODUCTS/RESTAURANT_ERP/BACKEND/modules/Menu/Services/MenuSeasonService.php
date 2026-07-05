<?php

declare(strict_types=1);

namespace Modules\Menu\Services;

use Modules\Menu\Models\MenuSeason;
use PDO;

class MenuSeasonService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function createSeason(array $data): MenuSeason
    {
        $sql = "INSERT INTO menu_seasons 
                (tenant_id, season_name, season_type, year, start_date, end_date, 
                 description, theme, status, created_by) 
                VALUES 
                (:tenant_id, :season_name, :season_type, :year, :start_date, :end_date,
                 :description, :theme, :status, :created_by)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':season_name' => $data['season_name'],
            ':season_type' => $data['season_type'],
            ':year' => $data['year'],
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date'],
            ':description' => $data['description'] ?? null,
            ':theme' => $data['theme'] ?? null,
            ':status' => $data['status'] ?? 'DRAFT',
            ':created_by' => $data['created_by']
        ]);

        $data['id'] = (int)$this->db->lastInsertId();
        return new MenuSeason($data);
    }

    public function getSeasonById(int $id): ?MenuSeason
    {
        $sql = "SELECT * FROM menu_seasons WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? new MenuSeason($result) : null;
    }

    public function getSeasonsByTenant(int $tenantId, ?int $year = null): array
    {
        $sql = "SELECT * FROM menu_seasons 
                WHERE tenant_id = :tenant_id AND deleted_at IS NULL";
        $params = [':tenant_id' => $tenantId];

        if ($year !== null) {
            $sql .= " AND year = :year";
            $params[':year'] = $year;
        }

        $sql .= " ORDER BY start_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($result) => new MenuSeason($result), $results);
    }

    public function getActiveSeasons(int $tenantId): array
    {
        $sql = "SELECT * FROM menu_seasons 
                WHERE tenant_id = :tenant_id 
                AND status = 'ACTIVE' 
                AND start_date <= CURDATE() 
                AND end_date >= CURDATE()
                AND deleted_at IS NULL
                ORDER BY start_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($result) => new MenuSeason($result), $results);
    }

    public function updateSeason(int $id, array $data): ?MenuSeason
    {
        $sql = "UPDATE menu_seasons 
                SET season_name = :season_name, 
                    season_type = :season_type, 
                    year = :year, 
                    start_date = :start_date, 
                    end_date = :end_date, 
                    description = :description, 
                    theme = :theme, 
                    status = :status, 
                    updated_by = :updated_by,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':season_name' => $data['season_name'],
            ':season_type' => $data['season_type'],
            ':year' => $data['year'],
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date'],
            ':description' => $data['description'] ?? null,
            ':theme' => $data['theme'] ?? null,
            ':status' => $data['status'],
            ':updated_by' => $data['updated_by']
        ]);

        return $this->getSeasonById($id);
    }

    public function deleteSeason(int $id, int $deletedBy): bool
    {
        $sql = "UPDATE menu_seasons 
                SET deleted_at = CURRENT_TIMESTAMP, 
                    updated_by = :deleted_by 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':deleted_by' => $deletedBy
        ]);
    }

    public function addSeasonItem(int $seasonId, array $itemData): bool
    {
        $sql = "INSERT INTO menu_season_items 
                (season_id, product_id, item_type, priority, pricing_override, 
                 seasonal_description, seasonal_image_url, availability_start_date, availability_end_date) 
                VALUES 
                (:season_id, :product_id, :item_type, :priority, :pricing_override,
                 :seasonal_description, :seasonal_image_url, :availability_start_date, :availability_end_date)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':season_id' => $seasonId,
            ':product_id' => $itemData['product_id'],
            ':item_type' => $itemData['item_type'],
            ':priority' => $itemData['priority'] ?? 0,
            ':pricing_override' => $itemData['pricing_override'] ?? null,
            ':seasonal_description' => $itemData['seasonal_description'] ?? null,
            ':seasonal_image_url' => $itemData['seasonal_image_url'] ?? null,
            ':availability_start_date' => $itemData['availability_start_date'] ?? null,
            ':availability_end_date' => $itemData['availability_end_date'] ?? null
        ]);
    }

    public function getSeasonItems(int $seasonId): array
    {
        $sql = "SELECT si.*, p.name as product_name, p.base_price 
                FROM menu_season_items si
                JOIN products p ON si.product_id = p.id
                WHERE si.season_id = :season_id
                ORDER BY si.priority DESC, si.id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':season_id' => $seasonId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function removeSeasonItem(int $seasonId, int $productId): bool
    {
        $sql = "DELETE FROM menu_season_items 
                WHERE season_id = :season_id AND product_id = :product_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':season_id' => $seasonId,
            ':product_id' => $productId
        ]);
    }

    public function recordSeasonMetric(int $seasonId, string $metricName, float $metricValue, ?float $comparisonValue = null): bool
    {
        $changePercentage = null;
        if ($comparisonValue !== null && $comparisonValue > 0) {
            $changePercentage = (($metricValue - $comparisonValue) / $comparisonValue) * 100;
        }

        $sql = "INSERT INTO menu_season_analytics 
                (season_id, metric_name, metric_value, comparison_value, change_percentage) 
                VALUES (:season_id, :metric_name, :metric_value, :comparison_value, :change_percentage)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':season_id' => $seasonId,
            ':metric_name' => $metricName,
            ':metric_value' => $metricValue,
            ':comparison_value' => $comparisonValue,
            ':change_percentage' => $changePercentage
        ]);
    }

    public function getSeasonAnalytics(int $seasonId): array
    {
        $sql = "SELECT * FROM menu_season_analytics 
                WHERE season_id = :season_id 
                ORDER BY recorded_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':season_id' => $seasonId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
