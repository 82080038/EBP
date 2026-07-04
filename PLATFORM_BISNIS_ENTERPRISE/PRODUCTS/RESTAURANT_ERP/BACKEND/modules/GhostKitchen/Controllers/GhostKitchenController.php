<?php

namespace App\Modules\GhostKitchen\Controllers;

use App\Core\BaseController;
use App\Modules\GhostKitchen\Models\VirtualBrand;
use App\Modules\GhostKitchen\Models\VirtualBrandMenuItem;
use App\Modules\GhostKitchen\Models\DeliveryPlatform;
use App\Modules\GhostKitchen\Services\GhostKitchenService;
use App\Core\Auth;

class GhostKitchenController extends BaseController
{
    private $ghostKitchenService;

    public function __construct()
    {
        parent::__construct();
        $this->ghostKitchenService = new GhostKitchenService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get virtual brands
     * GET /api/ghost-kitchen/brands
     */
    public function getVirtualBrands()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $status = $this->request->get('status', null);
        
        $brands = $this->ghostKitchenService->getVirtualBrands($restaurantId, $status);
        
        $this->jsonResponse($brands);
    }

    /**
     * Create virtual brand
     * POST /api/ghost-kitchen/brands
     */
    public function createVirtualBrand()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->ghostKitchenService->createVirtualBrand($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get virtual brand menu items
     * GET /api/ghost-kitchen/brands/{id}/menu-items
     */
    public function getBrandMenuItems($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $items = $this->ghostKitchenService->getBrandMenuItems($id, $restaurantId);
        
        $this->jsonResponse($items);
    }

    /**
     * Add menu item to brand
     * POST /api/ghost-kitchen/brands/{id}/menu-items
     */
    public function addBrandMenuItem($id)
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->ghostKitchenService->addBrandMenuItem($id, $restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get delivery platforms
     * GET /api/ghost-kitchen/platforms
     */
    public function getDeliveryPlatforms()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $platforms = $this->ghostKitchenService->getDeliveryPlatforms($restaurantId);
        
        $this->jsonResponse($platforms);
    }

    /**
     * Create delivery platform
     * POST /api/ghost-kitchen/platforms
     */
    public function createDeliveryPlatform()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->ghostKitchenService->createDeliveryPlatform($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Map brand to platform
     * POST /api/ghost-kitchen/brand-platform-mapping
     */
    public function mapBrandToPlatform()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->ghostKitchenService->mapBrandToPlatform($restaurantId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get ghost kitchen analytics
     * GET /api/ghost-kitchen/analytics
     */
    public function getAnalytics()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $brandId = $this->request->get('brand_id', null);
        $metricType = $this->request->get('type', 'monthly');
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        $limit = $this->request->get('limit', 12);
        
        $analytics = $this->ghostKitchenService->getAnalytics($restaurantId, $brandId, $metricType, $dateFrom, $dateTo, $limit);
        
        $this->jsonResponse($analytics);
    }

    /**
     * Get ghost kitchen summary
     * GET /api/ghost-kitchen/summary
     */
    public function getSummary()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $summary = $this->ghostKitchenService->getSummary($restaurantId);
        
        $this->jsonResponse($summary);
    }
}
