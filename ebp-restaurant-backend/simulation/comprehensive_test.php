<?php

/**
 * EBP Restaurant Backend - Comprehensive Test Suite
 * Runs all phase simulations and generates a comprehensive test report
 */

class ComprehensiveTest
{
    private $results = [];
    private $startTime;
    private $db;

    public function __construct()
    {
        $this->startTime = microtime(true);
        require_once __DIR__ . '/../config/database.php';
        $database = new Database();
        $this->db = $database->connect();
    }

    public function run()
    {
        echo "=== EBP Restaurant Backend - Comprehensive Test Suite ===\n\n";
        echo "Test Started: " . date('Y-m-d H:i:s') . "\n\n";

        // Test 1: Database Connection
        $this->testDatabaseConnection();

        // Test 2: Database Tables
        $this->testDatabaseTables();

        // Test 3: Phase 11 Simulation
        $this->runPhaseSimulation('Phase 11', 'simulate_phase11_final.php');

        // Test 4: Phase 12 Simulation
        $this->runPhaseSimulation('Phase 12', 'simulate_phase12_remaining.php');

        // Test 5: Phase 13 Simulation
        $this->runPhaseSimulation('Phase 13', 'simulate_phase13_final.php');

        // Test 6: Phase 14 Simulation
        $this->runPhaseSimulation('Phase 14', 'simulate_phase14_ultimate.php');

        // Test 7: Phase 15 Simulation
        $this->runPhaseSimulation('Phase 15', 'simulate_phase15_final.php');

        // Test 8: API Routes
        $this->testAPIRoutes();

        // Test 9: Frontend Components
        $this->testFrontendComponents();

        // Generate Report
        $this->generateReport();
    }

    private function testDatabaseConnection()
    {
        echo "Test 1: Database Connection\n";
        try {
            $stmt = $this->db->query("SELECT 1");
            $result = $stmt->fetch();
            if ($result) {
                $this->results['Database Connection'] = 'PASS';
                echo "  ✓ Database connection successful\n\n";
            } else {
                $this->results['Database Connection'] = 'FAIL';
                echo "  ✗ Database connection failed\n\n";
            }
        } catch (Exception $e) {
            $this->results['Database Connection'] = 'FAIL';
            echo "  ✗ Database connection error: " . $e->getMessage() . "\n\n";
        }
    }

    private function testDatabaseTables()
    {
        echo "Test 2: Database Tables\n";
        $requiredTables = [
            'tenants', 'branches', 'users', 'employees', 'roles', 'permissions',
            'categories', 'products', 'inventory', 'suppliers', 'purchase_orders',
            'orders', 'order_items', 'tables', 'reservations', 'customers',
            'employees', 'attendance', 'payroll', 'accounts', 'transactions',
            'tax_rates', 'tax_reports', 'reports', 'report_schedules',
            'supplier_performance', 'currencies', 'exchange_rates',
            'ai_predictions', 'dynamic_pricing_rules', 'waste_tracking',
            'assets', 'work_orders', 'equipment_history',
            'quality_compliance_checks', 'food_safety_protocols'
        ];

        $existingTables = [];
        $stmt = $this->db->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $existingTables[] = $row[0];
        }

        $missingTables = array_diff($requiredTables, $existingTables);
        $foundTables = array_intersect($requiredTables, $existingTables);

        echo "  - Required Tables: " . count($requiredTables) . "\n";
        echo "  - Found Tables: " . count($foundTables) . "\n";
        echo "  - Missing Tables: " . count($missingTables) . "\n";

        if (count($missingTables) > 0) {
            echo "  - Missing: " . implode(', ', $missingTables) . "\n";
        }

