<?php

if (!class_exists('Response')) {
    require_once __DIR__ . '/../Response.php';
}
if (!class_exists('database')) {
    require_once __DIR__ . '/../../config/database.php';
}

class PermissionMiddleware
{

    public function check($userId, $permission)
    {

        $database = new Database();

        $db = $database->connect();



        $sql = "
            SELECT COUNT(*) as count
            FROM user_roles ur
            INNER JOIN role_permissions rp ON ur.role_id = rp.role_id
            INNER JOIN permissions p ON rp.permission_id = p.permission_id
            WHERE ur.user_id = ? AND p.permission_code = ?
        ";



        $stmt = $db->prepare($sql);

        $stmt->execute([$userId, $permission]);



        $result = $stmt->fetch(PDO::FETCH_ASSOC);



        if ($result['count'] == 0) {

            return false;

        }



        return true;

    }

    public static function handle($request, $permission)
    {
        $middleware = new self();
        // Get user_id from request (should be set by AuthMiddleware)
        $userId = $request['user_id'] ?? null;
        if (!$userId) {
            Response::error("User not authenticated");
        }
        return $middleware->check($userId, $permission);
    }

}
