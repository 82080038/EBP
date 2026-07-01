<?php

class Router
{

    private $routes = [];


    public function add($method, $path, $handler)
    {

        $this->routes[] = [

            'method' => $method,

            'path' => $path,

            'handler' => $handler

        ];

    }



    public function dispatch()
    {

        $method = $_SERVER['REQUEST_METHOD'];

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);



        foreach ($this->routes as $route) {

            if ($route['method'] === $method && $route['path'] === $uri) {

                call_user_func($route['handler']);

                return;

            }

        }



        Response::error("Route not found");

    }

}
