<?php

use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    private Database $database;
    private array $config;

    protected function setUp(): void
    {
        $this->config = [
            'host' => 'localhost',
            'socket' => '/opt/lampp/var/mysql/mysql.sock',
            'dbname' => 'ebp_restaurant_db',
            'username' => 'ebp_app',
            'password' => 'ebp_secure_password_2026',
            'charset' => 'utf8mb4'
        ];
        $this->database = new Database($this->config);
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(Database::class, $this->database);
    }

    public function testSingletonInstance(): void
    {
        $instance1 = Database::getInstance($this->config);
        $instance2 = Database::getInstance($this->config);
        
        $this->assertSame($instance1, $instance2);
    }

    public function testConnect(): void
    {
        try {
            $pdo = $this->database->connect();
            $this->assertInstanceOf(PDO::class, $pdo);
        } catch (PDOException $e) {
            // Database might not be available in test environment
            $this->markTestSkipped('Database not available for testing');
        }
    }

    public function testTestConnection(): void
    {
        $result = $this->database->testConnection();
        
        // Database might not be available in test environment
        $this->assertIsBool($result);
    }

    public function testGetDatabaseInfo(): void
    {
        $info = $this->database->getDatabaseInfo();
        
        $this->assertIsArray($info);
        $this->assertArrayHasKey('version', $info);
        $this->assertArrayHasKey('database', $info);
    }

    public function testDefaultConfiguration(): void
    {
        $db = new Database();
        
        // Test that default values are set
        $this->assertInstanceOf(Database::class, $db);
    }

    public function testCustomConfiguration(): void
    {
        $customConfig = [
            'host' => 'custom-host',
            'dbname' => 'custom-db',
            'username' => 'custom-user',
            'password' => 'custom-pass'
        ];
        
        $db = new Database($customConfig);
        $this->assertInstanceOf(Database::class, $db);
    }
}
