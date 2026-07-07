<?php

use PHPUnit\Framework\TestCase;
use Modules\Payment\Repositories\PaymentRepository;
use Core\Database;

class PaymentRepositoryTest extends TestCase
{
    private $repository;
    private $db;
    private $testTenantId = 1;

    protected function setUp(): void
    {
        $this->db = Database::getInstance();
        $this->repository = new PaymentRepository();
    }

    protected function tearDown(): void
    {
        // Cleanup test data if needed
    }

    public function testFindAll()
    {
        $payments = $this->repository->findAll($this->testTenantId, 10, 0);
        
        $this->assertIsArray($payments);
    }

    public function testFindById()
    {
        // Create a test payment first
        $paymentData = [
            'tenant_id' => $this->testTenantId,
            'order_id' => 1,
            'payment_method' => 'CASH',
            'amount' => 100.00,
            'change_amount' => 0.00,
            'payment_status' => 'COMPLETED',
            'created_by' => 1
        ];
        
        $paymentId = $this->repository->create($paymentData);
        
        $payment = $this->repository->findById($paymentId, $this->testTenantId);
        
        $this->assertIsArray($payment);
        $this->assertEquals($paymentId, $payment['payment_id']);
        
        // Cleanup
        $this->repository->delete($paymentId, $this->testTenantId);
    }

    public function testFindByOrderId()
    {
        $paymentData = [
            'tenant_id' => $this->testTenantId,
            'order_id' => 1,
            'payment_method' => 'CASH',
            'amount' => 100.00,
            'change_amount' => 0.00,
            'payment_status' => 'COMPLETED',
            'created_by' => 1
        ];
        
        $paymentId = $this->repository->create($paymentData);
        
        $payments = $this->repository->findByOrderId(1, $this->testTenantId);
        
        $this->assertIsArray($payments);
        
        // Cleanup
        $this->repository->delete($paymentId, $this->testTenantId);
    }

    public function testCreate()
    {
        $paymentData = [
            'tenant_id' => $this->testTenantId,
            'order_id' => 1,
            'payment_method' => 'CASH',
            'amount' => 100.00,
            'change_amount' => 0.00,
            'payment_status' => 'COMPLETED',
            'created_by' => 1
        ];
        
        $paymentId = $this->repository->create($paymentData);
        
        $this->assertIsNumeric($paymentId);
        $this->assertGreaterThan(0, $paymentId);
        
        // Cleanup
        $this->repository->delete($paymentId, $this->testTenantId);
    }

    public function testUpdate()
    {
        $paymentData = [
            'tenant_id' => $this->testTenantId,
            'order_id' => 1,
            'payment_method' => 'CASH',
            'amount' => 100.00,
            'change_amount' => 0.00,
            'payment_status' => 'PENDING',
            'created_by' => 1
        ];
        
        $paymentId = $this->repository->create($paymentData);
        
        $updateData = [
            'payment_status' => 'COMPLETED',
            'amount' => 150.00,
            'updated_by' => 1
        ];
        
        $result = $this->repository->update($paymentId, $updateData, $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify update
        $payment = $this->repository->findById($paymentId, $this->testTenantId);
        $this->assertEquals('COMPLETED', $payment['payment_status']);
        
        // Cleanup
        $this->repository->delete($paymentId, $this->testTenantId);
    }

    public function testUpdateStatus()
    {
        $paymentData = [
            'tenant_id' => $this->testTenantId,
            'order_id' => 1,
            'payment_method' => 'CASH',
            'amount' => 100.00,
            'change_amount' => 0.00,
            'payment_status' => 'PENDING',
            'created_by' => 1
        ];
        
        $paymentId = $this->repository->create($paymentData);
        
        $result = $this->repository->updateStatus($paymentId, 'COMPLETED', $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify update
        $payment = $this->repository->findById($paymentId, $this->testTenantId);
        $this->assertEquals('COMPLETED', $payment['payment_status']);
        
        // Cleanup
        $this->repository->delete($paymentId, $this->testTenantId);
    }

    public function testDelete()
    {
        $paymentData = [
            'tenant_id' => $this->testTenantId,
            'order_id' => 1,
            'payment_method' => 'CASH',
            'amount' => 100.00,
            'change_amount' => 0.00,
            'payment_status' => 'PENDING',
            'created_by' => 1
        ];
        
        $paymentId = $this->repository->create($paymentData);
        
        $result = $this->repository->delete($paymentId, $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify deletion
        $payment = $this->repository->findById($paymentId, $this->testTenantId);
        $this->assertFalse($payment);
    }

    public function testGetByStatus()
    {
        $payments = $this->repository->getByStatus($this->testTenantId, 'COMPLETED', 10);
        
        $this->assertIsArray($payments);
    }

    public function testGetByDateRange()
    {
        $startDate = date('Y-m-d', strtotime('-7 days'));
        $endDate = date('Y-m-d');
        
        $payments = $this->repository->getByDateRange($this->testTenantId, $startDate, $endDate);
        
        $this->assertIsArray($payments);
    }

    public function testGetByPaymentMethod()
    {
        $payments = $this->repository->getByPaymentMethod($this->testTenantId, 'CASH', 10);
        
        $this->assertIsArray($payments);
    }

    public function testGetByOrderId()
    {
        $payments = $this->repository->getByOrderId(1, $this->testTenantId);
        
        $this->assertIsArray($payments);
    }

    public function testCountByStatus()
    {
        $count = $this->repository->countByStatus($this->testTenantId, 'COMPLETED');
        
        $this->assertIsNumeric($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testSumByDateRange()
    {
        $startDate = date('Y-m-d', strtotime('-7 days'));
        $endDate = date('Y-m-d');
        
        $sum = $this->repository->sumByDateRange($this->testTenantId, $startDate, $endDate);
        
        $this->assertIsNumeric($sum);
        $this->assertGreaterThanOrEqual(0, $sum);
    }

    public function testGetPaymentMethodBreakdown()
    {
        $startDate = date('Y-m-d', strtotime('-7 days'));
        $endDate = date('Y-m-d');
        
        $breakdown = $this->repository->getPaymentMethodBreakdown($this->testTenantId, $startDate, $endDate);
        
        $this->assertIsArray($breakdown);
    }
}
