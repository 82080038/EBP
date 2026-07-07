<?php

use PHPUnit\Framework\TestCase;
use Modules\Consumer\Repositories\ConsumerRepository;
use Core\Database;

class ConsumerRepositoryTest extends TestCase
{
    private $repository;
    private $db;
    private $testTenantId = 1;

    protected function setUp(): void
    {
        $this->db = Database::getInstance();
        $this->repository = new ConsumerRepository();
    }

    protected function tearDown(): void
    {
        // Cleanup test data if needed
    }

    public function testFindAll()
    {
        $consumers = $this->repository->findAll($this->testTenantId, 10, 0);
        
        $this->assertIsArray($consumers);
    }

    public function testFindById()
    {
        // Create a test consumer first
        $consumerData = [
            'tenant_id' => $this->testTenantId,
            'name' => 'Test Consumer',
            'phone' => '1234567890',
            'email' => 'test@example.com',
            'status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $consumerId = $this->repository->create($consumerData);
        
        $consumer = $this->repository->findById($consumerId, $this->testTenantId);
        
        $this->assertIsArray($consumer);
        $this->assertEquals($consumerId, $consumer['consumer_id']);
        
        // Cleanup
        $this->repository->delete($consumerId, $this->testTenantId);
    }

    public function testFindByPhone()
    {
        $phone = '1234567890';
        $consumer = $this->repository->findByPhone($phone, $this->testTenantId);
        
        $this->assertIsArray($consumer);
    }

    public function testFindByEmail()
    {
        $email = 'test@example.com';
        $consumer = $this->repository->findByEmail($email, $this->testTenantId);
        
        $this->assertIsArray($consumer);
    }

    public function testCreate()
    {
        $consumerData = [
            'tenant_id' => $this->testTenantId,
            'name' => 'Test Consumer',
            'phone' => '9876543210',
            'email' => 'newtest@example.com',
            'status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $consumerId = $this->repository->create($consumerData);
        
        $this->assertIsNumeric($consumerId);
        $this->assertGreaterThan(0, $consumerId);
        
        // Cleanup
        $this->repository->delete($consumerId, $this->testTenantId);
    }

    public function testUpdate()
    {
        $consumerData = [
            'tenant_id' => $this->testTenantId,
            'name' => 'Test Consumer',
            'phone' => '9876543210',
            'email' => 'newtest@example.com',
            'status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $consumerId = $this->repository->create($consumerData);
        
        $updateData = [
            'name' => 'Updated Consumer',
            'email' => 'updated@example.com',
            'updated_by' => 1
        ];
        
        $result = $this->repository->update($consumerId, $updateData, $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify update
        $consumer = $this->repository->findById($consumerId, $this->testTenantId);
        $this->assertEquals('Updated Consumer', $consumer['name']);
        
        // Cleanup
        $this->repository->delete($consumerId, $this->testTenantId);
    }

    public function testDelete()
    {
        $consumerData = [
            'tenant_id' => $this->testTenantId,
            'name' => 'Test Consumer',
            'phone' => '9876543210',
            'email' => 'newtest@example.com',
            'status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $consumerId = $this->repository->create($consumerData);
        
        $result = $this->repository->delete($consumerId, $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify deletion
        $consumer = $this->repository->findById($consumerId, $this->testTenantId);
        $this->assertFalse($consumer);
    }

    public function testSearch()
    {
        $consumers = $this->repository->search($this->testTenantId, 'test', 10);
        
        $this->assertIsArray($consumers);
    }

    public function testGetConsumerOrders()
    {
        // Create a test consumer first
        $consumerData = [
            'tenant_id' => $this->testTenantId,
            'name' => 'Test Consumer',
            'phone' => '9876543210',
            'email' => 'newtest@example.com',
            'status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $consumerId = $this->repository->create($consumerData);
        
        $orders = $this->repository->getConsumerOrders($consumerId, $this->testTenantId, 10);
        
        $this->assertIsArray($orders);
        
        // Cleanup
        $this->repository->delete($consumerId, $this->testTenantId);
    }

    public function testGetLoyaltyPoints()
    {
        // Create a test consumer first
        $consumerData = [
            'tenant_id' => $this->testTenantId,
            'name' => 'Test Consumer',
            'phone' => '9876543210',
            'email' => 'newtest@example.com',
            'status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $consumerId = $this->repository->create($consumerData);
        
        $points = $this->repository->getLoyaltyPoints($consumerId, $this->testTenantId);
        
        $this->assertIsArray($points);
        
        // Cleanup
        $this->repository->delete($consumerId, $this->testTenantId);
    }

    public function testGetTopConsumers()
    {
        $consumers = $this->repository->getTopConsumers($this->testTenantId, 10);
        
        $this->assertIsArray($consumers);
    }
}
