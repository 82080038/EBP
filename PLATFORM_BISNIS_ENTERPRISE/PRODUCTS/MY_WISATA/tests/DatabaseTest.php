<?php
/**
 * MyWisata Application - Database Tests
 * 
 * Tests for database connections and queries.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class DatabaseTest {
    
    private $db;
    private $testResults = [];
    
    public function __construct() {
        define('APP_ROOT', dirname(__FILE__) . '/..');
        require_once APP_ROOT . '/app/core/Database.php';
        require_once APP_ROOT . '/app/config/database.php';
        
        $this->db = Database::getInstance();
    }
    
    /**
     * Run all database tests
     */
    public function runAll() {
        echo "=== Running Database Tests ===\n\n";
        
        $this->testConnection();
        $this->testUserTable();
        $this->testDestinationTable();
        $this->testBookingTable();
        $this->testTransactionTable();
        $this->testTicketTable();
        $this->testTourGuideTable();
        $this->testHotelTable();
        $this->testRestaurantTable();
        $this->testEventTable();
        $this->testFavoriteTable();
        
        $this->printResults();
    }
    
    /**
     * Test database connection
     */
    private function testConnection() {
        echo "Testing Database Connection...\n";
        
        try {
            $result = $this->db->query("SELECT 1")->fetch();
            if ($result && $result[1] == 1) {
                echo "  ✓ Database connection successful\n";
                $this->testResults['Database Connection'] = true;
            } else {
                echo "  ✗ Database query failed\n";
                $this->testResults['Database Connection'] = false;
            }
        } catch (Exception $e) {
            echo "  ✗ Database connection failed: " . $e->getMessage() . "\n";
            $this->testResults['Database Connection'] = false;
        }
        
        echo "\n";
    }
    
    /**
     * Test user table
     */
    private function testUserTable() {
        echo "Testing User Table...\n";
        
        try {
            $result = $this->db->query("SELECT COUNT(*) as count FROM users")->fetch();
            if ($result && $result['count'] > 0) {
                echo "  ✓ User table exists and has data ({$result['count']} records)\n";
                $this->testResults['User Table'] = true;
            } else {
                echo "  ✗ User table is empty\n";
                $this->testResults['User Table'] = false;
            }
        } catch (Exception $e) {
            echo "  ✗ User table query failed: " . $e->getMessage() . "\n";
            $this->testResults['User Table'] = false;
        }
        
        echo "\n";
    }
    
    /**
     * Test destination table
     */
    private function testDestinationTable() {
        echo "Testing Destination Table...\n";
        
        try {
            $result = $this->db->query("SELECT COUNT(*) as count FROM destinations")->fetch();
            if ($result && $result['count'] > 0) {
                echo "  ✓ Destination table exists and has data ({$result['count']} records)\n";
                $this->testResults['Destination Table'] = true;
            } else {
                echo "  ✗ Destination table is empty\n";
                $this->testResults['Destination Table'] = false;
            }
        } catch (Exception $e) {
            echo "  ✗ Destination table query failed: " . $e->getMessage() . "\n";
            $this->testResults['Destination Table'] = false;
        }
        
        echo "\n";
    }
    
    /**
     * Test booking table
     */
    private function testBookingTable() {
        echo "Testing Booking Table...\n";
        
        try {
            $result = $this->db->query("SELECT COUNT(*) as count FROM bookings")->fetch();
            echo "  ✓ Booking table exists ({$result['count']} records)\n";
            $this->testResults['Booking Table'] = true;
        } catch (Exception $e) {
            echo "  ✗ Booking table query failed: " . $e->getMessage() . "\n";
            $this->testResults['Booking Table'] = false;
        }
        
        echo "\n";
    }
    
    /**
     * Test transaction table
     */
    private function testTransactionTable() {
        echo "Testing Transaction Table...\n";
        
        try {
            $result = $this->db->query("SELECT COUNT(*) as count FROM transactions")->fetch();
            echo "  ✓ Transaction table exists ({$result['count']} records)\n";
            $this->testResults['Transaction Table'] = true;
        } catch (Exception $e) {
            echo "  ✗ Transaction table query failed: " . $e->getMessage() . "\n";
            $this->testResults['Transaction Table'] = false;
        }
        
        echo "\n";
    }
    
    /**
     * Test ticket table
     */
    private function testTicketTable() {
        echo "Testing Ticket Table...\n";
        
        try {
            $result = $this->db->query("SELECT COUNT(*) as count FROM ticket_orders")->fetch();
            echo "  ✓ Ticket table exists ({$result['count']} records)\n";
            $this->testResults['Ticket Table'] = true;
        } catch (Exception $e) {
            echo "  ✗ Ticket table query failed: " . $e->getMessage() . "\n";
            $this->testResults['Ticket Table'] = false;
        }
        
        echo "\n";
    }
    
    /**
     * Test tour guide table
     */
    private function testTourGuideTable() {
        echo "Testing Tour Guide Table...\n";
        
        try {
            $result = $this->db->query("SELECT COUNT(*) as count FROM tour_guides")->fetch();
            if ($result && $result['count'] > 0) {
                echo "  ✓ Tour guide table exists and has data ({$result['count']} records)\n";
                $this->testResults['Tour Guide Table'] = true;
            } else {
                echo "  ✗ Tour guide table is empty\n";
                $this->testResults['Tour Guide Table'] = false;
            }
        } catch (Exception $e) {
            echo "  ✗ Tour guide table query failed: " . $e->getMessage() . "\n";
            $this->testResults['Tour Guide Table'] = false;
        }
        
        echo "\n";
    }
    
    /**
     * Test hotel table
     */
    private function testHotelTable() {
        echo "Testing Hotel Table...\n";
        
        try {
            $result = $this->db->query("SELECT COUNT(*) as count FROM hotels")->fetch();
            echo "  ✓ Hotel table exists ({$result['count']} records)\n";
            $this->testResults['Hotel Table'] = true;
        } catch (Exception $e) {
            echo "  ✗ Hotel table query failed: " . $e->getMessage() . "\n";
            $this->testResults['Hotel Table'] = false;
        }
        
        echo "\n";
    }
    
    /**
     * Test restaurant table
     */
    private function testRestaurantTable() {
        echo "Testing Restaurant Table...\n";
        
        try {
            $result = $this->db->query("SELECT COUNT(*) as count FROM restaurants")->fetch();
            echo "  ✓ Restaurant table exists ({$result['count']} records)\n";
            $this->testResults['Restaurant Table'] = true;
        } catch (Exception $e) {
            echo "  ✗ Restaurant table query failed: " . $e->getMessage() . "\n";
            $this->testResults['Restaurant Table'] = false;
        }
        
        echo "\n";
    }
    
    /**
     * Test event table
     */
    private function testEventTable() {
        echo "Testing Event Table...\n";
        
        try {
            $result = $this->db->query("SELECT COUNT(*) as count FROM events")->fetch();
            echo "  ✓ Event table exists ({$result['count']} records)\n";
            $this->testResults['Event Table'] = true;
        } catch (Exception $e) {
            echo "  ✗ Event table query failed: " . $e->getMessage() . "\n";
            $this->testResults['Event Table'] = false;
        }
        
        echo "\n";
    }
    
    /**
     * Test favorite table
     */
    private function testFavoriteTable() {
        echo "Testing Favorite Table...\n";
        
        try {
            $result = $this->db->query("SELECT COUNT(*) as count FROM user_favorites")->fetch();
            echo "  ✓ Favorite table exists ({$result['count']} records)\n";
            $this->testResults['Favorite Table'] = true;
        } catch (Exception $e) {
            echo "  ✗ Favorite table query failed: " . $e->getMessage() . "\n";
            $this->testResults['Favorite Table'] = false;
        }
        
        echo "\n";
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
    $test = new DatabaseTest();
    $test->runAll();
}
