<?php
/**
 * Comprehensive Role Integration Test
 * Tests all features for each role, including cross-role integration
 */

require_once __DIR__ . '/../config/path.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/includes/functions.php';

// Test configuration
$BASE_URL = 'http://localhost/kewer';
$TEST_PASSWORD = 'password'; // Default password for test users

// Test users by role
$test_users = [
    'appOwner' => [
        'username' => 'appowner',
        'password' => 'AppOwner2024!',
        'expected_pages' => [
            'pages/app_owner/dashboard.php',
            'pages/app_owner/approvals.php',
            'pages/app_owner/koperasi.php',
            'pages/app_owner/billing.php',
            'pages/app_owner/usage.php',
            'pages/app_owner/ai_advisor.php',
            'pages/app_owner/settings.php',
        ],
        'blocked_pages' => [
            'pages/nasabah/index.php',
            'pages/pinjaman/index.php',
            'pages/angsuran/index.php',
            'pages/petugas/index.php',
            'pages/users/index.php',
        ]
    ],
    'bos' => [
        'username' => 'patri',
        'password' => $TEST_PASSWORD,
        'expected_pages' => [
            'dashboard.php',
            'pages/nasabah/index.php',
            'pages/pinjaman/index.php',
            'pages/angsuran/index.php',
            'pages/petugas/index.php',
            'pages/users/index.php',
            'pages/cabang/index.php',
            'pages/kas_bon/index.php',
            'pages/pengeluaran/index.php',
            'pages/laporan/index.php',
            'pages/bos/delegated_permissions.php',
        ],
        'blocked_pages' => [
            'pages/app_owner/dashboard.php',
            'pages/app_owner/approvals.php',
            'pages/superadmin/bos_approvals.php',
        ]
    ],
    'manager_pusat' => [
        'username' => 'mgr_pusat',
        'password' => $TEST_PASSWORD,
        'expected_pages' => [
            'dashboard.php',
            'pages/nasabah/index.php',
            'pages/pinjaman/index.php',
            'pages/angsuran/index.php',
            'pages/petugas/index.php',
            'pages/users/index.php',
            'pages/cabang/index.php',
            'pages/kas_bon/index.php',
            'pages/pengeluaran/index.php',
            'pages/laporan/index.php',
        ],
        'blocked_pages' => [
            'pages/app_owner/dashboard.php',
            'pages/bos/delegated_permissions.php',
        ]
    ],
    'manager_cabang' => [
        'username' => 'mgr_balige',
        'password' => $TEST_PASSWORD,
        'expected_pages' => [
            'dashboard.php',
            'pages/nasabah/index.php',
            'pages/pinjaman/index.php',
            'pages/angsuran/index.php',
            'pages/petugas/index.php',
            'pages/users/index.php',
            'pages/cabang/index.php',
            'pages/kas_bon/index.php',
            'pages/pengeluaran/index.php',
            'pages/laporan/index.php',
        ],
        'blocked_pages' => [
            'pages/app_owner/dashboard.php',
            'pages/bos/delegated_permissions.php',
        ]
    ],
    'admin_pusat' => [
        'username' => 'adm_pusat',
        'password' => $TEST_PASSWORD,
        'expected_pages' => [
            'dashboard.php',
            'pages/nasabah/index.php',
            'pages/pinjaman/index.php',
            'pages/angsuran/index.php',
            'pages/petugas/index.php',
            'pages/users/index.php',
            'pages/cabang/index.php',
            'pages/kas_bon/index.php',
            'pages/pengeluaran/index.php',
            'pages/laporan/index.php',
        ],
        'blocked_pages' => [
            'pages/app_owner/dashboard.php',
            'pages/bos/delegated_permissions.php',
        ]
    ],
    'admin_cabang' => [
        'username' => 'adm_balige',
        'password' => $TEST_PASSWORD,
        'expected_pages' => [
            'dashboard.php',
            'pages/nasabah/index.php',
            'pages/pinjaman/index.php',
            'pages/angsuran/index.php',
            'pages/petugas/index.php',
            'pages/users/index.php',
            'pages/cabang/index.php',
            'pages/kas_bon/index.php',
            'pages/pengeluaran/index.php',
            'pages/laporan/index.php',
        ],
        'blocked_pages' => [
            'pages/app_owner/dashboard.php',
            'pages/bos/delegated_permissions.php',
        ]
    ],
    'petugas_pusat' => [
        'username' => 'ptr_pusat',
        'password' => $TEST_PASSWORD,
        'expected_pages' => [
            'dashboard.php',
            'pages/nasabah/index.php',
            'pages/pinjaman/index.php',
            'pages/angsuran/index.php',
            'pages/petugas/transaksi.php',
            'pages/laporan/index.php',
        ],
        'blocked_pages' => [
            'pages/app_owner/dashboard.php',
            'pages/petugas/index.php',
            'pages/users/index.php',
            'pages/cabang/index.php',
            'pages/kas_bon/index.php',
            'pages/pengeluaran/index.php',
            'pages/bos/delegated_permissions.php',
        ]
    ],
    'petugas_cabang' => [
        'username' => 'ptr_balige',
        'password' => $TEST_PASSWORD,
        'expected_pages' => [
            'dashboard.php',
            'pages/nasabah/index.php',
            'pages/pinjaman/index.php',
            'pages/angsuran/index.php',
            'pages/petugas/transaksi.php',
            'pages/laporan/index.php',
        ],
        'blocked_pages' => [
            'pages/app_owner/dashboard.php',
            'pages/petugas/index.php',
            'pages/users/index.php',
            'pages/cabang/index.php',
            'pages/kas_bon/index.php',
            'pages/pengeluaran/index.php',
            'pages/bos/delegated_permissions.php',
        ]
    ],
    'teller' => [
        'username' => 'krw_pusat',
        'password' => $TEST_PASSWORD,
        'expected_pages' => [
            'dashboard.php',
            'pages/nasabah/index.php',
            'pages/pinjaman/index.php',
            'pages/angsuran/index.php',
            'pages/cash_reconciliation/index.php',
            'pages/laporan/index.php',
        ],
        'blocked_pages' => [
            'pages/app_owner/dashboard.php',
            'pages/petugas/index.php',
            'pages/petugas/transaksi.php',
            'pages/users/index.php',
            'pages/cabang/index.php',
            'pages/kas_bon/index.php',
            'pages/pengeluaran/index.php',
            'pages/bos/delegated_permissions.php',
        ]
    ]
];

