<?php

/**
 * Loyalty Routes
 * 
 * @package EBP\Modules\Loyalty
 * @version 1.0.0
 */

// Require controller
require_once __DIR__ . '/../Controllers/LoyaltyController.php';

$controller = new LoyaltyController();

// ==================== Loyalty Points Routes ====================

// Get all points
$router->get('/api/v1/loyalty/points', function() use ($controller) {
    AuthMiddleware::check();
    PermissionMiddleware::check('LOYALTY_MANAGE');
    $controller->getPoints($_REQUEST);
});

// Award points to customer
$router->post('/api/v1/loyalty/points/award', function() use ($controller) {
    AuthMiddleware::check();
    PermissionMiddleware::check('LOYALTY_MANAGE');
    $controller->awardPoints($_REQUEST);
});

// Redeem points
$router->post('/api/v1/loyalty/points/redeem', function() use ($controller) {
    AuthMiddleware::check();
    PermissionMiddleware::check('LOYALTY_MANAGE');
    $controller->redeemPoints($_REQUEST);
});

// ==================== Loyalty Rewards Routes ====================

// Get all rewards
$router->get('/api/v1/loyalty/rewards', function() use ($controller) {
    AuthMiddleware::check();
    PermissionMiddleware::check('LOYALTY_VIEW');
    $controller->getRewards($_REQUEST);
});

// Get specific reward
$router->get('/api/v1/loyalty/rewards/:id', function($id) use ($controller) {
    AuthMiddleware::check();
    PermissionMiddleware::check('LOYALTY_VIEW');
    $_REQUEST['reward_id'] = $id;
    $controller->getReward($_REQUEST);
});

// Create new reward
$router->post('/api/v1/loyalty/rewards', function() use ($controller) {
    AuthMiddleware::check();
    PermissionMiddleware::check('LOYALTY_MANAGE');
    $controller->createReward($_REQUEST);
});

// Update reward
$router->put('/api/v1/loyalty/rewards/:id', function($id) use ($controller) {
    AuthMiddleware::check();
    PermissionMiddleware::check('LOYALTY_MANAGE');
    $_REQUEST['reward_id'] = $id;
    $controller->updateReward($_REQUEST);
});

// Delete reward
$router->delete('/api/v1/loyalty/rewards/:id', function($id) use ($controller) {
    AuthMiddleware::check();
    PermissionMiddleware::check('LOYALTY_MANAGE');
    $_REQUEST['reward_id'] = $id;
    $controller->deleteReward($_REQUEST);
});

// Redeem reward
$router->post('/api/v1/loyalty/rewards/:id/redeem', function($id) use ($controller) {
    AuthMiddleware::check();
    PermissionMiddleware::check('LOYALTY_MANAGE');
    $_REQUEST['reward_id'] = $id;
    $controller->redeemReward($_REQUEST);
});

// ==================== Customer Loyalty Routes ====================

// Get all customer loyalty
$router->get('/api/v1/loyalty/customers', function() use ($controller) {
    AuthMiddleware::check();
    PermissionMiddleware::check('LOYALTY_VIEW');
    $controller->getCustomerLoyalty($_REQUEST);
});

// Get customer loyalty by customer ID
$router->get('/api/v1/loyalty/customers/:id', function($id) use ($controller) {
    AuthMiddleware::check();
    PermissionMiddleware::check('LOYALTY_VIEW');
    $_REQUEST['customer_id'] = $id;
    $controller->getCustomerLoyaltyByCustomer($_REQUEST);
});

// Enroll customer in loyalty program
$router->post('/api/v1/loyalty/customers/enroll', function() use ($controller) {
    AuthMiddleware::check();
    PermissionMiddleware::check('LOYALTY_MANAGE');
    $controller->enrollCustomer($_REQUEST);
});

// Get top customers by points
$router->get('/api/v1/loyalty/customers/top', function() use ($controller) {
    AuthMiddleware::check();
    PermissionMiddleware::check('LOYALTY_VIEW');
    $controller->getTopCustomers($_REQUEST);
});

// Get customers by tier
$router->get('/api/v1/loyalty/customers/tier/:tier', function($tier) use ($controller) {
    AuthMiddleware::check();
    PermissionMiddleware::check('LOYALTY_VIEW');
    $_REQUEST['tier'] = $tier;
    $controller->getCustomersByTier($_REQUEST);
});
