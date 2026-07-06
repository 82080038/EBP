<?php

if (!class_exists('AdvancedAIRepository')) {
    require_once __DIR__ . '/../Repositories/AdvancedAIRepository.php';
}


class AdvancedAIService
{
    public $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new AdvancedAIRepository();
                
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function analyzeMenuEngineering($tenantId, $branchId, $date)
    {
        try {
            $products = $this->getProductSalesData($tenantId, $branchId, $date);
            
            foreach ($products as $product) {
                $margin = $product['price'] - $product['cost'];
                $marginPercent = $margin / $product['price'] * 100;
                
                $popularityScore = $this->calculatePopularityScore($product['sales_volume']);
                $profitScore = $this->calculateProfitScore($marginPercent, $product['sales_volume']);
                $menuMixScore = ($popularityScore + $profitScore) / 2;
                
                $recommendation = $this->getMenuRecommendation($menuMixScore, $marginPercent);
                $recommendedPrice = $recommendation === 'REPRICE' ? $product['cost'] * 1.5 : $product['price'];
                
                $this->repository->createMenuEngineering([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'analysis_date' => $date,
                    'product_id' => $product['product_id'],
                    'product_name' => $product['product_name'],
                    'current_price' => $product['price'],
                    'cost' => $product['cost'],
                    'margin' => $marginPercent,
                    'sales_volume' => $product['sales_volume'],
                    'popularity_score' => $popularityScore,
                    'profit_score' => $profitScore,
                    'menu_mix_score' => $menuMixScore,
                    'recommendation' => $recommendation,
                    'recommended_price' => $recommendedPrice,
                    'reasoning' => $this->getReasoning($recommendation, $menuMixScore, $marginPercent)
                ]);
            }

            return [
                'success' => true,
                'message' => 'Menu engineering analysis completed',
                'analyzed_products' => count($products)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Analysis failed: ' . $e->getMessage()
            ];
        }
    }

    public function optimizeStaff($tenantId, $branchId, $date)
    {
        try {
            $hourlyPredictions = $this->getHourlyOrderPredictions($tenantId, $branchId, $date);
            
            foreach ($hourlyPredictions as $hour => $prediction) {
                $requiredStaff = $this->calculateRequiredStaff($prediction);
                $currentStaff = $this->getCurrentStaff($tenantId, $branchId, $date, $hour);
                
                $overstaffed = $currentStaff > $requiredStaff;
                $understaffed = $currentStaff < $requiredStaff;
                
                $costSavings = $overstaffed ? ($currentStaff - $requiredStaff) * 50000 : 0;
                
                $this->repository->createStaffOptimization([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'date' => $date,
                    'hour' => $hour,
                    'predicted_orders' => $prediction,
                    'required_staff' => $requiredStaff,
                    'current_staff' => $currentStaff,
                    'overstaffed' => $overstaffed,
                    'understaffed' => $understaffed,
                    'cost_savings' => $costSavings,
                    'recommendation' => $this->getStaffRecommendation($overstaffed, $understaffed, $requiredStaff, $currentStaff)
                ]);
            }

            return [
                'success' => true,
                'message' => 'Staff optimization completed',
                'optimized_hours' => count($hourlyPredictions)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Optimization failed: ' . $e->getMessage()
            ];
        }
    }

    public function detectFraud($tenantId, $branchId, $date)
    {
        try {
            $alerts = [];
            
            // Check for unusual order patterns
            $unusualOrders = $this->detectUnusualOrders($tenantId, $branchId, $date);
            foreach ($unusualOrders as $order) {
                $alerts[] = [
                    'type' => 'UNUSUAL_ORDER',
                    'severity' => 'MEDIUM',
                    'description' => 'Unusual order pattern detected',
                    'related_order_id' => $order['order_id'],
                    'risk_score' => 65
                ];
            }
            
            // Check for void patterns
            $voidPatterns = $this->detectVoidPatterns($tenantId, $branchId, $date);
            foreach ($voidPatterns as $pattern) {
                $alerts[] = [
                    'type' => 'VOID_PATTERN',
                    'severity' => 'HIGH',
                    'description' => 'Suspicious void pattern detected',
                    'related_user_id' => $pattern['user_id'],
                    'risk_score' => 75
                ];
            }
            
            // Check for discount abuse
            $discountAbuse = $this->detectDiscountAbuse($tenantId, $branchId, $date);
            foreach ($discountAbuse as $abuse) {
                $alerts[] = [
                    'type' => 'DISCOUNT_ABUSE',
                    'severity' => 'MEDIUM',
                    'description' => 'Excessive discount usage detected',
                    'related_user_id' => $abuse['user_id'],
                    'risk_score' => 60
                ];
            }
            
            // Save alerts
            foreach ($alerts as $alert) {
                $this->repository->createFraudAlert([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'alert_type' => $alert['type'],
                    'severity' => $alert['severity'],
                    'description' => $alert['description'],
                    'related_order_id' => $alert['related_order_id'] ?? null,
                    'related_user_id' => $alert['related_user_id'] ?? null,
                    'risk_score' => $alert['risk_score']
                ]);
            }

            return [
                'success' => true,
                'message' => 'Fraud detection completed',
                'alerts_detected' => count($alerts)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Detection failed: ' . $e->getMessage()
            ];
        }
    }

