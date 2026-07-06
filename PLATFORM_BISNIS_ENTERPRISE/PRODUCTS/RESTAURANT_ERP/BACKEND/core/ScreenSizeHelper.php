<?php

declare(strict_types=1);

/**
 * EBP Core - Screen Size Helper
 * 
 * Handles screen size-based data filtering and field selection
 * for responsive API responses
 * 
 * @package EBP\Core
 * @version 1.0.0
 */

class ScreenSizeHelper
{
    /**
     * Screen size breakpoints
     */
    private const BREAKPOINTS = [
        'mobile' => 768,
        'tablet' => 1024
    ];

    /**
     * Default data limits per screen size
     */
    private const DEFAULT_LIMITS = [
        'mobile' => [
            'products' => 10,
            'orders' => 5,
            'reservations' => 5,
            'restaurants' => 5,
            'reviews' => 3,
            'categories' => 5,
            'tables' => 6
        ],
        'tablet' => [
            'products' => 20,
            'orders' => 10,
            'reservations' => 10,
            'restaurants' => 10,
            'reviews' => 5,
            'categories' => 10,
            'tables' => 12
        ],
        'desktop' => [
            'products' => 100,
            'orders' => 50,
            'reservations' => 50,
            'restaurants' => 50,
            'reviews' => 10,
            'categories' => 20,
            'tables' => 24
        ]
    ];

    /**
     * Field sets per screen size (simplified for mobile)
     */
    private const FIELD_SETS = [
        'mobile' => [
            'products' => ['product_id', 'product_name', 'price', 'category_name', 'image_url'],
            'orders' => ['order_id', 'order_number', 'table_id', 'status', 'total_amount'],
            'reservations' => ['reservation_id', 'restaurant_name', 'date', 'time', 'party_size', 'status'],
            'restaurants' => ['id', 'name', 'cuisine', 'rating', 'image', 'distance'],
            'tables' => ['table_id', 'table_number', 'status']
        ],
        'tablet' => [
            'products' => ['product_id', 'product_name', 'price', 'description', 'category_name', 'image_url'],
            'orders' => ['order_id', 'order_number', 'table_id', 'status', 'total_amount', 'created_at'],
            'reservations' => ['reservation_id', 'restaurant_name', 'date', 'time', 'party_size', 'status', 'special_requests'],
            'restaurants' => ['id', 'name', 'cuisine', 'rating', 'image', 'distance', 'address', 'phone'],
            'tables' => ['table_id', 'table_number', 'status', 'capacity']
        ],
        'desktop' => null // Return all fields
    ];

    /**
     * Get screen size from request headers or parameters
     * 
     * @param array $headers Request headers
     * @param array $params Request parameters
     * @return string Screen size category
     */
    public static function getScreenSize(array $headers = [], array $params = []): string
    {
        // Check header first
        if (isset($headers['X-Screen-Size'])) {
            return self::validateScreenSize($headers['X-Screen-Size']);
        }

        // Check parameter
        if (isset($params['screen_size'])) {
            return self::validateScreenSize($params['screen_size']);
        }

        // Check screen width from header
        if (isset($headers['X-Screen-Width'])) {
            $width = (int)$headers['X-Screen-Width'];
            return self::detectFromWidth($width);
        }

        // Default to desktop
        return 'desktop';
    }

    /**
     * Validate screen size value
     * 
     * @param string $size Screen size
     * @return string Valid screen size
     */
    private static function validateScreenSize(string $size): string
    {
        $validSizes = ['mobile', 'tablet', 'desktop'];
        return in_array($size, $validSizes) ? $size : 'desktop';
    }

    /**
     * Detect screen size from width
     * 
     * @param int $width Screen width
     * @return string Screen size category
     */
    private static function detectFromWidth(int $width): string
    {
        if ($width < self::BREAKPOINTS['mobile']) {
            return 'mobile';
        } elseif ($width < self::BREAKPOINTS['tablet']) {
            return 'tablet';
        }
        return 'desktop';
    }

    /**
     * Get data limit for resource type
     * 
     * @param string $screenSize Screen size
     * @param string $resourceType Resource type
     * @param int $default Default limit
     * @return int Data limit
     */
    public static function getLimit(string $screenSize, string $resourceType, int $default = 20): int
    {
        return self::DEFAULT_LIMITS[$screenSize][$resourceType] ?? $default;
    }

    /**
     * Get field list for resource type
     * 
     * @param string $screenSize Screen size
     * @param string $resourceType Resource type
     * @return array|null Field list or null for all fields
     */
    public static function getFields(string $screenSize, string $resourceType): ?array
    {
        if (isset(self::FIELD_SETS[$screenSize][$resourceType])) {
            return self::FIELD_SETS[$screenSize][$resourceType];
        }
        return null; // Return all fields
    }

    /**
     * Parse fields from comma-separated string
     * 
     * @param string|null $fields Comma-separated fields
     * @return array|null Field list or null for all fields
     */
    public static function parseFields(?string $fields): ?array
    {
        if ($fields === null || $fields === '*') {
            return null;
        }
        return array_map('trim', explode(',', $fields));
    }

    /**
     * Filter data fields based on screen size
     * 
     * @param array $data Original data
     * @param array|null $fields Fields to include
     * @return array Filtered data
     */
    public static function filterFields(array $data, ?array $fields): array
    {
        if ($fields === null) {
            return $data; // Return all fields
        }

        $filtered = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $filtered[$field] = $data[$field];
            }
        }

        return $filtered;
    }

    /**
     * Filter array of data items
     * 
     * @param array $items Array of data items
     * @param array|null $fields Fields to include
     * @return array Filtered items
     */
    public static function filterArrayFields(array $items, ?array $fields): array
    {
        if ($fields === null) {
            return $items; // Return all fields
        }

        return array_map(function($item) use ($fields) {
            return self::filterFields($item, $fields);
        }, $items);
    }

    /**
     * Apply screen size filtering to response data
     * 
     * @param array $data Response data
     * @param string $screenSize Screen size
     * @param string $resourceType Resource type
     * @return array Filtered response data
     */
    public static function applyScreenSizeFilter(array $data, string $screenSize, string $resourceType): array
    {
        $fields = self::getFields($screenSize, $resourceType);
        
        if (isset($data['data']) && is_array($data['data'])) {
            // Check if data is a list of items
            if (isset($data['data'][0]) && is_array($data['data'][0])) {
                $data['data'] = self::filterArrayFields($data['data'], $fields);
            } else {
                // Single item
                $data['data'] = self::filterFields($data['data'], $fields);
            }
        }

        return $data;
    }

    /**
     * Get pagination parameters with screen size defaults
     * 
     * @param array $params Request parameters
     * @param string $screenSize Screen size
     * @param string $resourceType Resource type
     * @return array Pagination parameters
     */
    public static function getPaginationParams(array $params, string $screenSize, string $resourceType): array
    {
        $defaultLimit = self::getLimit($screenSize, $resourceType);
        
        return [
            'page' => (int)($params['page'] ?? 1),
            'limit' => (int)($params['limit'] ?? $defaultLimit)
        ];
    }
}
