<?php

use PHPUnit\Framework\TestCase;
use Modules\CustomerAnalytics\Repositories\CustomerAnalyticsRepository;
use Core\Database;

class CustomerAnalyticsRepositoryTest extends TestCase
{
    private $repository;
    private $db;
    private $testTenantId = 1;
    private $testCustomerId = 1;

    protected function setUp(): void
    {
        $this->db = Database::getInstance();
        $this->repository = new CustomerAnalyticsRepository();
    }

    protected function tearDown(): void
    {
        // Cleanup test data if needed
    }

    public function testGetCustomerBehavior()
    {
        $startDate = date('Y-m-d', strtotime('-30 days'));
        $endDate = date('Y-m-d');
        
        $behavior = $this->repository->getCustomerBehavior($this->testTenantId, $this->testCustomerId, $startDate, $endDate);
        
        $this->assertIsArray($behavior);
    }

    public function testGetCohortAnalysis()
    {
        $startDate = date('Y-m-d', strtotime('-90 days'));
        $endDate = date('Y-m-d');
        
        $cohort = $this->repository->getCohortAnalysis($this->testTenantId, $startDate, $endDate);
        
        $this->assertIsArray($cohort);
    }

    public function testGetCustomerJourney()
    {
        $journey = $this->repository->getCustomerJourney($this->testTenantId, $this->testCustomerId);
        
        $this->assertIsArray($journey);
    }

    public function testGetCustomerSegment()
    {
        $segment = $this->repository->getCustomerSegment($this->testTenantId, $this->testCustomerId);
        
        $this->assertIsArray($segment);
    }

    public function testGetCustomerLifetimeValue()
    {
        $lifetimeValue = $this->repository->getCustomerLifetimeValue($this->testTenantId, $this->testCustomerId);
        
        $this->assertIsArray($lifetimeValue);
    }

    public function testGetRetentionRate()
    {
        $startDate = date('Y-m-d', strtotime('-30 days'));
        $endDate = date('Y-m-d');
        
        $retention = $this->repository->getRetentionRate($this->testTenantId, $startDate, $endDate);
        
        $this->assertIsArray($retention);
    }

    public function testGetChurnAnalysis()
    {
        $daysInactive = 90;
        
        $churn = $this->repository->getChurnAnalysis($this->testTenantId, $daysInactive);
        
        $this->assertIsArray($churn);
    }

    public function testGetPreferenceAnalytics()
    {
        $preferences = $this->repository->getPreferenceAnalytics($this->testTenantId, $this->testCustomerId);
        
        $this->assertIsArray($preferences);
    }

    public function testGetPeakHours()
    {
        $peakHours = $this->repository->getPeakHours($this->testTenantId, $this->testCustomerId);
        
        $this->assertIsArray($peakHours);
    }
}
