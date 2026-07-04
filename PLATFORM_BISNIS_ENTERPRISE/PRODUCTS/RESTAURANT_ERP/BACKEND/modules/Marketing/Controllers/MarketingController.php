<?php

namespace App\Modules\Marketing\Controllers;

use App\Core\BaseController;
use App\Modules\Marketing\Models\MarketingCampaign;
use App\Modules\Marketing\Models\Promotion;
use App\Modules\Marketing\Models\BrandAsset;
use App\Modules\Marketing\Models\SocialMediaPost;
use App\Modules\Marketing\Services\MarketingService;
use App\Core\Auth;

class MarketingController extends BaseController
{
    private $marketingService;

    public function __construct()
    {
        parent::__construct();
        $this->marketingService = new MarketingService();
        
        if (!Auth::check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    /**
     * Get marketing campaigns
     * GET /api/marketing/campaigns
     */
    public function getCampaigns()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $status = $this->request->get('status', null);
        $type = $this->request->get('type', null);
        
        $campaigns = $this->marketingService->getCampaigns($restaurantId, $status, $type);
        
        $this->jsonResponse($campaigns);
    }

    /**
     * Create campaign
     * POST /api/marketing/campaigns
     */
    public function createCampaign()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->marketingService->createCampaign($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get promotions
     * GET /api/marketing/promotions
     */
    public function getPromotions()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $status = $this->request->get('status', null);
        $type = $this->request->get('type', null);
        
        $promotions = $this->marketingService->getPromotions($restaurantId, $status, $type);
        
        $this->jsonResponse($promotions);
    }

    /**
     * Create promotion
     * POST /api/marketing/promotions
     */
    public function createPromotion()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->marketingService->createPromotion($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Validate promotion code
     * POST /api/marketing/promotions/validate
     */
    public function validatePromotionCode()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $data = $this->request->getJSON();
        
        $result = $this->marketingService->validatePromotionCode($restaurantId, $data->code);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result);
    }

    /**
     * Get brand assets
     * GET /api/marketing/brand-assets
     */
    public function getBrandAssets()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $type = $this->request->get('type', null);
        
        $assets = $this->marketingService->getBrandAssets($restaurantId, $type);
        
        $this->jsonResponse($assets);
    }

    /**
     * Create brand asset
     * POST /api/marketing/brand-assets
     */
    public function createBrandAsset()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->marketingService->createBrandAsset($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get social media posts
     * GET /api/marketing/social-posts
     */
    public function getSocialPosts()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $platform = $this->request->get('platform', null);
        $status = $this->request->get('status', null);
        
        $posts = $this->marketingService->getSocialPosts($restaurantId, $platform, $status);
        
        $this->jsonResponse($posts);
    }

    /**
     * Create social media post
     * POST /api/marketing/social-posts
     */
    public function createSocialPost()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $userId = Auth::user()->id;
        
        $data = $this->request->getJSON();
        
        $result = $this->marketingService->createSocialPost($restaurantId, $userId, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['error' => $result['message']], 400);
            return;
        }
        
        $this->jsonResponse($result, 201);
    }

    /**
     * Get marketing analytics
     * GET /api/marketing/analytics
     */
    public function getAnalytics()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $metricType = $this->request->get('type', 'monthly');
        $dateFrom = $this->request->get('date_from', null);
        $dateTo = $this->request->get('date_to', null);
        $limit = $this->request->get('limit', 12);
        
        $analytics = $this->marketingService->getAnalytics($restaurantId, $metricType, $dateFrom, $dateTo, $limit);
        
        $this->jsonResponse($analytics);
    }

    /**
     * Get marketing summary
     * GET /api/marketing/summary
     */
    public function getSummary()
    {
        $restaurantId = Auth::user()->restaurant_id;
        
        $summary = $this->marketingService->getSummary($restaurantId);
        
        $this->jsonResponse($summary);
    }
}
