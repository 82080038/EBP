<?php

if (!class_exists('UserService')) {
    require_once __DIR__ . '/../Services/UserService.php';
}
if (!class_exists('AuthMiddleware')) {
    require_once __DIR__ . '/../../../core/Middleware/AuthMiddleware.php';
}
if (!class_exists('PermissionMiddleware')) {
    require_once __DIR__ . '/../../../core/Middleware/PermissionMiddleware.php';
}
if (!class_exists('Response')) {
    require_once __DIR__ . '/../../../core/Response.php';
}

class UserController
{
    private $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function getUsers(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'USER_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $branchId = $request['branch_id'] ?? null;
        $users = $this->userService->getAllUsers($tenantId, $branchId);

        return Response::success($users);
    }

    public function getUser(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'USER_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $userId = $request['user_id'] ?? 0;

        $user = $this->userService->getUser($tenantId, $userId);

        if (!$user) {
            return Response::error('User not found', 404);
        }

        return Response::success($user);
    }

    public function createUser(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'USER_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($data['username'])) {
            return Response::error('Username is required', 400);
        }
        if (empty($data['email'])) {
            return Response::error('Email is required', 400);
        }
        if (empty($data['password'])) {
            return Response::error('Password is required', 400);
        }
        if (empty($data['full_name'])) {
            return Response::error('Full name is required', 400);
        }

        $result = $this->userService->createUser($tenantId, $data);

        if ($result) {
            return Response::success(['message' => 'User created successfully']);
        }

        return Response::error('Failed to create user or username/email already exists', 500);
    }

    public function updateUser(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'USER_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $userId = $request['user_id'] ?? 0;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($userId)) {
            return Response::error('User ID is required', 400);
        }
        if (empty($data['username'])) {
            return Response::error('Username is required', 400);
        }
        if (empty($data['email'])) {
            return Response::error('Email is required', 400);
        }
        if (empty($data['full_name'])) {
            return Response::error('Full name is required', 400);
        }

        $result = $this->userService->updateUser($tenantId, $userId, $data);

        if ($result) {
            return Response::success(['message' => 'User updated successfully']);
        }

        return Response::error('Failed to update user', 500);
    }

    public function changePassword(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'USER_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $userId = $request['user_id'] ?? 0;
        $data = $request['body'] ?? [];

        // Validation
        if (empty($userId)) {
            return Response::error('User ID is required', 400);
        }
        if (empty($data['old_password'])) {
            return Response::error('Old password is required', 400);
        }
        if (empty($data['new_password'])) {
            return Response::error('New password is required', 400);
        }

        $result = $this->userService->changePassword($tenantId, $userId, $data['old_password'], $data['new_password']);

        if ($result) {
            return Response::success(['message' => 'Password changed successfully']);
        }

        return Response::error('Failed to change password or old password is incorrect', 500);
    }

    public function deleteUser(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'USER_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $userId = $request['user_id'] ?? 0;

        // Validation
        if (empty($userId)) {
            return Response::error('User ID is required', 400);
        }

        $result = $this->userService->deleteUser($tenantId, $userId);

        if ($result) {
            return Response::success(['message' => 'User deleted successfully']);
        }

        return Response::error('Failed to delete user', 500);
    }
}
