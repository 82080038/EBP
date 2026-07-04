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
if (!class_exists('Messages')) {
    require_once __DIR__ . '/../../../core/Messages.php';
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
            return Response::error(Messages::USER_NOT_FOUND, 404);
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
            return Response::error(Messages::USER_USERNAME_REQUIRED, 400);
        }
        if (empty($data['email'])) {
            return Response::error(Messages::USER_EMAIL_REQUIRED, 400);
        }
        if (empty($data['password'])) {
            return Response::error(Messages::USER_PASSWORD_REQUIRED, 400);
        }
        if (empty($data['full_name'])) {
            return Response::error(Messages::USER_FULL_NAME_REQUIRED, 400);
        }

        $result = $this->userService->createUser($tenantId, $data);

        if ($result) {
            return Response::success(['message' => Messages::USER_CREATED]);
        }

        return Response::error(Messages::USER_FAILED_CREATE, 500);
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
            return Response::error(Messages::USER_ID_REQUIRED, 400);
        }
        if (empty($data['username'])) {
            return Response::error(Messages::USER_USERNAME_REQUIRED, 400);
        }
        if (empty($data['email'])) {
            return Response::error(Messages::USER_EMAIL_REQUIRED, 400);
        }
        if (empty($data['full_name'])) {
            return Response::error(Messages::USER_FULL_NAME_REQUIRED, 400);
        }

        $result = $this->userService->updateUser($tenantId, $userId, $data);

        if ($result) {
            return Response::success(['message' => Messages::USER_UPDATED]);
        }

        return Response::error(Messages::USER_FAILED_UPDATE, 500);
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
            return Response::error(Messages::USER_ID_REQUIRED, 400);
        }
        if (empty($data['old_password'])) {
            return Response::error(Messages::USER_OLD_PASSWORD_REQUIRED, 400);
        }
        if (empty($data['new_password'])) {
            return Response::error(Messages::USER_NEW_PASSWORD_REQUIRED, 400);
        }

        $result = $this->userService->changePassword($tenantId, $userId, $data['old_password'], $data['new_password']);

        if ($result) {
            return Response::success(['message' => Messages::USER_PASSWORD_CHANGED]);
        }

        return Response::error(Messages::USER_FAILED_PASSWORD_CHANGE, 500);
    }

    public function deleteUser(array $request)
    {
        $request = AuthMiddleware::handle($request);
        PermissionMiddleware::handle($request, 'USER_MANAGE');

        $tenantId = $request['tenant_id'] ?? 1;
        $userId = $request['user_id'] ?? 0;

        // Validation
        if (empty($userId)) {
            return Response::error(Messages::USER_ID_REQUIRED, 400);
        }

        $result = $this->userService->deleteUser($tenantId, $userId);

        if ($result) {
            return Response::success(['message' => Messages::USER_DELETED]);
        }

        return Response::error(Messages::USER_FAILED_DELETE, 500);
    }
}
