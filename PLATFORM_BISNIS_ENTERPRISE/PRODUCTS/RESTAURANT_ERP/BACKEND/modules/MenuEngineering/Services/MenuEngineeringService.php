<?php

use PDO;
use PDOException;

global $pdo;

/**
 * Menu Engineering Service
 * 
 * Analyzes menu profitability, food cost percentage, and menu mix
 * Essential for menu optimization and profit maximization
 */
class MenuEngineeringService
{
    private $db;
    private $tenantId;
    private $branchId;

    public function __construct($tenantId = null, $branchId = null)
    {
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->tenantId = $tenantId;
        $this->branchId = $branchId;
    }

    /**
     * Calculate menu item profitability
     */
    public function calculateMenuItemProfitability($productId, $startDate = null, $endDate = null)
    {
        try {
            // Get product info
            $sql = "SELECT p.*, r.total_cost as recipe_cost, 
                    (r.production_cost_labor + r.production_cost_equipment + r.production_cost_overhead) as cost_per_portion 
                    FROM products p 
                    LEFT JOIN recipes r ON p.product_id = r.product_id 
                    WHERE p.product_id = :product_id AND p.tenant_id = :tenant_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':product_id' => $productId,
                ':tenant_id' => $this->tenantId
            ]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Product not found'
                ];
            }

            // Calculate food cost percentage
            $recipeCost = $product['recipe_cost'] ?? 0;
            $sellingPrice = $product['price'] ?? 0;
            $foodCostPercentage = $sellingPrice > 0 ? ($recipeCost / $sellingPrice) * 100 : 0;
            $grossMargin = $sellingPrice - $recipeCost;
            $grossMarginPercentage = $sellingPrice > 0 ? ($grossMargin / $sellingPrice) * 100 : 0;

            // Get sales data for the period
            $dateFilter = '';
            $params = [':product_id' => $productId, ':tenant_id' => $this->tenantId];
            
            if ($startDate && $endDate) {
                $dateFilter = " AND oi.created_at BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }

            $sql = "SELECT 
                    COUNT(DISTINCT oi.order_id) as order_count,
                    SUM(oi.quantity) as total_quantity,
                    SUM(oi.quantity * oi.unit_price) as total_revenue
                    FROM order_items oi
                    INNER JOIN orders o ON oi.order_id = o.order_id
                    WHERE oi.product_id = :product_id AND o.tenant_id = :tenant_id
                    $dateFilter";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $salesData = $stmt->fetch(PDO::FETCH_ASSOC);

            $totalRevenue = $salesData['total_revenue'] ?? 0;
            $totalFoodCost = $totalRevenue * ($recipeCost / 100);
            $totalQuantity = $salesData['total_quantity'] ?? 0;
            $orderCount = $salesData['order_count'] ?? 0;

            // Calculate contribution margin
            $contributionMargin = $totalRevenue - $totalFoodCost;
            $contributionMarginPercentage = $totalRevenue > 0 ? ($contributionMargin / $totalRevenue) * 100 : 0;

            return [
                'success' => true,
                'data' => [
                    'product' => $product,
                    'profitability' => [
                        'food_cost' => $recipeCost,
                        'selling_price' => $sellingPrice,
                        'food_cost_percentage' => round($foodCostPercentage, 2),
                        'gross_margin' => $grossMargin,
                        'gross_margin_percentage' => round($grossMarginPercentage, 2)
                    ],
                    'sales' => [
                        'order_count' => $orderCount,
                        'total_quantity' => $totalQuantity,
                        'total_revenue' => $totalRevenue,
                        'total_food_cost' => $totalFoodCost,
                        'contribution_margin' => $contributionMargin,
                        'contribution_margin_percentage' => round($contributionMarginPercentage, 2)
                    ]
                ]
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to calculate profitability: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get menu mix analysis (star, plowhorse, puzzle, dog classification)
     */
    public function getMenuMixAnalysis($startDate = null, $endDate = null)
    {
        try {
            $dateFilter = '';
            $params = [':tenant_id' => $this->tenantId];
            
            if ($startDate && $endDate) {
                $dateFilter = " AND oi.created_at BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }

            // Get sales data for all products
            $sql = "SELECT 
                    p.product_id,
                    p.product_name as name,
                    p.price,
                    p.category_id,
                    c.category_name as category_name,
                    (r.production_cost_labor + r.production_cost_equipment + r.production_cost_overhead) as recipe_cost,
                    COUNT(DISTINCT oi.order_id) as order_count,
                    SUM(oi.quantity) as total_quantity,
                    SUM(oi.quantity * oi.unit_price) as total_revenue
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.category_id
                    LEFT JOIN recipes r ON p.product_id = r.product_id
                    LEFT JOIN order_items oi ON p.product_id = oi.product_id
                    LEFT JOIN orders o ON oi.order_id = o.order_id AND o.tenant_id = p.tenant_id
                    WHERE p.tenant_id = :tenant_id
                    $dateFilter
                    GROUP BY p.product_id, p.product_name, p.price, p.category_id, c.category_name, r.production_cost_labor, r.production_cost_equipment, r.production_cost_overhead
                    ORDER BY total_revenue DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate averages for classification
            $totalProducts = count($products);
            if ($totalProducts === 0) {
                return [
                    'success' => true,
                    'data' => [
                        'products' => [],
                        'classification' => []
                    ]
                ];
            }

            $avgPopularity = array_sum(array_column($products, 'order_count')) / $totalProducts;
            $avgRevenue = array_sum(array_column($products, 'total_revenue')) / $totalProducts;

            // Classify each product
            $classification = [
                'stars' => [],      // High popularity, high margin
                'plowhorses' => [], // High popularity, low margin
                'puzzles' => [],    // Low popularity, high margin
                'dogs' => []        // Low popularity, low margin
            ];

            foreach ($products as &$product) {
                $foodCost = $product['recipe_cost'] ?? 0;
                $margin = $product['price'] - $foodCost;
                $marginPercentage = $product['price'] > 0 ? ($margin / $product['price']) * 100 : 0;
                
                $product['margin'] = $margin;
                $product['margin_percentage'] = round($marginPercentage, 2);
                $product['food_cost_percentage'] = $product['price'] > 0 ? ($foodCost / $product['price']) * 100 : 0;

                // Classify based on popularity and margin
                $isHighPopularity = $product['order_count'] >= $avgPopularity;
                $isHighMargin = $marginPercentage >= 30; // 30% margin threshold

                if ($isHighPopularity && $isHighMargin) {
                    $product['classification'] = 'star';
                    $classification['stars'][] = $product;
                } elseif ($isHighPopularity && !$isHighMargin) {
                    $product['classification'] = 'plowhorse';
                    $classification['plowhorses'][] = $product;
                } elseif (!$isHighPopularity && $isHighMargin) {
                    $product['classification'] = 'puzzle';
                    $classification['puzzles'][] = $product;
                } else {
                    $product['classification'] = 'dog';
                    $classification['dogs'][] = $product;
                }
            }

            return [
                'success' => true,
                'data' => [
                    'products' => $products,
                    'classification' => $classification,
                    'averages' => [
                        'avg_popularity' => round($avgPopularity, 2),
                        'avg_revenue' => round($avgRevenue, 2)
                    ]
                ]
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to get menu mix analysis: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get category performance analysis
     */
    public function getCategoryPerformance($startDate = null, $endDate = null)
    {
        try {
            $dateFilter = '';
            $params = [':tenant_id' => $this->tenantId];
            
            if ($startDate && $endDate) {
                $dateFilter = " AND oi.created_at BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }

            $sql = "SELECT 
                    c.category_id,
                    c.category_name as category_name,
                    COUNT(DISTINCT p.product_id) as product_count,
                    COUNT(DISTINCT oi.order_id) as order_count,
                    SUM(oi.quantity) as total_quantity,
                    SUM(oi.quantity * oi.unit_price) as total_revenue,
                    AVG(p.price) as avg_price
                    FROM categories c
                    LEFT JOIN products p ON c.category_id = p.category_id AND p.tenant_id = :tenant_id
                    LEFT JOIN order_items oi ON p.product_id = oi.product_id
                    LEFT JOIN orders o ON oi.order_id = o.order_id AND o.tenant_id = :tenant_id
                    WHERE c.tenant_id = :tenant_id
                    $dateFilter
                    GROUP BY c.category_id, c.category_name
                    ORDER BY total_revenue DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate category contribution
            $totalRevenue = array_sum(array_column($categories, 'total_revenue'));
            foreach ($categories as &$category) {
                $category['revenue_percentage'] = $totalRevenue > 0 
                    ? round(($category['total_revenue'] / $totalRevenue) * 100, 2) 
                    : 0;
            }

            return [
                'success' => true,
                'data' => [
                    'categories' => $categories,
                    'total_revenue' => $totalRevenue
                ]
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to get category performance: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get menu optimization recommendations
     */
    public function getMenuOptimizationRecommendations()
    {
        try {
            $menuMix = $this->getMenuMixAnalysis();
            if (!$menuMix['success']) {
                return $menuMix;
            }

            $recommendations = [];
            $classification = $menuMix['data']['classification'];

            // Analyze stars (keep and promote)
            if (!empty($classification['stars'])) {
                $recommendations[] = [
                    'type' => 'maintain',
                    'priority' => 'high',
                    'message' => 'Maintain star items - these are high performers',
                    'items' => array_column($classification['stars'], 'name')
                ];
            }

            // Analyze plowhorses (increase price or reduce cost)
            if (!empty($classification['plowhorses'])) {
                $recommendations[] = [
                    'type' => 'optimize',
                    'priority' => 'high',
                    'message' => 'Consider increasing price or reducing food cost for plowhorses',
                    'items' => array_column($classification['plowhorses'], 'name')
                ];
            }

            // Analyze puzzles (promote or reposition)
            if (!empty($classification['puzzles'])) {
                $recommendations[] = [
                    'type' => 'promote',
                    'priority' => 'medium',
                    'message' => 'Promote puzzle items through marketing or better placement',
                    'items' => array_column($classification['puzzles'], 'name')
                ];
            }

            // Analyze dogs (consider removing)
            if (!empty($classification['dogs'])) {
                $recommendations[] = [
                    'type' => 'remove',
                    'priority' => 'low',
                    'message' => 'Consider removing or replacing dog items',
                    'items' => array_column($classification['dogs'], 'name')
                ];
            }

            return [
                'success' => true,
                'data' => [
                    'recommendations' => $recommendations,
                    'summary' => [
                        'stars' => count($classification['stars']),
                        'plowhorses' => count($classification['plowhorses']),
                        'puzzles' => count($classification['puzzles']),
                        'dogs' => count($classification['dogs'])
                    ]
                ]
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to get recommendations: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calculate ideal food cost vs actual food cost
     */
    public function getFoodCostVariance($startDate = null, $endDate = null)
    {
        try {
            $dateFilter = '';
            $params = [':tenant_id' => $this->tenantId];
            
            if ($startDate && $endDate) {
                $dateFilter = " AND oi.created_at BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }

            // Get actual food cost from sales
            $sql = "SELECT 
                    SUM(oi.quantity * (r.production_cost_labor + r.production_cost_equipment + r.production_cost_overhead)) as actual_food_cost,
                    SUM(oi.quantity * oi.unit_price) as total_revenue
                    FROM order_items oi
                    LEFT JOIN products p ON oi.product_id = p.product_id
                    LEFT JOIN recipes r ON p.product_id = r.product_id
                    LEFT JOIN orders o ON oi.order_id = o.order_id
                    WHERE o.tenant_id = :tenant_id
                    $dateFilter";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $actualData = $stmt->fetch(PDO::FETCH_ASSOC);

            $actualFoodCost = $actualData['actual_food_cost'] ?? 0;
            $totalRevenue = $actualData['total_revenue'] ?? 0;
            $actualFoodCostPercentage = $totalRevenue > 0 ? ($actualFoodCost / $totalRevenue) * 100 : 0;

            // Get ideal food cost (theoretical based on recipes)
            $sql = "SELECT 
                    AVG((r.production_cost_labor + r.production_cost_equipment + r.production_cost_overhead) / p.price) * 100 as ideal_food_cost_percentage
                    FROM products p
                    LEFT JOIN recipes r ON p.product_id = r.product_id
                    WHERE p.tenant_id = :tenant_id AND r.recipe_id IS NOT NULL";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':tenant_id' => $this->tenantId]);
            $idealData = $stmt->fetch(PDO::FETCH_ASSOC);

            $idealFoodCostPercentage = $idealData['ideal_food_cost_percentage'] ?? 0;
            $variance = $actualFoodCostPercentage - $idealFoodCostPercentage;
            $variancePercentage = $idealFoodCostPercentage > 0 ? ($variance / $idealFoodCostPercentage) * 100 : 0;

            return [
                'success' => true,
                'data' => [
                    'actual_food_cost' => $actualFoodCost,
                    'actual_food_cost_percentage' => round($actualFoodCostPercentage, 2),
                    'ideal_food_cost_percentage' => round($idealFoodCostPercentage, 2),
                    'variance' => round($variance, 2),
                    'variance_percentage' => round($variancePercentage, 2),
                    'total_revenue' => $totalRevenue
                ]
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Failed to get food cost variance: ' . $e->getMessage()
            ];
        }
    }
}
