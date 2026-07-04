<?php

namespace App\Modules\GhostKitchen\Services;

use App\Modules\GhostKitchen\Models\VirtualBrand;
use App\Modules\GhostKitchen\Models\VirtualBrandMenuItem;
use App\Modules\GhostKitchen\Models\DeliveryPlatform;
use App\Core\Database;

class GhostKitchenService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get virtual brands
     */
    public function getVirtualBrands($restaurantId, $status)
    {
        $brandModel = new VirtualBrand();
        return $brandModel->getByRestaurant($restaurantId, $status);
    }

    /**
     * Create virtual brand
     */
    public function createVirtualBrand($restaurantId, $userId, $data)
    {
        $brandModel = new VirtualBrand();
        
        $brandData = [
            'restaurant_id' => $restaurantId,
            'brand_name' => $data->brand_name,
            'brand_description' => $data->brand_description ?? null,
            'brand_logo_url' => $data->brand_logo_url ?? null,
            'brand_color_hex' => $data->brand_color_hex ?? null,
            'cuisine_type' => $data->cuisine_type,
            'price_range' => $data->price_range,
            'brand_status' => 'draft',
            'target_audience' => $data->target_audience ?? null,
            'created_by' => $userId
        ];
        
        $brandId = $brandModel->create($brandData);
        
        if (!$brandId) {
            return ['success' => false, 'message' => 'Failed to create virtual brand'];
        }
        
        return ['success' => true, 'message' => 'Virtual brand created', 'brand_id' => $brandId];
    }

    /**
     * Get brand menu items
     */
    public function getBrandMenuItems($brandId, $restaurantId)
    {
        $menuItemModel = new VirtualBrandMenuItem();
        return $menuItemModel->getByBrand($brandId, $restaurantId);
    }

    /**
     * Add menu item to brand
     */
    public function addBrandMenuItem($brandId, $restaurantId, $data)
    {
        $menuItemModel = new VirtualBrandMenuItem();
        
        $menuItemData = [
            'restaurant_id' => $restaurantId,
            'virtual_brand_id' => $brandId,
            'inventory_item_id' => $data->inventory_item_id,
            'item_name' => $data->item_name,
            'item_description' => $data->item_description ?? null,
            'price' => $data->price,
            'item_image_url' => $data->item_image_url ?? null,
            'is_available' => true
        ];
        
        $menuItemId = $menuItemModel->create($menuItemData);
        
        if (!$menuItemId) {
            return ['success' => false, 'message' => 'Failed to add menu item'];
        }
        
        return ['success' => true, 'message' => 'Menu item added', 'menu_item_id' => $menuItemId];
    }

    /**
     * Get delivery platforms
     */
    public function getDeliveryPlatforms($restaurantId)
    {
        $platformModel = new DeliveryPlatform();
        return $platformModel->getByRestaurant($restaurantId);
    }

    /**
     * Create delivery platform
     */
    public function createDeliveryPlatform($restaurantId, $data)
    {
        $platformModel = new DeliveryPlatform();
        
        $platformData = [
            'restaurant_id' => $restaurantId,
            'platform_name' => $data->platform_name,
            'platform_type' => $data->platform_type,
            'api_key' => $data->api_key ?? null,
            'api_secret' => $data->api_secret ?? null,
            'webhook_url' => $data->webhook_url ?? null,
            'platform_config' => json_encode($data->platform_config ?? []),
            'is_active' => true
        ];
        
        $platformId = $platformModel->create($platformData);
        
        if (!$platformId) {
            return ['success' => false, 'message' => 'Failed to create delivery platform'];
        }
        
        return ['success' => true, 'message' => 'Delivery platform created', 'platform_id' => $platformId];
    }

    /**
     * Map brand to platform
     */
    public function mapBrandToPlatform($restaurantId, $data)
    {
        $sql = "INSERT INTO virtual_brand_platforms (restaurant_id, virtual_brand_id, delivery_platform_id, external_brand_id, external_store_id, platform_menu_config, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $inserted = $this->db->query($sql, [
            $restaurantId,
            $data->virtual_brand_id,
            $data->delivery_platform_id,
            $data->external_brand_id ?? null,
            $data->external_store_id ?? null,
            json_encode($data->platform_menu_config ?? []),
            true
        ]);
        
        if (!$inserted) {
            return ['success' => false, 'message' => 'Failed to map brand to platform'];
        }
        
        return ['success' => true, 'message' => 'Brand mapped to platform'];
    }

    /**
     * Get analytics
     */
    public function getAnalytics($restaurantId, $brandId, $metricType, $dateFrom, $dateTo, $limit)
    {
        $params = [$restaurantId];
        $where = "WHERE restaurant_id = ?";
        
        if ($brandId) {
            $where .= " AND virtual_brand_id = ?";
            $params[] = $brandId;
        }
        
        $where .= " AND metric_type = ?";
        $params[] = $metricType;
        
        if ($dateFrom) {
            $where .= " AND metric_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $where .= " AND metric_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT * FROM ghost_kitchen_analytics {$where} ORDER BY metric_date DESC LIMIT ?";
        $params[] = $limit;
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get summary
     */
    public function getSummary($restaurantId)
    {
        $brandModel = new VirtualBrand();
        $platformModel = new DeliveryPlatform();
        
        // Active brands
        $activeBrands = $brandModel->countByStatus($restaurantId, 'active');
        
        // Active platforms
        $activePlatforms = $platformModel->countActive($restaurantId);
        
        // Latest analytics
        $latestAnalytics = $this->getAnalytics($restaurantId, null, 'monthly', null, null, 1);
        
        return [
            'active_brands' => $activeBrands,
            'active_platforms' => $activePlatforms,
            'latest_analytics' => $latestAnalytics[0] ?? null
        ];
    }
}
