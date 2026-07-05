<?php

/**
 * RESTAURANT_ERP - 3-Month Production Simulation
 * 
 * This script simulates 90 days of restaurant operations
 * with realistic growth patterns, seasonal variations,
 * and business metrics.
 */

class ProductionSimulation {
    private $startDate;
    private $endDate;
    private $dailyData = [];
    private $monthlyData = [];
    private $totalMetrics = [];
    
    // Menu items with prices
    private $menuItems = [
        'Nasi Goreng' => 25000,
        'Mie Goreng' => 22000,
        'Gado-Gado' => 20000,
        'Es Teh Manis' => 5000,
        'Jus Jeruk' => 10000,
        'Ayam Bakar' => 35000,
        'Sate Ayam' => 30000,
        'Rendang' => 40000,
        'Bakso' => 18000,
        'Soto' => 20000,
    ];
    
    // Peak days (weekends, holidays)
    private $peakDays = [0, 5, 6]; // Sunday, Friday, Saturday
    
    // Seasonal factors
    private $seasonalFactors = [
        'month1' => 1.0,  // Normal
        'month2' => 1.2,  // Growth
        'month3' => 1.4,  // Peak season
    ];
    
    public function __construct() {
        $this->startDate = new DateTime('2026-07-01');
        $this->endDate = new DateTime('2026-09-30');
    }
    
    public function run() {
        echo "🚀 Starting 3-Month Production Simulation\n";
        echo "==========================================\n\n";
        
        $this->simulateDailyOperations();
        $this->calculateMonthlyMetrics();
        $this->calculateTotalMetrics();
        $this->generateReports();
        
        echo "\n✅ Production Simulation Complete\n";
        echo "📊 Reports generated in: production-simulation-reports/\n";
    }
    
    private function simulateDailyOperations() {
        echo "📍 Simulating Daily Operations (90 days)...\n";
        
        $currentDate = clone $this->startDate;
        $dayCount = 0;
        
        while ($currentDate <= $this->endDate) {
            $dayCount++;
            $dayOfWeek = (int)$currentDate->format('w');
            $month = (int)$currentDate->format('n');
            
            // Determine month factor
            $monthFactor = 1.0;
            if ($month == 7) $monthFactor = $this->seasonalFactors['month1'];
            elseif ($month == 8) $monthFactor = $this->seasonalFactors['month2'];
            elseif ($month == 9) $monthFactor = $this->seasonalFactors['month3'];
            
            // Determine if peak day
            $isPeakDay = in_array($dayOfWeek, $this->peakDays);
            $dayFactor = $isPeakDay ? 1.5 : 1.0;
            
            // Calculate base orders with growth
            $baseOrders = 50 + ($dayCount * 0.5); // Growth over time
            $totalOrders = round($baseOrders * $monthFactor * $dayFactor);
            
            // Generate orders
            $orders = $this->generateOrders($totalOrders, $currentDate);
            
            // Calculate revenue
            $totalRevenue = array_sum(array_column($orders, 'total'));
            
            // Calculate costs
            $totalCost = $totalRevenue * 0.35; // 35% food cost
            
            // Simulate inventory
            $inventory = $this->simulateInventory($totalOrders);
            
            // Simulate staff performance
            $staff = $this->simulateStaffPerformance($totalOrders);
            
            // Store daily data
            $this->dailyData[] = [
                'date' => $currentDate->format('Y-m-d'),
                'day_of_week' => $dayOfWeek,
                'is_peak_day' => $isPeakDay,
                'month_factor' => $monthFactor,
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'total_cost' => $totalCost,
                'gross_profit' => $totalRevenue - $totalCost,
                'average_order_value' => $totalOrders > 0 ? $totalRevenue / $totalOrders : 0,
                'inventory_used' => $inventory,
                'staff_performance' => $staff,
                'orders' => $orders,
            ];
            
            $currentDate->modify('+1 day');
            
            if ($dayCount % 30 == 0) {
                echo "   ✅ Day $dayCount/90 completed\n";
            }
        }
        
        echo "   ✅ All 90 days simulated\n\n";
    }
    
