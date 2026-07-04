<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/error_handler.php';
require_once __DIR__ . '/bunga_calculator.php';
require_once __DIR__ . '/family_risk.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/usage_tracker.php';
require_once __DIR__ . '/feature_flags.php';

// Auto-validate CSRF for all POST requests (except API endpoints)
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') === false) {
    validateCsrfRequest();
}

// Standard API response helper
function apiResponse($success, $data = null, $message = null, $error = null, $statusCode = null) {
    if ($statusCode !== null) {
        http_response_code($statusCode);
    }
    $response = [
        'success' => $success,
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    if ($message !== null) {
        $response['message'] = $message;
    }
    
    if ($error !== null) {
        $response['error'] = $error;
    } elseif (!$success) {
        $response['error'] = $message ?? 'An error occurred';
    }
    
    echo json_encode($response);
    exit();
}

// API error response helper
function apiError($message, $statusCode = 400) {
    http_response_code($statusCode);
    apiResponse(false, null, $message);
}

// API success response helper
function apiSuccess($data = null, $message = null) {
    apiResponse(true, $data, $message);
}

// Simple rate limiting (session-based)
function checkRateLimit($maxRequests = 60, $windowSeconds = 60) {
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [
            'requests' => [],
            'window_start' => time()
        ];
    }
    
    $rateLimit = $_SESSION['rate_limit'];
    $currentTime = time();
    
    // Reset window if expired
    if ($currentTime - $rateLimit['window_start'] > $windowSeconds) {
        $_SESSION['rate_limit'] = [
            'requests' => [],
            'window_start' => $currentTime
        ];
        $rateLimit = $_SESSION['rate_limit'];
    }
    
    // Add current request
    $_SESSION['rate_limit']['requests'][] = $currentTime;
    
    // Count requests in window
    $requestCount = count($rateLimit['requests']);
    
    if ($requestCount > $maxRequests) {
        return false;
    }
    
    return true;
}

// Include validation helper
require_once __DIR__ . '/validation.php';

// Common helper: Redirect with message
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION[$type] = $message;
    header('Location: ' . baseUrl($url));
    exit();
}

// Common helper: Get POST value with default
function post($key, $default = '') {
    return $_POST[$key] ?? $default;
}

// Common helper: Get GET value with default
function get($key, $default = '') {
    return $_GET[$key] ?? $default;
}

// Common helper: Format currency for display
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Common helper: Check if user can manage resource
function canManage($permission) {
    return hasPermission($permission);
}

// Common helper: Check if user can view resource
function canView($permission) {
    return hasPermission($permission) || hasPermission(str_replace('manage', 'view', $permission));
}

// Common helper: Safe array access
function arrayGet($array, $key, $default = null) {
    return $array[$key] ?? $default;
}

// Common helper: Check if array is not empty
function isNotEmpty($array) {
    return is_array($array) && !empty($array);
}

