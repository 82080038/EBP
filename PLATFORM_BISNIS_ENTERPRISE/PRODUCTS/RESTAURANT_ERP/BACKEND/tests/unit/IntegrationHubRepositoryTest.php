<?php

use PHPUnit\Framework\TestCase;
use Modules\IntegrationHub\Repositories\IntegrationHubRepository;
use Core\Database;

class IntegrationHubRepositoryTest extends TestCase
{
    private $repository;
    private $db;
    private $testTenantId = 1;

    protected function setUp(): void
    {
        $this->db = Database::getInstance();
        $this->repository = new IntegrationHubRepository();
    }

    protected function tearDown(): void
    {
        // Cleanup test data if needed
    }

    public function testFindAll()
    {
        $integrations = $this->repository->findAll($this->testTenantId, 10, 0);
        
        $this->assertIsArray($integrations);
    }

    public function testFindById()
    {
        // Create a test integration first
        $integrationData = [
            'tenant_id' => $this->testTenantId,
            'integration_name' => 'Test Integration',
            'integration_type' => 'PAYMENT_GATEWAY',
            'api_endpoint' => 'https://api.example.com',
            'integration_status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $integrationId = $this->repository->create($integrationData);
        
        $integration = $this->repository->findById($integrationId, $this->testTenantId);
        
        $this->assertIsArray($integration);
        $this->assertEquals($integrationId, $integration['integration_id']);
        
        // Cleanup
        $this->repository->delete($integrationId, $this->testTenantId);
    }

    public function testCreate()
    {
        $integrationData = [
            'tenant_id' => $this->testTenantId,
            'integration_name' => 'Test Integration',
            'integration_type' => 'PAYMENT_GATEWAY',
            'api_endpoint' => 'https://api.example.com',
            'integration_status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $integrationId = $this->repository->create($integrationData);
        
        $this->assertIsNumeric($integrationId);
        $this->assertGreaterThan(0, $integrationId);
        
        // Cleanup
        $this->repository->delete($integrationId, $this->testTenantId);
    }

    public function testUpdate()
    {
        $integrationData = [
            'tenant_id' => $this->testTenantId,
            'integration_name' => 'Test Integration',
            'integration_type' => 'PAYMENT_GATEWAY',
            'api_endpoint' => 'https://api.example.com',
            'integration_status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $integrationId = $this->repository->create($integrationData);
        
        $updateData = [
            'integration_name' => 'Updated Integration',
            'api_endpoint' => 'https://api.updated.com',
            'updated_by' => 1
        ];
        
        $result = $this->repository->update($integrationId, $updateData, $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify update
        $integration = $this->repository->findById($integrationId, $this->testTenantId);
        $this->assertEquals('Updated Integration', $integration['integration_name']);
        
        // Cleanup
        $this->repository->delete($integrationId, $this->testTenantId);
    }

    public function testUpdateSyncStatus()
    {
        $integrationData = [
            'tenant_id' => $this->testTenantId,
            'integration_name' => 'Test Integration',
            'integration_type' => 'PAYMENT_GATEWAY',
            'api_endpoint' => 'https://api.example.com',
            'integration_status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $integrationId = $this->repository->create($integrationData);
        
        $result = $this->repository->updateSyncStatus($integrationId, 'IN_PROGRESS', $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify update
        $integration = $this->repository->findById($integrationId, $this->testTenantId);
        $this->assertEquals('IN_PROGRESS', $integration['sync_status']);
        
        // Cleanup
        $this->repository->delete($integrationId, $this->testTenantId);
    }

    public function testUpdateHealthStatus()
    {
        $integrationData = [
            'tenant_id' => $this->testTenantId,
            'integration_name' => 'Test Integration',
            'integration_type' => 'PAYMENT_GATEWAY',
            'api_endpoint' => 'https://api.example.com',
            'integration_status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $integrationId = $this->repository->create($integrationData);
        
        $result = $this->repository->updateHealthStatus($integrationId, 'HEALTHY', $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify update
        $integration = $this->repository->findById($integrationId, $this->testTenantId);
        $this->assertEquals('HEALTHY', $integration['health_status']);
        
        // Cleanup
        $this->repository->delete($integrationId, $this->testTenantId);
    }

    public function testDelete()
    {
        $integrationData = [
            'tenant_id' => $this->testTenantId,
            'integration_name' => 'Test Integration',
            'integration_type' => 'PAYMENT_GATEWAY',
            'api_endpoint' => 'https://api.example.com',
            'integration_status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $integrationId = $this->repository->create($integrationData);
        
        $result = $this->repository->delete($integrationId, $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify deletion
        $integration = $this->repository->findById($integrationId, $this->testTenantId);
        $this->assertFalse($integration);
    }

    public function testGetByType()
    {
        $integrations = $this->repository->getByType($this->testTenantId, 'PAYMENT_GATEWAY', 10);
        
        $this->assertIsArray($integrations);
    }

    public function testGetByStatus()
    {
        $integrations = $this->repository->getByStatus($this->testTenantId, 'ACTIVE', 10);
        
        $this->assertIsArray($integrations);
    }

    public function testGetActive()
    {
        $integrations = $this->repository->getActive($this->testTenantId);
        
        $this->assertIsArray($integrations);
    }

    public function testGetByRestaurant()
    {
        $integrations = $this->repository->getByRestaurant($this->testTenantId, 1, 10);
        
        $this->assertIsArray($integrations);
    }

    public function testGetIntegrationMappings()
    {
        // Create a test integration first
        $integrationData = [
            'tenant_id' => $this->testTenantId,
            'integration_name' => 'Test Integration',
            'integration_type' => 'PAYMENT_GATEWAY',
            'api_endpoint' => 'https://api.example.com',
            'integration_status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $integrationId = $this->repository->create($integrationData);
        
        $mappings = $this->repository->getIntegrationMappings($integrationId, $this->testTenantId);
        
        $this->assertIsArray($mappings);
        
        // Cleanup
        $this->repository->delete($integrationId, $this->testTenantId);
    }

    public function testGetSyncLogs()
    {
        // Create a test integration first
        $integrationData = [
            'tenant_id' => $this->testTenantId,
            'integration_name' => 'Test Integration',
            'integration_type' => 'PAYMENT_GATEWAY',
            'api_endpoint' => 'https://api.example.com',
            'integration_status' => 'ACTIVE',
            'created_by' => 1
        ];
        
        $integrationId = $this->repository->create($integrationData);
        
        $syncLogs = $this->repository->getSyncLogs($integrationId, $this->testTenantId, 10);
        
        $this->assertIsArray($syncLogs);
        
        // Cleanup
        $this->repository->delete($integrationId, $this->testTenantId);
    }
}
