<?php

use PHPUnit\Framework\TestCase;
use Modules\Reconciliation\Repositories\ReconciliationRepository;
use Core\Database;

class ReconciliationRepositoryTest extends TestCase
{
    private $repository;
    private $db;
    private $testTenantId = 1;

    protected function setUp(): void
    {
        $this->db = Database::getInstance();
        $this->repository = new ReconciliationRepository();
    }

    protected function tearDown(): void
    {
        // Cleanup test data if needed
    }

    public function testFindAll()
    {
        $transactions = $this->repository->findAll($this->testTenantId, 10, 0);
        
        $this->assertIsArray($transactions);
    }

    public function testFindById()
    {
        // Create a test transaction first
        $transactionData = [
            'tenant_id' => $this->testTenantId,
            'transaction_date' => date('Y-m-d'),
            'source' => 'POS',
            'amount' => 100.00,
            'reconciliation_status' => 'PENDING',
            'created_by' => 1
        ];
        
        $transactionId = $this->repository->create($transactionData);
        
        $transaction = $this->repository->findById($transactionId, $this->testTenantId);
        
        $this->assertIsArray($transaction);
        $this->assertEquals($transactionId, $transaction['transaction_id']);
        
        // Cleanup
        $this->repository->delete($transactionId, $this->testTenantId);
    }

    public function testCreate()
    {
        $transactionData = [
            'tenant_id' => $this->testTenantId,
            'transaction_date' => date('Y-m-d'),
            'source' => 'POS',
            'amount' => 100.00,
            'reconciliation_status' => 'PENDING',
            'created_by' => 1
        ];
        
        $transactionId = $this->repository->create($transactionData);
        
        $this->assertIsNumeric($transactionId);
        $this->assertGreaterThan(0, $transactionId);
        
        // Cleanup
        $this->repository->delete($transactionId, $this->testTenantId);
    }

    public function testUpdate()
    {
        $transactionData = [
            'tenant_id' => $this->testTenantId,
            'transaction_date' => date('Y-m-d'),
            'source' => 'POS',
            'amount' => 100.00,
            'reconciliation_status' => 'PENDING',
            'created_by' => 1
        ];
        
        $transactionId = $this->repository->create($transactionData);
        
        $updateData = [
            'reconciliation_status' => 'RECONCILED',
            'amount' => 150.00,
            'updated_by' => 1
        ];
        
        $result = $this->repository->update($transactionId, $updateData, $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify update
        $transaction = $this->repository->findById($transactionId, $this->testTenantId);
        $this->assertEquals('RECONCILED', $transaction['reconciliation_status']);
        
        // Cleanup
        $this->repository->delete($transactionId, $this->testTenantId);
    }

    public function testUpdateStatus()
    {
        $transactionData = [
            'tenant_id' => $this->testTenantId,
            'transaction_date' => date('Y-m-d'),
            'source' => 'POS',
            'amount' => 100.00,
            'reconciliation_status' => 'PENDING',
            'created_by' => 1
        ];
        
        $transactionId = $this->repository->create($transactionData);
        
        $result = $this->repository->updateStatus($transactionId, 'RECONCILED', $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify update
        $transaction = $this->repository->findById($transactionId, $this->testTenantId);
        $this->assertEquals('RECONCILED', $transaction['reconciliation_status']);
        
        // Cleanup
        $this->repository->delete($transactionId, $this->testTenantId);
    }

    public function testDelete()
    {
        $transactionData = [
            'tenant_id' => $this->testTenantId,
            'transaction_date' => date('Y-m-d'),
            'source' => 'POS',
            'amount' => 100.00,
            'reconciliation_status' => 'PENDING',
            'created_by' => 1
        ];
        
        $transactionId = $this->repository->create($transactionData);
        
        $result = $this->repository->delete($transactionId, $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify deletion
        $transaction = $this->repository->findById($transactionId, $this->testTenantId);
        $this->assertFalse($transaction);
    }

    public function testGetByStatus()
    {
        $transactions = $this->repository->getByStatus($this->testTenantId, 'PENDING', 10);
        
        $this->assertIsArray($transactions);
    }

    public function testGetByDateRange()
    {
        $startDate = date('Y-m-d', strtotime('-7 days'));
        $endDate = date('Y-m-d');
        
        $transactions = $this->repository->getByDateRange($this->testTenantId, $startDate, $endDate);
        
        $this->assertIsArray($transactions);
    }

    public function testGetBySource()
    {
        $transactions = $this->repository->getBySource($this->testTenantId, 'POS', 10);
        
        $this->assertIsArray($transactions);
    }

    public function testGetDiscrepancies()
    {
        $discrepancies = $this->repository->getDiscrepancies($this->testTenantId);
        
        $this->assertIsArray($discrepancies);
    }

    public function testGetSummary()
    {
        $summary = $this->repository->getSummary($this->testTenantId);
        
        $this->assertIsArray($summary);
    }

    public function testGetSources()
    {
        $sources = $this->repository->getSources($this->testTenantId);
        
        $this->assertIsArray($sources);
    }

    public function testGetRules()
    {
        $rules = $this->repository->getRules($this->testTenantId);
        
        $this->assertIsArray($rules);
    }
}
