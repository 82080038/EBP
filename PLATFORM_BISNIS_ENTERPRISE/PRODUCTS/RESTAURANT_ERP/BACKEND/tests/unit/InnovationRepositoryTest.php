<?php

use PHPUnit\Framework\TestCase;
use Modules\Innovation\Repositories\InnovationRepository;
use Core\Database;

class InnovationRepositoryTest extends TestCase
{
    private $repository;
    private $db;
    private $testTenantId = 1;

    protected function setUp(): void
    {
        $this->db = Database::getInstance();
        $this->repository = new InnovationRepository();
    }

    protected function tearDown(): void
    {
        // Cleanup test data if needed
    }

    public function testFindAll()
    {
        $projects = $this->repository->findAll($this->testTenantId, 10, 0);
        
        $this->assertIsArray($projects);
    }

    public function testFindById()
    {
        // Create a test project first
        $projectData = [
            'tenant_id' => $this->testTenantId,
            'project_name' => 'Test Innovation Project',
            'project_code' => 'IP-' . time(),
            'project_type' => 'MENU_INNOVATION',
            'project_status' => 'DRAFT',
            'created_by' => 1
        ];
        
        $projectId = $this->repository->create($projectData);
        
        $project = $this->repository->findById($projectId, $this->testTenantId);
        
        $this->assertIsArray($project);
        $this->assertEquals($projectId, $project['project_id']);
        
        // Cleanup
        $this->repository->delete($projectId, $this->testTenantId);
    }

    public function testCreate()
    {
        $projectData = [
            'tenant_id' => $this->testTenantId,
            'project_name' => 'Test Innovation Project',
            'project_code' => 'IP-' . time(),
            'project_type' => 'MENU_INNOVATION',
            'project_status' => 'DRAFT',
            'created_by' => 1
        ];
        
        $projectId = $this->repository->create($projectData);
        
        $this->assertIsNumeric($projectId);
        $this->assertGreaterThan(0, $projectId);
        
        // Cleanup
        $this->repository->delete($projectId, $this->testTenantId);
    }

    public function testUpdate()
    {
        $projectData = [
            'tenant_id' => $this->testTenantId,
            'project_name' => 'Test Innovation Project',
            'project_code' => 'IP-' . time(),
            'project_type' => 'MENU_INNOVATION',
            'project_status' => 'DRAFT',
            'created_by' => 1
        ];
        
        $projectId = $this->repository->create($projectData);
        
        $updateData = [
            'project_name' => 'Updated Innovation Project',
            'project_status' => 'IN_PROGRESS',
            'updated_by' => 1
        ];
        
        $result = $this->repository->update($projectId, $updateData, $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify update
        $project = $this->repository->findById($projectId, $this->testTenantId);
        $this->assertEquals('Updated Innovation Project', $project['project_name']);
        
        // Cleanup
        $this->repository->delete($projectId, $this->testTenantId);
    }

    public function testUpdateStatus()
    {
        $projectData = [
            'tenant_id' => $this->testTenantId,
            'project_name' => 'Test Innovation Project',
            'project_code' => 'IP-' . time(),
            'project_type' => 'MENU_INNOVATION',
            'project_status' => 'DRAFT',
            'created_by' => 1
        ];
        
        $projectId = $this->repository->create($projectData);
        
        $result = $this->repository->updateStatus($projectId, 'COMPLETED', $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify update
        $project = $this->repository->findById($projectId, $this->testTenantId);
        $this->assertEquals('COMPLETED', $project['project_status']);
        
        // Cleanup
        $this->repository->delete($projectId, $this->testTenantId);
    }

    public function testDelete()
    {
        $projectData = [
            'tenant_id' => $this->testTenantId,
            'project_name' => 'Test Innovation Project',
            'project_code' => 'IP-' . time(),
            'project_type' => 'MENU_INNOVATION',
            'project_status' => 'DRAFT',
            'created_by' => 1
        ];
        
        $projectId = $this->repository->create($projectData);
        
        $result = $this->repository->delete($projectId, $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify deletion
        $project = $this->repository->findById($projectId, $this->testTenantId);
        $this->assertFalse($project);
    }

    public function testGetByStatus()
    {
        $projects = $this->repository->getByStatus($this->testTenantId, 'DRAFT', 10);
        
        $this->assertIsArray($projects);
    }

    public function testGetByType()
    {
        $projects = $this->repository->getByType($this->testTenantId, 'MENU_INNOVATION', 10);
        
        $this->assertIsArray($projects);
    }

    public function testGetActive()
    {
        $projects = $this->repository->getActive($this->testTenantId);
        
        $this->assertIsArray($projects);
    }

    public function testGetInnovationIdeas()
    {
        $ideas = $this->repository->getInnovationIdeas($this->testTenantId, 10);
        
        $this->assertIsArray($ideas);
    }

    public function testGetInnovationMetrics()
    {
        // Create a test project first
        $projectData = [
            'tenant_id' => $this->testTenantId,
            'project_name' => 'Test Innovation Project',
            'project_code' => 'IP-' . time(),
            'project_type' => 'MENU_INNOVATION',
            'project_status' => 'DRAFT',
            'created_by' => 1
        ];
        
        $projectId = $this->repository->create($projectData);
        
        $metrics = $this->repository->getInnovationMetrics($projectId, $this->testTenantId);
        
        $this->assertIsArray($metrics);
        
        // Cleanup
        $this->repository->delete($projectId, $this->testTenantId);
    }
}
