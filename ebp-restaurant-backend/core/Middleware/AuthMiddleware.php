<?php

require_once __DIR__ . '/../JWT.php';



class AuthMiddleware
{

    private $jwt;



    public function __construct()
    {

        $this->jwt = new JWT();

    }



    public function authenticate()
    {

        $headers = getallheaders();



        if (!isset($headers['Authorization'])) {

            Response::error("Authorization header missing");

        }



        $token = str_replace('Bearer ', '', $headers['Authorization']);



        $payload = $this->jwt->decode($token);



        if (!$payload) {

            Response::error("Invalid or expired token");

        }



        return $payload;

    }

    public static function handle($request)
    {
        $middleware = new self();
        $payload = $middleware->authenticate();
        // Set user_id in request for PermissionMiddleware
        $request['user_id'] = $payload['user_id'];
        $request['tenant_id'] = $payload['tenant_id'];
        $request['branch_id'] = $payload['branch_id'];
        return $request;
    }

}
