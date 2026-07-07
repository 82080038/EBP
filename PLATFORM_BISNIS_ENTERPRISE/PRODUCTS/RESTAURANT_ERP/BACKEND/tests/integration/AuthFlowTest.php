<?php

use PHPUnit\Framework\TestCase;
use Modules\Auth\Services\AuthService;
use Modules\Auth\Repositories\AuthRepository;
use Core\Database;

class AuthFlowTest extends TestCase
{
    private $authService;
    private $authRepository;
    private $db;
    private $testTenantId = 1;
    private $testUserId = 1;

    protected function setUp(): void
    {
        $this->db = Database::getInstance();
        $this->authRepository = new AuthRepository();
        $this->authService = new AuthService();
    }

    protected function tearDown(): void
    {
        // Cleanup test data if needed
    }

    /**
     * Test complete authentication flow:
     * 1. Login with valid credentials
     * 2. Validate token
     * 3. Get user permissions
     * 4. Logout
     */
    public function testCompleteAuthFlow()
    {
        // Step 1: Login
        $loginResult = $this->authService->login('admin', 'admin123');
        
        $this->assertIsArray($loginResult);
        $this->assertTrue($loginResult['success']);
        $this->assertArrayHasKey('token', $loginResult);
        $this->assertArrayHasKey('user', $loginResult);
        
        $token = $loginResult['token'];
        $user = $loginResult['user'];
        
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        $this->assertIsArray($user);
        $this->assertEquals('admin', $user['username']);
        
        // Step 2: Validate token
        $validationResult = $this->authService->validateToken($token);
        
        $this->assertIsArray($validationResult);
        $this->assertTrue($validationResult['success']);
        $this->assertArrayHasKey('payload', $validationResult);
        
        // Step 3: Get user permissions
        $permissions = $this->authService->getUserPermissions($user['user_id']);
        
        $this->assertIsArray($permissions);
        
        // Step 4: Logout (clear auth data)
        $this->authService->logout();
        
        // Verify token is no longer valid after logout
        $validationResult = $this->authService->validateToken($token);
        $this->assertFalse($validationResult['success']);
    }

    /**
     * Test user creation and authentication flow
     */
    public function testUserCreationAndAuthFlow()
    {
        // Step 1: Create a new user
        $userData = [
            'username' => 'test_user_' . time(),
            'password' => 'test123',
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'status' => 'ACTIVE'
        ];
        
        $createResult = $this->authService->createUser($userData, $this->testTenantId, $this->testUserId);
        
        $this->assertIsArray($createResult);
        $this->assertTrue($createResult['success']);
        $this->assertArrayHasKey('user_id', $createResult);
        
        $newUserId = $createResult['user_id'];
        
        // Step 2: Login with the new user
        $loginResult = $this->authService->login($userData['username'], $userData['password']);
        
        $this->assertIsArray($loginResult);
        $this->assertTrue($loginResult['success']);
        $this->assertArrayHasKey('token', $loginResult);
        
        // Step 3: Get user details
        $user = $this->authService->getUserById($newUserId, $this->testTenantId);
        
        $this->assertIsArray($user);
        $this->assertEquals($userData['username'], $user['username']);
        $this->assertEquals($userData['email'], $user['email']);
        
        // Step 4: Update user
        $updateData = [
            'full_name' => 'Updated Test User',
            'email' => 'updated@example.com'
        ];
        
        $updateResult = $this->authService->updateUser($newUserId, $updateData, $this->testTenantId, $this->testUserId);
        
        $this->assertIsArray($updateResult);
        $this->assertTrue($updateResult['success']);
        
        // Verify update
        $updatedUser = $this->authService->getUserById($newUserId, $this->testTenantId);
        $this->assertEquals('Updated Test User', $updatedUser['full_name']);
        
        // Step 5: Delete user
        $deleteResult = $this->authService->deleteUser($newUserId, $this->testTenantId, $this->testUserId);
        
        $this->assertIsArray($deleteResult);
        $this->assertTrue($deleteResult['success']);
        
        // Verify deletion
        $deletedUser = $this->authService->getUserById($newUserId, $this->testTenantId);
        $this->assertFalse($deletedUser);
    }

    /**
     * Test role assignment flow
     */
    public function testRoleAssignmentFlow()
    {
        // Step 1: Create a new user
        $userData = [
            'username' => 'test_user_' . time(),
            'password' => 'test123',
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'status' => 'ACTIVE'
        ];
        
        $createResult = $this->authService->createUser($userData, $this->testTenantId, $this->testUserId);
        $newUserId = $createResult['user_id'];
        
        // Step 2: Assign a role to the user
        $assignResult = $this->authRepository->assignRole($newUserId, 1); // Assuming role_id 1 exists
        
        $this->assertTrue($assignResult);
        
        // Step 3: Get user permissions (should include role permissions)
        $permissions = $this->authService->getUserPermissions($newUserId);
        
        $this->assertIsArray($permissions);
        
        // Step 4: Remove role from user
        $removeResult = $this->authRepository->removeRole($newUserId, 1);
        
        $this->assertTrue($removeResult);
        
        // Cleanup
        $this->authService->deleteUser($newUserId, $this->testTenantId, $this->testUserId);
    }

    /**
     * Test password change flow
     */
    public function testPasswordChangeFlow()
    {
        // Step 1: Create a new user
        $userData = [
            'username' => 'test_user_' . time(),
            'password' => 'oldpassword',
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'status' => 'ACTIVE'
        ];
        
        $createResult = $this->authService->createUser($userData, $this->testTenantId, $this->testUserId);
        $newUserId = $createResult['user_id'];
        
        // Step 2: Login with old password
        $loginResult = $this->authService->login($userData['username'], $userData['password']);
        $this->assertTrue($loginResult['success']);
        
        // Step 3: Change password
        $updateResult = $this->authService->updateUser($newUserId, ['password' => 'newpassword'], $this->testTenantId, $this->testUserId);
        $this->assertTrue($updateResult['success']);
        
        // Step 4: Login with new password
        $newLoginResult = $this->authService->login($userData['username'], 'newpassword');
        $this->assertTrue($newLoginResult['success']);
        
        // Step 5: Verify old password no longer works
        $oldLoginResult = $this->authService->login($userData['username'], $userData['password']);
        $this->assertFalse($oldLoginResult['success']);
        
        // Cleanup
        $this->authService->deleteUser($newUserId, $this->testTenantId, $this->testUserId);
    }
}
