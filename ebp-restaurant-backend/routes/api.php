<?php


require_once 
"../modules/Sales/Controllers/OrderController.php";

require_once 
"../modules/Auth/Controllers/AuthController.php";


$method = $_SERVER['REQUEST_METHOD'];

$url = $_SERVER['REQUEST_URI'];



if(
    $method=="POST" &&
    $url=="/api/v1/auth/login"
){

    $controller =
        new AuthController();


    $controller->login();


}



if(
    $method=="POST" &&
    $url=="/api/v1/orders"
){

    $controller =
        new OrderController();


    $controller->create();


}