    private function generateOrders($orderCount, $date) {
        $orders = [];
        
        for ($i = 0; $i < $orderCount; $i++) {
            // Random number of items per order (1-5)
            $itemCount = rand(1, 5);
            $orderItems = [];
            $orderTotal = 0;
            
            for ($j = 0; $j < $itemCount; $j++) {
                $item = array_rand($this->menuItems);
                $price = $this->menuItems[$item];
                $quantity = rand(1, 3);
                
                $orderItems[] = [
                    'item' => $item,
                    'price' => $price,
                    'quantity' => $quantity,
                    'subtotal' => $price * $quantity,
                ];
                
                $orderTotal += $price * $quantity;
            }
            
            // Random payment method
            $paymentMethods = ['CASH' => 0.4, 'CARD' => 0.4, 'QRIS' => 0.2];
            $paymentMethod = $this->weightedRandom($paymentMethods);
            
            // Random table
            $table = 'T' . rand(1, 10);
            
            // Random guests
            $guests = rand(1, 8);
            
            $orders[] = [
                'order_id' => $i + 1,
                'date' => $date->format('Y-m-d'),
                'table' => $table,
                'guests' => $guests,
                'items' => $orderItems,
                'total' => $orderTotal,
                'payment_method' => $paymentMethod,
            ];
        }
        
        return $orders;
    }
    
    private function simulateInventory($orderCount) {
        // Simulate inventory usage based on orders
        $baseUsage = $orderCount * 2; // 2 items per order average
        $fluctuation = rand(-10, 10);
        
        return [
            'items_used' => $baseUsage + $fluctuation,
            'restocked' => rand(0, 1) == 1 ? rand(20, 50) : 0,
            'low_stock_alerts' => rand(0, 3),
        ];
    }
    
    private function simulateStaffPerformance($orderCount) {
        // Simulate staff performance metrics
        return [
            'orders_per_hour' => round($orderCount / 8, 2),
            'average_service_time' => rand(15, 30), // minutes
            'customer_satisfaction' => rand(80, 100), // percentage
            'staff_on_duty' => rand(4, 8),
        ];
    }
    
    private function weightedRandom($weights) {
        $total = array_sum($weights);
        $random = mt_rand(1, $total * 100) / 100;
        
        foreach ($weights as $key => $weight) {
            $random -= $weight;
            if ($random <= 0) {
                return $key;
            }
        }
        
        return array_key_last($weights);
    }
    
    private function calculateMonthlyMetrics() {
        echo "📍 Calculating Monthly Metrics...\n";
        
        $months = [7, 8, 9];
        
        foreach ($months as $month) {
            $monthData = array_filter($this->dailyData, function($day) use ($month) {
                return (int)date('n', strtotime($day['date'])) == $month;
            });
            
            $monthOrders = array_sum(array_column($monthData, 'total_orders'));
            $monthRevenue = array_sum(array_column($monthData, 'total_revenue'));
            $monthCost = array_sum(array_column($monthData, 'total_cost'));
            $monthProfit = $monthRevenue - $monthCost;
            
            $this->monthlyData[$month] = [
                'month' => $month,
                'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
                'days' => count($monthData),
                'total_orders' => $monthOrders,
                'total_revenue' => $monthRevenue,
                'total_cost' => $monthCost,
                'gross_profit' => $monthProfit,
                'average_daily_orders' => round($monthOrders / count($monthData), 2),
                'average_daily_revenue' => round($monthRevenue / count($monthData), 2),
                'average_order_value' => $monthOrders > 0 ? round($monthRevenue / $monthOrders, 2) : 0,
                'profit_margin' => $monthRevenue > 0 ? round(($monthProfit / $monthRevenue) * 100, 2) : 0,
            ];
            
            echo "   ✅ {$this->monthlyData[$month]['month_name']}: Rp " . number_format($monthRevenue, 0, ',', '.') . "\n";
        }
        
        echo "\n";
    }
    
