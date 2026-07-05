<?php

use PHPUnit\Framework\TestCase;

class LoyaltyServiceTest extends TestCase
{
    private $service;
    private $mockRepository;
    private $mockDb;

    protected function setUp(): void
    {
        $this->mockRepository = $this->createMock(LoyaltyRepository::class);
        $this->mockDb = $this->createMock(Database::class);
        $this->service = new LoyaltyService();
    }

    public function testAwardPointsSuccess()
    {
        // Arrange
        $tenantId = 1;
        $customerId = 1;
        $points = 100;
        
        $this->mockRepository->method('createPoint')
            ->willReturn(1);
        
        // Act
        $result = $this->service->awardPoints($tenantId, $customerId, $points);
        
        // Assert
        $this->assertTrue($result['success']);
    }

    public function testAwardPointsInvalidPoints()
    {
        // Arrange
        $tenantId = 1;
        $customerId = 1;
        $points = -10;
        
        // Act
        $result = $this->service->awardPoints($tenantId, $customerId, $points);
        
        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('Points must be greater than 0', $result['message']);
    }

    public function testRedeemPointsSuccess()
    {
        // Arrange
        $tenantId = 1;
        $customerId = 1;
        $points = 50;
        
        $this->mockRepository->method('getCustomerTotalPoints')
            ->willReturn(100);
        $this->mockRepository->method('createPoint')
            ->willReturn(1);
        
        // Act
        $result = $this->service->redeemPoints($tenantId, $customerId, $points);
        
        // Assert
        $this->assertTrue($result['success']);
    }

    public function testRedeemPointsInsufficientPoints()
    {
        // Arrange
        $tenantId = 1;
        $customerId = 1;
        $points = 200;
        
        $this->mockRepository->method('getCustomerTotalPoints')
            ->willReturn(100);
        
        // Act
        $result = $this->service->redeemPoints($tenantId, $customerId, $points);
        
        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('Insufficient points', $result['message']);
    }

    public function testCreateRewardSuccess()
    {
        // Arrange
        $tenantId = 1;
        $data = [
            'reward_code' => 'TEST_REWARD',
            'reward_name' => 'Test Reward',
            'points_required' => 100,
            'reward_type' => 'DISCOUNT'
        ];
        
        $this->mockRepository->method('createReward')
            ->willReturn(1);
        
        // Act
        $result = $this->service->createReward($tenantId, $data);
        
        // Assert
        $this->assertTrue($result['success']);
    }

    public function testCreateRewardValidationError()
    {
        // Arrange
        $tenantId = 1;
        $data = [
            'reward_code' => '',
            'reward_name' => '',
            'points_required' => 0
        ];
        
        // Act
        $result = $this->service->createReward($tenantId, $data);
        
        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
    }

    public function testEnrollCustomerSuccess()
    {
        // Arrange
        $tenantId = 1;
        $customerId = 1;
        
        $this->mockRepository->method('findCustomerLoyaltyByCustomer')
            ->willReturn(null);
        $this->mockRepository->method('createCustomerLoyalty')
            ->willReturn(1);
        
        // Act
        $result = $this->service->enrollCustomer($tenantId, $customerId);
        
        // Assert
        $this->assertTrue($result['success']);
    }

    public function testEnrollCustomerAlreadyEnrolled()
    {
        // Arrange
        $tenantId = 1;
        $customerId = 1;
        
        $this->mockRepository->method('findCustomerLoyaltyByCustomer')
            ->willReturn(['customer_loyalty_id' => 1]);
        
        // Act
        $result = $this->service->enrollCustomer($tenantId, $customerId);
        
        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('Customer already enrolled in loyalty program', $result['message']);
    }

    public function testRedeemRewardSuccess()
    {
        // Arrange
        $tenantId = 1;
        $customerId = 1;
        $rewardId = 1;
        
        $this->mockRepository->method('findRewardById')
            ->willReturn([
                'reward_id' => 1,
                'reward_code' => 'TEST',
                'reward_name' => 'Test Reward',
                'points_required' => 100,
                'status' => 'ACTIVE',
                'valid_from' => null,
                'valid_until' => null
            ]);
        $this->mockRepository->method('getCustomerTotalPoints')
            ->willReturn(200);
        $this->mockRepository->method('createPoint')
            ->willReturn(1);
        
        // Act
        $result = $this->service->redeemReward($tenantId, $customerId, $rewardId);
        
        // Assert
        $this->assertTrue($result['success']);
    }

    public function testRedeemRewardNotFound()
    {
        // Arrange
        $tenantId = 1;
        $customerId = 1;
        $rewardId = 999;
        
        $this->mockRepository->method('findRewardById')
            ->willReturn(null);
        
        // Act
        $result = $this->service->redeemReward($tenantId, $customerId, $rewardId);
        
        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('Reward not found', $result['message']);
    }
}
