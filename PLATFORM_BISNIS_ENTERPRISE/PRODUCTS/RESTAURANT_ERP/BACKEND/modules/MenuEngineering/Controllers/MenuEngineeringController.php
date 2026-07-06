<?php

require_once __DIR__ . '/../Services/MenuEngineeringService.php';
require_once __DIR__ . '/../../../core/Response.php';

/**
 * Menu Engineering Controller
 * 
 * Handles HTTP requests for menu engineering operations
 */
class MenuEngineeringController
{
    private $menuEngineeringService;

    public function __construct()
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $branchId = $_SESSION['branch_id'] ?? null;
        $this->menuEngineeringService = new MenuEngineeringService($tenantId, $branchId);
    }

    /**
     * Get menu item profitability
     * GET /api/v1/menu-engineering/profitability/{product_id}
     */
    public function getProfitability($productId)
    {
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        
        $result = $this->menuEngineeringService->calculateMenuItemProfitability($productId, $startDate, $endDate);
        
        if ($result['success']) {
            Response::json($result, 200);
        } else {
            Response::json($result, 404);
        }
    }

    /**
     * Get menu mix analysis
     * GET /api/v1/menu-engineering/menu-mix
     */
    public function getMenuMix()
    {
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        
        $result = $this->menuEngineeringService->getMenuMixAnalysis($startDate, $endDate);
        
        Response::json($result, 200);
    }

    /**
     * Get category performance
     * GET /api/v1/menu-engineering/category-performance
     */
    public function getCategoryPerformance()
    {
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        
        $result = $this->menuEngineeringService->getCategoryPerformance($startDate, $endDate);
        
        Response::json($result, 200);
    }

    /**
     * Get menu optimization recommendations
     * GET /api/v1/menu-engineering/recommendations
     */
    public function getRecommendations()
    {
        $result = $this->menuEngineeringService->getMenuOptimizationRecommendations();
        
        Response::json($result, 200);
    }

    /**
     * Get food cost variance
     * GET /api/v1/menu-engineering/food-cost-variance
     */
    public function getFoodCostVariance()
    {
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        
        $result = $this->menuEngineeringService->getFoodCostVariance($startDate, $endDate);
        
        Response::json($result, 200);
    }
}
