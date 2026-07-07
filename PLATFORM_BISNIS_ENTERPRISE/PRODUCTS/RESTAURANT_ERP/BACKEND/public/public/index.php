<?php


require_once 
"../config/database.php";

require_once 
"../core/Response.php";

require_once 
"../core/Router.php";

require_once 
"../routes/api.php";


header("Access-Control-Allow-Origin: *");

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Screen-Width, X-Screen-Height, X-Device-Type, x-screen-size, x-screen-width, x-screen-height, x-device-type");


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {

    http_response_code(200);

    exit;

}
