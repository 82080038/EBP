<?php

class Response
{

    public static function json($data, $statusCode = 200)
    {

        http_response_code($statusCode);

        header("Content-Type: application/json");

        echo json_encode($data);

        exit;

    }



    public static function success($message, $data = [])
    {

        self::json([

            "success" => true,

            "message" => $message,

            "data" => $data

        ]);

    }



    public static function error($message, $errors = [])
    {

        self::json([

            "success" => false,

            "message" => $message,

            "errors" => $errors

        ], 400);

    }

}
