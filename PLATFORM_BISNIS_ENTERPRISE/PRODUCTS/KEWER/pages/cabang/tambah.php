<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/includes/alamat_helper.php';
require_once BASE_PATH . '/includes/people_helper.php';
requireLogin();

// Only users with manage_cabang permission can add cabang
if (!hasPermission('manage_cabang')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$error = '';
$success = '';
$current_user = getCurrentUser();

if ($_POST) {
    $kode_cabang = $_POST['kode_cabang'] ?? '';
    $nama_cabang = $_POST['nama_cabang'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $telp = $_POST['telp'] ?? '';
    $email = $_POST['email'] ?? '';
    $province_id = $_POST['province_id'] ?? '';
    $regency_id = $_POST['regency_id'] ?? '';
    $district_id = $_POST['district_id'] ?? '';
    $village_id = $_POST['village_id'] ?? '';
    $is_headquarters = $_POST['is_headquarters'] ?? '0';
    
    // Get current user
    $current_user = getCurrentUser();
    $owner_bos_id = null;
    
    // If current user is bos, set owner_bos_id
    if ($current_user && $current_user['role'] === 'bos') {
        $owner_bos_id = $current_user['id'];
    }
    
    // Validation
    if (empty($kode_cabang) || empty($nama_cabang)) {
        $error = 'Kode cabang dan nama cabang wajib diisi';
    } else {
        // Check duplicate kode_cabang
        $check = query("SELECT id FROM cabang WHERE kode_cabang = ?", [$kode_cabang]);
        if ($check) {
            $error = 'Kode cabang sudah digunakan';
        } else {
            // If bos, check if they already have a headquarters (only one allowed)
            if ($current_user && $current_user['role'] === 'bos' && $is_headquarters == '1') {
                $existing_hq = query("SELECT id FROM cabang WHERE owner_bos_id = ? AND is_headquarters = 1", [$current_user['id']]);
                if ($existing_hq) {
                    $error = 'Bos hanya dapat memiliki satu kantor pusat';
                }
            }
        }
        
        if (!$error) {
            // Insert cabang
            $result = query("INSERT INTO cabang (kode_cabang, nama_cabang, alamat, telp, email, province_id, regency_id, district_id, village_id, is_headquarters, owner_bos_id, created_by_user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                $kode_cabang, $nama_cabang, $alamat, $telp, $email, $province_id ?: null, $regency_id ?: null, $district_id ?: null, $village_id ?: null, $is_headquarters, $owner_bos_id, $current_user ? $current_user['id'] : null
            ]);
            
            if ($result) {
                $cabang_id = query("SELECT LAST_INSERT_ID() as id")[0]['id'];
                
                // Log the CRUD operation
                logCrudOperation('cabang', 'CREATE', $cabang_id, null, [
                    'kode_cabang' => $kode_cabang,
                    'nama_cabang' => $nama_cabang,
                    'alamat' => $alamat
                ]);
                
                // Create person record in db_orang for address management
                try {
                    createPerson([
                        'user_id' => 'cabang_' . $cabang_id,
                        'street_address' => $alamat,
                        'house_number' => '',
                        'province_id' => $province_id,
                        'regency_id' => $regency_id,
                        'district_id' => $district_id,
                        'village_id' => $village_id,
                        'postal_code' => ''
                    ]);
                } catch (Exception $e) {
                    // Log error but don't fail the cabang creation
                    error_log("Failed to create person record: " . $e->getMessage());
                }
                
                $success = 'Cabang berhasil ditambahkan';
                $_POST = [];
            } else {
                $error = 'Gagal menambahkan cabang';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Cabang - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>
        
        <main class="content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Tambah Cabang</h1>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                    <div class="text-center mb-4">
                        <a href="index.php" class="btn btn-primary">Lihat Daftar Cabang</a>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <form method="POST">
                                <?= csrfField() ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Kode Cabang *</label>
                                            <input type="text" name="kode_cabang" class="form-control" value="<?php echo $_POST['kode_cabang'] ?? ''; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Nama Cabang *</label>
                                            <input type="text" name="nama_cabang" class="form-control" value="<?php echo $_POST['nama_cabang'] ?? ''; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Telp</label>
                                            <input type="text" name="telp" class="form-control" value="<?php echo $_POST['telp'] ?? ''; ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" class="form-control" value="<?php echo $_POST['email'] ?? ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Provinsi</label>
                                            <?php echo provinceDropdown('province_id', $_POST['province_id'] ?? '', 'onchange="loadRegencies(this.value)"'); ?>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Kabupaten/Kota</label>
                                            <?php echo regencyDropdown('regency_id', $_POST['regency_id'] ?? '', $_POST['province_id'] ?? '', 'onchange="loadDistricts(this.value)"'); ?>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Kecamatan</label>
                                            <?php echo districtDropdown('district_id', $_POST['district_id'] ?? '', $_POST['regency_id'] ?? '', 'onchange="loadVillages(this.value)"'); ?>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Desa/Kelurahan</label>
                                            <?php echo villageDropdown('village_id', $_POST['village_id'] ?? '', $_POST['district_id'] ?? ''); ?>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Keterangan Tambahan (Opsional)</label>
                                            <textarea name="alamat" class="form-control" rows="2" placeholder="Nama jalan, nomor rumah, RT/RW (opsional)"><?php echo $_POST['alamat'] ?? ''; ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="aktif" <?php echo ($_POST['status'] ?? '') == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                                <option value="nonaktif" <?php echo ($_POST['status'] ?? '') == 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                                            </select>
                                        </div>
                                        <?php if ($current_user && $current_user['role'] === 'bos'): ?>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_headquarters" value="1" id="is_headquarters" <?php echo ($_POST['is_headquarters'] ?? '') == '1' ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="is_headquarters">
                                                    <strong>Jadikan Kantor Pusat</strong>
                                                </label>
                                                <small class="form-text d-block">Bos hanya dapat memiliki satu kantor pusat</small>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <a href="index.php" class="btn btn-secondary me-2">Batal</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Simpan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/kewer/includes/js/auto-focus.js"></script>
    <script src="/kewer/includes/js/enter-navigation.js"></script>
    <script src="/kewer/includes/js/alamat-loader.js"></script>
    <script>
    </script>
</body>
</html>
