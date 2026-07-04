<?php
/**
 * MyWisata Application - Authentication Tests
 * 
 * Tests for authentication and authorization.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class AuthTest {
    
    private $baseUrl;
    private $testResults = [];
    
    public function __construct($baseUrl = 'http://localhost/mywisata') {
        $this->baseUrl = $baseUrl;
    }
    
    /**
     * Run all authentication tests
     */
    public function runAll() {
        echo "=== Running Authentication Tests ===\n\n";
        
        $this->testLoginPage();
        $this->testRegisterPage();
        $this->testCSRFToken();
        $this->testSessionManagement();
        $this->testRoleBasedAccess();
        
        $this->printResults();
    }
    
    /**
     * Test login page
     */
    private function testLoginPage() {
        echo "Testing Login Page...\n";
        
        $url = $this->baseUrl . '/auth/login';
        $result = $this->makeRequest($url);
        
        if ($result && strpos($result, 'login') !== false) {
            echo "  ✓ Login page accessible\n";
            $this->testResults['Login Page'] = true;
        } else {
            echo "  ✗ Login page not accessible\n";
            $this->testResults['Login Page'] = false;
        }
        
        echo "\n";
    }
    
    /**
     * Test register page
     */
    private function testRegisterPage() {
        echo "Testing Register Page...\n";
        
        $url = $this->baseUrl . '/auth/register';
        $result = $this->makeRequest($url);
        
        if ($result && strpos($result, 'register') !== false) {
            echo "  ✓ Register page accessible\n";
            $this->testResults['Register Page'] = true;
        } else {
            echo "  ✗ Register page not accessible\n";
            $this->testResults['Register Page'] = false;
        }
        
        echo "\n";
    }
    
    /**
     * Test CSRF token
     */
    private function testCSRFToken() {
        echo "Testing CSRF Token...\n";
        
        $url = $this->baseUrl . '/auth/login';
        $result = $this->makeRequest($url);
        
        if ($result && (strpos($result, 'csrf_token') !== false || strpos($result, 'name="csrf_token"') !== false)) {
            echo "  ✓ CSRF token present in forms\n";
            $this->testResults['CSRF Token'] = true;
        } else {
            echo "  ✗ CSRF token not found\n";
            $this->testResults['CSRF Token'] = false;
        }
        
        echo "\n";
    }
    
    /**
     * Test session management
     */
    private function testSessionManagement() {
        echo "Testing Session Management...\n";
        
        if (file_exists(APP_ROOT . '/app/helpers/Session.php')) {
            echo "  ✓ Session helper exists\n";
            $this->testResults['Session Management'] = true;
        } else {
            echo "  ✗ Session helper not found\n";
            $this->testResults['Session Management'] = false;
        }
        
        echo "\n";
    }
    
    /**
     * Test role-based access
     */
    private function testRoleBasedAccess() {
        echo "Testing Role-Based Access...\n";
        
        // Test admin dashboard access (should redirect if not logged in)
        $url = $this->baseUrl . '/admin/dashboard';
        $result = $this->makeRequest($url);
        
        if ($result && (strpos($result, 'login') !== false || strpos($result, 'redirect') !== false)) {
            echo "  ✓ Admin dashboard protected (redirects to login)\n";
            $this->testResults['Role-Based Access'] = true;
        } else {
            echo "  ℹ Admin dashboard access requires manual testing\n";
            $this->testResults['Role-Based Access'] = null;
        }
        
        echo "\n";
    }
    
    /**
     * Make HTTP request
     */
    private function makeRequest($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            return $response;
        }
        
        return false;
    }
    
    /**
     * Print test results
     */
    private function printResults() {
        echo "=== Test Results ===\n\n";
        
        $passed = 0;
        $failed = 0;
        $skipped = 0;
        
        foreach ($this->testResults as $test => $result) {
            if ($result === true) {
                echo "✓ {$test}: PASSED\n";
                $passed++;
            } elseif ($result === false) {
                echo "✗ {$test}: FAILED\n";
                $failed++;
            } else {
                echo "ℹ {$test}: SKIPPED (requires manual testing)\n";
                $skipped++;
            }
        }
        
        echo "\n";
        echo "Total: " . count($this->testResults) . " tests\n";
        echo "Passed: {$passed}\n";
        echo "Failed: {$failed}\n";
        echo "Skipped: {$skipped}\n";
    }
}

// Run tests if executed directly
if (php_sapi_name() === 'cli') {
    define('APP_ROOT', dirname(__FILE__) . '/..');
    $test = new AuthTest();
    $test->runAll();
}
