<?php

if (!class_exists('Database')) {
    if (!class_exists('database')) {
    require_once __DIR__ . '/../../../config/database.php';
}
}
if (!class_exists('JWT')) {
    if (!class_exists('JWT')) {
    require_once __DIR__ . '/../../../core/JWT.php';
}
}
if (!class_exists('Response')) {
    if (!class_exists('Response')) {
    require_once __DIR__ . '/../../../core/Response.php';
}
}



class AuthController
{


    public function login()
    {

        $input = json_decode(
            file_get_contents("php://input"),
            true
        );



        $database = new Database();

        $db = $database->connect();



        $sql = "
            SELECT u.user_id, u.username, u.password, u.tenant_id, u.branch_id, r.role_name
            FROM users u
            INNER JOIN user_roles ur ON u.user_id = ur.user_id
            INNER JOIN roles r ON ur.role_id = r.role_id
            WHERE u.username = ? AND u.status = 'ACTIVE'
        ";



        $stmt = $db->prepare($sql);

        $stmt->execute([$input['username']]);



        $user = $stmt->fetch(PDO::FETCH_ASSOC);



        if (!$user || !password_verify($input['password'], $user['password'])) {

            Response::error("Invalid credentials");

        }



        $jwt = new JWT();



        $payload = [

            'user_id' => $user['user_id'],

            'username' => $user['username'],

            'tenant_id' => $user['tenant_id'],

            'branch_id' => $user['branch_id'],

            'role' => $user['role_name'],

            'exp' => time() + (60 * 60 * 8)

        ];



        $token = $jwt->encode($payload);



        Response::success([

            'access_token' => $token,

            'user' => [

                'id' => $user['user_id'],

                'username' => $user['username'],

                'role' => $user['role_name']

            ]

        ]);

    }

}
