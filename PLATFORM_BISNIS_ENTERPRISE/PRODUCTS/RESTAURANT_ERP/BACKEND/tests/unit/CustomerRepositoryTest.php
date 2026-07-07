<?php

use PHPUnit\Framework\TestCase;
use Modules\Customer\Repositories\CustomerRepository;
use Core\Database;

class CustomerRepositoryTest extends TestCase
{
    private $repository;
    private $db;
    private $testTenantId = 1;

    protected function setUp(): void
    {
        $this->db = Database::getInstance();
        $this->repository = new CustomerRepository();
    }

    protected function tearDown(): void
    {
        // Cleanup test data if needed
    }

    public function testFindAll()
    {
        $customers = $this->repository->findAll($this->testTenantId, 10, 0);
        
        $this->assertIsArray($customers);
    }

    public function testFindById()
    {
        // Create a test customer first
        $customerData = [
            'tenant_id' => $this->testTenantId,
            'name' => 'Test Customer',
            'phone' => '1234567890',
            'email' => 'test@example.com',
            'status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $customerId = $this->repository->create($customerData);
        
        $customer = $this->repository->findById($customerId, $this->testTenantId);
        
        $this->assertIsArray($customer);
        $this->assertEquals($customerId, $customer['customer_id']);
        
        // Cleanup
        $this->repository->delete($customerId, $this->testTenantId);
    }

    public function testFindByPhone()
    {
        $phone = '1234567890';
        $customer = $this->repository->findByPhone($phone, $this->testTenantId);
        
        $this->assertIsArray($customer);
    }

    public function testFindByEmail()
    {
        $email = 'test@example.com';
        $customer = $this->repository->findByEmail($email, $this->testTenantId);
        
        $this->assertIsArray($customer);
    }

    public function testCreate()
    {
        $customerData = [
            'tenant_id' => $this->testTenantId,
            'name' => 'Test Customer',
            'phone' => '9876543210',
            'email' => 'newtest@example.com',
            'status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $customerId = $this->repository->create($customerData);
        
        $this->assertIsNumeric($customerId);
        $this->assertGreaterThan(0, $customerId);
        
        // Cleanup
        $this->repository->delete($customerId, $this->testTenantId);
    }

    public function testUpdate()
    {
        $customerData = [
            'tenant_id' => $this->testTenantId,
            'name' => 'Test Customer',
            'phone' => '9876543210',
            'email' => 'newtest@example.com',
            'status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $customerId = $this->repository->create($customerData);
        
        $updateData = [
            'name' => 'Updated Customer',
            'email' => 'updated@example.com',
            'updated_by' => 1
        ];
        
        $result = $this->repository->update($customerId, $updateData, $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify update
        $customer = $this->repository->findById($customerId, $this->testTenantId);
        $this->assertEquals('Updated Customer', $customer['name']);
        
        // Cleanup
        $this->repository->delete($customerId, $this->testTenantId);
    }

    public function testDelete()
    {
        $customerData = [
            'tenant_id' => $this->testTenantId,
            'name' => 'Test Customer',
            'phone' => '9876543210',
            'email' => 'newtest@example.com',
            'status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $customerId = $this->repository->create($customerData);
        
        $result = $this->repository->delete($customerId, $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify deletion
        $customer = $this->repository->findById($customerId, $this->testTenantId);
        $this->assertFalse($customer);
    }

    public function testSearch()
    {
        $customers = $this->repository->search($this->testTenantId, 'test', 10);
        
        $this->assertIsArray($customers);
    }

    public function testGetByStatus()
    {
        $customers = $this->repository->getByStatus($this->testTenantId, 'ACTIVE', 10);
        
        $this->assertIsArray($customers);
    }

    public function testGetCustomerVisits()
    {
        // Create a test customer first
        $customerData = [
            'tenant_id' => $this->testTenantId,
            'name' => 'Test Customer',
            'phone' => '9876543210',
            'email' => 'newtest@example.com',
            'status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $customerId = $this->repository->create($customerData);
        
        $visits = $this->repository->getCustomerVisits($customerId, $this->testTenantId, 10);
        
        $this->assertIsArray($visits);
        
        // Cleanup
        $this->repository->delete($customerId, $this->testTenantId);
    }

    public function testGetCustomerPreferences()
    {
        // Create a test customer first
        $customerData = [
            'tenant_id' => $this->testTenantId,
            'name' => 'Test Customer',
            'phone' => '9876543210',
            'email' => 'newtest@example.com',
            'status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $customerId = $this->repository->create($customerData);
        
        $preferences = $this->repository->getCustomerPreferences($customerId, $this->testTenantId);
        
        $this->assertIsArray($preferences);
        
        // Cleanup
        $this->repository->delete($customerId, $this->testTenantId);
    }

    public function testGetCustomerTags()
    {
        // Create a test customer first
        $customerData = [
            'tenant_id' => $this->testTenantId,
            'name' => 'Test Customer',
            'phone' => '9876543210',
            'email' => 'newtest@example.com',
            'status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $customerId = $this->repository->create($customerData);
        
        $tags = $this->repository->getCustomerTags($customerId, $this->testTenantId);
        
        $this->assertIsArray($tags);
        
        // Cleanup
        $this->repository->delete($customerId, $this->testTenantId);
    }

    public function testCountByStatus()
    {
        $count = $this->repository->countByStatus($this->testTenantId, 'ACTIVE');
        
        $this->assertIsNumeric($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }
}