    private function calculateTotalMetrics() {
        echo "📍 Calculating Total Metrics...\n";
        
        $totalOrders = array_sum(array_column($this->dailyData, 'total_orders'));
        $totalRevenue = array_sum(array_column($this->dailyData, 'total_revenue'));
        $totalCost = array_sum(array_column($this->dailyData, 'total_cost'));
        $totalProfit = $totalRevenue - $totalCost;
        
        $peakDayData = $this->dailyData[0];
        $slowDayData = $this->dailyData[0];
        
        foreach ($this->dailyData as $day) {
            if ($day['total_revenue'] > $peakDayData['total_revenue']) {
                $peakDayData = $day;
            }
            if ($day['total_revenue'] < $slowDayData['total_revenue']) {
                $slowDayData = $day;
            }
        }
        
        $this->totalMetrics = [
            'period' => '90 Days',
            'start_date' => $this->startDate->format('Y-m-d'),
            'end_date' => $this->endDate->format('Y-m-d'),
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
            'gross_profit' => $totalProfit,
            'average_daily_orders' => round($totalOrders / 90, 2),
            'average_daily_revenue' => round($totalRevenue / 90, 2),
            'average_order_value' => $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0,
            'profit_margin' => $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 2) : 0,
            'peak_day' => $peakDayData['date'],
            'peak_revenue' => $peakDayData['total_revenue'],
            'slow_day' => $slowDayData['date'],
            'slow_revenue' => $slowDayData['total_revenue'],
            'growth_rate' => $this->calculateGrowthRate(),
        ];
        
