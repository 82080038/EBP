<?php

use PHPUnit\Framework\TestCase;
use Modules\Auth\Repositories\AuthRepository;
use Core\Database;

class AuthRepositoryTest extends TestCase
{
    private $repository;
    private $db;
    private $testTenantId = 1;
    private $testUserId = 1;

    protected function setUp(): void
    {
        $this->db = Database::getInstance();
        $this->repository = new AuthRepository();
    }

    protected function tearDown(): void
    {
        // Cleanup test data if needed
    }

    public function testFindByUsername()
    {
        $user = $this->repository->findByUsername('admin');
        
        $this->assertIsArray($user);
        $this->assertArrayHasKey('user_id', $user);
        $this->assertArrayHasKey('username', $user);
        $this->assertEquals('admin', $user['username']);
    }

    public function testFindByUsernameNotFound()
    {
        $user = $this->repository->findByUsername('nonexistent_user');
        
        $this->assertFalse($user);
    }

    public function testFindById()
    {
        $user = $this->repository->findById($this->testUserId, $this->testTenantId);
        
        $this->assertIsArray($user);
        $this->assertArrayHasKey('user_id', $user);
        $this->assertEquals($this->testUserId, $user['user_id']);
    }

    public function testFindByIdNotFound()
    {
        $user = $this->repository->findById(99999, $this->testTenantId);
        
        $this->assertFalse($user);
    }

    public function testGetAllUsers()
    {
        $users = $this->repository->getAllUsers($this->testTenantId, 10, 0);
        
        $this->assertIsArray($users);
        $this->assertGreaterThan(0, count($users));
    }

    public function testGetUserPermissions()
    {
        $permissions = $this->repository->getUserPermissions($this->testUserId);
        
        $this->assertIsArray($permissions);
    }

    public function testUsernameExists()
    {
        $exists = $this->repository->usernameExists('admin');
        
        $this->assertTrue($exists);
    }

    public function testUsernameNotExists()
    {
        $exists = $this->repository->usernameExists('nonexistent_user');
        
        $this->assertFalse($exists);
    }

    public function testCreateUser()
    {
        $userData = [
            'username' => 'test_user_' . time(),
            'password_hash' => password_hash('test123', PASSWORD_BCRYPT),
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'status' => 'ACTIVE',
            'created_by' => $this->testUserId
        ];

        $userId = $this->repository->createUser($userData);
        
        $this->assertIsNumeric($userId);
        $this->assertGreaterThan(0, $userId);
        
        // Cleanup
        $this->repository->deleteUser($userId, $this->testTenantId);
    }

    public function testUpdateUser()
    {
        $userData = [
            'username' => 'test_user_' . time(),
            'password_hash' => password_hash('test123', PASSWORD_BCRYPT),
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'status' => 'ACTIVE',
            'created_by' => $this->testUserId
        ];

        $userId = $this->repository->createUser($userData);
        
        $updateData = [
            'full_name' => 'Updated Test User',
            'email' => 'updated@example.com',
            'updated_by' => $this->testUserId
        ];

        $result = $this->repository->updateUser($userId, $updateData, $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify update
        $user = $this->repository->findById($userId, $this->testTenantId);
        $this->assertEquals('Updated Test User', $user['full_name']);
        
        // Cleanup
        $this->repository->deleteUser($userId, $this->testTenantId);
    }

    public function testUpdatePassword()
    {
        $userData = [
            'username' => 'test_user_' . time(),
            'password_hash' => password_hash('test123', PASSWORD_BCRYPT),
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'status' => 'ACTIVE',
            'created_by' => $this->testUserId
        ];

        $userId = $this->repository->createUser($userData);
        
        $newPasswordHash = password_hash('newpassword', PASSWORD_BCRYPT);
        $result = $this->repository->updatePassword($userId, $newPasswordHash, $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Cleanup
        $this->repository->deleteUser($userId, $this->testTenantId);
    }

    public function testDeleteUser()
    {
        $userData = [
            'username' => 'test_user_' . time(),
            'password_hash' => password_hash('test123', PASSWORD_BCRYPT),
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'status' => 'ACTIVE',
            'created_by' => $this->testUserId
        ];

        $userId = $this->repository->createUser($userData);
        
        $result = $this->repository->deleteUser($userId, $this->testTenantId);
        
        $this->assertTrue($result);
        
        // Verify deletion
        $user = $this->repository->findById($userId, $this->testTenantId);
        $this->assertFalse($user);
    }

    public function testAssignRole()
    {
        $userData = [
            'username' => 'test_user_' . time(),
            'password_hash' => password_hash('test123', PASSWORD_BCRYPT),
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'status' => 'ACTIVE',
            'created_by' => $this->testUserId
        ];

        $userId = $this->repository->createUser($userData);
        
        $result = $this->repository->assignRole($userId, 1); // Assuming role_id 1 exists
        
        $this->assertTrue($result);
        
        // Cleanup
        $this->repository->deleteUser($userId, $this->testTenantId);
    }

    public function testRemoveRole()
    {
        $userData = [
            'username' => 'test_user_' . time(),
            'password_hash' => password_hash('test123', PASSWORD_BCRYPT),
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'status' => 'ACTIVE',
            'created_by' => $this->testUserId
        ];

        $userId = $this->repository->createUser($userData);
        $this->repository->assignRole($userId, 1);
        
        $result = $this->repository->removeRole($userId, 1);
        
        $this->assertTrue($result);
        
        // Cleanup
        $this->repository->deleteUser($userId, $this->testTenantId);
    }
}
