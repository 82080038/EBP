<?php

use PHPUnit\Framework\TestCase;
use Modules\Feedback\Repositories\FeedbackRepository;
use Core\Database;

class FeedbackRepositoryTest extends TestCase
{
    private $repository;
    private $db;
    private $testTenantId = 1;

    protected function setUp(): void
    {
        $this->db = Database::getInstance();
        $this->repository = new FeedbackRepository();
    }

    protected function tearDown(): void
    {
        // Cleanup test data if needed
    }

    public function testFindAll()
    {
        $feedback = $this->repository->findAll($this->testTenantId, 10, 0);
        
        $this->assertIsArray($feedback);
    }

    public function testFindById()
    {
        // Create a test feedback first
        $feedbackData = [
            'tenant_id' => $this->testTenantId,
            'feedback_type' => 'COMPLAINT',
            'subject' => 'Test Feedback',
            'message' => 'This is a test feedback message',
            'feedback_status' => 'PENDING',
            'priority' => 'medium',
            'created_by' => 1
        ];
        
        $feedbackId = $this->repository->create($feedbackData);
        
        $feedback = $this->repository->findById($feedbackId, $this->testTenantId);
        
        $this->assertIsArray($feedback);
        $this->assertEquals($feedbackId, $feedback['id']);
        
        // Cleanup
        $this->repository->delete($feedbackId, $this->testTenantId);
    }

    public function testCreate()
    {
        $feedbackData = [
            'tenant_id' => $this->testTenantId,
            'feedback_type' => 'COMPLAINT',
            'subject' => 'Test Feedback',
            'message' => 'This is a test feedback message',
            'feedback_status' => 'PENDING',
            'priority' => 'medium',
            'created_by' => 1
        ];
        
        $feedbackId = $this->repository->create($feedbackData);
        
        $this->assertIsNumeric($feedbackId);
        $this->assertGreaterThan(0, $feedbackId);
        
        // Cleanup
        $this->repository->delete($feedbackId, $this->testTenantId);
    }

    public function testUpdate()
    {
        $feedbackData = [
            'tenant_id' => $this->testTenantId,
            'feedback_type' => 'COMPLAINT',
            'subject' => 'Test Feedback',
            'message' => 'This is a test feedback message',
            'feedback_status' => 'PENDING',
            'priority' => 'medium',
            'created_by' => 1
        ];
        
        $feedbackId = $this->repository->create($feedbackData);
        
        $updateData = [
            'feedback_status' => 'RESOLVED',
            'priority' => 'low',
            'updated_by' => 1
        ];
        
        $result = $this->repository->update($feedbackId, $updateData, $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify update
        $feedback = $this->repository->findById($feedbackId, $this->testTenantId);
        $this->assertEquals('RESOLVED', $feedback['feedback_status']);
        
        // Cleanup
        $this->repository->delete($feedbackId, $this->testTenantId);
    }

    public function testUpdateStatus()
    {
        $feedbackData = [
            'tenant_id' => $this->testTenantId,
            'feedback_type' => 'COMPLAINT',
            'subject' => 'Test Feedback',
            'message' => 'This is a test feedback message',
            'feedback_status' => 'PENDING',
            'priority' => 'medium',
            'created_by' => 1
        ];
        
        $feedbackId = $this->repository->create($feedbackData);
        
        $result = $this->repository->updateStatus($feedbackId, 'RESOLVED', $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify update
        $feedback = $this->repository->findById($feedbackId, $this->testTenantId);
        $this->assertEquals('RESOLVED', $feedback['feedback_status']);
        
        // Cleanup
        $this->repository->delete($feedbackId, $this->testTenantId);
    }

    public function testDelete()
    {
        $feedbackData = [
            'tenant_id' => $this->testTenantId,
            'feedback_type' => 'COMPLAINT',
            'subject' => 'Test Feedback',
            'message' => 'This is a test feedback message',
            'feedback_status' => 'PENDING',
            'priority' => 'medium',
            'created_by' => 1
        ];
        
        $feedbackId = $this->repository->create($feedbackData);
        
        $result = $this->repository->delete($feedbackId, $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify deletion
        $feedback = $this->repository->findById($feedbackId, $this->testTenantId);
        $this->assertFalse($feedback);
    }

    public function testGetByType()
    {
        $feedback = $this->repository->getByType($this->testTenantId, 'COMPLAINT', 10);
        
        $this->assertIsArray($feedback);
    }

    public function testGetByStatus()
    {
        $feedback = $this->repository->getByStatus($this->testTenantId, 'PENDING', 10);
        
        $this->assertIsArray($feedback);
    }

    public function testGetByPriority()
    {
        $feedback = $this->repository->getByPriority($this->testTenantId, 'high', 10);
        
        $this->assertIsArray($feedback);
    }

    public function testGetSummary()
    {
        $summary = $this->repository->getSummary($this->testTenantId);
        
        $this->assertIsArray($summary);
    }

    public function testCountByStatus()
    {
        $count = $this->repository->countByStatus($this->testTenantId, 'PENDING');
        
        $this->assertIsNumeric($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }
}
