<?php
/**
 * MyWisata Application - API Tests
 * 
 * Tests for API endpoints.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class APITest {
    
    private $baseUrl;
    private $testResults = [];
    
    public function __construct($baseUrl = 'http://localhost/mywisata') {
        $this->baseUrl = $baseUrl;
    }
    
    /**
     * Run all API tests
     */
    public function runAll() {
        echo "=== Running API Tests ===\n\n";
        
        $this->testDestinationsAPI();
        $this->testTourGuidesAPI();
        $this->testHotelsAPI();
        $this->testRestaurantsAPI();
        $this->testEventsAPI();
        $this->testSearchAPI();
        
        $this->printResults();
    }
    
    /**
     * Test Destinations API
     */
    private function testDestinationsAPI() {
        echo "Testing Destinations API...\n";
        
        $url = $this->baseUrl . '/api/getDestinations';
        $result = $this->makeRequest($url);
        
        if ($result && isset($result['status']) && $result['status'] === 'success') {
            echo "  ✓ Destinations API working\n";
            $this->testResults['Destinations API'] = true;
        } else {
            echo "  ✗ Destinations API failed\n";
            $this->testResults['Destinations API'] = false;
        }
        
        echo "\n";
    }
    
    /**
     * Test Tour Guides API
     */
    private function testTourGuidesAPI() {
        echo "Testing Tour Guides API...\n";
        
        $url = $this->baseUrl . '/api/getTourGuides';
        $result = $this->makeRequest($url);
        
        if ($result && isset($result['status']) && $result['status'] === 'success') {
            echo "  ✓ Tour Guides API working\n";
            $this->testResults['Tour Guides API'] = true;
        } else {
            echo "  ✗ Tour Guides API failed\n";
            $this->testResults['Tour Guides API'] = false;
        }
        
        echo "\n";
    }
    
    /**
     * Test Hotels API
     */
    private function testHotelsAPI() {
        echo "Testing Hotels API...\n";
        
        $url = $this->baseUrl . '/api/getHotels';
        $result = $this->makeRequest($url);
        
        if ($result && isset($result['status']) && $result['status'] === 'success') {
            echo "  ✓ Hotels API working\n";
            $this->testResults['Hotels API'] = true;
        } else {
            echo "  ✗ Hotels API failed\n";
            $this->testResults['Hotels API'] = false;
        }
        
        echo "\n";
    }
    
    /**
     * Test Restaurants API
     */
    private function testRestaurantsAPI() {
        echo "Testing Restaurants API...\n";
        
        $url = $this->baseUrl . '/api/getRestaurants';
        $result = $this->makeRequest($url);
        
        if ($result && isset($result['status']) && $result['status'] === 'success') {
            echo "  ✓ Restaurants API working\n";
            $this->testResults['Restaurants API'] = true;
        } else {
            echo "  ✗ Restaurants API failed\n";
            $this->testResults['Restaurants API'] = false;
        }
        
        echo "\n";
    }
    
    /**
     * Test Events API
     */
    private function testEventsAPI() {
        echo "Testing Events API...\n";
        
        $url = $this->baseUrl . '/api/getEvents';
        $result = $this->makeRequest($url);
        
        if ($result && isset($result['status']) && $result['status'] === 'success') {
            echo "  ✓ Events API working\n";
            $this->testResults['Events API'] = true;
        } else {
            echo "  ✗ Events API failed\n";
            $this->testResults['Events API'] = false;
        }
        
        echo "\n";
    }
    
    /**
     * Test Search API
     */
    private function testSearchAPI() {
        echo "Testing Search API...\n";
        
        $url = $this->baseUrl . '/api/search?q=Jakarta';
        $result = $this->makeRequest($url);
        
        if ($result && isset($result['status']) && $result['status'] === 'success') {
            echo "  ✓ Search API working\n";
            $this->testResults['Search API'] = true;
        } else {
            echo "  ✗ Search API failed\n";
            $this->testResults['Search API'] = false;
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
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            return json_decode($response, true);
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
        
        foreach ($this->testResults as $test => $result) {
            if ($result === true) {
                echo "✓ {$test}: PASSED\n";
                $passed++;
            } else {
                echo "✗ {$test}: FAILED\n";
                $failed++;
            }
        }
        
        echo "\n";
        echo "Total: " . count($this->testResults) . " tests\n";
        echo "Passed: {$passed}\n";
        echo "Failed: {$failed}\n";
    }
}

// Run tests if executed directly
if (php_sapi_name() === 'cli') {
    $test = new APITest();
    $test->runAll();
}
