<?php

use PHPUnit\Framework\TestCase;
use Modules\Consumer\Services\ConsumerService;
use Modules\Consumer\Repositories\ConsumerRepository;
use Core\Database;

class ConsumerServiceTest extends TestCase
{
    private $service;
    private $repository;
    private $db;
    private $testTenantId = 1;
    private $testUserId = 1;

    protected function setUp(): void
    {
        $this->db = Database::getInstance();
        $this->repository = new ConsumerRepository();
        $this->service = new ConsumerService();
    }

    protected function tearDown(): void
    {
        // Cleanup test data if needed
    }

    public function testGetAll()
    {
        $consumers = $this->service->getAll($this->testTenantId, 10, 0);
        
        $this->assertIsArray($consumers);
    }

    public function testGetById()
    {
        // Create a test consumer first
        $consumerData = [
            'name' => 'Test Consumer',
            'phone' => '1234567890',
            'email' => 'test@example.com',
            'status' => 'ACTIVE'
        ];
        
        $createResult = $this->service->create($consumerData, $this->testTenantId, $this->testUserId);
        $consumerId = $createResult['consumer_id'];
        
        $consumer = $this->service->getById($consumerId, $this->testTenantId);
        
        $this->assertIsArray($consumer);
        $this->assertEquals($consumerId, $consumer['consumer_id']);
        
        // Cleanup
        $this->repository->delete($consumerId, $this->testTenantId);
    }

    public function testFindByPhone()
    {
        $phone = '1234567890';
        $consumer = $this->service->findByPhone($phone, $this->testTenantId);
        
        $this->assertIsArray($consumer);
    }

    public function testFindByEmail()
    {
        $email = 'test@example.com';
        $consumer = $this->service->findByEmail($email, $this->testTenantId);
        
        $this->assertIsArray($consumer);
    }

    public function testCreate()
    {
        $consumerData = [
            'name' => 'Test Consumer',
            'phone' => '9876543210',
            'email' => 'newtest@example.com',
            'status' => 'ACTIVE'
        ];
        
        $result = $this->service->create($consumerData, $this->testTenantId, $this->testUserId);
        
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('consumer_id', $result);
        
        // Cleanup
        $this->repository->delete($result['consumer_id'], $this->testTenantId);
    }

    public function testUpdate()
    {
        $consumerData = [
            'name' => 'Test Consumer',
            'phone' => '9876543210',
            'email' => 'newtest@example.com',
            'status' => 'ACTIVE'
        ];
        
        $createResult = $this->service->create($consumerData, $this->testTenantId, $this->testUserId);
        $consumerId = $createResult['consumer_id'];
        
        $updateData = [
            'name' => 'Updated Consumer',
            'email' => 'updated@example.com'
        ];
        
        $result = $this->service->update($consumerId, $updateData, $this->testTenantId, $this->testUserId);
        
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        
        // Cleanup
        $this->repository->delete($consumerId, $this->testTenantId);
    }

    public function testDelete()
    {
        $consumerData = [
            'name' => 'Test Consumer',
            'phone' => '9876543210',
            'email' => 'newtest@example.com',
            'status' => 'ACTIVE'
        ];
        
        $createResult = $this->service->create($consumerData, $this->testTenantId, $this->testUserId);
        $consumerId = $createResult['consumer_id'];
        
        $result = $this->service->delete($consumerId, $this->testTenantId, $this->testUserId);
        
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
    }

    public function testSearch()
    {
        $consumers = $this->service->search($this->testTenantId, 'test', 10);
        
        $this->assertIsArray($consumers);
    }

    public function testGetConsumerOrders()
    {
        // Create a test consumer first
        $consumerData = [
            'name' => 'Test Consumer',
            'phone' => '9876543210',
            'email' => 'newtest@example.com',
            'status' => 'ACTIVE'
        ];
        
        $createResult = $this->service->create($consumerData, $this->testTenantId, $this->testUserId);
        $consumerId = $createResult['consumer_id'];
        
        $orders = $this->service->getConsumerOrders($consumerId, $this->testTenantId, 10);
        
        $this->assertIsArray($orders);
        
        // Cleanup
        $this->repository->delete($consumerId, $this->testTenantId);
    }

    public function testGetLoyaltyPoints()
    {
        // Create a test consumer first
        $consumerData = [
            'name' => 'Test Consumer',
            'phone' => '9876543210',
            'email' => 'newtest@example.com',
            'status' => 'ACTIVE'
        ];
        
        $createResult = $this->service->create($consumerData, $this->testTenantId, $this->testUserId);
        $consumerId = $createResult['consumer_id'];
        
        $points = $this->service->getLoyaltyPoints($consumerId, $this->testTenantId);
        
        $this->assertIsArray($points);
        
        // Cleanup
        $this->repository->delete($consumerId, $this->testTenantId);
    }

    public function testGetTopConsumers()
    {
        $consumers = $this->service->getTopConsumers($this->testTenantId, 10);
        
        $this->assertIsArray($consumers);
    }
}
