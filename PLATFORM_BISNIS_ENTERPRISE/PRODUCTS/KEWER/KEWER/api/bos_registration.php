<?php
/**
 * Bos Registration API
 * Handles bos registration and approval workflow
 */

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

// Suppress errors to ensure JSON output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
    require_once BASE_PATH . '/config/database.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit();
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($action) {
        case 'register':
            if ($method === 'POST') {
                handleBosRegistration();
            } else {
                apiError('Method not allowed', 405);
            }
            break;
            
        case 'approve':
            if ($method === 'POST') {
                handleBosApproval();
            } else {
                apiError('Method not allowed', 405);
            }
            break;
            
        case 'reject':
            if ($method === 'POST') {
                handleBosRejection();
            } else {
                apiError('Method not allowed', 405);
            }
            break;
            
        case 'list':
            if ($method === 'GET') {
                handleBosList();
            } else {
                apiError('Method not allowed', 405);
            }
            break;
            
        case 'pending':
            if ($method === 'GET') {
                handlePendingBosList();
            } else {
                apiError('Method not allowed', 405);
            }
            break;
            
        case 'delete':
            if ($method === 'DELETE') {
                handleBosDelete();
            } else {
                apiError('Method not allowed', 405);
            }
            break;
            
        default:
            apiError('Invalid action');
            break;
    }
} catch (Exception $e) {
    apiError($e->getMessage());
}

/**
 * Handle bos registration
 */
