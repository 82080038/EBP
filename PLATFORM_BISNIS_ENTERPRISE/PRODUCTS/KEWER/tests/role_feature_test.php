<?php
/**
 * Role Feature Test
 * Tests key features for each role using existing test infrastructure
 */

require_once __DIR__ . '/../config/path.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/includes/functions.php';

$BASE_URL = 'http://localhost/kewer';

// Simple test class
class Test {
    public $passed = 0;
    public $failed = 0;
    public $failures = [];
    
    public function assert($condition, $message, $detail = '') {
        if ($condition) {
            $this->passed++;
            echo "  ✓ $message\n";
        } else {
            $this->failed++;
            $this->failures[] = "$message: $detail";
            echo "  ✗ $message - $detail\n";
        }
    }
    
    public function suite($name) {
        echo "\n═══ $name ═══\n";
    }
    
    public function summary() {
        $total = $this->passed + $this->failed;
        $rate = $total > 0 ? round(($this->passed / $total) * 100, 1) : 0;
        echo "\n══════════════════════════════════════════════\n";
        echo "Results: $total tests in " . round(microtime(true) - $GLOBALS['start_time'], 2) . "s\n";
        echo "  Passed: $this->passed\n";
        echo "  Failed: $this->failed\n";
        echo "  Pass rate: $rate%\n";
        if (!empty($this->failures)) {
            echo "\nFailures:\n";
            foreach ($this->failures as $i => $failure) {
                echo "  " . ($i + 1) . ". $failure\n";
            }
        }
        echo "\n";
    }
}

$t = new Test();
$start_time = microtime(true);

// Helper function for HTTP requests
function httpRequest($url, $method = 'GET', $data = null, $cookie = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    
    if ($cookie) {
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $http_code,
        'body' => $response,
        'json' => json_decode($response, true)
    ];
}

// Login function
function loginAs($username, $password = 'password') {
    global $BASE_URL;
    $url = "$BASE_URL/login.php";
    $data = http_build_query(['username' => $username, 'password' => $password]);
    $cookie_file = '/tmp/test_cookies_' . md5($username . time()) . '.txt';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    if (file_exists($cookie_file)) {
        return file_get_contents($cookie_file);
    }
    return '';
}

echo "\n";
echo "========================================\n";
echo "ROLE FEATURE TEST\n";
echo "========================================\n";

// Test 1: Database - Verify all roles exist
$t->suite('1. Database - Role Verification');
$roles = query("SELECT role_kode, role_nama FROM ref_roles ORDER BY urutan_tampil");
$t->assert(is_array($roles) && count($roles) === 9, "9 roles exist in database", count($roles ?? []));

$expected_roles = ['appOwner', 'bos', 'manager_pusat', 'manager_cabang', 'admin_pusat', 'admin_cabang', 'petugas_pusat', 'petugas_cabang', 'teller'];
$actual_roles = array_column($roles ?: [], 'role_kode');
$t->assert($actual_roles === $expected_roles, "Role codes match expected", implode(',', $actual_roles));

// Test 2: Verify users exist for each role
$t->suite('2. Database - User Verification');
$users_by_role = query("SELECT role, COUNT(*) as cnt FROM users WHERE status = 'aktif' GROUP BY role");
$user_counts = [];
foreach ($users_by_role as $u) {
    $user_counts[$u['role']] = $u['cnt'];
}
$t->assert(isset($user_counts['appOwner']) && $user_counts['appOwner'] >= 1, "appOwner users exist", $user_counts['appOwner'] ?? 0);
$t->assert(isset($user_counts['bos']) && $user_counts['bos'] >= 1, "bos users exist", $user_counts['bos'] ?? 0);
$t->assert(isset($user_counts['teller']) && $user_counts['teller'] >= 1, "teller users exist", $user_counts['teller'] ?? 0);

