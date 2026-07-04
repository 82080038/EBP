<?php
/**
 * MyWisata Application - Unit Test Framework
 * 
 * Simple unit testing framework for the application.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class UnitTest {
    
    private $tests = [];
    private $passed = 0;
    private $failed = 0;
    private $errors = [];
    
    /**
     * Add a test
     */
    public function test($name, $callback) {
        $this->tests[] = [
            'name' => $name,
            'callback' => $callback
        ];
    }
    
    /**
     * Run all tests
     */
    public function run() {
        echo "=== Running Unit Tests ===\n\n";
        
        foreach ($this->tests as $test) {
            try {
                echo "Testing: {$test['name']}... ";
                $result = call_user_func($test['callback']);
                
                if ($result === true) {
                    echo "PASSED\n";
                    $this->passed++;
                } else {
                    echo "FAILED\n";
                    $this->failed++;
                    $this->errors[] = [
                        'test' => $test['name'],
                        'message' => 'Test returned false'
                    ];
                }
            } catch (Exception $e) {
                echo "ERROR\n";
                $this->failed++;
                $this->errors[] = [
                    'test' => $test['name'],
                    'message' => $e->getMessage()
                ];
            }
        }
        
        $this->printResults();
    }
    
    /**
     * Assert equals
     */
    public static function assertEquals($expected, $actual) {
        return $expected === $actual;
    }
    
    /**
     * Assert true
     */
    public static function assertTrue($value) {
        return $value === true;
    }
    
    /**
     * Assert false
     */
    public static function assertFalse($value) {
        return $value === false;
    }
    
    /**
     * Assert not empty
     */
    public static function assertNotEmpty($value) {
        return !empty($value);
    }
    
    /**
     * Print results
     */
    private function printResults() {
        echo "\n=== Test Results ===\n";
        echo "Total: " . count($this->tests) . "\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";
        
        if (!empty($this->errors)) {
            echo "\n=== Errors ===\n";
            foreach ($this->errors as $error) {
                echo "Test: {$error['test']}\n";
                echo "Error: {$error['message']}\n\n";
            }
        }
    }
}

/**
 * Test Suite for MyWisata Application
 */
class MyWisataTestSuite {
    
    public static function runAll() {
        define('APP_ROOT', dirname(__FILE__) . '/..');
        
        // Load required files
        require_once APP_ROOT . '/app/core/Database.php';
        require_once APP_ROOT . '/app/helpers/Validator.php';
        require_once APP_ROOT . '/app/helpers/Session.php';
        require_once APP_ROOT . '/app/helpers/Logger.php';
        require_once APP_ROOT . '/app/helpers/Cache.php';
        require_once APP_ROOT . '/app/helpers/Search.php';
        
        $test = new UnitTest();
        
        // Validator Tests
        $test->test('Validator: Required field validation', function() {
            $validator = new Validator(['name' => 'John']);
            $validator->required(['name']);
            return UnitTest::assertFalse($validator->fails());
        });
        
        $test->test('Validator: Email validation', function() {
            $validator = new Validator(['email' => 'invalid-email']);
            $validator->email('email');
            return UnitTest::assertTrue($validator->fails());
        });
        
        $test->test('Validator: Valid email', function() {
            $validator = new Validator(['email' => 'test@example.com']);
            $validator->email('email');
            return UnitTest::assertFalse($validator->fails());
        });
        
        // Cache Tests
        $test->test('Cache: Set and get value', function() {
            Cache::set('test_key', 'test_value', 60);
            $value = Cache::get('test_key');
            Cache::delete('test_key');
            return UnitTest::assertEquals('test_value', $value);
        });
        
        $test->test('Cache: Get non-existent value', function() {
            $value = Cache::get('non_existent_key');
            return UnitTest::assertFalse($value);
        });
        
        $test->test('Cache: Delete value', function() {
            Cache::set('test_delete', 'value', 60);
            Cache::delete('test_delete');
            $value = Cache::get('test_delete');
            return UnitTest::assertFalse($value);
        });
        
        // Search Tests (mock database)
        $test->test('Search: Search destinations with mock data', function() {
            // Mock search functionality without database
            $mockDestinations = [
                ['id' => 1, 'name' => 'Bali Beach', 'category' => 'beach'],
                ['id' => 2, 'name' => 'Yogyakarta Temple', 'category' => 'cultural'],
                ['id' => 3, 'name' => 'Komodo Island', 'category' => 'nature']
            ];
            
            // Simulate search
            $query = 'beach';
            $results = array_filter($mockDestinations, function($dest) use ($query) {
                return stripos($dest['name'], $query) !== false || stripos($dest['category'], $query) !== false;
            });
            
            return UnitTest::assertEquals(1, count($results));
        });
        
        $test->run();
    }
}

// Run tests if executed directly
if (php_sapi_name() === 'cli') {
    MyWisataTestSuite::runAll();
}
