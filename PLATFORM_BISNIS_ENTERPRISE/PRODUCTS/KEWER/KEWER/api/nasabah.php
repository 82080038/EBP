<?php
// Suppress errors to ensure JSON output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    require_once __DIR__ . '/../config/path.php';
    require_once BASE_PATH . '/includes/functions.php';
    require_once BASE_PATH . '/includes/business_logic.php';
    require_once BASE_PATH . '/includes/people_helper.php';
    require_once BASE_PATH . '/models/Nasabah.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit();
}

// Auth check
try {
    requireLogin();
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication failed: ' . $e->getMessage()]);
    exit();
}

try {
    $user = getCurrentUser();
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Failed to get user: ' . $e->getMessage()]);
    exit();
}

if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Validasi kantor_id: harus milik bos yang sedang login
$kantor_id = $_GET['kantor_id'] ?? null;

if ($kantor_id) {
    // Jika kantor_id eksplisit disertakan, validasi kepemilikan
    if (!validateCabangOwnership($kantor_id)) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden - Kantor ini bukan milik koperasi Anda']);
        exit();
    }
} else {
    // Default ke cabang pertama milik bos yang login
    $owned = getBosOwnedCabangIds();
    $kantor_id = $owned[0] ?? 1;
}

// Get kantor info
$kantor = query("SELECT * FROM cabang WHERE id = ?", [$kantor_id]);
if (!$kantor) {
    http_response_code(404);
    echo json_encode(['error' => 'Kantor tidak ditemukan']);
    exit();
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Get nasabah list
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $where = [];
        $params = [];
        
        if ($search) {
            $where[] = "(n.nama LIKE ? OR n.kode_nasabah LIKE ? OR n.ktp LIKE ? OR n.telp LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if ($status) {
            $where[] = "n.status = ?";
            $params[] = $status;
        }
        
        // Isolasi data: hanya tampilkan nasabah dari cabang milik bos yang login
        $cabang_filter = buildCabangFilter('n.cabang_id');
        if ($cabang_filter) {
            $where[] = ltrim($cabang_filter['clause'], 'AND ');
            $params  = array_merge($params, $cabang_filter['params']);
        }

        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $nasabah = query("
            SELECT n.*, c.nama_cabang 
            FROM nasabah n 
            LEFT JOIN cabang c ON n.cabang_id = c.id 
            $where_clause 
            ORDER BY n.created_at DESC
        ", $params);
        
        if (!is_array($nasabah)) $nasabah = [];
        
        echo json_encode([
            'success' => true,
            'data' => $nasabah,
            'total' => count($nasabah)
        ]);
        break;
        
    case 'POST':
        // Create new nasabah
        if (!hasPermission('manage_nasabah')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Forbidden - No permission to manage nasabah']);
            break;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        
        $nama = $input['nama'] ?? '';
        $alamat = $input['alamat'] ?? '';
        $province_id = $input['province_id'] ?? '';
        $regency_id = $input['regency_id'] ?? '';
        $district_id = $input['district_id'] ?? '';
        $village_id = $input['village_id'] ?? '';
        $ktp = $input['ktp'] ?? '';
        $telp = $input['telp'] ?? '';
        $email = $input['email'] ?? '';
        $jenis_usaha = $input['jenis_usaha'] ?? '';
        $lokasi_pasar = $input['lokasi_pasar'] ?? '';
        
        // Support new fields from db_orang structure
        $nama_depan = $input['nama_depan'] ?? null;
        $nama_tengah = $input['nama_tengah'] ?? null;
        $nama_belakang = $input['nama_belakang'] ?? null;
        $jenis_kelamin = $input['jenis_kelamin'] ?? null;
        $tanggal_lahir = $input['tanggal_lahir'] ?? null;
        $tempat_lahir = $input['tempat_lahir'] ?? null;
        $agama = $input['agama'] ?? null;
        $golongan_darah_id = $input['golongan_darah_id'] ?? null;
        $suku_id = $input['suku_id'] ?? null;
        $status_perkawinan_id = $input['status_perkawinan_id'] ?? null;
        $catatan = $input['catatan'] ?? null;
        
        // Validation
        if (!validateKTP($ktp)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Format KTP tidak valid']);
            break;
        }
        
        if (!validatePhone($telp)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Format telepon tidak valid']);
            break;
        }
        
        // Check duplicate KTP per koperasi (v2.2.0: satu nasabah bisa di banyak koperasi)
        $owner_bos = getOwnerBosId();
        $check = query("SELECT id FROM nasabah WHERE ktp = ? AND owner_bos_id = ?", [$ktp, $owner_bos]);
        if ($check) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'KTP sudah terdaftar di koperasi ini']);
            break;
        }

        // Cek apakah nasabah ini pernah pinjam di koperasi lain (platform blacklist)
        $platform_bl = query("SELECT id, platform_blacklist FROM nasabah WHERE ktp = ? AND platform_blacklist = 1 LIMIT 1", [$ktp]);
        if ($platform_bl) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'KTP ini diblacklist secara platform-wide. Tidak bisa didaftarkan di koperasi manapun.']);
            break;
        }
        
        // Generate kode nasabah
        $kode_nasabah = generateKode('NSB', 'nasabah', 'kode_nasabah');
        
        // Tentukan cabang_id dari request atau default
        $req_cabang = (int)($input['cabang_id'] ?? 0);
        if ($req_cabang && !validateCabangOwnership($req_cabang)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Cabang bukan milik koperasi Anda']);
            break;
        }
        $cabang_id_insert = $req_cabang ?: $kantor_id;

        // Use Nasabah model which integrates with db_orang
        $nasabahModel = new Nasabah();
        $nasabahData = [
            'kode_nasabah' => $kode_nasabah,
            'nama' => $nama,
            'nama_depan' => $nama_depan,
            'nama_tengah' => $nama_tengah,
            'nama_belakang' => $nama_belakang,
            'alamat' => $alamat,
            'province_id' => $province_id ?: null,
            'regency_id' => $regency_id ?: null,
            'district_id' => $district_id ?: null,
            'village_id' => $village_id ?: null,
            'ktp' => $ktp,
            'telp' => $telp,
            'email' => $email,
            'jenis_usaha' => $jenis_usaha,
            'lokasi_pasar' => $lokasi_pasar,
            'jenis_kelamin' => $jenis_kelamin,
            'tanggal_lahir' => $tanggal_lahir,
            'tempat_lahir' => $tempat_lahir,
            'agama' => $agama,
            'golongan_darah_id' => $golongan_darah_id,
            'suku_id' => $suku_id,
            'status_perkawinan_id' => $status_perkawinan_id,
            'catatan' => $catatan,
            'status' => 'aktif'
        ];
        
        // Note: We still need to insert directly to nasabah table for owner_bos_id and cabang_id
        // The model creates person in db_orang, but we need to add the nasabah record
        $result = query("INSERT INTO nasabah (cabang_id, owner_bos_id, kode_nasabah, nama, alamat, province_id, regency_id, district_id, village_id, ktp, telp, email, jenis_usaha, lokasi_pasar, skor_kredit) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $cabang_id_insert, $owner_bos, $kode_nasabah, $nama, $alamat,
            $province_id ?: null, $regency_id ?: null, $district_id ?: null, $village_id ?: null,
            $ktp, $telp, $email, $jenis_usaha, $lokasi_pasar, 100
        ]);
        
        if ($result) {
            $nasabah_id = query("SELECT LAST_INSERT_ID() as id")[0]['id'];
            
            // Now create/update person in db_orang with the new fields
            if ($nasabah_id) {
                $person_data = array_merge($nasabahData, [
                    'kewer_nasabah_id' => $nasabah_id
                ]);
                $person_id = createPerson($person_data);
                
                if ($person_id) {
                    // Update nasabah with db_orang_user_id
                    query("UPDATE nasabah SET db_orang_user_id = ? WHERE id = ?", [$person_id, $nasabah_id]);
                    
                    // Create address in db_orang
                    if (!empty($alamat) || !empty($province_id)) {
                        $address_data = [
                            'label' => 'rumah',
                            'street_address' => $alamat,
                            'province_id' => $province_id ?: null,
                            'regency_id' => $regency_id ?: null,
                            'district_id' => $district_id ?: null,
                            'village_id' => $village_id ?: null,
                            'postal_code' => $input['kode_pos'] ?? null,
                            'is_primary' => 1
                        ];
                        createPersonAddress($person_id, $address_data);
                    }
                }
            }
            
            $result = query("SELECT * FROM nasabah WHERE id = ?", [$nasabah_id]);
            $new_nasabah = is_array($result) && isset($result[0]) ? $result[0] : null;
            echo json_encode([
                'success' => true,
                'message' => 'Nasabah berhasil ditambahkan',
                'data' => $new_nasabah
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Gagal menambahkan nasabah']);
        }
        break;
        
    case 'PUT':
        // Update nasabah
        $nasabah_id = $_GET['id'] ?? '';
        if (!$nasabah_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID required']);
            break;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Check if nasabah exists
        $nasabah = query("SELECT * FROM nasabah WHERE id = ?", [$nasabah_id]);
        if (!$nasabah) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Nasabah tidak ditemukan']);
            break;
        }
        
        // Update fields
        $fields = [];
        $params = [];
        
        // Pastikan nasabah milik koperasi yang login
        $nasabah_existing = $nasabah[0];
        $owner_bos_upd = getOwnerBosId();
        if ($nasabah_existing['owner_bos_id'] && $nasabah_existing['owner_bos_id'] != $owner_bos_upd && getCurrentUser()['role'] !== 'appOwner') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Nasabah bukan milik koperasi Anda']);
            break;
        }

        $updatable_fields = ['nama', 'alamat', 'province_id', 'regency_id', 'district_id', 'village_id', 'telp', 'email', 'jenis_usaha', 'lokasi_pasar', 'status', 'catatan_risiko', 'alamat_rumah'];
        
        foreach ($updatable_fields as $field) {
            if (isset($input[$field])) {
                $fields[] = "$field = ?";
                $params[] = $input[$field];
            }
        }
        
        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No fields to update']);
            break;
        }
        
        $params[] = $nasabah_id;
        
        $result = query("UPDATE nasabah SET " . implode(', ', $fields) . " WHERE id = ?", $params);
        
        // Update person data in db_orang if db_orang_user_id exists
        if ($result && $nasabah_existing['db_orang_user_id']) {
            $person_fields = [];
            $person_params = [];
            
            $person_updatable = [
                'nama', 'nama_depan', 'nama_tengah', 'nama_belakang',
                'telp', 'email', 'jenis_kelamin', 'tanggal_lahir', 'tempat_lahir',
                'agama', 'pekerjaan', 'golongan_darah_id', 'suku_id', 'status_perkawinan_id', 'catatan'
            ];
            
            foreach ($person_updatable as $field) {
                if (isset($input[$field])) {
                    $person_fields[] = "$field = ?";
                    $person_params[] = $input[$field];
                }
            }
            
            if (!empty($person_fields)) {
                $person_params[] = $nasabah_existing['db_orang_user_id'];
                query_orang("UPDATE people SET " . implode(', ', $person_fields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = ?", $person_params);
            }
            
            // Update address in db_orang if address data changed
            if (isset($input['alamat']) || isset($input['province_id'])) {
                $address = getPrimaryAddress($nasabah_existing['db_orang_user_id']);
                if ($address) {
                    $addr_fields = [];
                    $addr_params = [];
                    
                    if (isset($input['alamat'])) {
                        $addr_fields[] = "street_address = ?";
                        $addr_params[] = $input['alamat'];
                    }
                    if (isset($input['province_id'])) {
                        $addr_fields[] = "province_id = ?";
                        $addr_params[] = $input['province_id'];
                    }
                    if (isset($input['regency_id'])) {
                        $addr_fields[] = "regency_id = ?";
                        $addr_params[] = $input['regency_id'];
                    }
                    if (isset($input['district_id'])) {
                        $addr_fields[] = "district_id = ?";
                        $addr_params[] = $input['district_id'];
                    }
                    if (isset($input['village_id'])) {
                        $addr_fields[] = "village_id = ?";
                        $addr_params[] = $input['village_id'];
                    }
                    if (isset($input['kode_pos'])) {
                        $addr_fields[] = "postal_code = ?";
                        $addr_params[] = $input['kode_pos'];
                    }
                    
                    if (!empty($addr_fields)) {
                        $addr_fields[] = "updated_at = CURRENT_TIMESTAMP";
                        $addr_params[] = $address['id'];
                        query_orang("UPDATE addresses SET " . implode(', ', $addr_fields) . " WHERE id = ?", $addr_params);
                    }
                }
            }
        }
        
        if ($result) {
            $updated_nasabah_result = query("SELECT * FROM nasabah WHERE id = ?", [$nasabah_id]);
            $updated_nasabah = is_array($updated_nasabah_result) && isset($updated_nasabah_result[0]) ? $updated_nasabah_result[0] : null;
            echo json_encode([
                'success' => true,
                'message' => 'Data nasabah berhasil diperbarui',
                'data' => $updated_nasabah
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Gagal memperbarui data nasabah']);
        }
        break;
        
    case 'DELETE':
        // Delete nasabah
        $nasabah_id = $_GET['id'] ?? '';
        if (!$nasabah_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID required']);
            break;
        }
        
        // Check if nasabah exists and has no active loans
        $nasabah = query("SELECT * FROM nasabah WHERE id = ?", [$nasabah_id]);
        if (!$nasabah) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Nasabah tidak ditemukan']);
            break;
        }
        
        $active_loans_result = query("SELECT COUNT(*) as count FROM pinjaman WHERE nasabah_id = ? AND status IN ('pengajuan', 'disetujui', 'aktif')", [$nasabah_id]);
        $active_loans = is_array($active_loans_result) && isset($active_loans_result[0]) ? $active_loans_result[0]['count'] : 0;
        
        if ($active_loans > 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Tidak dapat menghapus nasabah yang masih memiliki pinjaman aktif']);
            break;
        }
        
        $result = query("DELETE FROM nasabah WHERE id = ?", [$nasabah_id]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Nasabah berhasil dihapus'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Gagal menghapus nasabah']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
