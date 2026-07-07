<?php

use PHPUnit\Framework\TestCase;
use Modules\Order\Repositories\OrderRepository;
use Core\Database;

class OrderRepositoryTest extends TestCase
{
    private $repository;
    private $db;
    private $testTenantId = 1;
    private $testBranchId = 1;

    protected function setUp(): void
    {
        $this->db = Database::getInstance();
        $this->repository = new OrderRepository();
    }

    protected function tearDown(): void
    {
        // Cleanup test data if needed
    }

    public function testFindAll()
    {
        $orders = $this->repository->findAll($this->testTenantId, 10, 0);
        
        $this->assertIsArray($orders);
    }

    public function testFindById()
    {
        // Create a test order first
        $orderData = [
            'tenant_id' => $this->testTenantId,
            'branch_id' => $this->testBranchId,
            'table_id' => 1,
            'order_number' => 'TEST-' . time(),
            'order_type' => 'DINE_IN',
            'status' => 'PENDING',
            'total_amount' => 100.00,
            'created_by' => 1
        ];
        
        $orderId = $this->repository->create($orderData);
        
        $order = $this->repository->findById($orderId, $this->testTenantId);
        
        $this->assertIsArray($order);
        $this->assertEquals($orderId, $order['order_id']);
        
        // Cleanup
        $this->repository->delete($orderId, $this->testTenantId);
    }

    public function testFindByOrderNumber()
    {
        $orderNumber = 'TEST-' . time();
        $orderData = [
            'tenant_id' => $this->testTenantId,
            'branch_id' => $this->testBranchId,
            'table_id' => 1,
            'order_number' => $orderNumber,
            'order_type' => 'DINE_IN',
            'status' => 'PENDING',
            'total_amount' => 100.00,
            'created_by' => 1
        ];
        
        $orderId = $this->repository->create($orderData);
        
        $order = $this->repository->findByOrderNumber($orderNumber, $this->testTenantId);
        
        $this->assertIsArray($order);
        $this->assertEquals($orderNumber, $order['order_number']);
        
        // Cleanup
        $this->repository->delete($orderId, $this->testTenantId);
    }

    public function testCreate()
    {
        $orderData = [
            'tenant_id' => $this->testTenantId,
            'branch_id' => $this->testBranchId,
            'table_id' => 1,
            'order_number' => 'TEST-' . time(),
            'order_type' => 'DINE_IN',
            'status' => 'PENDING',
            'total_amount' => 100.00,
            'created_by' => 1
        ];
        
        $orderId = $this->repository->create($orderData);
        
        $this->assertIsNumeric($orderId);
        $this->assertGreaterThan(0, $orderId);
        
        // Cleanup
        $this->repository->delete($orderId, $this->testTenantId);
    }

    public function testUpdate()
    {
        $orderData = [
            'tenant_id' => $this->testTenantId,
            'branch_id' => $this->testBranchId,
            'table_id' => 1,
            'order_number' => 'TEST-' . time(),
            'order_type' => 'DINE_IN',
            'status' => 'PENDING',
            'total_amount' => 100.00,
            'created_by' => 1
        ];
        
        $orderId = $this->repository->create($orderData);
        
        $updateData = [
            'status' => 'IN_PROGRESS',
            'total_amount' => 150.00,
            'updated_by' => 1
        ];
        
        $result = $this->repository->update($orderId, $updateData, $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify update
        $order = $this->repository->findById($orderId, $this->testTenantId);
        $this->assertEquals('IN_PROGRESS', $order['status']);
        
        // Cleanup
        $this->repository->delete($orderId, $this->testTenantId);
    }

    public function testUpdateStatus()
    {
        $orderData = [
            'tenant_id' => $this->testTenantId,
            'branch_id' => $this->testBranchId,
            'table_id' => 1,
            'order_number' => 'TEST-' . time(),
            'order_type' => 'DINE_IN',
            'status' => 'PENDING',
            'total_amount' => 100.00,
            'created_by' => 1
        ];
        
        $orderId = $this->repository->create($orderData);
        
        $result = $this->repository->updateStatus($orderId, 'COMPLETED', $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify update
        $order = $this->repository->findById($orderId, $this->testTenantId);
        $this->assertEquals('COMPLETED', $order['status']);
        
        // Cleanup
        $this->repository->delete($orderId, $this->testTenantId);
    }

    public function testDelete()
    {
        $orderData = [
            'tenant_id' => $this->testTenantId,
            'branch_id' => $this->testBranchId,
            'table_id' => 1,
            'order_number' => 'TEST-' . time(),
            'order_type' => 'DINE_IN',
            'status' => 'PENDING',
            'total_amount' => 100.00,
            'created_by' => 1
        ];
        
        $orderId = $this->repository->create($orderData);
        
        $result = $this->repository->delete($orderId, $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify deletion
        $order = $this->repository->findById($orderId, $this->testTenantId);
        $this->assertFalse($order);
    }

    public function testGetByStatus()
    {
        $orders = $this->repository->getByStatus($this->testTenantId, 'PENDING', 10);
        
        $this->assertIsArray($orders);
    }

    public function testGetByDateRange()
    {
        $startDate = date('Y-m-d', strtotime('-7 days'));
        $endDate = date('Y-m-d');
        
        $orders = $this->repository->getByDateRange($this->testTenantId, $startDate, $endDate);
        
        $this->assertIsArray($orders);
    }

    public function testGetByTable()
    {
        $orders = $this->repository->getByTable($this->testTenantId, 1, 10);
        
        $this->assertIsArray($orders);
    }

    public function testGetByCustomer()
    {
        $orders = $this->repository->getByCustomer($this->testTenantId, 1, 10);
        
        $this->assertIsArray($orders);
    }

    public function testGetOrderItems()
    {
        // Create a test order first
        $orderData = [
            'tenant_id' => $this->testTenantId,
            'branch_id' => $this->testBranchId,
            'table_id' => 1,
            'order_number' => 'TEST-' . time(),
            'order_type' => 'DINE_IN',
            'status' => 'PENDING',
            'total_amount' => 100.00,
            'created_by' => 1
        ];
        
        $orderId = $this->repository->create($orderData);
        
        $items = $this->repository->getOrderItems($orderId, $this->testTenantId);
        
        $this->assertIsArray($items);
        
        // Cleanup
        $this->repository->delete($orderId, $this->testTenantId);
    }

    public function testCountByStatus()
    {
        $count = $this->repository->countByStatus($this->testTenantId, 'PENDING');
        
        $this->assertIsNumeric($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }
}
