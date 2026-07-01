<?php

require_once __DIR__ . '/../Repositories/AIRepository.php';
require_once __DIR__ . '/../../../config/database.php';

class KitchenIntelligenceService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new AIRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function analyzeKitchenPerformance($tenantId, $branchId, $dateFrom, $dateTo)
    {
        try {
            // Get kitchen orders data
            $kitchenData = $this->repository->getKitchenPerformanceData($tenantId, $branchId, $dateFrom, $dateTo);
            
            // Calculate metrics
            $metrics = [
                'total_orders' => count($kitchenData),
                'avg_preparation_time' => $this->calculateAvgPrepTime($kitchenData),
                'on_time_rate' => $this->calculateOnTimeRate($kitchenData),
                'bottleneck_hours' => $this->identifyBottlenecks($kitchenData),
                'peak_hours' => $this->identifyPeakHours($kitchenData),
                'chef_performance' => $this->analyzeChefPerformance($kitchenData)
            ];

            // Generate recommendations
            $recommendations = $this->generateKitchenRecommendations($metrics);

            $predictionData = [
                'metrics' => $metrics,
                'recommendations' => $recommendations,
                'analysis_period' => ['start' => $dateFrom, 'end' => $dateTo]
            ];
            
            $this->repository->savePrediction($tenantId, $branchId, 'KITCHEN_OPTIMIZATION', $predictionData, 80.0);

            return [
                'success' => true,
                'message' => 'Kitchen intelligence analysis completed',
                'data' => $predictionData
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to analyze kitchen: ' . $e->getMessage()
            ];
        }
    }

    private function calculateAvgPrepTime($kitchenData)
    {
        if (empty($kitchenData)) return 0;
        
        $totalTime = 0;
        $count = 0;
        
        foreach ($kitchenData as $order) {
            if ($order['preparation_time']) {
                $totalTime += $order['preparation_time'];
                $count++;
            }
        }
        
        return $count > 0 ? $totalTime / $count : 0;
    }

    private function calculateOnTimeRate($kitchenData)
    {
        if (empty($kitchenData)) return 0;
        
        $onTime = 0;
        $total = count($kitchenData);
        
        foreach ($kitchenData as $order) {
            if ($order['is_on_time'] ?? false) {
                $onTime++;
            }
        }
        
        return $total > 0 ? ($onTime / $total) * 100 : 0;
    }

    private function identifyBottlenecks($kitchenData)
    {
        $hourlyDelays = [];
        
        foreach ($kitchenData as $order) {
            $hour = date('H', strtotime($order['created_at']));
            if (!isset($hourlyDelays[$hour])) {
                $hourlyDelays[$hour] = ['total' => 0, 'delayed' => 0];
            }
            $hourlyDelays[$hour]['total']++;
            if (!($order['is_on_time'] ?? false)) {
                $hourlyDelays[$hour]['delayed']++;
            }
        }
        
        $bottlenecks = [];
        foreach ($hourlyDelays as $hour => $data) {
            $delayRate = ($data['delayed'] / $data['total']) * 100;
            if ($delayRate > 30) {
                $bottlenecks[] = [
                    'hour' => $hour,
                    'delay_rate' => $delayRate,
                    'total_orders' => $data['total']
                ];
            }
        }
        
        return $bottlenecks;
    }

    private function identifyPeakHours($kitchenData)
    {
        $hourlyOrders = [];
        
        foreach ($kitchenData as $order) {
            $hour = date('H', strtotime($order['created_at']));
            if (!isset($hourlyOrders[$hour])) {
                $hourlyOrders[$hour] = 0;
            }
            $hourlyOrders[$hour]++;
        }
        
        arsort($hourlyOrders);
        return array_slice(array_keys($hourlyOrders), 0, 3, true);
    }

    private function analyzeChefPerformance($kitchenData)
    {
        $chefStats = [];
        
        foreach ($kitchenData as $order) {
            $chefId = $order['prepared_by'] ?? null;
            if (!$chefId) continue;
            
            if (!isset($chefStats[$chefId])) {
                $chefStats[$chefId] = ['total' => 0, 'on_time' => 0, 'avg_time' => 0, 'total_time' => 0];
            }
            
            $chefStats[$chefId]['total']++;
            if ($order['is_on_time'] ?? false) {
                $chefStats[$chefId]['on_time']++;
            }
            if ($order['preparation_time']) {
                $chefStats[$chefId]['total_time'] += $order['preparation_time'];
            }
        }
        
        $performance = [];
        foreach ($chefStats as $chefId => $stats) {
            $performance[$chefId] = [
                'total_orders' => $stats['total'],
                'on_time_rate' => ($stats['on_time'] / $stats['total']) * 100,
                'avg_preparation_time' => $stats['total_time'] / $stats['total']
            ];
        }
        
        return $performance;
    }

    private function generateKitchenRecommendations($metrics)
    {
        $recommendations = [];
        
        if ($metrics['on_time_rate'] < 80) {
            $recommendations[] = [
                'type' => 'IMPROVEMENT',
                'priority' => 'HIGH',
                'message' => 'On-time rate is below 80%. Consider optimizing kitchen workflow during peak hours.'
            ];
        }
        
        if (!empty($metrics['bottleneck_hours'])) {
            $recommendations[] = [
                'type' => 'STAFFING',
                'priority' => 'MEDIUM',
                'message' => 'Bottlenecks detected during hours: ' . implode(', ', array_column($metrics['bottleneck_hours'], 'hour')) . '. Consider additional staffing.'
            ];
        }
        
        if ($metrics['avg_preparation_time'] > 20) {
            $recommendations[] = [
                'type' => 'EFFICIENCY',
                'priority' => 'MEDIUM',
                'message' => 'Average preparation time is high. Review preparation processes and station layout.'
            ];
        }
        
        return $recommendations;
    }
}