// Audit logging function for fraud prevention
function logAudit($action, $table, $recordId = null, $oldValue = null, $newValue = null) {
    $user = getCurrentUser();
    $userId = $user ? $user['id'] : null;
    
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $sql = "INSERT INTO audit_log 
            (user_id, action, table_name, record_id, old_value, new_value, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    return query($sql, [
        $userId,
        $action,
        $table,
        $recordId,
        $oldValue ? json_encode($oldValue) : null,
        $newValue ? json_encode($newValue) : null,
        $ipAddress,
        $userAgent
    ]);
}

// Generate unique code
function generateKode($prefix, $table, $field) {
    $result = query("SELECT MAX(CAST(SUBSTRING($field, 4) AS UNSIGNED)) as max_num FROM $table WHERE $field LIKE ?", ["$prefix%"]);
    $next_num = ($result[0]['max_num'] ?? 0) + 1;
    return $prefix . str_pad($next_num, 3, '0', STR_PAD_LEFT);
}

// Calculate loan interest (Flat Rate) - supports harian/mingguan/bulanan
function calculateLoan($plafon, $tenor, $bunga_per_bulan, $frekuensi = 'bulanan') {
    // Convert frequency ID to code if needed
    $frekuensi_code = getFrequencyCode($frekuensi);
    
    // Convert monthly rate to per-period rate
    switch ($frekuensi_code) {
        case 'HARIAN':
            $bunga_per_period = $bunga_per_bulan / 30; // daily rate from monthly
            break;
        case 'MINGGUAN':
            $bunga_per_period = $bunga_per_bulan / 4; // weekly rate from monthly
            break;
        default: // bulanan
            $bunga_per_period = $bunga_per_bulan;
            break;
    }
    
    $total_bunga = $plafon * ($bunga_per_period / 100) * $tenor;
    $total_pembayaran = $plafon + $total_bunga;
    $angsuran_pokok = $plafon / $tenor;
    $angsuran_bunga = $total_bunga / $tenor;
    $angsuran_total = $angsuran_pokok + $angsuran_bunga;
    
    return [
        'total_bunga' => round($total_bunga, 2),
        'total_pembayaran' => round($total_pembayaran, 2),
        'angsuran_pokok' => round($angsuran_pokok, 2),
        'angsuran_bunga' => round($angsuran_bunga, 2),
        'angsuran_total' => round($angsuran_total, 2)
    ];
}

// Calculate loan with dynamic interest rate (NEW)
function calculateLoanDinamis($plafon, $tenor, $jenis_pinjaman, $nasabah_id = null, $jaminan_tipe = 'tanpa', $metode = 'flat') {
    $cabangId = getCurrentCabang();
    $calculator = new BungaCalculator($cabangId);
    
    // Get dynamic interest rate
    $bungaInfo = $calculator->hitungBungaDinamis($jenis_pinjaman, $tenor, $nasabah_id, $jaminan_tipe);
    $sukuBunga = $bungaInfo['suku_bunga'];
    
    // Calculate installment
    $calc = $calculator->hitungAngsuran($plafon, $tenor, $sukuBunga, $metode);
    
    return array_merge($calc, [
        'suku_bunga' => $sukuBunga,
        'bunga_dasar' => $bungaInfo['bunga_dasar'],
        'risiko_adjustment' => $bungaInfo['risiko_adjustment'],
        'jaminan_adjustment' => $bungaInfo['jaminan_adjustment']
    ]);
}

// Create loan schedule - supports harian/mingguan/bulanan
function createLoanSchedule($pinjaman_id, $plafon, $tenor, $bunga_per_bulan, $tanggal_akad, $frekuensi = 'bulanan') {
    $calc = calculateLoan($plafon, $tenor, $bunga_per_bulan, $frekuensi);
    
    // Convert frequency ID to code if needed
    $frekuensi_code = getFrequencyCode($frekuensi);
    
    // Get cabang_id from pinjaman record
    $pinjaman_data = query("SELECT cabang_id FROM pinjaman WHERE id = ?", [$pinjaman_id]);
    $cabang_id = is_array($pinjaman_data) && isset($pinjaman_data[0]) ? $pinjaman_data[0]['cabang_id'] : 1;
    
    $success_count = 0;
    
    for ($i = 1; $i <= $tenor; $i++) {
        switch ($frekuensi_code) {
            case 'HARIAN':
                $jatuh_tempo = date('Y-m-d', strtotime("+$i day", strtotime($tanggal_akad)));
                break;
            case 'MINGGUAN':
                $jatuh_tempo = date('Y-m-d', strtotime("+$i week", strtotime($tanggal_akad)));
                break;
            default: // bulanan
                $jatuh_tempo = date('Y-m-d', strtotime("+$i month", strtotime($tanggal_akad)));
                break;
        }
        
        $result = query("INSERT INTO angsuran (pinjaman_id, no_angsuran, jatuh_tempo, pokok, bunga, total_angsuran) VALUES (?, ?, ?, ?, ?, ?)", [
            $pinjaman_id,
            $i,
            $jatuh_tempo,
            $calc['angsuran_pokok'],
            $calc['angsuran_bunga'],
            $calc['angsuran_total']
        ]);
        
        if ($result) {
            $success_count++;
        }
    }
    
    return $success_count > 0;
}

// Create loan schedule with dynamic interest (NEW) - supports harian/mingguan/bulanan
function createLoanScheduleDinamis($pinjaman_id, $plafon, $tenor, $jenis_pinjaman, $tanggal_akad, $nasabah_id = null, $jaminan_tipe = 'tanpa', $metode = 'flat', $frekuensi = 'bulanan') {
    $calc = calculateLoanDinamis($plafon, $tenor, $jenis_pinjaman, $nasabah_id, $jaminan_tipe, $metode);
    
    // Get cabang_id from pinjaman record
    $pinjaman_data = query("SELECT cabang_id FROM pinjaman WHERE id = ?", [$pinjaman_id]);
    $cabang_id = is_array($pinjaman_data) && isset($pinjaman_data[0]) ? $pinjaman_data[0]['cabang_id'] : 1;
    
    for ($i = 1; $i <= $tenor; $i++) {
        switch ($frekuensi) {
            case 'harian':
                $jatuh_tempo = date('Y-m-d', strtotime("+$i day", strtotime($tanggal_akad)));
                break;
            case 'mingguan':
                $jatuh_tempo = date('Y-m-d', strtotime("+$i week", strtotime($tanggal_akad)));
                break;
            default:
                $jatuh_tempo = date('Y-m-d', strtotime("+$i month", strtotime($tanggal_akad)));
                break;
        }
        
        query("INSERT INTO angsuran (pinjaman_id, no_angsuran, jatuh_tempo, pokok, bunga, total_angsuran) VALUES (?, ?, ?, ?, ?, ?)", [
            $pinjaman_id,
            $i,
            $jatuh_tempo,
            $calc['angsuran_pokok'],
            $calc['angsuran_bunga'],
            $calc['angsuran_total']
        ]);
    }
    
    return $calc;
}

// Check late payments and auto-calculate denda
function checkLatePayments() {
    $kantor_id = 1; // Single office
    
    // Update status to 'telat' for payments past due date
    query("UPDATE angsuran SET status = 'telat' WHERE status = 'belum' AND jatuh_tempo < CURDATE()");
    
    // Auto-calculate denda for late installments
    calculateAutoDenda($kantor_id);
    
    // Get list of late payments
    return query("SELECT a.*, n.nama, n.telp, p.kode_pinjaman, p.frekuensi_id,
                  DATEDIFF(CURDATE(), a.jatuh_tempo) as hari_telat
                  FROM angsuran a 
                  JOIN pinjaman p ON a.pinjaman_id = p.id 
                  JOIN nasabah n ON p.nasabah_id = n.id 
                  WHERE a.status = 'telat' 
                  ORDER BY a.jatuh_tempo");
}

// Auto-calculate denda for late installments
function calculateAutoDenda($kantor_id) {
    // Get late installments without denda calculated today
    $late = query("SELECT a.id, a.total_angsuran, a.jatuh_tempo, a.denda, p.frekuensi_id
                   FROM angsuran a
                   JOIN pinjaman p ON a.pinjaman_id = p.id
                   WHERE a.status = 'telat' AND a.jatuh_tempo < CURDATE()");
    
    if (!$late || !is_array($late) || empty($late)) return;
    
    foreach ($late as $row) {
        $hari_telat = (int)((strtotime(date('Y-m-d')) - strtotime($row['jatuh_tempo'])) / 86400);
        
        // Get denda settings for this frekuensi
        $frekuensi_value = $row['frekuensi_id'];
        $setting = query("SELECT * FROM setting_denda 
                          WHERE is_active = 1 AND frekuensi_id = ? 
                          LIMIT 1", [$frekuensi_value]);
        
        if (!$setting || !is_array($setting) || empty($setting)) continue;
        $s = $setting[0];
        
        // Apply grace period
        $hari_efektif = max(0, $hari_telat - (int)$s['grace_period']);
        if ($hari_efektif <= 0) continue;
        
        // Calculate denda
        if ($s['tipe_denda'] === 'persentase') {
            $denda = $row['total_angsuran'] * ($s['nilai_denda'] / 100) * $hari_efektif;
        } else {
            $denda = $s['nilai_denda'] * $hari_efektif;
        }
        
        // Apply max cap
        if ($s['denda_maksimal'] !== null && $denda > $s['denda_maksimal']) {
            $denda = $s['denda_maksimal'];
        }
        
        $denda = round($denda, 2);
        
        // Update denda on angsuran
        if ($denda != $row['denda']) {
            query("UPDATE angsuran SET denda = ? WHERE id = ?", [$denda, $row['id']]);
        }
    }
}

// Get frequency label in Indonesian
function getFrequencyLabel($frekuensi) {
    // Try to get from ref_frekuensi_angsuran table first
    if (is_numeric($frekuensi)) {
        $result = query("SELECT nama FROM ref_frekuensi_angsuran WHERE id = ? AND status = 'aktif'", [$frekuensi]);
        if ($result && is_array($result) && isset($result[0])) {
            return $result[0]['nama'];
        }
    }
    
    // Fallback to old enum-based labels for backward compatibility
    $labels = [
        'harian' => 'Harian',
        'mingguan' => 'Mingguan',
        'bulanan' => 'Bulanan'
    ];
    return $labels[$frekuensi] ?? 'Bulanan';
}

// Get frequency period label
function getFrequencyPeriodLabel($frekuensi) {
    // Try to get from ref_frekuensi_angsuran table first
    if (is_numeric($frekuensi)) {
        $result = query("SELECT nama, hari_per_periode FROM ref_frekuensi_angsuran WHERE id = ? AND status = 'aktif'", [$frekuensi]);
        if ($result && is_array($result) && isset($result[0])) {
            $freq = $result[0];
            if ($freq['hari_per_periode'] === 1) return 'Hari';
            if ($freq['hari_per_periode'] === 7) return 'Minggu';
            if ($freq['hari_per_periode'] === 30) return 'Bulan';
            return 'Periode';
        }
    }
    
    // Fallback to old enum-based labels for backward compatibility
    $labels = [
        'harian' => 'Hari',
        'mingguan' => 'Minggu',
        'bulanan' => 'Bulan'
    ];
    return $labels[$frekuensi] ?? 'Bulan';
}

// Get max tenor by frequency
function getMaxTenor($frekuensi) {
    // Try to get from ref_frekuensi_angsuran table first
    if (is_numeric($frekuensi)) {
        $result = query("SELECT tenor_max FROM ref_frekuensi_angsuran WHERE id = ? AND status = 'aktif'", [$frekuensi]);
        if ($result && is_array($result) && isset($result[0])) {
            return (int)$result[0]['tenor_max'];
        }
    }
    
    // Fallback to old enum-based labels for backward compatibility
    $max = [
        'harian' => 365,
        'mingguan' => 52,
        'bulanan' => 24
    ];
    return $max[$frekuensi] ?? 24;
}

// Get frequency code from ID (for backward compatibility)
function getFrequencyCode($frekuensi_id) {
    if (is_numeric($frekuensi_id)) {
        $result = query("SELECT kode FROM ref_frekuensi_angsuran WHERE id = ? AND status = 'aktif'", [$frekuensi_id]);
        if ($result && is_array($result) && isset($result[0])) {
            return $result[0]['kode'];
        }
    } else {
        // Handle string input (backward compatibility)
        $codes = [
            'harian' => 'HARIAN',
            'mingguan' => 'MINGGUAN',
            'bulanan' => 'BULANAN'
        ];
        return $codes[$frekuensi_id] ?? 'BULANAN';
    }
    return 'BULANAN';
}

// Get frequency ID from code (for backward compatibility)
function getFrequencyId($frekuensi_code) {
    if (is_numeric($frekuensi_code)) {
        return $frekuensi_code; // Already an ID
    }
    
    $result = query("SELECT id FROM ref_frekuensi_angsuran WHERE kode = ? AND status = 'aktif'", [$frekuensi_code]);
    if ($result && is_array($result) && isset($result[0])) {
        return $result[0]['id'];
    }
    
    // Default to bulanan (id 3)
    return 3;
}

// Get all active frequencies for dropdown
function getActiveFrequencies() {
    $result = query("SELECT id, kode, nama, hari_per_periode, tenor_min, tenor_max FROM ref_frekuensi_angsuran WHERE status = 'aktif' ORDER BY urutan_tampil");
    return $result ?? [];
}

// Blacklist / unblacklist nasabah
function toggleBlacklist($nasabah_id, $aksi, $alasan) {
    $user = getCurrentUser();
    if (!$user) return false;
    
    $new_status = ($aksi === 'blacklist') ? 'blacklist' : 'aktif';
    
    $result = query("UPDATE nasabah SET status = ? WHERE id = ?", [$new_status, $nasabah_id]);
    
    if ($result) {
        // Log blacklist action
        query("INSERT INTO blacklist_log (nasabah_id, aksi, alasan, dilakukan_oleh) VALUES (?, ?, ?, ?)",
            [$nasabah_id, $aksi, $alasan, $user['id']]);
        
        logAudit($aksi, 'nasabah', $nasabah_id, ['status' => ($aksi === 'blacklist' ? 'aktif' : 'blacklist')], ['status' => $new_status]);
    }
    
    return $result;
}

// Format currency (Indonesian Rupiah)
function formatRupiah($amount, $with_symbol = true) {
    if ($amount === null) {
        $amount = 0;
    }
    $formatted = number_format($amount, 0, ',', '.');
    return $with_symbol ? 'Rp ' . $formatted : $formatted;
}

// Format number with Indonesian separators
function formatNumber($amount, $decimals = 0) {
    if ($amount === null) {
        $amount = 0;
    }
    return number_format($amount, $decimals, ',', '.');
}

// Convert number to Indonesian words (terbilang)
function terbilang($number) {
    $number = abs($number);
    $words = "";
    
    $units = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
    
    if ($number < 12) {
        $words = $units[$number];
    } elseif ($number < 20) {
        $words = $units[$number - 10] . " belas";
    } elseif ($number < 100) {
        $tens = floor($number / 10);
        $unit = $number % 10;
        $words = $units[$tens] . " puluh" . ($unit > 0 ? " " . $units[$unit] : "");
    } elseif ($number < 200) {
        $words = "seratus" . ($number > 100 ? " " . terbilang($number - 100) : "");
    } elseif ($number < 1000) {
        $hundreds = floor($number / 100);
        $remainder = $number % 100;
        $words = $units[$hundreds] . " ratus" . ($remainder > 0 ? " " . terbilang($remainder) : "");
    } elseif ($number < 2000) {
        $words = "seribu" . ($number > 1000 ? " " . terbilang($number - 1000) : "");
    } elseif ($number < 1000000) {
        $thousands = floor($number / 1000);
        $remainder = $number % 1000;
        $words = terbilang($thousands) . " ribu" . ($remainder > 0 ? " " . terbilang($remainder) : "");
    } elseif ($number < 1000000000) {
        $millions = floor($number / 1000000);
        $remainder = $number % 1000000;
        $words = terbilang($millions) . " juta" . ($remainder > 0 ? " " . terbilang($remainder) : "");
    } elseif ($number < 1000000000000) {
        $billions = floor($number / 1000000000);
        $remainder = $number % 1000000000;
        $words = terbilang($billions) . " miliar" . ($remainder > 0 ? " " . terbilang($remainder) : "");
    } elseif ($number < 1000000000000000) {
        $trillions = floor($number / 1000000000000);
        $remainder = $number % 1000000000000;
        $words = terbilang($trillions) . " triliun" . ($remainder > 0 ? " " . terbilang($remainder) : "");
    }
    
    return ucwords($words);
}

// Format currency with terbilang
function formatRupiahTerbilang($amount) {
    if ($amount === null) {
        $amount = 0;
    }
    return ucwords(terbilang($amount) . " Rupiah");
}

// Format date with Indonesian month names
function formatDate($date, $format = 'd F Y') {
    if (empty($date)) return '-';
    
    $timestamp = strtotime($date);
    if ($timestamp === false) return '-';
    
    // Indonesian month names
    $indonesian_months = [
        'January' => 'Januari',
        'February' => 'Februari',
        'March' => 'Maret',
        'April' => 'April',
        'May' => 'Mei',
        'June' => 'Juni',
        'July' => 'Juli',
        'August' => 'Agustus',
        'September' => 'September',
        'October' => 'Oktober',
        'November' => 'November',
        'December' => 'Desember'
    ];
    
    // Format the date first with English names
    $formatted = date($format, $timestamp);
    
    // Replace English month names with Indonesian
    foreach ($indonesian_months as $english => $indonesian) {
        $formatted = str_replace($english, $indonesian, $formatted);
    }
    
    return $formatted;
}

// Get reference data helper
function getReferenceData($table, $where = [], $order_by = 'id ASC') {
    $sql = "SELECT * FROM $table";
    $params = [];
    
    if (!empty($where)) {
        $conditions = [];
        foreach ($where as $key => $value) {
            $conditions[] = "$key = ?";
            $params[] = $value;
        }
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }
    
    $sql .= " ORDER BY $order_by";
    
    return query($sql, $params);
}

// Get active reference data
function getActiveReferenceData($table, $order_by = 'id ASC') {
    return getReferenceData($table, ['status' => 'aktif'], $order_by);
}

// Get reference by kode
function getReferenceByKode($table, $kode) {
    $sql = "SELECT * FROM $table WHERE kode = ?";
    return query($sql, [$kode]);
}

// Generate auto-advance focus JavaScript
function getAutoAdvanceFocusJS() {
    return '
    // Auto-advance focus when select element is changed
    document.addEventListener("DOMContentLoaded", function() {
        const selects = document.querySelectorAll("select");
        selects.forEach(function(select) {
            select.addEventListener("change", function() {
                // Find the next form element
                const form = this.form;
                if (form) {
                    const elements = Array.from(form.elements);
                    const currentIndex = elements.indexOf(this);
                    
                    // Find the next visible, non-disabled, non-readonly element
                    for (let i = currentIndex + 1; i < elements.length; i++) {
                        const nextElement = elements[i];
                        if (nextElement &&
                            nextElement.tagName !== "BUTTON" &&
                            nextElement.type !== "hidden" &&
                            nextElement.type !== "submit" &&
                            !nextElement.disabled &&
                            !nextElement.readOnly &&
                            nextElement.offsetParent !== null) {
                            nextElement.focus();
                            break;
                        }
                    }
                }
            });
        });
    });
    ';
}

// Generate SweetAlert2 session alerts JavaScript
function getSessionAlertsJS() {
    $js = '';
    if (isset($_SESSION['success'])) {
        $js .= "Swal.fire({icon: 'success', title: 'Berhasil', text: '" . addslashes($_SESSION['success']) . "', timer: 3000, showConfirmButton: false});";
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        $js .= "Swal.fire({icon: 'error', title: 'Gagal', text: '" . addslashes($_SESSION['error']) . "', timer: 3000, showConfirmButton: false});";
        unset($_SESSION['error']);
    }
    return $js;
}

// Standardized CRUD helper with transaction support
function crudTransaction($callback) {
    global $conn;
    
    try {
        $conn->begin_transaction();
        $result = $callback();
        $conn->commit();
        return $result;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("CRUD Transaction Error: " . $e->getMessage());
        return false;
    }
}

// Log CRUD operation to audit trail
function logCrudOperation($table, $action, $record_id, $old_data = null, $new_data = null) {
    global $conn;
    
    try {
        $user = getCurrentUser();
        $user_id = $user ? $user['id'] : null;
        
        $old_value_json = json_encode($old_data);
        $new_value_json = json_encode($new_data);
        
        $sql = "INSERT INTO audit_log (user_id, action, table_name, record_id, old_value, new_value, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ississ', $user_id, $action, $table, $record_id, 
                          $old_value_json, $new_value_json);
        $stmt->execute();
    } catch (Exception $e) {
        error_log("Failed to log CRUD operation: " . $e->getMessage());
    }
}

/**
 * Send WhatsApp notification (In-App System)
 * 
 * Uses in-app notification system - no external services required
 * 
 * @param string $phone Phone number (format: 628xxxxxxxxxx or 08xxxxxxxxxx)
 * @param string $message Message content
 * @return bool Success status
 */
function sendWhatsApp($phone, $message) {
    // Check feature flag for WhatsApp notifications
    if (!isFeatureEnabled('wa_notifikasi')) {
        error_log("In-app notification feature disabled - WA to $phone: $message");
        return false;
    }
    
    // Normalize phone number to international format (62)
    $phone = normalizePhoneNumber($phone);
    
    // Use in-app notification system
    try {
        require_once BASE_PATH . '/includes/wa_notifikasi.php';
        $result = kirimWA($phone, $message, null, null, 'notification', true);
        return $result['success'] ?? false;
    } catch (Exception $e) {
        error_log("In-app notification send failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Normalize phone number to international format
 */
function normalizePhoneNumber($phone) {
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // If starts with 0, replace with 62
    if (strpos($phone, '0') === 0) {
        $phone = '62' . substr($phone, 1);
    }
    
    return $phone;
}

// Validate loan application with family risk check (NEW)
function validateLoanApplicationWithFamilyRisk($nasabah_id, $plafon) {
    $cabangId = getCurrentCabang();
    $familyRisk = new FamilyRisk($cabangId);
    
    return $familyRisk->validateLoanApplication($nasabah_id, $plafon);
}

// Check family risk for nasabah (NEW)
function checkFamilyRisk($nasabah_id) {
    $cabangId = getCurrentCabang();
    $familyRisk = new FamilyRisk($cabangId);
    
    return $familyRisk->checkFamilyRisk($nasabah_id);
}

// ============================================
// Multi-Tenant Isolation Helpers
// ============================================

/**
 * Dapatkan bos_id dari user yang sedang login.
 * - Jika role 'bos'     → return id user itu sendiri
 * - Jika role lain      → return owner_bos_id mereka
 * - Jika role appOwner  → return null (tidak akses data koperasi)
 */
function getOwnerBosId() {
    $user = getCurrentUser();
    if (!$user) return null;
    if ($user['role'] === 'appOwner') return null;
    if ($user['role'] === 'bos') return (int)$user['id'];
    return $user['owner_bos_id'] ? (int)$user['owner_bos_id'] : null;
}

/**
 * Dapatkan array cabang_id yang dimiliki bos dari user yang sedang login.
 * Digunakan sebagai filter isolasi data agar user A tidak lihat data bos B.
 */
function getBosOwnedCabangIds() {
    $bos_id = getOwnerBosId();
    if (!$bos_id) return [];
    $rows = query("SELECT id FROM cabang WHERE owner_bos_id = ? AND status = 'aktif'", [$bos_id]);
    if (!is_array($rows) || empty($rows)) return [];
    return array_column($rows, 'id');
}

/**
 * Validasi apakah sebuah cabang_id dimiliki oleh bos dari user yang sedang login.
 * Return true jika valid, false jika bukan miliknya.
 */
function validateCabangOwnership($cabang_id) {
    $bos_id = getOwnerBosId();
    if (!$bos_id) return false;
    $result = query("SELECT id FROM cabang WHERE id = ? AND owner_bos_id = ?", [$cabang_id, $bos_id]);
    return is_array($result) && count($result) > 0;
}

/**
 * Bangun klausa SQL IN untuk filter cabang_id milik bos yang login.
 * Return ['clause' => 'AND field IN (?,?)', 'params' => [1,2]] atau false jika gagal.
 */
function buildCabangFilter($field = 'cabang_id') {
    $ids = getBosOwnedCabangIds();
    if (empty($ids)) return false;
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    return [
        'clause'  => "AND $field IN ($placeholders)",
        'params'  => $ids,
    ];
}

/**
 * Get cabang filter based on role for dashboard and pages
 * Returns WHERE clause string for filtering data by cabang
 */
function getCabangFilterForRole($role, $user_cabang_id, $user_id) {
    switch($role) {
        case 'bos':
            // Bos melihat semua cabang yang dia miliki
            $owned_cabangs = getBosOwnedCabangIds();
            if (empty($owned_cabangs)) return "";
            return "cabang_id IN (" . implode(',', array_map('intval', $owned_cabangs)) . ")";
        case 'manager_pusat':
            // Manager pusat melihat semua cabang
            return "";
        default:
            // Role lain hanya melihat cabang mereka sendiri
            if ($user_cabang_id) {
                return "cabang_id = " . intval($user_cabang_id);
            }
            return "";
    }
}

/**
 * Get cabang filter for page-specific tables with alias
 * Returns WHERE clause string with table alias prefix
 */
function getPageCabangFilter($role, $user_cabang_id, $user_id, $table_alias = '') {
    $prefix = $table_alias ? $table_alias . '.' : '';
    switch($role) {
        case 'bos':
            $owned_cabangs = getBosOwnedCabangIds();
            if (empty($owned_cabangs)) return "";
            return "{$prefix}cabang_id IN (" . implode(',', array_map('intval', $owned_cabangs)) . ")";
        case 'manager_pusat':
            return "";
        default:
            if ($user_cabang_id) {
                return "{$prefix}cabang_id = " . intval($user_cabang_id);
            }
            return "";
    }
}

/**
 * Get cabang filter for reports
 * Returns array of cabang IDs or null
 */
function getReportCabangFilter($role, $user_cabang_id, $user_id) {
    switch($role) {
        case 'bos':
            $owned_cabangs = getBosOwnedCabangIds();
            if (empty($owned_cabangs)) return null;
            return $owned_cabangs;
        case 'manager_pusat':
            return null; // All branches
        default:
            if ($user_cabang_id) {
                return [$user_cabang_id];
            }
            return null;
    }
}

// ============================================
// Permission System Functions
// ============================================

/**
 * Check if user has a specific permission
 * Also checks delegated permissions
 */
function hasPermission($permission_code) {
    $user = getCurrentUser();
    if (!$user) return false;
    
    // appOwner only has app-level permissions, not koperasi operations
    if ($user['role'] === 'appOwner') {
        $app_permissions = ['manage_app', 'approve_bos', 'view_koperasi', 'suspend_koperasi'];
        return in_array($permission_code, $app_permissions);
    }
    
    if ($user['role'] === 'bos') {
        return true;
    }
    
    // Check role permissions
    $role_permission = query("SELECT granted FROM role_permissions 
                               WHERE role = ? AND permission_code = ?", 
                               [$user['role'], $permission_code]);
    if ($role_permission && is_array($role_permission) && count($role_permission) > 0) {
        return (bool)$role_permission[0]['granted'];
    }
    
    // Check user-specific permission overrides
    $user_permission = query("SELECT granted FROM user_permissions 
                              WHERE user_id = ? AND permission_id = (SELECT id FROM permissions WHERE kode = ?)", 
                              [$user['id'], $permission_code]);
    if ($user_permission && is_array($user_permission) && count($user_permission) > 0) {
        return (bool)$user_permission[0]['granted'];
    }
    
    // Check delegated permissions
    $delegated = query("SELECT dp.permission_scope, dp.scope_limitation, dp.is_active 
                        FROM delegated_permissions dp 
                        WHERE dp.delegatee_id = ? AND dp.is_active = true 
                        AND (dp.expires_at IS NULL OR dp.expires_at > CURRENT_TIMESTAMP)",
                        [$user['id']]);
    
    if ($delegated && is_array($delegated) && count($delegated) > 0) {
        foreach ($delegated as $delegation) {
            if ($delegation['permission_scope'] === 'all_operations') {
                return true;
            }
            
            // Map permission scopes to permission codes
            $scope_mapping = [
                'employee_crud' => ['manage_users', 'manage_petugas'],
                'branch_crud' => ['manage_cabang'],
                'branch_employee_crud' => ['manage_users', 'manage_petugas']
            ];
            
            if (isset($scope_mapping[$delegation['permission_scope']])) {
                if (in_array($permission_code, $scope_mapping[$delegation['permission_scope']])) {
                    // Check scope limitations if any
                    if ($delegation['scope_limitation']) {
                        $limitations = json_decode($delegation['scope_limitation'], true);
                        // Additional scope limitation checks can be added here
                        return true;
                    }
                    return true;
                }
            }
        }
    }
    
    return false;
}

// Check if user can manage specific role
function canManageRole($target_role) {
    $user = getCurrentUser();
    if (!$user) return false;
    
    // Bos can manage all roles except bos
    // appOwner doesn't manage koperasi roles
    if ($user['role'] === 'appOwner') return false;
    
    if ($user['role'] === 'bos') return $target_role !== 'bos';
    
    // Manager pusat can manage roles below manager_pusat
    if ($user['role'] === 'manager_pusat') {
        $manageable_roles = ['admin_pusat', 'admin_cabang', 'manager_cabang', 'petugas_pusat', 'petugas_cabang', 'teller'];
        return in_array($target_role, $manageable_roles);
    }
    
    // Manager cabang can manage roles below manager_cabang
    if ($user['role'] === 'manager_cabang') {
        $manageable_roles = ['admin_cabang', 'petugas_cabang', 'teller'];
        return in_array($target_role, $manageable_roles);
    }
    
    // Admin pusat can manage roles below admin_pusat
    if ($user['role'] === 'admin_pusat') {
        $manageable_roles = ['petugas_pusat', 'petugas_cabang', 'teller'];
        return in_array($target_role, $manageable_roles);
    }
    
    // Admin cabang can manage roles below admin_cabang
    if ($user['role'] === 'admin_cabang') {
        $manageable_roles = ['petugas_cabang', 'teller'];
        return in_array($target_role, $manageable_roles);
    }
    
    return false;
}

// Grant permission to user
function grantPermission($user_id, $permission_code, $granted = true) {
    $user = getCurrentUser();
    if (!$user || !hasPermission('assign_permissions')) return false;
    
    // Check if user can manage the target user
    $target_user = query("SELECT * FROM users WHERE id = ?", [$user_id]);
    if (!$target_user) return false;
    
    if (!canManageRole($target_user[0]['role'])) return false;
    
    // Get permission id
    $permission = query("SELECT id FROM permissions WHERE kode = ?", [$permission_code]);
    if (!$permission) return false;
    
    // Log the change
    $current_granted = query("SELECT granted FROM user_permissions WHERE user_id = ? AND permission_id = ?", 
                              [$user_id, $permission[0]['id']]);
    
    query("INSERT INTO permission_audit_log (user_id, target_user_id, action, permission_id, old_value, new_value) 
          VALUES (?, ?, ?, ?, ?, ?)", 
          [$user['id'], $user_id, 'grant_permission', $permission[0]['id'], 
           $current_granted ? $current_granted[0]['granted'] : null, $granted]);
    
    // Upsert permission
    query("INSERT INTO user_permissions (user_id, permission_id, granted, created_by) 
          VALUES (?, ?, ?, ?) 
          ON DUPLICATE KEY UPDATE granted = ?, created_by = ?", 
          [$user_id, $permission[0]['id'], $granted, $user['id'], $granted, $user['id']]);
    
    return true;
}

// Get user permissions
function getUserPermissions($user_id) {
    $permissions = query("SELECT p.kode, p.nama, p.kategori, COALESCE(up.granted, 1) as granted 
                         FROM permissions p
                         LEFT JOIN role_permissions rp ON p.id = rp.permission_id
                         LEFT JOIN users u ON u.role = rp.role_kode AND u.id = ?
                         LEFT JOIN user_permissions up ON up.permission_id = p.id AND up.user_id = ?
                         WHERE u.id = ? OR rp.role_kode = (SELECT role FROM users WHERE id = ?)
                         GROUP BY p.id, up.granted
                         ORDER BY p.kategori, p.nama", 
                         [$user_id, $user_id, $user_id, $user_id]);
    
    return $permissions;
}

// Revoke permission from user
function revokePermission($user_id, $permission_code) {
    $user = getCurrentUser();
    if (!$user || !hasPermission('assign_permissions')) return false;
    
    // Check if user can manage the target user
    $target_user = query("SELECT * FROM users WHERE id = ?", [$user_id]);
    if (!$target_user) return false;
    
    if (!canManageRole($target_user[0]['role'])) return false;
    
    // Get permission id
    $permission = query("SELECT id FROM permissions WHERE kode = ?", [$permission_code]);
    if (!$permission) return false;
    
    // Log the change
    $current_granted = query("SELECT granted FROM user_permissions WHERE user_id = ? AND permission_id = ?", 
                              [$user_id, $permission[0]['id']]);
    
    query("INSERT INTO permission_audit_log (user_id, target_user_id, action, permission_id, old_value, new_value) 
          VALUES (?, ?, ?, ?, ?, ?)", 
          [$user['id'], $user_id, 'revoke_permission', $permission[0]['id'], 
           $current_granted ? $current_granted[0]['granted'] : null, 0]);
    
    // Delete user permission
    $result = query("DELETE FROM user_permissions WHERE user_id = ? AND permission_id = ?", 
                    [$user_id, $permission[0]['id']]);
    
    return $result;
}

// Get role hierarchy level (lower number = higher hierarchy)
function getRoleHierarchyLevel($role) {
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
    
    return $hierarchy[$role] ?? 999;
}

// Check if user has higher role than target user
function hasHigherRole($user_id, $target_user_id) {
    $user = getCurrentUser();
    if (!$user) return false;
    
    $target_user = query("SELECT role FROM users WHERE id = ?", [$target_user_id]);
    if (!$target_user) return false;
    
    return getRoleHierarchyLevel($user['role']) < getRoleHierarchyLevel($target_user[0]['role']);
}