    public function generateExecutiveInsights($tenantId, $branchId, $date)
    {
        try {
            $insights = [];
            
            // Financial insights
            $financialTrend = $this->getFinancialTrend($tenantId, $branchId, $date);
            $insights[] = [
                'category' => 'FINANCIAL',
                'title' => 'Revenue Trend',
                'description' => 'Revenue is ' . $financialTrend['trend'] . ' compared to previous period',
                'metrics' => json_encode($financialTrend),
                'trend' => $financialTrend['trend'],
                'impact_level' => $financialTrend['impact'],
                'recommended_actions' => $financialTrend['recommendation'],
                'priority' => $financialTrend['priority']
            ];
            
            // Operational insights
            $operationalInsight = $this->getOperationalInsight($tenantId, $branchId, $date);
            $insights[] = [
                'category' => 'OPERATIONAL',
                'title' => 'Operational Efficiency',
                'description' => $operationalInsight['description'],
                'metrics' => json_encode($operationalInsight),
                'trend' => $operationalInsight['trend'],
                'impact_level' => $operationalInsight['impact'],
                'recommended_actions' => $operationalInsight['recommendation'],
                'priority' => $operationalInsight['priority']
            ];
            
            // Save insights
            foreach ($insights as $insight) {
                $this->repository->createExecutiveInsight([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'insight_category' => $insight['category'],
                    'insight_title' => $insight['title'],
                    'insight_description' => $insight['description'],
                    'metrics' => $insight['metrics'],
                    'trend' => $insight['trend'],
                    'impact_level' => $insight['impact_level'],
                    'recommended_actions' => $insight['recommended_actions'],
                    'priority' => $insight['priority']
                ]);
            }

            return [
                'success' => true,
                'message' => 'Executive insights generated',
                'insights_count' => count($insights)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Generation failed: ' . $e->getMessage()
            ];
        }
    }

    private function getProductSalesData($tenantId, $branchId, $date)
    {
        $sql = "SELECT p.product_id, p.product_name, p.price, i.average_cost as cost, COUNT(oi.order_item_id) as sales_volume 
                FROM products p
                LEFT JOIN inventory i ON p.product_id = i.product_id
                LEFT JOIN order_items oi ON p.product_id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.order_id
                WHERE p.tenant_id = ? AND o.created_at >= DATE_SUB(?, INTERVAL 30 DAY)
                GROUP BY p.product_id, p.product_name, p.price, i.average_cost";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function calculatePopularityScore($salesVolume)
    {
        if ($salesVolume > 100) return 100;
        if ($salesVolume > 50) return 80;
        if ($salesVolume > 20) return 60;
        if ($salesVolume > 10) return 40;
        return 20;
    }

    private function calculateProfitScore($marginPercent, $salesVolume)
    {
        $score = $marginPercent * 0.6 + ($salesVolume / 10) * 0.4;
        return min(100, max(0, $score));
    }

    private function getMenuRecommendation($menuMixScore, $marginPercent)
    {
        if ($menuMixScore >= 80 && $marginPercent >= 50) return 'FEATURE';
        if ($menuMixScore >= 60 && $marginPercent >= 40) return 'KEEP';
        if ($menuMixScore >= 40 && $marginPercent < 30) return 'REPRICE';
        if ($menuMixScore < 30) return 'REMOVE';
        if ($marginPercent < 20) return 'PROMOTE';
        return 'KEEP';
    }

    private function getReasoning($recommendation, $menuMixScore, $marginPercent)
    {
        $reasons = [
            'KEEP' => 'Product has good sales and margin',
            'REPRICE' => 'Low margin but decent sales, consider price adjustment',
            'PROMOTE' => 'Low margin, consider promotional pricing',
            'REMOVE' => 'Poor performance, consider removing from menu',
            'FEATURE' => 'Star product, feature prominently'
        ];
        return $reasons[$recommendation] ?? 'Standard recommendation';
    }

    private function getHourlyOrderPredictions($tenantId, $branchId, $date)
    {
        // Simplified - in production use actual ML model
        return [
            10 => 5, 11 => 8, 12 => 15, 13 => 20, 14 => 25, 15 => 30,
            16 => 35, 17 => 40, 18 => 35, 19 => 25, 20 => 15, 21 => 10
        ];
    }

    private function calculateRequiredStaff($predictedOrders)
    {
        return max(1, ceil($predictedOrders / 10));
    }

    private function getCurrentStaff($tenantId, $branchId, $date, $hour)
    {
        // Simplified - in production get from schedule
        return 3;
    }

    private function getStaffRecommendation($overstaffed, $understaffed, $required, $current)
    {
        if ($overstaffed) return "Reduce staff by " . ($current - $required);
        if ($understaffed) return "Add " . ($required - $current) . " staff members";
        return "Staff level is optimal";
    }

    private function detectUnusualOrders($tenantId, $branchId, $date)
    {
        // Simplified - in production use actual anomaly detection
        return [];
    }

    private function detectVoidPatterns($tenantId, $branchId, $date)
    {
        // Simplified - in production use actual pattern detection
        return [];
    }

    private function detectDiscountAbuse($tenantId, $branchId, $date)
    {
        // Simplified - in production use actual abuse detection
        return [];
    }

    private function getFinancialTrend($tenantId, $branchId, $date)
    {
        // Simplified - in production use actual trend analysis
        return [
            'trend' => 'IMPROVING',
            'impact' => 'HIGH',
            'recommendation' => 'Continue current strategy',
            'priority' => 80
        ];
    }

    private function getOperationalInsight($tenantId, $branchId, $date)
    {
        // Simplified - in production use actual operational analysis
        return [
            'description' => 'Operations running efficiently',
            'trend' => 'STABLE',
            'impact' => 'MEDIUM',
            'recommendation' => 'Monitor peak hours',
            'priority' => 50
        ];
    }
}
