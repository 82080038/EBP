<?php

use PHPUnit\Framework\TestCase;
use Modules\Auth\Services\AuthService;
use Modules\Auth\Repositories\AuthRepository;
use Core\Database;

class AuthServiceTest extends TestCase
{
    private $service;
    private $repository;
    private $db;
    private $testTenantId = 1;
    private $testUserId = 1;

    protected function setUp(): void
    {
        $this->db = Database::getInstance();
        $this->repository = new AuthRepository();
        $this->service = new AuthService();
    }

    protected function tearDown(): void
    {
        // Cleanup test data if needed
    }

    public function testLoginSuccess()
    {
        $result = $this->service->login('admin', 'admin123');
        
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('user', $result);
    }

    public function testLoginInvalidCredentials()
    {
        $result = $this->service->login('admin', 'wrongpassword');
        
        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
    }

    public function testLoginNonexistentUser()
    {
        $result = $this->service->login('nonexistent', 'password');
        
        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
    }

    public function testValidateTokenSuccess()
    {
        // First login to get a valid token
        $loginResult = $this->service->login('admin', 'admin123');
        $token = $loginResult['token'];
        
        $result = $this->service->validateToken($token);
        
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('payload', $result);
    }

    public function testValidateTokenInvalid()
    {
        $result = $this->service->validateToken('invalid_token_string');
        
        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
    }

    public function testCreateUserSuccess()
    {
        $userData = [
            'username' => 'test_user_' . time(),
            'password' => 'test123',
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'status' => 'ACTIVE'
        ];
        
        $result = $this->service->createUser($userData, $this->testTenantId, $this->testUserId);
        
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('user_id', $result);
        
        // Cleanup
        $this->repository->deleteUser($result['user_id'], $this->testTenantId);
    }

    public function testCreateUserDuplicateUsername()
    {
        $userData = [
            'username' => 'admin', // Already exists
            'password' => 'test123',
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'status' => 'ACTIVE'
        ];
        
        $result = $this->service->createUser($userData, $this->testTenantId, $this->testUserId);
        
        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
    }

    public function testUpdateUserSuccess()
    {
        // Create a test user first
        $userData = [
            'username' => 'test_user_' . time(),
            'password' => 'test123',
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'status' => 'ACTIVE'
        ];
        
        $createResult = $this->service->createUser($userData, $this->testTenantId, $this->testUserId);
        $userId = $createResult['user_id'];
        
        $updateData = [
            'full_name' => 'Updated Test User',
            'email' => 'updated@example.com'
        ];
        
        $result = $this->service->updateUser($userId, $updateData, $this->testTenantId, $this->testUserId);
        
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        
        // Cleanup
        $this->repository->deleteUser($userId, $this->testTenantId);
    }

    public function testUpdateUserWithPassword()
    {
        $userData = [
            'username' => 'test_user_' . time(),
            'password' => 'test123',
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'status' => 'ACTIVE'
        ];
        
        $createResult = $this->service->createUser($userData, $this->testTenantId, $this->testUserId);
        $userId = $createResult['user_id'];
        
        $updateData = [
            'password' => 'newpassword123'
        ];
        
        $result = $this->service->updateUser($userId, $updateData, $this->testTenantId, $this->testUserId);
        
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        
        // Cleanup
        $this->repository->deleteUser($userId, $this->testTenantId);
    }

    public function testDeleteUserSuccess()
    {
        $userData = [
            'username' => 'test_user_' . time(),
            'password' => 'test123',
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'status' => 'ACTIVE'
        ];
        
        $createResult = $this->service->createUser($userData, $this->testTenantId, $this->testUserId);
        $userId = $createResult['user_id'];
        
        $result = $this->service->deleteUser($userId, $this->testTenantId, $this->testUserId);
        
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
    }

    public function testGetUserPermissions()
    {
        $permissions = $this->service->getUserPermissions($this->testUserId);
        
        $this->assertIsArray($permissions);
    }

    public function testGetAllUsers()
    {
        $users = $this->service->getAllUsers($this->testTenantId, 10, 0);
        
        $this->assertIsArray($users);
    }

    public function testGetUserById()
    {
        $user = $this->service->getUserById($this->testUserId, $this->testTenantId);
        
        $this->assertIsArray($user);
        $this->assertEquals($this->testUserId, $user['user_id']);
    }
}