function handleBosRegistration() {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $telp = $_POST['telp'] ?? '';
    $nama_perusahaan = $_POST['nama_perusahaan'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $province_id = $_POST['province_id'] ?? '';
    $regency_id = $_POST['regency_id'] ?? '';
    $district_id = $_POST['district_id'] ?? '';
    $village_id = $_POST['village_id'] ?? '';
    
    // Validation
    if (empty($username) || empty($password) || empty($nama) || empty($nama_perusahaan)) {
        apiError('Username, password, nama, dan nama perusahaan wajib diisi');
    }
    
    // Check if username already exists in users table
    $existing_user = query("SELECT id FROM users WHERE username = ?", [$username]);
    if ($existing_user) {
        apiError('Username sudah digunakan');
    }
    
    // Check if username already exists in bos_registrations table
    $existing_registration = query("SELECT id FROM bos_registrations WHERE username = ?", [$username]);
    if ($existing_registration) {
        apiError('Username sudah terdaftar dalam pendaftaran');
    }
    
    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // All bos registrations go to pending — appOwner will approve
    $result = query(
        "INSERT INTO bos_registrations (username, password, nama, email, telp, nama_usaha, alamat_usaha, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')",
        [$username, $password_hash, $nama, $email, $telp, $nama_perusahaan, $alamat]
    );
    
    if ($result) {
        apiSuccess(['message' => 'Pendaftaran berhasil dikirim. Menunggu persetujuan App Owner.']);
    } else {
        apiError('Gagal mendaftar');
    }
}

/**
 * Handle bos approval
 */
function handleBosApproval() {
    // Check if user is appOwner
    if (!isLoggedIn()) {
        apiError('Unauthorized', 401);
    }
    
    $user = getCurrentUser();
    if (!$user || $user['role'] !== 'appOwner') {
        apiError('Hanya App Owner yang dapat menyetujui pendaftaran bos', 403);
    }
    
    $registration_id = $_POST['registration_id'] ?? '';
    
    if (empty($registration_id)) {
        apiError('Registration ID diperlukan');
    }
    
    // Get registration details
    $registration = query("SELECT * FROM bos_registrations WHERE id = ? AND status = 'pending'", [$registration_id]);
    if (!$registration) {
        apiError('Pendaftaran tidak ditemukan atau sudah diproses');
    }
    
    $registration = $registration[0];
    
    // Create user account
    $result = query(
        "INSERT INTO users (username, password, nama, email, telp, role, cabang_id, status, owner_bos_id) VALUES (?, ?, ?, ?, ?, 'bos', NULL, 'aktif', NULL)",
        [$registration['username'], $registration['password'], $registration['nama'], $registration['email'], $registration['telp']]
    );
    
    if ($result) {
        $bos_user_id = query("SELECT LAST_INSERT_ID() as id")[0]['id'];
        
        // Create person record in db_orang for address management
        try {
            require_once BASE_PATH . '/includes/people_helper.php';
            createPersonWithAddress(
                [
                    'kewer_user_id' => $bos_user_id,
                    'nama' => $registration['nama'],
                    'email' => $registration['email'] ?? null,
                    'telp' => $registration['telp'] ?? null
                ],
                [
                    'label' => 'kantor',
                    'street_address' => $registration['alamat_usaha'] ?? '',
                    'province_id' => $registration['province_id'] ?? null,
                    'regency_id' => $registration['regency_id'] ?? null,
                    'district_id' => $registration['district_id'] ?? null,
                    'village_id' => $registration['village_id'] ?? null
                ]
            );
        } catch (Exception $e) {
            // Log error but don't fail the bos approval
            error_log("Failed to create person record: " . $e->getMessage());
        }
        
        // Update registration status
        query(
            "UPDATE bos_registrations SET status = 'approved', approved_at = CURRENT_TIMESTAMP, approved_by = ? WHERE id = ?",
            [$user['id'], $registration_id]
        );
        
        // Send approval email notification
        sendBosApprovalEmail($registration['email'], $registration['nama'], $registration['username']);
        
        apiSuccess(['message' => 'Bos berhasil disetujui', 'bos_user_id' => $bos_user_id]);
    } else {
        apiError('Gagal membuat user bos');
    }
}

/**
 * Handle bos rejection
 */
function handleBosRejection() {
    // Check if user is appOwner
    if (!isLoggedIn()) {
        apiError('Unauthorized', 401);
    }
    
    $user = getCurrentUser();
    if (!$user || $user['role'] !== 'appOwner') {
        apiError('Hanya App Owner yang dapat menolak pendaftaran bos', 403);
    }
    
    $registration_id = $_POST['registration_id'] ?? '';
    $rejection_reason = $_POST['rejection_reason'] ?? '';
    
    if (empty($registration_id)) {
        apiError('Registration ID diperlukan');
    }
    
    // Update registration status
    $result = query(
        "UPDATE bos_registrations SET status = 'rejected', rejection_reason = ?, approved_at = CURRENT_TIMESTAMP, approved_by = ? WHERE id = ?",
        [$rejection_reason, $user['id'], $registration_id]
    );
    
    if ($result) {
        // Send rejection email notification
        sendBosRejectionEmail($registration['email'], $registration['nama'], $rejection_reason);
        
        apiSuccess(['message' => 'Pendaftaran bos ditolak']);
    } else {
        apiError('Gagal menolak pendaftaran');
    }
}

/**
 * Handle bos list (for bos)
 */
function handleBosList() {
    // Check if user is appOwner
    if (!isLoggedIn()) {
        apiError('Unauthorized', 401);
    }
    
    $user = getCurrentUser();
    if (!$user || $user['role'] !== 'appOwner') {
        apiError('Hanya App Owner yang dapat melihat daftar bos', 403);
    }
    
    // Get all bos users
    $bos_users = query("SELECT id, username, nama, email, telp, status, created_at FROM users WHERE role = 'bos' ORDER BY created_at DESC");
    
    apiSuccess($bos_users);
}

/**
 * Handle pending bos list (for appOwner)
 */
function handlePendingBosList() {
    // Check if user is appOwner
    if (!isLoggedIn()) {
        apiError('Unauthorized', 401);
    }
    
    $user = getCurrentUser();
    if (!$user || $user['role'] !== 'appOwner') {
        apiError('Hanya App Owner yang dapat melihat pendaftaran pending', 403);
    }
    
    // Get pending registrations
    $pending_registrations = query(
        "SELECT id, username, nama, email, telp, nama_usaha, alamat_usaha, created_at FROM bos_registrations WHERE status = 'pending' ORDER BY created_at DESC"
    );
    
    apiSuccess($pending_registrations);
}

/**
 * Send approval email to bos
 */
function sendBosApprovalEmail($email, $nama, $username) {
    if (empty($email)) {
        return false;
    }
    
    try {
        require_once BASE_PATH . '/vendor/autoload.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = getenv('SMTP_HOST') ?? 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = getenv('SMTP_USERNAME');
        $mail->Password = getenv('SMTP_PASSWORD');
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = getenv('SMTP_PORT') ?? 587;
        
        // Recipients
        $mail->setFrom(getenv('SMTP_FROM') ?? 'noreply@kewer.com', 'Kewer System');
        $mail->addAddress($email, $nama);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Pendaftaran Bos Disetujui';
        $mail->Body = "
            <h2>Selamat, $nama!</h2>
            <p>Pendaftaran Anda sebagai Bos di sistem Kewer telah disetujui.</p>
            <p><strong>Username:</strong> $username</p>
            <p>Anda sekarang dapat login ke sistem menggunakan username dan password yang Anda daftarkan.</p>
            <p>Silakan login di: <a href='" . BASE_URL . "'>" . BASE_URL . "</a></p>
            <br>
            <p>Terima kasih telah bergabung dengan Kewer!</p>
        ";
        $mail->AltBody = "
            Selamat, $nama!\n\n
            Pendaftaran Anda sebagai Bos di sistem Kewer telah disetujui.\n
            Username: $username\n
            Anda sekarang dapat login ke sistem menggunakan username dan password yang Anda daftarkan.\n
            Silakan login di: " . BASE_URL . "\n\n
            Terima kasih telah bergabung dengan Kewer!
        ";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email send failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Send rejection email to bos
 */
function sendBosRejectionEmail($email, $nama, $rejection_reason) {
    if (empty($email)) {
        return false;
    }
    
    try {
        require_once BASE_PATH . '/vendor/autoload.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = getenv('SMTP_HOST') ?? 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = getenv('SMTP_USERNAME');
        $mail->Password = getenv('SMTP_PASSWORD');
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = getenv('SMTP_PORT') ?? 587;
        
        // Recipients
        $mail->setFrom(getenv('SMTP_FROM') ?? 'noreply@kewer.com', 'Kewer System');
        $mail->addAddress($email, $nama);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Pendaftaran Bos Ditolak';
        $mail->Body = "
            <h2>Informasi Penolakan Pendaftaran</h2>
            <p>Halo, $nama</p>
            <p>Mohon maaf, pendaftaran Anda sebagai Bos di sistem Kewer tidak dapat disetujui.</p>
            <p><strong>Alasan:</strong> " . htmlspecialchars($rejection_reason) . "</p>
            <p>Jika Anda memiliki pertanyaan atau ingin melakukan pendaftaran ulang, silakan hubungi administrator.</p>
            <br>
            <p>Terima kasih.</p>
        ";
        $mail->AltBody = "
            Informasi Penolakan Pendaftaran\n\n
            Halo, $nama\n
            Mohon maaf, pendaftaran Anda sebagai Bos di sistem Kewer tidak dapat disetujui.\n
            Alasan: $rejection_reason\n
            Jika Anda memiliki pertanyaan atau ingin melakukan pendaftaran ulang, silakan hubungi administrator.\n\n
            Terima kasih.
        ";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email send failed: " . $e->getMessage());
        return false;
    }
}
