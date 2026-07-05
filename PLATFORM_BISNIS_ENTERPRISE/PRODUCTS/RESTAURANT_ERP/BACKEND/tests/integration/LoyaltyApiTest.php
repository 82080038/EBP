<?php

use PHPUnit\Framework\TestCase;

class LoyaltyApiTest extends TestCase
{
    private $client;
    private $token;
    private $baseUrl = 'http://localhost/api/v1';

    protected function setUp(): void
    {
        $this->client = new GuzzleHttp\Client(['base_uri' => $this->baseUrl]);
        $this->token = $this->login();
    }

    private function login(): string
    {
        $response = $this->client->post('/auth/login', [
            'json' => [
                'username' => 'admin',
                'password' => 'admin123'
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['data']['access_token'];
    }

    // ==================== Loyalty Points Tests ====================

    public function testGetPoints()
    {
        $response = $this->client->get('/loyalty/points', [
            'headers' => ['Authorization' => "Bearer {$this->token}"]
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertTrue($data['success']);
    }

    public function testAwardPoints()
    {
        $response = $this->client->post('/loyalty/points/award', [
            'headers' => ['Authorization' => "Bearer {$this->token}"],
            'json' => [
                'customer_id' => 1,
                'points' => 100,
                'transaction_type' => 'EARNED'
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertTrue($data['success']);
    }

    public function testAwardPointsValidationError()
    {
        $response = $this->client->post('/loyalty/points/award', [
            'headers' => ['Authorization' => "Bearer {$this->token}"],
            'json' => [
                'customer_id' => 1,
                'points' => -10
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertFalse($data['success']);
    }

    public function testRedeemPoints()
    {
        $response = $this->client->post('/loyalty/points/redeem', [
            'headers' => ['Authorization' => "Bearer {$this->token}"],
            'json' => [
                'customer_id' => 1,
                'points' => 50
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertTrue($data['success']);
    }

    // ==================== Loyalty Rewards Tests ====================

    public function testGetRewards()
    {
        $response = $this->client->get('/loyalty/rewards', [
            'headers' => ['Authorization' => "Bearer {$this->token}"]
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertTrue($data['success']);
    }

    public function testCreateReward()
    {
        $response = $this->client->post('/loyalty/rewards', [
            'headers' => ['Authorization' => "Bearer {$this->token}"],
            'json' => [
                'reward_code' => 'TEST_REWARD',
                'reward_name' => 'Test Reward',
                'points_required' => 100,
                'reward_type' => 'DISCOUNT'
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertTrue($data['success']);
    }

    public function testCreateRewardValidationError()
    {
        $response = $this->client->post('/loyalty/rewards', [
            'headers' => ['Authorization' => "Bearer {$this->token}"],
            'json' => [
                'reward_code' => '',
                'reward_name' => ''
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertFalse($data['success']);
    }

    public function testUpdateReward()
    {
        $response = $this->client->put('/loyalty/rewards/1', [
            'headers' => ['Authorization' => "Bearer {$this->token}"],
            'json' => [
                'reward_name' => 'Updated Reward',
                'points_required' => 150
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertTrue($data['success']);
    }

    public function testDeleteReward()
    {
        $response = $this->client->delete('/loyalty/rewards/1', [
            'headers' => ['Authorization' => "Bearer {$this->token}"]
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertTrue($data['success']);
    }

    public function testRedeemReward()
    {
        $response = $this->client->post('/loyalty/rewards/1/redeem', [
            'headers' => ['Authorization' => "Bearer {$this->token}"],
            'json' => [
                'customer_id' => 1
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertTrue($data['success']);
    }

    // ==================== Customer Loyalty Tests ====================

    public function testGetCustomerLoyalty()
    {
        $response = $this->client->get('/loyalty/customers', [
            'headers' => ['Authorization' => "Bearer {$this->token}"]
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertTrue($data['success']);
    }

    public function testEnrollCustomer()
    {
        $response = $this->client->post('/loyalty/customers/enroll', [
            'headers' => ['Authorization' => "Bearer {$this->token}"],
            'json' => [
                'customer_id' => 1
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertTrue($data['success']);
    }

    public function testGetTopCustomers()
    {
        $response = $this->client->get('/loyalty/customers/top', [
            'headers' => ['Authorization' => "Bearer {$this->token}"]
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertTrue($data['success']);
    }

    public function testGetCustomersByTier()
    {
        $response = $this->client->get('/loyalty/customers/tier/GOLD', [
            'headers' => ['Authorization' => "Bearer {$this->token}"]
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertTrue($data['success']);
    }

    // ==================== Authentication Tests ====================

    public function testUnauthorizedAccess()
    {
        $response = $this->client->get('/loyalty/points', [
            'headers' => ['Authorization' => 'Bearer invalid_token']
        ]);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testNoToken()
    {
        $response = $this->client->get('/loyalty/points');

        $this->assertEquals(401, $response->getStatusCode());
    }
}
