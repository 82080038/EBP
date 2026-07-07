<?php

use PHPUnit\Framework\TestCase;
use Modules\Franchise\Repositories\FranchiseRepository;
use Core\Database;

class FranchiseRepositoryTest extends TestCase
{
    private $repository;
    private $db;
    private $testTenantId = 1;

    protected function setUp(): void
    {
        $this->db = Database::getInstance();
        $this->repository = new FranchiseRepository();
    }

    protected function tearDown(): void
    {
        // Cleanup test data if needed
    }

    public function testFindAll()
    {
        $franchisees = $this->repository->findAll($this->testTenantId, 10, 0);
        
        $this->assertIsArray($franchisees);
    }

    public function testFindById()
    {
        // Create a test franchisee first
        $franchiseeData = [
            'tenant_id' => $this->testTenantId,
            'franchisee_code' => 'FR-' . time(),
            'franchisee_name' => 'Test Franchisee',
            'contact_person' => 'John Doe',
            'phone' => '1234567890',
            'email' => 'test@example.com',
            'franchisee_status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $franchiseeId = $this->repository->create($franchiseeData);
        
        $franchisee = $this->repository->findById($franchiseeId, $this->testTenantId);
        
        $this->assertIsArray($franchisee);
        $this->assertEquals($franchiseeId, $franchisee['franchisee_id']);
        
        // Cleanup
        $this->repository->delete($franchiseeId, $this->testTenantId);
    }

    public function testCreate()
    {
        $franchiseeData = [
            'tenant_id' => $this->testTenantId,
            'franchisee_code' => 'FR-' . time(),
            'franchisee_name' => 'Test Franchisee',
            'contact_person' => 'John Doe',
            'phone' => '1234567890',
            'email' => 'test@example.com',
            'franchisee_status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $franchiseeId = $this->repository->create($franchiseeData);
        
        $this->assertIsNumeric($franchiseeId);
        $this->assertGreaterThan(0, $franchiseeId);
        
        // Cleanup
        $this->repository->delete($franchiseeId, $this->testTenantId);
    }

    public function testUpdate()
    {
        $franchiseeData = [
            'tenant_id' => $this->testTenantId,
            'franchisee_code' => 'FR-' . time(),
            'franchisee_name' => 'Test Franchisee',
            'contact_person' => 'John Doe',
            'phone' => '1234567890',
            'email' => 'test@example.com',
            'franchisee_status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $franchiseeId = $this->repository->create($franchiseeData);
        
        $updateData = [
            'franchisee_name' => 'Updated Franchisee',
            'email' => 'updated@example.com',
            'updated_by' => 1
        ];
        
        $result = $this->repository->update($franchiseeId, $updateData, $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify update
        $franchisee = $this->repository->findById($franchiseeId, $this->testTenantId);
        $this->assertEquals('Updated Franchisee', $franchisee['franchisee_name']);
        
        // Cleanup
        $this->repository->delete($franchiseeId, $this->testTenantId);
    }

    public function testUpdateStatus()
    {
        $franchiseeData = [
            'tenant_id' => $this->testTenantId,
            'franchisee_code' => 'FR-' . time(),
            'franchisee_name' => 'Test Franchisee',
            'contact_person' => 'John Doe',
            'phone' => '1234567890',
            'email' => 'test@example.com',
            'franchisee_status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $franchiseeId = $this->repository->create($franchiseeData);
        
        $result = $this->repository->updateStatus($franchiseeId, 'INACTIVE', $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify update
        $franchisee = $this->repository->findById($franchiseeId, $this->testTenantId);
        $this->assertEquals('INACTIVE', $franchisee['franchisee_status']);
        
        // Cleanup
        $this->repository->delete($franchiseeId, $this->testTenantId);
    }

    public function testDelete()
    {
        $franchiseeData = [
            'tenant_id' => $this->testTenantId,
            'franchisee_code' => 'FR-' . time(),
            'franchisee_name' => 'Test Franchisee',
            'contact_person' => 'John Doe',
            'phone' => '1234567890',
            'email' => 'test@example.com',
            'franchisee_status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $franchiseeId = $this->repository->create($franchiseeData);
        
        $result = $this->repository->delete($franchiseeId, $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify deletion
        $franchisee = $this->repository->findById($franchiseeId, $this->testTenantId);
        $this->assertFalse($franchisee);
    }

    public function testGetByStatus()
    {
        $franchisees = $this->repository->getByStatus($this->testTenantId, 'ACTIVE', 10);
        
        $this->assertIsArray($franchisees);
    }

    public function testGetActive()
    {
        $franchisees = $this->repository->getActive($this->testTenantId);
        
        $this->assertIsArray($franchisees);
    }

    public function testGetFranchiseAgreements()
    {
        // Create a test franchisee first
        $franchiseeData = [
            'tenant_id' => $this->testTenantId,
            'franchisee_code' => 'FR-' . time(),
            'franchisee_name' => 'Test Franchisee',
            'contact_person' => 'John Doe',
            'phone' => '1234567890',
            'email' => 'test@example.com',
            'franchisee_status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $franchiseeId = $this->repository->create($franchiseeData);
        
        $agreements = $this->repository->getFranchiseAgreements($franchiseeId, $this->testTenantId);
        
        $this->assertIsArray($agreements);
        
        // Cleanup
        $this->repository->delete($franchiseeId, $this->testTenantId);
    }

    public function testGetFranchisePerformance()
    {
        // Create a test franchisee first
        $franchiseeData = [
            'tenant_id' => $this->testTenantId,
            'franchisee_code' => 'FR-' . time(),
            'franchisee_name' => 'Test Franchisee',
            'contact_person' => 'John Doe',
            'phone' => '1234567890',
            'email' => 'test@example.com',
            'franchisee_status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $franchiseeId = $this->repository->create($franchiseeData);
        
        $performance = $this->repository->getFranchisePerformance($franchiseeId, $this->testTenantId);
        
        $this->assertIsArray($performance);
        
        // Cleanup
        $this->repository->delete($franchiseeId, $this->testTenantId);
    }

    public function testGetFranchiseRoyalties()
    {
        // Create a test franchisee first
        $franchiseeData = [
            'tenant_id' => $this->testTenantId,
            'franchisee_code' => 'FR-' . time(),
            'franchisee_name' => 'Test Franchisee',
            'contact_person' => 'John Doe',
            'phone' => '1234567890',
            'email' => 'test@example.com',
            'franchisee_status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $franchiseeId = $this->repository->create($franchiseeData);
        
        $royalties = $this->repository->getFranchiseRoyalties($franchiseeId, $this->testTenantId);
        
        $this->assertIsArray($royalties);
        
        // Cleanup
        $this->repository->delete($franchiseeId, $this->testTenantId);
    }
}
