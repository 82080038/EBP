<?php

require_once __DIR__ . '/../Repositories/AIRepository.php';
require_once __DIR__ . '/../../../config/database.php';

class CustomerIntelligenceService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new AIRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function analyzeCustomerBehavior($tenantId, $branchId, $dateFrom, $dateTo)
    {
        try {
            // Get customer behavior data
            $behaviorData = $this->repository->getCustomerBehaviorData($tenantId, $branchId, $dateFrom, $dateTo);
            
            // Analyze patterns
            $insights = [
                'total_customers' => $this->countUniqueCustomers($behaviorData),
                'peak_hours' => $this->identifyCustomerPeakHours($behaviorData),
                'avg_order_value' => $this->calculateAvgOrderValue($behaviorData),
                'customer_segments' => $this->segmentCustomers($behaviorData),
                'churn_risk' => $this->identifyChurnRisk($behaviorData, $dateFrom),
                'recommendations' => []
            ];

            // Generate recommendations
            $insights['recommendations'] = $this->generateCustomerRecommendations($insights);

            $predictionData = [
                'insights' => $insights,
                'analysis_period' => ['start' => $dateFrom, 'end' => $dateTo]
            ];
            
            $this->repository->savePrediction($tenantId, $branchId, 'CUSTOMER_INSIGHT', $predictionData, 75.0);

            return [
                'success' => true,
                'message' => 'Customer intelligence analysis completed',
                'data' => $predictionData
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to analyze customers: ' . $e->getMessage()
            ];
        }
    }

    private function countUniqueCustomers($behaviorData)
    {
        $customers = [];
        foreach ($behaviorData as $data) {
            $customerId = $data['customer_id'] ?? $data['user_id'];
            if ($customerId) {
                $customers[$customerId] = true;
            }
        }
        return count($customers);
    }

    private function identifyCustomerPeakHours($behaviorData)
    {
        $hourlyOrders = [];
        
        foreach ($behaviorData as $data) {
            $hour = $data['order_hour'];
            if (!isset($hourlyOrders[$hour])) {
                $hourlyOrders[$hour] = 0;
            }
            $hourlyOrders[$hour] += $data['order_count'];
        }
        
        arsort($hourlyOrders);
        return array_slice(array_keys($hourlyOrders), 0, 3, true);
    }

    private function calculateAvgOrderValue($behaviorData)
    {
        if (empty($behaviorData)) return 0;
        
        $total = 0;
        $count = 0;
        
        foreach ($behaviorData as $data) {
            $total += $data['total_amount'];
            $count++;
        }
        
        return $count > 0 ? $total / $count : 0;
    }

    private function segmentCustomers($behaviorData)
    {
        $customerStats = [];
        
        foreach ($behaviorData as $data) {
            $customerId = $data['user_id'];
            if (!$customerId) continue;
            
            if (!isset($customerStats[$customerId])) {
                $customerStats[$customerId] = ['orders' => 0, 'total_spent' => 0];
            }
            
            $customerStats[$customerId]['orders'] += $data['order_count'];
            $customerStats[$customerId]['total_spent'] += $data['total_amount'];
        }
        
        $segments = [
            'VIP' => 0,
            'REGULAR' => 0,
            'OCCASIONAL' => 0
        ];
        
        foreach ($customerStats as $stats) {
            if ($stats['total_spent'] > 1000000 || $stats['orders'] > 20) {
                $segments['VIP']++;
            } elseif ($stats['orders'] > 5) {
                $segments['REGULAR']++;
            } else {
                $segments['OCCASIONAL']++;
            }
        }
        
        return $segments;
    }

    private function identifyChurnRisk($behaviorData, $dateFrom)
    {
        $lastOrderDates = [];
        
        foreach ($behaviorData as $data) {
            $customerId = $data['user_id'];
            if (!$customerId) continue;
            
            if (!isset($lastOrderDates[$customerId]) || $data['order_date'] > $lastOrderDates[$customerId]) {
                $lastOrderDates[$customerId] = $data['order_date'];
            }
        }
        
        $riskCustomers = [];
        $daysSinceAnalysis = (strtotime(date('Y-m-d')) - strtotime($dateFrom)) / 86400;
        
        foreach ($lastOrderDates as $customerId => $lastDate) {
            $daysSinceLastOrder = (strtotime(date('Y-m-d')) - strtotime($lastDate)) / 86400;
            
            if ($daysSinceLastOrder > $daysSinceAnalysis * 2) {
                $riskCustomers[] = [
                    'customer_id' => $customerId,
                    'days_since_last_order' => $daysSinceLastOrder,
                    'risk_level' => $daysSinceLastOrder > 60 ? 'HIGH' : 'MEDIUM'
                ];
            }
        }
        
        return $riskCustomers;
    }

    private function generateCustomerRecommendations($insights)
    {
        $recommendations = [];
        
        if ($insights['churn_risk'] > 0) {
            $recommendations[] = [
                'type' => 'RETENTION',
                'priority' => 'HIGH',
                'message' => count($insights['churn_risk']) . ' customers at risk of churn. Consider sending promotional offers.'
            ];
        }
        
        if ($insights['customer_segments']['VIP'] > 0) {
            $recommendations[] = [
                'type' => 'LOYALTY',
                'priority' => 'MEDIUM',
                'message' => 'Focus on VIP segment with exclusive offers and personalized service.'
            ];
        }
        
        if (!empty($insights['peak_hours'])) {
            $recommendations[] = [
                'type' => 'MARKETING',
                'priority' => 'LOW',
                'message' => 'Peak customer hours: ' . implode(', ', $insights['peak_hours']) . '. Schedule promotions accordingly.'
            ];
        }
        
        return $recommendations;
    }
}