// Test results
$results = [];
$total_tests = 0;
$passed_tests = 0;
$failed_tests = 0;

// Helper function to test page access using existing test infrastructure
function testPageAccess($username, $password, $page, $expected_access) {
    global $BASE_URL, $total_tests, $passed_tests, $failed_tests;
    
    $total_tests++;
    
    // Use the existing httpRequest function from comprehensive_test.php
    // First login to get cookie
    $login_url = $BASE_URL . '/login.php';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $login_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'username' => $username,
        'password' => $password
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/test_cookies.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/test_cookies.txt');
    $login_response = curl_exec($ch);
    curl_close($ch);
    
    // Now test the page
    $page_url = $BASE_URL . '/' . $page;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $page_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/test_cookies.txt');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    @unlink('/tmp/test_cookies.txt');
    
    // Check if redirected (blocked) or not (access)
    // HTTP 302/303 = redirect = blocked access
    // HTTP 200 = no redirect = has access (unless it's the login page)
    $is_redirect = ($http_code == 302 || $http_code == 303);
    
    // Extract the Location header to see where it redirected
    preg_match('/Location: (.*)/i', $response, $matches);
    $redirect_location = isset($matches[1]) ? trim($matches[1]) : '';
    
    // If redirected to the same page or different page, it's blocked
    // If HTTP 200 and no redirect, it's access
    $has_access = (!$is_redirect && $http_code == 200);
    
    if ($has_access == $expected_access) {
        $passed_tests++;
        return ['passed' => true, 'message' => 'OK'];
    } else {
        $failed_tests++;
        return [
            'passed' => false,
            'message' => "Expected " . ($expected_access ? 'access' : 'blocked') . ", got " . ($has_access ? 'access' : 'blocked') . " (HTTP $http_code)"
        ];
    }
}

echo "\n";
echo "========================================\n";
echo "COMPREHENSIVE ROLE INTEGRATION TEST\n";
echo "========================================\n";
echo "\n";

foreach ($test_users as $role => $user_config) {
    echo "\n";
    echo "----------------------------------------\n";
    echo "TESTING ROLE: $role\n";
    echo "User: {$user_config['username']}\n";
    echo "----------------------------------------\n";
    
    $results[$role] = [
        'expected_pages' => [],
        'blocked_pages' => []
    ];
    
    // Test expected pages (should have access)
    echo "\n[Expected Access]\n";
    foreach ($user_config['expected_pages'] as $page) {
        $result = testPageAccess($user_config['username'], $user_config['password'], $page, true);
        $results[$role]['expected_pages'][$page] = $result;
        $status = $result['passed'] ? '✓' : '✗';
        echo "  $status $page - {$result['message']}\n";
    }
    
    // Test blocked pages (should be blocked)
    echo "\n[Expected Blocked]\n";
    foreach ($user_config['blocked_pages'] as $page) {
        $result = testPageAccess($user_config['username'], $user_config['password'], $page, false);
        $results[$role]['blocked_pages'][$page] = $result;
        $status = $result['passed'] ? '✓' : '✗';
        echo "  $status $page - {$result['message']}\n";
    }
}

// Summary
echo "\n";
echo "========================================\n";
echo "TEST SUMMARY\n";
echo "========================================\n";
echo "Total Tests: $total_tests\n";
echo "Passed: $passed_tests\n";
echo "Failed: $failed_tests\n";
$pass_rate = $total_tests > 0 ? round(($passed_tests / $total_tests) * 100, 1) : 0;
echo "Pass Rate: $pass_rate%\n";
echo "\n";

// Detailed results by role
foreach ($results as $role => $role_results) {
    $role_expected_passed = count(array_filter($role_results['expected_pages'], fn($r) => $r['passed']));
    $role_expected_total = count($role_results['expected_pages']);
    $role_blocked_passed = count(array_filter($role_results['blocked_pages'], fn($r) => $r['passed']));
    $role_blocked_total = count($role_results['blocked_pages']);
    
    echo "[$role]\n";
    echo "  Expected Access: $role_expected_passed/$role_expected_total passed\n";
    echo "  Expected Blocked: $role_blocked_passed/$role_blocked_total passed\n";
    
    if ($role_expected_passed < $role_expected_total || $role_blocked_passed < $role_blocked_total) {
        echo "  FAILED PAGES:\n";
        foreach ($role_results['expected_pages'] as $page => $result) {
            if (!$result['passed']) {
                echo "    - $page: {$result['message']}\n";
            }
        }
        foreach ($role_results['blocked_pages'] as $page => $result) {
            if (!$result['passed']) {
                echo "    - $page: {$result['message']}\n";
            }
        }
    }
    echo "\n";
}

echo "\n";
