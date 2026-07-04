<?php
/**
 * Koperasi Data Isolation Helper
 * 
 * Helper functions untuk mengisolasi data antar koperasi
 * Mencegah data koperasi satu dengan koperasi lain tercampur
 * 
 * Multi-Tenancy Architecture for Kewer Application
 */

/**
 * Get koperasi ID for current logged in user
 * @return int|null Koperasi ID atau null jika tidak ada
 */
function getCurrentKoperasiId() {
    if (!isLoggedIn()) {
        return null;
    }
    
    // Priority: Session > Database > Default
    if (isset($_SESSION['koperasi_id']) && !empty($_SESSION['koperasi_id'])) {
        return (int)$_SESSION['koperasi_id'];
    }
    
    // If not in session, get from database
    if (isset($_SESSION['user_id'])) {
        $user = query("SELECT koperasi_id FROM users WHERE id = ?", [$_SESSION['user_id']]);
        if ($user && !empty($user[0]['koperasi_id'])) {
            $_SESSION['koperasi_id'] = (int)$user[0]['koperasi_id'];
            return (int)$user[0]['koperasi_id'];
        }
    }
    
    return null;
}

/**
 * Get bos ID for current logged in user
 * @return int|null Bos ID atau null
 */
function getCurrentBosId() {
    if (!isLoggedIn()) {
        return null;
    }
    
    // If user is bos, return their own ID
    if ($_SESSION['role'] === 'bos' && isset($_SESSION['user_id'])) {
        return (int)$_SESSION['user_id'];
    }
    
    // Get from session
    if (isset($_SESSION['owner_bos_id']) && !empty($_SESSION['owner_bos_id'])) {
        return (int)$_SESSION['owner_bos_id'];
    }
    
    // Get from database
    if (isset($_SESSION['user_id'])) {
        $user = query("SELECT owner_bos_id FROM users WHERE id = ?", [$_SESSION['user_id']]);
        if ($user && !empty($user[0]['owner_bos_id'])) {
            $_SESSION['owner_bos_id'] = (int)$user[0]['owner_bos_id'];
            return (int)$user[0]['owner_bos_id'];
        }
    }
    
    return null;
}

/**
 * Check if user has access to specific koperasi data
 * @param int $koperasi_id Koperasi ID to check
 * @return bool True if authorized, false otherwise
 */
function isAuthorizedKoperasi($koperasi_id) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $current_koperasi_id = getCurrentKoperasiId();
    $current_bos_id = getCurrentBosId();
    $role = $_SESSION['role'];
    
    // appOwner has access to all koperasi
    if ($role === 'appOwner') {
        return true;
    }
    
    // Bos only has access to their own koperasi
    if ($role === 'bos') {
        return $current_koperasi_id === (int)$koperasi_id;
    }
    
    // Manager, Admin, Petugas only has access to their assigned koperasi
    if (in_array($role, ['manager_pusat', 'manager_cabang', 'admin_pusat', 'admin_cabang', 'petugas_pusat', 'petugas_cabang', 'karyawan'])) {
        return $current_koperasi_id === (int)$koperasi_id;
    }
    
    // Nasabah only has access to their own koperasi
    if ($role === 'nasabah') {
        return $current_koperasi_id === (int)$koperasi_id;
    }
    
    return false;
}

/**
 * Get SQL WHERE clause for koperasi isolation
 * @param string $table_alias Table alias (e.g., 'n' for nasabah)
 * @return string SQL WHERE clause
 */
function getKoperasiWhereClause($table_alias = '') {
    $koperasi_id = getCurrentKoperasiId();
    $role = $_SESSION['role'] ?? null;
    
    // appOwner sees all data
    if ($role === 'appOwner') {
        return '';
    }
    
    $prefix = $table_alias ? "$table_alias." : '';
    
    if ($koperasi_id) {
        return " AND {$prefix}koperasi_id = $koperasi_id";
    }
    
    // If no koperasi_id, use owner_bos_id as fallback
    $bos_id = getCurrentBosId();
    if ($bos_id) {
        return " AND {$prefix}owner_bos_id = $bos_id";
    }
    
    // Fallback: no data
    return " AND 1=0";
}

/**
 * Add koperasi filter to existing SQL query
 * @param string $sql Existing SQL query
 * @param string $table_alias Table alias
 * @return string Modified SQL with koperasi filter
 */
function addKoperasiFilter($sql, $table_alias = '') {
    $where_clause = getKoperasiWhereClause($table_alias);
    
    if (empty($where_clause)) {
        return $sql;
    }
    
    // Check if SQL already has WHERE clause
    if (stripos($sql, 'WHERE') !== false) {
        // Add to existing WHERE
        return $sql . $where_clause;
    } else {
        // Add new WHERE clause before ORDER BY, GROUP BY, or LIMIT
        $pattern = '/\s+(ORDER\s+BY|GROUP\s+BY|LIMIT|HAVING)/i';
        if (preg_match($pattern, $sql, $matches, PREG_OFFSET_CAPTURE)) {
            $pos = $matches[0][1];
            return substr($sql, 0, $pos) . " WHERE 1=1 $where_clause " . substr($sql, $pos);
        }
        return $sql . " WHERE 1=1 $where_clause";
    }
}

/**
 * Validate koperasi access and redirect if not authorized
 * @param int $koperasi_id Koperasi ID to validate
 * @param string $redirect_url URL to redirect if not authorized
 */
function requireKoperasiAccess($koperasi_id, $redirect_url = 'pages/dashboard.php') {
    if (!isAuthorizedKoperasi($koperasi_id)) {
        $_SESSION['error'] = 'Anda tidak memiliki akses ke data koperasi ini.';
        header('Location: ' . baseUrl($redirect_url));
        exit();
    }
}

