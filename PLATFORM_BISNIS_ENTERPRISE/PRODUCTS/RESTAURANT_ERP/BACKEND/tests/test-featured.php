<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../modules/Consumer/Controllers/ConsumerController.php';

$consumerController = new ConsumerController();
$result = $consumerController->getFeaturedRestaurants([]);
echo json_encode($result);