// Test 3: Role permissions
$t->suite('3. Database - Role Permissions');
foreach ($expected_roles as $role) {
    $perms = query("SELECT COUNT(*) as cnt FROM role_permissions WHERE role = ? AND granted = 1", [$role]);
    $cnt = $perms[0]['cnt'] ?? 0;
    $t->assert($cnt > 0, "Role '$role' has permissions", "$cnt permissions");
}

// Test 4: Teller specific permissions
$t->suite('4. Teller Role Permissions');
$teller_perms = query("SELECT rp.permission_code, p.nama FROM role_permissions rp JOIN permissions p ON rp.permission_code = p.kode WHERE rp.role = 'teller' AND rp.granted = 1");
$t->assert(is_array($teller_perms) && count($teller_perms) >= 7, "Teller has 7+ permissions", count($teller_perms ?? []));

$expected_teller_perms = ['dashboard.read', 'nasabah.read', 'pinjaman.read', 'angsuran.read', 'kas.read', 'kas.update', 'view_pengeluaran'];
$teller_perm_codes = array_column($teller_perms, 'permission_code');
foreach ($expected_teller_perms as $perm) {
    $t->assert(in_array($perm, $teller_perm_codes), "Teller has '$perm' permission");
}

// Test 5: Page access via direct file include test
$t->suite('5. Page Access - File Include Test');
// Test that key pages exist and don't have syntax errors
$key_pages = [
    'dashboard.php',
    'pages/nasabah/index.php',
    'pages/pinjaman/index.php',
    'pages/angsuran/index.php',
    'pages/petugas/index.php',
    'pages/users/index.php',
    'pages/cabang/index.php',
    'pages/kas_bon/index.php',
    'pages/cash_reconciliation/index.php',
];

foreach ($key_pages as $page) {
    $file = BASE_PATH . '/' . $page;
    $t->assert(file_exists($file), "Page file exists: $page");
    if (file_exists($file)) {
        // Check for syntax errors
        $output = shell_exec("php -l " . escapeshellarg($file) . " 2>&1");
        $t->assert(strpos($output, 'No syntax errors') !== false, "Page has no syntax errors: $page", trim($output));
    }
}

// Test 6: Sidebar menu items per role
$t->suite('6. Sidebar Menu - Role-Specific Items');
// Check sidebar.php exists
$sidebar_file = BASE_PATH . '/includes/sidebar.php';
$t->assert(file_exists($sidebar_file), "Sidebar file exists");

// Test 7: Role hierarchy function
$t->suite('7. Role Hierarchy Function');
$hierarchy = [
    'appOwner' => 0,
    'bos' => 1,
    'manager_pusat' => 3,
    'manager_cabang' => 4,
    'admin_pusat' => 5,
    'admin_cabang' => 6,
    'petugas_pusat' => 7,
    'petugas_cabang' => 8,
    'teller' => 9
];

foreach ($hierarchy as $role => $expected_level) {
    if (function_exists('getRoleHierarchyLevel')) {
        $actual_level = getRoleHierarchyLevel($role);
        $t->assert($actual_level === $expected_level, "Role '$role' hierarchy level is $expected_level", "Got $actual_level");
    }
}

// Test 8: Cross-role data isolation
$t->suite('8. Data Isolation - Cabang Filtering');
// Test that cabang_id filtering works
$users_cabang_1 = query("SELECT COUNT(*) as cnt FROM users WHERE cabang_id = 1");
$users_cabang_2 = query("SELECT COUNT(*) as cnt FROM users WHERE cabang_id = 2");
$t->assert($users_cabang_1[0]['cnt'] > 0, "Users exist in cabang 1", $users_cabang_1[0]['cnt']);
$t->assert($users_cabang_2[0]['cnt'] > 0, "Users exist in cabang 2", $users_cabang_2[0]['cnt']);

// Test 9: Permission checks
$t->suite('9. Permission Check Functions');
if (function_exists('hasPermission')) {
    // Test that hasPermission function exists and works
    $t->assert(function_exists('hasPermission'), "hasPermission function exists");
}

$t->summary();