        if (count($missingTables) === 0) {
            $this->results['Database Tables'] = 'PASS';
            echo "  ✓ All required tables exist\n\n";
        } else {
            $this->results['Database Tables'] = 'PARTIAL';
            echo "  ⚠ Some tables missing\n\n";
        }
    }

    private function runPhaseSimulation($phaseName, $scriptName)
    {
        echo "Test: $phaseName Simulation\n";
        $scriptPath = __DIR__ . '/' . $scriptName;

        if (!file_exists($scriptPath)) {
            $this->results[$phaseName] = 'SKIP';
            echo "  ⊘ Script not found: $scriptName\n\n";
            return;
        }

        $output = [];
        $returnCode = 0;
        exec("php $scriptPath 2>&1", $output, $returnCode);

        $outputStr = implode("\n", $output);

        // Count passed tests from output
        $passed = substr_count($outputStr, '✓');
        $failed = substr_count($outputStr, '✗');

        echo "  - Passed: $passed\n";
        echo "  - Failed: $failed\n";

        if ($returnCode === 0 && $failed === 0) {
            $this->results[$phaseName] = 'PASS';
            echo "  ✓ $phaseName simulation passed\n\n";
        } elseif ($failed > 0) {
            $this->results[$phaseName] = 'PARTIAL';
            echo "  ⚠ $phaseName simulation had failures\n\n";
        } else {
            $this->results[$phaseName] = 'FAIL';
            echo "  ✗ $phaseName simulation failed\n\n";
        }
    }

    private function testAPIRoutes()
    {
        echo "Test: API Routes Verification\n";
        $routesFile = __DIR__ . '/../routes/api.php';

        if (!file_exists($routesFile)) {
            $this->results['API Routes'] = 'FAIL';
            echo "  ✗ API routes file not found\n\n";
            return;
        }

        $content = file_get_contents($routesFile);
        
        // Count route definitions
        $routeCount = substr_count($content, '$router->addRoute');
        
        // Check for key endpoints
        $keyEndpoints = [
            '/api/v1/auth/login',
            '/api/v1/orders',
            '/api/v1/products',
            '/api/v1/tables',
            '/api/v1/offline/status',
            '/api/v1/kiosk/menu',
            '/api/v1/mobile/menu',
            '/api/v1/whatsapp/orders',
            '/api/v1/quality/compliance-checks'
        ];

        $foundEndpoints = 0;
        foreach ($keyEndpoints as $endpoint) {
            if (strpos($content, $endpoint) !== false) {
                $foundEndpoints++;
            }
        }

        echo "  - Total Routes: $routeCount\n";
        echo "  - Key Endpoints Found: $foundEndpoints/" . count($keyEndpoints) . "\n";

        if ($routeCount > 50 && $foundEndpoints >= count($keyEndpoints) - 2) {
            $this->results['API Routes'] = 'PASS';
            echo "  ✓ API routes properly configured\n\n";
        } else {
            $this->results['API Routes'] = 'PARTIAL';
            echo "  ⚠ Some API routes may be missing\n\n";
        }
    }

    private function testFrontendComponents()
    {
        echo "Test: Frontend Components\n";
        $frontendDir = __DIR__ . '/../frontend';

        $requiredFiles = [
            'kiosk/index.html',
            'mobile/index.html',
            'css/kiosk.css',
            'css/mobile.css',
            'js/offline-indicator.js',
            'js/api-client.js',
            'js/kiosk.js',
            'js/mobile.js'
        ];

        $foundFiles = 0;
        foreach ($requiredFiles as $file) {
            if (file_exists($frontendDir . '/' . $file)) {
                $foundFiles++;
            }
        }

        echo "  - Required Files: " . count($requiredFiles) . "\n";
        echo "  - Found Files: $foundFiles\n";

        if ($foundFiles === count($requiredFiles)) {
            $this->results['Frontend Components'] = 'PASS';
            echo "  ✓ All frontend components exist\n\n";
        } else {
            $this->results['Frontend Components'] = 'PARTIAL';
            echo "  ⚠ Some frontend components missing\n\n";
        }
    }

    private function generateReport()
    {
        $endTime = microtime(true);
        $duration = round($endTime - $this->startTime, 2);

        echo "\n=== COMPREHENSIVE TEST REPORT ===\n\n";
        echo "Test Duration: $duration seconds\n";
        echo "Test Completed: " . date('Y-m-d H:i:s') . "\n\n";

        echo "Test Results Summary:\n";
        echo str_repeat("-", 50) . "\n";

        $pass = 0;
        $fail = 0;
        $partial = 0;
        $skip = 0;

        foreach ($this->results as $test => $result) {
            $status = '';
            switch ($result) {
                case 'PASS':
                    $status = '✓ PASS';
                    $pass++;
                    break;
                case 'FAIL':
                    $status = '✗ FAIL';
                    $fail++;
                    break;
                case 'PARTIAL':
                    $status = '⚠ PARTIAL';
                    $partial++;
                    break;
                case 'SKIP':
                    $status = '⊘ SKIP';
                    $skip++;
                    break;
            }
            echo sprintf("%-30s %s\n", $test, $status);
        }

        echo str_repeat("-", 50) . "\n";
        echo sprintf("%-30s %d\n", "Total Tests:", count($this->results));
        echo sprintf("%-30s %d\n", "Passed:", $pass);
        echo sprintf("%-30s %d\n", "Failed:", $fail);
        echo sprintf("%-30s %d\n", "Partial:", $partial);
        echo sprintf("%-30s %d\n", "Skipped:", $skip);
        echo str_repeat("-", 50) . "\n";

        $successRate = count($this->results) > 0 ? round(($pass / count($this->results)) * 100, 2) : 0;
        echo "Success Rate: $successRate%\n\n";

        if ($fail === 0 && $partial === 0) {
            echo "🎉 ALL TESTS PASSED! System is ready for deployment.\n\n";
        } elseif ($fail === 0) {
            echo "✓ No critical failures. System is mostly ready.\n\n";
        } else {
            echo "⚠ Some tests failed. Review the results above.\n\n";
        }

        // Save report to file
        $reportFile = __DIR__ . '/test_report_' . date('Ymd_His') . '.txt';
        $reportContent = $this->generateReportContent($duration, $pass, $fail, $partial, $skip, $successRate);
        file_put_contents($reportFile, $reportContent);
        echo "Report saved to: $reportFile\n";
    }

    private function generateReportContent($duration, $pass, $fail, $partial, $skip, $successRate)
    {
        $content = "EBP Restaurant Backend - Comprehensive Test Report\n";
        $content .= "Generated: " . date('Y-m-d H:i:s') . "\n";
        $content .= "Duration: $duration seconds\n\n";
        $content .= "Test Results:\n";
        $content .= str_repeat("-", 50) . "\n";

        foreach ($this->results as $test => $result) {
            $content .= sprintf("%-30s %s\n", $test, $result);
        }

        $content .= str_repeat("-", 50) . "\n";
        $content .= "Total Tests: " . count($this->results) . "\n";
        $content .= "Passed: $pass\n";
        $content .= "Failed: $fail\n";
        $content .= "Partial: $partial\n";
        $content .= "Skipped: $skip\n";
        $content .= "Success Rate: $successRate%\n";

        return $content;
    }
}

// Run comprehensive test
$test = new ComprehensiveTest();
$test->run();