/**
 * Set koperasi context for current session
 * @param int $koperasi_id Koperasi ID
 * @return bool Success
 */
function setKoperasiContext($koperasi_id) {
    if (!isAuthorizedKoperasi($koperasi_id)) {
        return false;
    }
    
    $_SESSION['koperasi_id'] = (int)$koperasi_id;
    
    // Get koperasi details
    $koperasi = query("SELECT * FROM koperasi_master WHERE id = ?", [$koperasi_id]);
    if ($koperasi) {
        $_SESSION['koperasi_nama'] = $koperasi[0]['nama_koperasi'];
        $_SESSION['koperasi_kode'] = $koperasi[0]['kode_koperasi'];
    }
    
    return true;
}

/**
 * Get koperasi details
 * @param int|null $koperasi_id Koperasi ID, null for current
 * @return array|null Koperasi details
 */
function getKoperasiDetails($koperasi_id = null) {
    if ($koperasi_id === null) {
        $koperasi_id = getCurrentKoperasiId();
    }
    
    if (!$koperasi_id) {
        return null;
    }
    
    $koperasi = query("SELECT * FROM koperasi_master WHERE id = ?", [$koperasi_id]);
    return $koperasi ? $koperasi[0] : null;
}

/**
 * List all koperasi for appOwner
 * @return array List of koperasi
 */
function listAllKoperasi() {
    if ($_SESSION['role'] !== 'appOwner') {
        return [];
    }
    
    return query("SELECT * FROM koperasi_master ORDER BY nama_koperasi ASC");
}

/**
 * List koperasi for current bos
 * @return array List of koperasi
 */
function listKoperasiForBos($bos_id = null) {
    if ($bos_id === null) {
        $bos_id = getCurrentBosId();
    }
    
    if (!$bos_id) {
        return [];
    }
    
    return query("SELECT * FROM koperasi_master WHERE bos_id = ? ORDER BY nama_koperasi ASC", [$bos_id]);
}

/**
 * Switch koperasi context (for bos with multiple koperasi)
 * @param int $koperasi_id Koperasi ID to switch to
 * @return bool Success
 */
function switchKoperasi($koperasi_id) {
    $bos_id = getCurrentBosId();
    
    // Verify this koperasi belongs to current bos
    $koperasi = query("SELECT * FROM koperasi_master WHERE id = ? AND bos_id = ?", [$koperasi_id, $bos_id]);
    
    if (!$koperasi) {
        return false;
    }
    
    return setKoperasiContext($koperasi_id);
}

/**
 * Log access attempt for audit
 * @param int $target_koperasi_id Target koperasi ID
 * @param string $action Action performed
 * @param bool $authorized Whether access was authorized
 */
function logKoperasiAccess($target_koperasi_id, $action, $authorized) {
    if (!isset($_SESSION['user_id'])) {
        return;
    }
    
    $user_id = $_SESSION['user_id'];
    $current_koperasi_id = getCurrentKoperasiId();
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    query("
        INSERT INTO koperasi_activities 
        (user_id, activity_type, description, table_name, record_id, created_at)
        VALUES (?, 'security', ?, 'koperasi_access', ?, NOW())
    ", [
        $user_id,
        "Koperasi access attempt: action=$action, target_koperasi=$target_koperasi_id, current_koperasi=$current_koperasi_id, authorized=" . ($authorized ? 'yes' : 'no') . ", ip=$ip",
        $target_koperasi_id
    ]);
}

/**
 * API middleware untuk validasi koperasi access
 * Gunakan ini di setiap API endpoint yang mengakses data koperasi
 * 
 * @param int $target_koperasi_id Koperasi ID yang akan diakses
 * @return void
 */
function apiRequireKoperasiAccess($target_koperasi_id) {
    $authorized = isAuthorizedKoperasi($target_koperasi_id);
    logKoperasiAccess($target_koperasi_id, 'api_access', $authorized);
    
    if (!$authorized) {
        http_response_code(403);
        echo json_encode([
            'error' => 'Access denied. Anda tidak memiliki akses ke data koperasi ini.',
            'code' => 'KOPERASI_ACCESS_DENIED'
        ]);
        exit();
    }
}

/**
 * Get current user's cabang ID
 * @return int|null Cabang ID
 */
function getCurrentCabangId() {
    if (!isLoggedIn()) {
        return null;
    }
    
    if (isset($_SESSION['cabang_id']) && !empty($_SESSION['cabang_id'])) {
        return (int)$_SESSION['cabang_id'];
    }
    
    if (isset($_SESSION['user_id'])) {
        $user = query("SELECT cabang_id FROM users WHERE id = ?", [$_SESSION['user_id']]);
        if ($user && !empty($user[0]['cabang_id'])) {
            $_SESSION['cabang_id'] = (int)$user[0]['cabang_id'];
            return (int)$user[0]['cabang_id'];
        }
    }
    
    return null;
}

/**
 * Get cabang where clause for additional filtering
 * @param string $table_alias Table alias
 * @return string SQL clause
 */
function getCabangWhereClause($table_alias = '') {
    $cabang_id = getCurrentCabangId();
    $role = $_SESSION['role'] ?? null;
    
    // Roles that can see all cabang in their koperasi
    $all_cabang_roles = ['bos', 'manager_pusat', 'admin_pusat', 'appOwner'];
    
    if (in_array($role, $all_cabang_roles)) {
        return '';
    }
    
    // Roles that can only see their assigned cabang
    $prefix = $table_alias ? "$table_alias." : '';
    
    if ($cabang_id) {
        return " AND {$prefix}cabang_id = $cabang_id";
    }
    
    return "";
}
