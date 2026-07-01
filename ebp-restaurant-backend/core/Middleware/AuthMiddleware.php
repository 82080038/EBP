<?php

require_once "../JWT.php";



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

}
