<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../modules/Consumer/Controllers/ConsumerController.php';

$consumerController = new ConsumerController();
$request = ['body' => ['email' => 'consumer1@example.com', 'password' => 'password']];
$result = $consumerController->login($request);
echo json_encode($result);
