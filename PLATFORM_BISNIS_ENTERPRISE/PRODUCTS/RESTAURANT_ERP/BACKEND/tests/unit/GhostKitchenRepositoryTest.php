<?php

use PHPUnit\Framework\TestCase;
use Modules\GhostKitchen\Repositories\GhostKitchenRepository;
use Core\Database;

class GhostKitchenRepositoryTest extends TestCase
{
    private $repository;
    private $db;
    private $testTenantId = 1;

    protected function setUp(): void
    {
        $this->db = Database::getInstance();
        $this->repository = new GhostKitchenRepository();
    }

    protected function tearDown(): void
    {
        // Cleanup test data if needed
    }

    public function testFindAll()
    {
        $brands = $this->repository->findAll($this->testTenantId, 10, 0);
        
        $this->assertIsArray($brands);
    }

    public function testFindById()
    {
        // Create a test virtual brand first
        $brandData = [
            'tenant_id' => $this->testTenantId,
            'brand_name' => 'Test Virtual Brand',
            'brand_code' => 'VB-' . time(),
            'cuisine_type' => 'Asian',
            'brand_status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $brandId = $this->repository->create($brandData);
        
        $brand = $this->repository->findById($brandId, $this->testTenantId);
        
        $this->assertIsArray($brand);
        $this->assertEquals($brandId, $brand['brand_id']);
        
        // Cleanup
        $this->repository->delete($brandId, $this->testTenantId);
    }

    public function testCreate()
    {
        $brandData = [
            'tenant_id' => $this->testTenantId,
            'brand_name' => 'Test Virtual Brand',
            'brand_code' => 'VB-' . time(),
            'cuisine_type' => 'Asian',
            'brand_status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $brandId = $this->repository->create($brandData);
        
        $this->assertIsNumeric($brandId);
        $this->assertGreaterThan(0, $brandId);
        
        // Cleanup
        $this->repository->delete($brandId, $this->testTenantId);
    }

    public function testUpdate()
    {
        $brandData = [
            'tenant_id' => $this->testTenantId,
            'brand_name' => 'Test Virtual Brand',
            'brand_code' => 'VB-' . time(),
            'cuisine_type' => 'Asian',
            'brand_status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $brandId = $this->repository->create($brandData);
        
        $updateData = [
            'brand_name' => 'Updated Virtual Brand',
            'cuisine_type' => 'Italian',
            'updated_by' => 1
        ];
        
        $result = $this->repository->update($brandId, $updateData, $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify update
        $brand = $this->repository->findById($brandId, $this->testTenantId);
        $this->assertEquals('Updated Virtual Brand', $brand['brand_name']);
        
        // Cleanup
        $this->repository->delete($brandId, $this->testTenantId);
    }

    public function testUpdateStatus()
    {
        $brandData = [
            'tenant_id' => $this->testTenantId,
            'brand_name' => 'Test Virtual Brand',
            'brand_code' => 'VB-' . time(),
            'cuisine_type' => 'Asian',
            'brand_status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $brandId = $this->repository->create($brandData);
        
        $result = $this->repository->updateStatus($brandId, 'INACTIVE', $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify update
        $brand = $this->repository->findById($brandId, $this->testTenantId);
        $this->assertEquals('INACTIVE', $brand['brand_status']);
        
        // Cleanup
        $this->repository->delete($brandId, $this->testTenantId);
    }

    public function testDelete()
    {
        $brandData = [
            'tenant_id' => $this->testTenantId,
            'brand_name' => 'Test Virtual Brand',
            'brand_code' => 'VB-' . time(),
            'cuisine_type' => 'Asian',
            'brand_status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $brandId = $this->repository->create($brandData);
        
        $result = $this->repository->delete($brandId, $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify deletion
        $brand = $this->repository->findById($brandId, $this->testTenantId);
        $this->assertFalse($brand);
    }

    public function testGetByStatus()
    {
        $brands = $this->repository->getByStatus($this->testTenantId, 'ACTIVE', 10);
        
        $this->assertIsArray($brands);
    }

    public function testGetByType()
    {
        $brands = $this->repository->getByType($this->testTenantId, 'Asian', 10);
        
        $this->assertIsArray($brands);
    }

    public function testGetActive()
    {
        $brands = $this->repository->getActive($this->testTenantId);
        
        $this->assertIsArray($brands);
    }

    public function testGetBrandMenuItems()
    {
        // Create a test virtual brand first
        $brandData = [
            'tenant_id' => $this->testTenantId,
            'brand_name' => 'Test Virtual Brand',
            'brand_code' => 'VB-' . time(),
            'cuisine_type' => 'Asian',
            'brand_status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $brandId = $this->repository->create($brandData);
        
        $menuItems = $this->repository->getBrandMenuItems($brandId, $this->testTenantId);
        
        $this->assertIsArray($menuItems);
        
        // Cleanup
        $this->repository->delete($brandId, $this->testTenantId);
    }

    public function testGetDeliveryPlatforms()
    {
        $platforms = $this->repository->getDeliveryPlatforms($this->testTenantId);
        
        $this->assertIsArray($platforms);
    }

    public function testGetBrandDeliveryPlatforms()
    {
        // Create a test virtual brand first
        $brandData = [
            'tenant_id' => $this->testTenantId,
            'brand_name' => 'Test Virtual Brand',
            'brand_code' => 'VB-' . time(),
            'cuisine_type' => 'Asian',
            'brand_status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $brandId = $this->repository->create($brandData);
        
        $platforms = $this->repository->getBrandDeliveryPlatforms($brandId, $this->testTenantId);
        
        $this->assertIsArray($platforms);
        
        // Cleanup
        $this->repository->delete($brandId, $this->testTenantId);
    }
}
