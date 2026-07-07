<?php

use PHPUnit\Framework\TestCase;
use Modules\Analytics\Repositories\AnalyticsRepository;
use Core\Database;

class AnalyticsRepositoryTest extends TestCase
{
    private $repository;
    private $db;
    private $testTenantId = 1;

    protected function setUp(): void
    {
        $this->db = Database::getInstance();
        $this->repository = new AnalyticsRepository();
    }

    protected function tearDown(): void
    {
        // Cleanup test data if needed
    }

    public function testGetDailySalesSummary()
    {
        $startDate = date('Y-m-d', strtotime('-7 days'));
        $endDate = date('Y-m-d');
        
        $summary = $this->repository->getDailySalesSummary($this->testTenantId, $startDate, $endDate);
        
        $this->assertIsArray($summary);
    }

    public function testGetHourlySalesSummary()
    {
        $date = date('Y-m-d');
        
        $summary = $this->repository->getHourlySalesSummary($this->testTenantId, $date);
        
        $this->assertIsArray($summary);
    }

    public function testGetTopSellingProducts()
    {
        $startDate = date('Y-m-d', strtotime('-7 days'));
        $endDate = date('Y-m-d');
        
        $products = $this->repository->getTopSellingProducts($this->testTenantId, $startDate, $endDate, 10);
        
        $this->assertIsArray($products);
    }

    public function testGetCategoryPerformance()
    {
        $startDate = date('Y-m-d', strtotime('-7 days'));
        $endDate = date('Y-m-d');
        
        $performance = $this->repository->getCategoryPerformance($this->testTenantId, $startDate, $endDate);
        
        $this->assertIsArray($performance);
    }

    public function testGetPaymentMethodBreakdown()
    {
        $startDate = date('Y-m-d', strtotime('-7 days'));
        $endDate = date('Y-m-d');
        
        $breakdown = $this->repository->getPaymentMethodBreakdown($this->testTenantId, $startDate, $endDate);
        
        $this->assertIsArray($breakdown);
    }

    public function testGetOrderTypeBreakdown()
    {
        $startDate = date('Y-m-d', strtotime('-7 days'));
        $endDate = date('Y-m-d');
        
        $breakdown = $this->repository->getOrderTypeBreakdown($this->testTenantId, $startDate, $endDate);
        
        $this->assertIsArray($breakdown);
    }

    public function testGetCustomerAnalytics()
    {
        $startDate = date('Y-m-d', strtotime('-7 days'));
        $endDate = date('Y-m-d');
        
        $analytics = $this->repository->getCustomerAnalytics($this->testTenantId, $startDate, $endDate);
        
        $this->assertIsArray($analytics);
    }

    public function testGetTablePerformance()
    {
        $startDate = date('Y-m-d', strtotime('-7 days'));
        $endDate = date('Y-m-d');
        
        $performance = $this->repository->getTablePerformance($this->testTenantId, $startDate, $endDate);
        
        $this->assertIsArray($performance);
    }

    public function testGetStaffPerformance()
    {
        $startDate = date('Y-m-d', strtotime('-7 days'));
        $endDate = date('Y-m-d');
        
        $performance = $this->repository->getStaffPerformance($this->testTenantId, $startDate, $endDate);
        
        $this->assertIsArray($performance);
    }

    public function testGetRevenueTrends()
    {
        $months = 12;
        
        $trends = $this->repository->getRevenueTrends($this->testTenantId, $months);
        
        $this->assertIsArray($trends);
    }

    public function testGetComparisonWithPrevious()
    {
        $startDate = date('Y-m-d', strtotime('-7 days'));
        $endDate = date('Y-m-d');
        
        $comparison = $this->repository->getComparisonWithPrevious($this->testTenantId, $startDate, $endDate);
        
        $this->assertIsArray($comparison);
    }
}
