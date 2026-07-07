<?php

/**
 * Test API endpoints with different simulation users
 * Verifies permission-based access control across all roles
 */

$baseUrl = 'http://localhost:8000/api/v1';

echo "========================================\n";
echo "API Simulation Testing\n";
echo "========================================\n\n";

// Test users with their expected permissions
$testUsers = [
    'sim_platform_owner' => 'Platform Owner - Should have platform-level access',
    'sim_admin' => 'Administrator - Should have full tenant access',
    'sim_manager' => 'Manager - Should have management access',
    'sim_kasir' => 'Kasir - Should have order/payment access',
    'sim_koki' => 'Koki - Should have kitchen access',
    'sim_waiter' => 'Waiter - Should have table/order access',
    'sim_stok' => 'Stok - Should have inventory access'
];

$password = 'Sim123456';

// Test endpoints and expected access
$testEndpoints = [
    [
        'name' => 'Login',
        'method' => 'POST',
        'endpoint' => '/auth/login',
        'auth_required' => false,
        'data' => null
    ],
    [
        'name' => 'Get Orders',
        'method' => 'GET',
        'endpoint' => '/orders',
        'auth_required' => true,
        'data' => null
    ],
    [
        'name' => 'Create Order',
        'method' => 'POST',
        'endpoint' => '/orders',
        'auth_required' => true,
        'data' => [
            'order_type' => 'TAKE_AWAY',
            'items' => [
                [
                    'product_id' => 1,
                    'qty' => 1,
                    'price' => 30000
                ]
            ]
        ]
    ],
    [
        'name' => 'Get Menu Products',
        'method' => 'GET',
        'endpoint' => '/menu/products',
        'auth_required' => true,
        'data' => null
    ],
    [
        'name' => 'Get Tables',
        'method' => 'GET',
        'endpoint' => '/tables',
        'auth_required' => true,
        'data' => null
    ],
    [
        'name' => 'Get Inventory',
        'method' => 'GET',
        'endpoint' => '/public/inventory',
        'auth_required' => false,
        'data' => null
    ]
];

$results = [];

foreach ($testUsers as $username => $description) {
    echo "Testing User: $username\n";
    echo "Description: $description\n";
    echo str_repeat("-", 60) . "\n";
    
    // Login to get token
    $ch = curl_init($baseUrl . '/auth/login');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'username' => $username,
        'password' => $password
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $loginResult = json_decode($response, true);
    $token = null;
    
    if ($httpCode === 200 && isset($loginResult['data']['access_token'])) {
        $token = $loginResult['data']['access_token'];
        echo "✓ Login successful\n";
    } else {
        echo "✗ Login failed: " . ($loginResult['message'] ?? 'Unknown error') . "\n";
        echo "\n";
        continue;
    }
    
    // Test each endpoint
    foreach ($testEndpoints as $endpoint) {
        if ($endpoint['name'] === 'Login') continue; // Skip login, already tested
        
        $ch = curl_init($baseUrl . $endpoint['endpoint']);
        
        if ($endpoint['method'] === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($endpoint['data']));
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $headers = ['Content-Type: application/json'];
        if ($endpoint['auth_required'] && $token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        $status = ($httpCode === 200 || $httpCode === 201) ? '✓' : '✗';
        $message = $result['message'] ?? 'Success';
        
        echo "  {$status} {$endpoint['name']}: HTTP {$httpCode} - {$message}\n";
        
        $results[$username][$endpoint['name']] = [
            'http_code' => $httpCode,
            'success' => ($httpCode === 200 || $httpCode === 201),
            'message' => $message
        ];
    }
    
    echo "\n";
}

// Summary
echo "========================================\n";
echo "Simulation Test Summary\n";
echo "========================================\n\n";

$totalTests = 0;
$passedTests = 0;

foreach ($results as $username => $userResults) {
    echo "User: $username\n";
    foreach ($userResults as $endpoint => $result) {
        $totalTests++;
        if ($result['success']) {
            $passedTests++;
            echo "  ✓ {$endpoint}\n";
        } else {
            echo "  ✗ {$endpoint} (HTTP {$result['http_code']})\n";
        }
    }
    echo "\n";
}

echo "Total Tests: $totalTests\n";
echo "Passed: $passedTests\n";
echo "Failed: " . ($totalTests - $passedTests) . "\n";
echo "Success Rate: " . round(($passedTests / $totalTests) * 100, 2) . "%\n";

echo "\n========================================\n";
echo "Simulation API Testing Complete\n";
echo "========================================\n";
