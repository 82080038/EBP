<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/includes/alamat_helper.php';
require_once BASE_PATH . '/includes/people_helper.php';
require_once BASE_PATH . '/includes/business_logic.php';
requireLogin();

// Permission check
if (!hasPermission('manage_nasabah')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$error = '';
$success = '';

if ($_POST) {
    $nama = $_POST['nama'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $province_id = $_POST['province_id'] ?? '';
    $regency_id = $_POST['regency_id'] ?? '';
    $district_id = $_POST['district_id'] ?? '';
    $village_id = $_POST['village_id'] ?? '';
    $ktp = $_POST['ktp'] ?? '';
    $telp = $_POST['telp'] ?? '';
    $jenis_usaha = $_POST['jenis_usaha'] ?? '';
    $lokasi_pasar = $_POST['lokasi_pasar'] ?? '';
    $alamat_rumah = $_POST['alamat_rumah'] ?? '';

    // Resolve cabang + owner untuk INSERT
    $user_now   = getCurrentUser();
    $owner_bos  = getOwnerBosId();
    $owned_ids  = getBosOwnedCabangIds();
    $kantor_id  = $user_now['cabang_id'] ?? ($owned_ids[0] ?? 1);
    
    // Validation
    if (!validateKTP($ktp)) {
        $error = 'Format KTP tidak valid (16 digit angka)';
    } elseif (!validatePhone($telp)) {
        $error = 'Format telepon tidak valid (08xxxxxxxxxx)';
    } else {
        // Cek platform blacklist
        $platform_bl = query("SELECT id FROM nasabah WHERE ktp = ? AND platform_blacklist = 1 LIMIT 1", [$ktp]);
        if ($platform_bl) {
            $error = 'KTP ini diblacklist platform-wide. Tidak bisa mendaftar di koperasi manapun.';
        }
        // Check duplicate KTP per koperasi
        $check = !$platform_bl ? query("SELECT id FROM nasabah WHERE ktp = ? AND owner_bos_id = ?", [$ktp, $owner_bos]) : null;
        if ($check) {
            $error = 'KTP sudah terdaftar di koperasi ini';
        }
        if (!$error) {
            // Generate kode nasabah
            $kode_nasabah = generateKode('NSB', 'nasabah', 'kode_nasabah');
            
            // Handle file uploads
            $foto_ktp = '';
            $foto_selfie = '';
            
            if (isset($_FILES['foto_ktp']) && $_FILES['foto_ktp']['error'] === 0) {
                $foto_ktp = 'uploads/ktp_' . $kode_nasabah . '_' . time() . '.jpg';
                move_uploaded_file($_FILES['foto_ktp']['tmp_name'], BASE_PATH . '/' . $foto_ktp);
            }

            if (isset($_FILES['foto_selfie']) && $_FILES['foto_selfie']['error'] === 0) {
                $foto_selfie = 'uploads/selfie_' . $kode_nasabah . '_' . time() . '.jpg';
                move_uploaded_file($_FILES['foto_selfie']['tmp_name'], BASE_PATH . '/' . $foto_selfie);
            }
            
            // Insert nasabah dengan owner_bos_id dan skor_kredit
            $result = query("INSERT INTO nasabah (cabang_id, owner_bos_id, kode_nasabah, nama, alamat, alamat_rumah, province_id, regency_id, district_id, village_id, ktp, telp, jenis_usaha, lokasi_pasar, foto_ktp, foto_selfie, skor_kredit) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                $kantor_id, $owner_bos, $kode_nasabah, $nama, $alamat, $alamat_rumah ?: null,
                $province_id ?: null, $regency_id ?: null, $district_id ?: null, $village_id ?: null,
                $ktp, $telp, $jenis_usaha, $lokasi_pasar, $foto_ktp, $foto_selfie, 100
            ]);
            
            if ($result) {
                $nasabah_id = query("SELECT LAST_INSERT_ID() as id")[0]['id'];
                
                // Log the CRUD operation
                logCrudOperation('nasabah', 'CREATE', $nasabah_id, null, [
                    'cabang_id' => $kantor_id,
                    'owner_bos_id' => $owner_bos,
                    'kode_nasabah' => $kode_nasabah,
                    'nama' => $nama,
                    'ktp' => $ktp
                ]);
                
                // Create person record in db_orang for address management
                try {
                    createPerson([
                        'user_id' => 'nasabah_' . $nasabah_id,
                        'street_address' => $alamat,
                        'house_number' => '',
                        'province_id' => $province_id,
                        'regency_id' => $regency_id,
                        'district_id' => $district_id,
                        'village_id' => $village_id,
                        'postal_code' => ''
                    ]);
                } catch (Exception $e) {
                    // Log error but don't fail the nasabah creation
                    error_log("Failed to create person record: " . $e->getMessage());
                }
                
                $success = 'Nasabah berhasil ditambahkan';
                // Clear form
                $_POST = [];
            } else {
                $error = 'Gagal menambahkan nasabah';
            }
        } // end !$error check
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Nasabah - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>
        
        <main class="content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Tambah Nasabah</h1>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <?= csrfField() ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Lengkap *</label>
                                        <input type="text" name="nama" class="form-control" value="<?php echo $_POST['nama'] ?? ''; ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">No. KTP *</label>
                                        <input type="text" name="ktp" class="form-control" value="<?php echo $_POST['ktp'] ?? ''; ?>" maxlength="16" required>
                                        <small class="form-text">16 digit angka</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">No. Telepon *</label>
                                        <input type="tel" name="telp" class="form-control" value="<?php echo $_POST['telp'] ?? ''; ?>" required>
                                        <small class="form-text">08xxxxxxxxxx</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Jenis Usaha</label>
                                        <select name="jenis_usaha" class="form-select">
                                            <option value="">Pilih Jenis Usaha</option>
                                            <?php
                                            $jenis_usaha_list = getActiveReferenceData('ref_jenis_usaha');
                                            if (is_array($jenis_usaha_list)):
                                                foreach ($jenis_usaha_list as $ju):
                                                    $kode = str_replace('J', '', strtolower($ju['jenis_kode']));
                                            ?>
                                                <option value="<?php echo $kode; ?>" <?php echo ($_POST['jenis_usaha'] ?? '') === $kode ? 'selected' : ''; ?>><?php echo htmlspecialchars($ju['jenis_nama']); ?></option>
                                            <?php
                                                endforeach;
                                            endif;
                                            ?>
                                        </select>
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
                                        <label class="form-label">Lokasi Pasar/Warung</label>
                                        <input type="text" name="lokasi_pasar" class="form-control" value="<?php echo $_POST['lokasi_pasar'] ?? ''; ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Foto KTP</label>
                                        <div class="input-group">
                                            <input type="file" name="foto_ktp" class="form-control" accept="image/*" id="foto_ktp">
                                            <button type="button" class="btn btn-outline-secondary" onclick="scanKTP()">
                                                <i class="bi bi-camera"></i> Scan OCR
                                            </button>
                                        </div>
                                        <small class="form-text">Upload foto KTP dan klik Scan OCR untuk ekstrak data otomatis</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Foto Selfie + KTP</label>
                                        <input type="file" name="foto_selfie" class="form-control" accept="image/*">
                                    </div>
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
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/kewer/includes/js/auto-focus.js"></script>
    <script src="/kewer/includes/js/enter-navigation.js"></script>
    <script src="/kewer/includes/js/alamat-loader.js"></script>
    <script>
        function scanKTP() {
            const fileInput = document.getElementById('foto_ktp');
            const file = fileInput.files[0];
            
            if (!file) {
                alert('Pilih foto KTP terlebih dahulu');
                return;
            }
            
            const formData = new FormData();
            formData.append('ktp_image', file);
            
            fetch('/api/ocr?action=scan', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer kewer-api-token-2024'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const extracted = data.data;
                    
                    // Fill form fields with extracted data
                    if (extracted.nama) {
                        document.querySelector('input[name="nama"]').value = extracted.nama;
                    }
                    if (extracted.nik) {
                        document.querySelector('input[name="ktp"]').value = extracted.nik;
                    }
                    if (extracted.alamat) {
                        document.querySelector('textarea[name="alamat"]').value = extracted.alamat;
                    }
                    if (extracted.tempat_lahir) {
                        // Could add tempat_lahir field if needed
                    }
                    if (extracted.tanggal_lahir) {
                        // Could add tanggal_lahir field if needed
                    }
                    
                    alert('Data KTP berhasil diekstrak! Silakan periksa dan lengkapi data yang kurang.');
                } else {
                    alert('Gagal scan KTP: ' + data.error);
                }
            })
            .catch(error => {
                alert('Terjadi kesalahan: ' + error.message);
            });
        }
    </script>
</body>
</html>
