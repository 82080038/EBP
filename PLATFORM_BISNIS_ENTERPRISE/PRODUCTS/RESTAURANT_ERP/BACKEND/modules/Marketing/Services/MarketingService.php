<?php

namespace App\Modules\Marketing\Services;

use App\Modules\Marketing\Models\MarketingCampaign;
use App\Modules\Marketing\Models\Promotion;
use App\Modules\Marketing\Models\BrandAsset;
use App\Modules\Marketing\Models\SocialMediaPost;
use App\Core\Database;

class MarketingService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get marketing campaigns
     */
    public function getCampaigns($restaurantId, $status, $type)
    {
        $campaignModel = new MarketingCampaign();
        return $campaignModel->getByRestaurant($restaurantId, $status, $type);
    }

    /**
     * Create campaign
     */
    public function createCampaign($restaurantId, $userId, $data)
    {
        $campaignModel = new MarketingCampaign();
        
        $campaignData = [
            'restaurant_id' => $restaurantId,
            'campaign_name' => $data->campaign_name,
            'campaign_description' => $data->campaign_description ?? null,
            'campaign_type' => $data->campaign_type,
            'start_date' => $data->start_date,
            'end_date' => $data->end_date,
            'budget_amount' => $data->budget_amount,
            'marketing_channels' => json_encode($data->marketing_channels ?? []),
            'target_audience' => json_encode($data->target_audience ?? []),
            'target_segments' => json_encode($data->target_segments ?? []),
            'campaign_status' => 'draft',
            'created_by' => $userId,
            'managed_by' => $data->managed_by ?? null
        ];
        
        $campaignId = $campaignModel->create($campaignData);
        
        if (!$campaignId) {
            return ['success' => false, 'message' => 'Failed to create campaign'];
        }
        
        return ['success' => true, 'message' => 'Campaign created', 'campaign_id' => $campaignId];
    }

    /**
     * Get promotions
     */
    public function getPromotions($restaurantId, $status, $type)
    {
        $promotionModel = new Promotion();
        return $promotionModel->getByRestaurant($restaurantId, $status, $type);
    }

    /**
     * Create promotion
     */
    public function createPromotion($restaurantId, $userId, $data)
    {
        $promotionModel = new Promotion();
        
        $promotionData = [
            'restaurant_id' => $restaurantId,
            'promotion_name' => $data->promotion_name,
            'promotion_description' => $data->promotion_description ?? null,
            'promotion_code' => $data->promotion_code ?? null,
            'promotion_type' => $data->promotion_type,
            'discount_value' => $data->discount_value,
            'discount_type' => $data->discount_type,
            'applies_to' => $data->applies_to,
            'applicable_items' => json_encode($data->applicable_items ?? []),
            'applicable_categories' => json_encode($data->applicable_categories ?? []),
            'minimum_order_value' => $data->minimum_order_value ?? null,
            'minimum_quantity' => $data->minimum_quantity ?? null,
            'maximum_discount' => $data->maximum_discount ?? null,
            'start_date' => $data->start_date,
            'end_date' => $data->end_date,
            'usage_limit' => $data->usage_limit ?? null,
            'usage_limit_per_customer' => $data->usage_limit_per_customer ?? null,
            'promotion_status' => 'draft',
            'created_by' => $userId
        ];
        
        $promotionId = $promotionModel->create($promotionData);
        
        if (!$promotionId) {
            return ['success' => false, 'message' => 'Failed to create promotion'];
        }
        
        return ['success' => true, 'message' => 'Promotion created', 'promotion_id' => $promotionId];
    }

    /**
     * Validate promotion code
     */
    public function validatePromotionCode($restaurantId, $code)
    {
        $promotionModel = new Promotion();
        $promotion = $promotionModel->findByCode($code, $restaurantId);
        
        if (!$promotion) {
            return ['success' => false, 'message' => 'Invalid promotion code'];
        }
        
        if ($promotion['promotion_status'] !== 'active') {
            return ['success' => false, 'message' => 'Promotion is not active'];
        }
        
        $today = date('Y-m-d');
        if ($promotion['start_date'] > $today || $promotion['end_date'] < $today) {
            return ['success' => false, 'message' => 'Promotion is expired or not yet started'];
        }
        
        if ($promotion['usage_limit'] && $promotion['usage_count'] >= $promotion['usage_limit']) {
            return ['success' => false, 'message' => 'Promotion usage limit reached'];
        }
        
        return [
            'success' => true,
            'message' => 'Promotion valid',
            'promotion' => [
                'id' => $promotion['id'],
                'name' => $promotion['promotion_name'],
                'type' => $promotion['promotion_type'],
                'discount_value' => $promotion['discount_value'],
                'discount_type' => $promotion['discount_type'],
                'applies_to' => $promotion['applies_to']
            ]
        ];
    }

    /**
     * Get brand assets
     */
    public function getBrandAssets($restaurantId, $type)
    {
        $assetModel = new BrandAsset();
        return $assetModel->getByRestaurant($restaurantId, $type);
    }

    /**
     * Create brand asset
     */
    public function createBrandAsset($restaurantId, $userId, $data)
    {
        $assetModel = new BrandAsset();
        
        $assetData = [
            'restaurant_id' => $restaurantId,
            'asset_name' => $data->asset_name,
            'asset_type' => $data->asset_type,
            'asset_category' => $data->asset_category ?? null,
            'file_url' => $data->file_url,
            'file_name' => $data->file_name,
            'file_size' => $data->file_size,
            'file_format' => $data->file_format,
            'usage_context' => $data->usage_context ?? null,
            'dimensions' => $data->dimensions ?? null,
            'is_active' => true,
            'uploaded_by' => $userId
        ];
        
        $assetId = $assetModel->create($assetData);
        
        if (!$assetId) {
            return ['success' => false, 'message' => 'Failed to create asset'];
        }
        
        return ['success' => true, 'message' => 'Asset created', 'asset_id' => $assetId];
    }

    /**
     * Get social media posts
     */
    public function getSocialPosts($restaurantId, $platform, $status)
    {
        $postModel = new SocialMediaPost();
        return $postModel->getByRestaurant($restaurantId, $platform, $status);
    }

    /**
     * Create social media post
     */
    public function createSocialPost($restaurantId, $userId, $data)
    {
        $postModel = new SocialMediaPost();
        
        $postData = [
            'restaurant_id' => $restaurantId,
            'post_title' => $data->post_title ?? null,
            'post_content' => $data->post_content,
            'platform' => $data->platform,
            'media_urls' => json_encode($data->media_urls ?? []),
            'scheduled_date' => $data->scheduled_date ?? null,
            'post_status' => 'draft',
            'created_by' => $userId
        ];
        
        $postId = $postModel->create($postData);
        
        if (!$postId) {
            return ['success' => false, 'message' => 'Failed to create post'];
        }
        
        return ['success' => true, 'message' => 'Post created', 'post_id' => $postId];
    }

    /**
     * Get marketing analytics
     */
    public function getAnalytics($restaurantId, $metricType, $dateFrom, $dateTo, $limit)
    {
        $sql = "SELECT * FROM marketing_analytics WHERE restaurant_id = ? AND metric_type = ?";
        $params = [$restaurantId, $metricType];
        
        if ($dateFrom) {
            $sql .= " AND metric_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND metric_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql .= " ORDER BY metric_date DESC LIMIT ?";
        $params[] = $limit;
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get summary
     */
    public function getSummary($restaurantId)
    {
        $campaignModel = new MarketingCampaign();
        $promotionModel = new Promotion();
        $postModel = new SocialMediaPost();
        
        // Active campaigns
        $activeCampaigns = $campaignModel->countByStatus($restaurantId, 'active');
        
        // Active promotions
        $activePromotions = $promotionModel->countByStatus($restaurantId, 'active');
        
        // Scheduled posts
        $scheduledPosts = $postModel->countByStatus($restaurantId, 'scheduled');
        
        // Latest analytics
        $latestAnalytics = $this->getAnalytics($restaurantId, 'monthly', null, null, 1);
        
        return [
            'active_campaigns' => $activeCampaigns,
            'active_promotions' => $activePromotions,
            'scheduled_posts' => $scheduledPosts,
            'latest_analytics' => $latestAnalytics[0] ?? null
        ];
    }
}