        echo "   ✅ Total Revenue: Rp " . number_format($totalRevenue, 0, ',', '.') . "\n";
        echo "   ✅ Total Profit: Rp " . number_format($totalProfit, 0, ',', '.') . "\n";
        echo "   ✅ Growth Rate: " . $this->totalMetrics['growth_rate'] . "%\n\n";
    }
    
    private function calculateGrowthRate() {
        if (count($this->monthlyData) < 2) return 0;
        
        $firstMonth = reset($this->monthlyData);
        $lastMonth = end($this->monthlyData);
        
        if ($firstMonth['total_revenue'] == 0) return 0;
        
        $growth = (($lastMonth['total_revenue'] - $firstMonth['total_revenue']) / $firstMonth['total_revenue']) * 100;
        
        return round($growth, 2);
    }
    
    private function generateReports() {
        echo "📍 Generating Reports...\n";
        
        $reportDir = __DIR__ . '/production-simulation-reports';
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0755, true);
        }
        
        // Generate JSON reports
        $this->generateJsonReport($reportDir);
        
        // Generate CSV reports
        $this->generateCsvReport($reportDir);
        
        // Generate HTML report
        $this->generateHtmlReport($reportDir);
        
        // Generate summary report
        $this->generateSummaryReport($reportDir);
        
        echo "   ✅ JSON report generated\n";
        echo "   ✅ CSV report generated\n";
        echo "   ✅ HTML report generated\n";
        echo "   ✅ Summary report generated\n\n";
    }
    
    private function generateJsonReport($dir) {
        $data = [
            'simulation_period' => [
                'start_date' => $this->startDate->format('Y-m-d'),
                'end_date' => $this->endDate->format('Y-m-d'),
                'total_days' => 90,
            ],
            'total_metrics' => $this->totalMetrics,
            'monthly_metrics' => $this->monthlyData,
            'daily_data' => $this->dailyData,
        ];
        
        file_put_contents($dir . '/production-simulation-data.json', json_encode($data, JSON_PRETTY_PRINT));
    }
    
    private function generateCsvReport($dir) {
        $csvFile = fopen($dir . '/daily-data.csv', 'w');
        
        // Header
        fputcsv($csvFile, ['Date', 'Day of Week', 'Peak Day', 'Orders', 'Revenue', 'Cost', 'Profit', 'Avg Order Value']);
        
        // Data
        foreach ($this->dailyData as $day) {
            fputcsv($csvFile, [
                $day['date'],
                $day['day_of_week'],
                $day['is_peak_day'] ? 'Yes' : 'No',
                $day['total_orders'],
                $day['total_revenue'],
                $day['total_cost'],
                $day['gross_profit'],
                $day['average_order_value'],
            ]);
        }
        
        fclose($csvFile);
    }
    
    private function generateHtmlReport($dir) {
        $html = $this->generateHtmlContent();
        file_put_contents($dir . '/production-simulation-report.html', $html);
    }
    
    private function generateHtmlContent() {
        $m = $this->totalMetrics;
        
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RESTAURANT_ERP - 3-Month Production Simulation Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; }
        .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0; }
        .metric-card { background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #007bff; }
        .metric-label { color: #666; font-size: 14px; }
        .metric-value { color: #333; font-size: 24px; font-weight: bold; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #007bff; color: white; }
        tr:hover { background: #f5f5f5; }
        .positive { color: green; }
        .negative { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 RESTAURANT_ERP - 3-Month Production Simulation Report</h1>
        <p><strong>Simulation Period:</strong> ' . $m['start_date'] . ' to ' . $m['end_date'] . ' (90 Days)</p>
        
        <h2>🎯 Total Metrics</h2>
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-label">Total Orders</div>
                <div class="metric-value">' . number_format($m['total_orders']) . '</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Total Revenue</div>
                <div class="metric-value">Rp ' . number_format($m['total_revenue'], 0, ',', '.') . '</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Total Cost</div>
                <div class="metric-value">Rp ' . number_format($m['total_cost'], 0, ',', '.') . '</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Gross Profit</div>
                <div class="metric-value">Rp ' . number_format($m['gross_profit'], 0, ',', '.') . '</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Average Daily Orders</div>
                <div class="metric-value">' . number_format($m['average_daily_orders'], 2) . '</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Average Daily Revenue</div>
                <div class="metric-value">Rp ' . number_format($m['average_daily_revenue'], 0, ',', '.') . '</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Average Order Value</div>
                <div class="metric-value">Rp ' . number_format($m['average_order_value'], 0, ',', '.') . '</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Profit Margin</div>
                <div class="metric-value">' . $m['profit_margin'] . '%</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Growth Rate</div>
                <div class="metric-value ' . ($m['growth_rate'] >= 0 ? 'positive' : 'negative') . '">' . $m['growth_rate'] . '%</div>
            </div>
        </div>
        
        <h2>📅 Monthly Breakdown</h2>
        <table>
            <tr>
                <th>Month</th>
                <th>Days</th>
                <th>Total Orders</th>
                <th>Total Revenue</th>
                <th>Total Cost</th>
                <th>Gross Profit</th>
                <th>Avg Daily Revenue</th>
                <th>Profit Margin</th>
            </tr>';
        
        foreach ($this->monthlyData as $month) {
            $html .= '<tr>
                <td>' . $month['month_name'] . '</td>
                <td>' . $month['days'] . '</td>
                <td>' . number_format($month['total_orders']) . '</td>
                <td>Rp ' . number_format($month['total_revenue'], 0, ',', '.') . '</td>
                <td>Rp ' . number_format($month['total_cost'], 0, ',', '.') . '</td>
                <td>Rp ' . number_format($month['gross_profit'], 0, ',', '.') . '</td>
                <td>Rp ' . number_format($month['average_daily_revenue'], 0, ',', '.') . '</td>
                <td>' . $month['profit_margin'] . '%</td>
            </tr>';
        }
        
        $html .= '</table>
        
        <h2>📈 Peak Performance</h2>
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-label">Peak Day</div>
                <div class="metric-value">' . $m['peak_day'] . '</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Peak Revenue</div>
                <div class="metric-value">Rp ' . number_format($m['peak_revenue'], 0, ',', '.') . '</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Slow Day</div>
                <div class="metric-value">' . $m['slow_day'] . '</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Slow Revenue</div>
                <div class="metric-value">Rp ' . number_format($m['slow_revenue'], 0, ',', '.') . '</div>
            </div>
        </div>
        
        <h2>📊 Daily Performance (Last 10 Days)</h2>
        <table>
            <tr>
                <th>Date</th>
                <th>Day</th>
                <th>Peak</th>
                <th>Orders</th>
                <th>Revenue</th>
                <th>Profit</th>
            </tr>';
        
        $last10Days = array_slice($this->dailyData, -10);
        foreach ($last10Days as $day) {
            $html .= '<tr>
                <td>' . $day['date'] . '</td>
                <td>' . $day['day_of_week'] . '</td>
                <td>' . ($day['is_peak_day'] ? 'Yes' : 'No') . '</td>
                <td>' . $day['total_orders'] . '</td>
                <td>Rp ' . number_format($day['total_revenue'], 0, ',', '.') . '</td>
                <td>Rp ' . number_format($day['gross_profit'], 0, ',', '.') . '</td>
            </tr>';
        }
        
        $html .= '</table>
        
        <p style="margin-top: 30px; color: #666; font-size: 12px;">
            Report generated on ' . date('Y-m-d H:i:s') . ' by RESTAURANT_ERP Production Simulation System
        </p>
    </div>
</body>
</html>';
        
        return $html;
    }
    
    private function generateSummaryReport($dir) {
        $m = $this->totalMetrics;
        
        $summary = "RESTAURANT_ERP - 3-Month Production Simulation Summary
========================================================

SIMULATION PERIOD
-----------------
Start Date: {$m['start_date']}
End Date: {$m['end_date']}
Total Days: 90

TOTAL METRICS
-------------
Total Orders: " . number_format($m['total_orders']) . "
Total Revenue: Rp " . number_format($m['total_revenue'], 0, ',', '.') . "
Total Cost: Rp " . number_format($m['total_cost'], 0, ',', '.') . "
Gross Profit: Rp " . number_format($m['gross_profit'], 0, ',', '.') . "
Average Daily Orders: " . number_format($m['average_daily_orders'], 2) . "
Average Daily Revenue: Rp " . number_format($m['average_daily_revenue'], 0, ',', '.') . "
Average Order Value: Rp " . number_format($m['average_order_value'], 0, ',', '.') . "
Profit Margin: {$m['profit_margin']}%
Growth Rate: {$m['growth_rate']}%

PEAK PERFORMANCE
----------------
Peak Day: {$m['peak_day']}
Peak Revenue: Rp " . number_format($m['peak_revenue'], 0, ',', '.') . "
Slow Day: {$m['slow_day']}
Slow Revenue: Rp " . number_format($m['slow_revenue'], 0, ',', '.') . "

MONTHLY BREAKDOWN
----------------";
        
        foreach ($this->monthlyData as $month) {
            $summary .= "\n{$month['month_name']}:
  Days: {$month['days']}
  Orders: " . number_format($month['total_orders']) . "
  Revenue: Rp " . number_format($month['total_revenue'], 0, ',', '.') . "
  Profit: Rp " . number_format($month['gross_profit'], 0, ',', '.') . "
  Avg Daily Revenue: Rp " . number_format($month['average_daily_revenue'], 0, ',', '.') . "
  Profit Margin: {$month['profit_margin']}%";
        }
        
        $summary .= "\n\nGenerated: " . date('Y-m-d H:i:s');
        
        file_put_contents($dir . '/production-simulation-summary.txt', $summary);
    }
}

// Run simulation
$simulation = new ProductionSimulation();
$simulation->run();
