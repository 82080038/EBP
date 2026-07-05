<?php

declare(strict_types=1);

namespace Modules\Menu\Controllers;

use Modules\Menu\Services\MenuSeasonService;
use Response;

class MenuSeasonController
{
    private MenuSeasonService $seasonService;

    public function __construct()
    {
        $db = Database::getInstance()->connect();
        $this->seasonService = new MenuSeasonService($db);
    }

    public function createSeason(array $request): void
    {
        try {
            $season = $this->seasonService->createSeason($request);
            Response::success($season->toArray(), 'Season created successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function getSeason(array $request): void
    {
        try {
            $id = (int)$request['id'];
            $season = $this->seasonService->getSeasonById($id);
            
            if (!$season) {
                Response::notFound('Season not found');
                return;
            }

            Response::success($season->toArray());
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function getSeasons(array $request): void
    {
        try {
            $tenantId = (int)$request['tenant_id'];
            $year = isset($request['year']) ? (int)$request['year'] : null;
            
            $seasons = $this->seasonService->getSeasonsByTenant($tenantId, $year);
            Response::success(array_map(fn($season) => $season->toArray(), $seasons));
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function getActiveSeasons(array $request): void
    {
        try {
            $tenantId = (int)$request['tenant_id'];
            $seasons = $this->seasonService->getActiveSeasons($tenantId);
            Response::success(array_map(fn($season) => $season->toArray(), $seasons));
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function updateSeason(array $request): void
    {
        try {
            $id = (int)$request['id'];
            $season = $this->seasonService->updateSeason($id, $request);
            
            if (!$season) {
                Response::notFound('Season not found');
                return;
            }

            Response::success($season->toArray(), 'Season updated successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function deleteSeason(array $request): void
    {
        try {
            $id = (int)$request['id'];
            $deletedBy = (int)$request['deleted_by'];
            
            $result = $this->seasonService->deleteSeason($id, $deletedBy);
            
            if (!$result) {
                Response::notFound('Season not found');
                return;
            }

            Response::success(null, 'Season deleted successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function addSeasonItem(array $request): void
    {
        try {
            $seasonId = (int)$request['season_id'];
            $itemData = $request;
            unset($itemData['season_id']);
            
            $result = $this->seasonService->addSeasonItem($seasonId, $itemData);
            
            if (!$result) {
                Response::error('Failed to add season item', 500);
                return;
            }

            Response::success(null, 'Season item added successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function getSeasonItems(array $request): void
    {
        try {
            $seasonId = (int)$request['season_id'];
            $items = $this->seasonService->getSeasonItems($seasonId);
            Response::success($items);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function removeSeasonItem(array $request): void
    {
        try {
            $seasonId = (int)$request['season_id'];
            $productId = (int)$request['product_id'];
            
            $result = $this->seasonService->removeSeasonItem($seasonId, $productId);
            
            if (!$result) {
                Response::error('Failed to remove season item', 500);
                return;
            }

            Response::success(null, 'Season item removed successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function recordSeasonMetric(array $request): void
    {
        try {
            $seasonId = (int)$request['season_id'];
            $metricName = $request['metric_name'];
            $metricValue = (float)$request['metric_value'];
            $comparisonValue = isset($request['comparison_value']) ? (float)$request['comparison_value'] : null;
            
            $result = $this->seasonService->recordSeasonMetric($seasonId, $metricName, $metricValue, $comparisonValue);
            
            if (!$result) {
                Response::error('Failed to record season metric', 500);
                return;
            }

            Response::success(null, 'Season metric recorded successfully');
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    public function getSeasonAnalytics(array $request): void
    {
        try {
            $seasonId = (int)$request['season_id'];
            $analytics = $this->seasonService->getSeasonAnalytics($seasonId);
            Response::success($analytics);
        } catch (\Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }
}
