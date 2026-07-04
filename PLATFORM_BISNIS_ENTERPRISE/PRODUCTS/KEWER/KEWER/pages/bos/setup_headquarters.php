<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/includes/alamat_helper.php';
require_once BASE_PATH . '/includes/people_helper.php';
requireLogin();

// Only bos can access this page
$user = getCurrentUser();
if (!$user || $user['role'] !== 'bos') {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

// Check if bos already has a headquarters
$existing_hq = query("SELECT * FROM cabang WHERE owner_bos_id = ? AND is_headquarters = 1", [$user['id']]);

if ($existing_hq) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$error = '';
$success = '';

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
    
    // Validation
    if (empty($kode_cabang) || empty($nama_cabang)) {
        $error = 'Kode cabang dan nama cabang wajib diisi';
    } else {
        // Check duplicate kode_cabang
        $check = query("SELECT id FROM cabang WHERE kode_cabang = ?", [$kode_cabang]);
        if ($check) {
            $error = 'Kode cabang sudah digunakan';
        } else {
            // Insert headquarters
            $result = query("INSERT INTO cabang (kode_cabang, nama_cabang, alamat, telp, email, province_id, regency_id, district_id, village_id, is_headquarters, owner_bos_id, created_by_user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?)", [
                $kode_cabang, $nama_cabang, $alamat, $telp, $email, $province_id ?: null, $regency_id ?: null, $district_id ?: null, $village_id ?: null, $user['id'], $user['id']
            ]);
            
            if ($result) {
                logAudit('CREATE', 'cabang', $result, null, ['kode_cabang' => $kode_cabang, 'nama_cabang' => $nama_cabang, 'is_headquarters' => true]);
                
                // Update bos cabang_id to headquarters
                query("UPDATE users SET cabang_id = ? WHERE id = ?", [$result, $user['id']]);
                
                $success = 'Kantor pusat berhasil dibuat. Anda sekarang dapat menambahkan karyawan dan cabang.';
                $_POST = [];
                
                // Redirect to dashboard after 3 seconds
                header('Refresh: 3; url=' . baseUrl('dashboard.php'));
            } else {
                $error = 'Gagal membuat kantor pusat';
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
    <title>Setup Kantor Pusat - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo baseUrl('dashboard.php'); ?>"><?php echo APP_NAME; ?></a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($user['nama']); ?> <span class="badge bg-secondary ms-1"><?php echo ucfirst($user['role']); ?></span>
                </span>
                <a class="nav-link" href="<?php echo baseUrl('logout.php'); ?>">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card mt-5">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-building"></i> Setup Kantor Pusat</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Selamat datang! Sebelum dapat menggunakan aplikasi, Anda harus membuat kantor pusat terlebih dahulu.
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?>
                                <div class="mt-3">
                                    <a href="<?php echo baseUrl('dashboard.php'); ?>" class="btn btn-primary">
                                        <i class="bi bi-speedometer2"></i> Ke Dashboard
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Kode Cabang *</label>
                                            <input type="text" name="kode_cabang" class="form-control" value="<?php echo $_POST['kode_cabang'] ?? 'HQ'; ?>" required>
                                            <small class="form-text">Kode unik untuk kantor pusat</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Nama Kantor Pusat *</label>
                                            <input type="text" name="nama_cabang" class="form-control" value="<?php echo $_POST['nama_cabang'] ?? 'Kantor Pusat'; ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">No. Telepon</label>
                                            <input type="text" name="telp" class="form-control" value="<?php echo $_POST['telp'] ?? ''; ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" class="form-control" value="<?php echo $_POST['email'] ?? ''; ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Provinsi *</label>
                                            <?php echo provinceDropdown('province_id', $_POST['province_id'] ?? '', 'onchange="loadRegencies(this.value)"', true); ?>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Kabupaten/Kota *</label>
                                            <?php echo regencyDropdown('regency_id', $_POST['regency_id'] ?? '', $_POST['province_id'] ?? '', 'onchange="loadDistricts(this.value)"', true); ?>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Kecamatan *</label>
                                            <?php echo districtDropdown('district_id', $_POST['district_id'] ?? '', $_POST['regency_id'] ?? '', 'onchange="loadVillages(this.value)"', true); ?>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Desa/Kelurahan *</label>
                                            <?php echo villageDropdown('village_id', $_POST['village_id'] ?? '', $_POST['district_id'] ?? '', true); ?>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Alamat Lengkap</label>
                                            <textarea name="alamat" class="form-control" rows="2" placeholder="Nama jalan, nomor rumah, RT/RW"><?php echo $_POST['alamat'] ?? ''; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-building"></i> Buat Kantor Pusat
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/kewer/includes/js/alamat-loader.js"></script>
    </script>
</body>
</html>
