<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/includes/alamat_helper.php';
require_once BASE_PATH . '/includes/people_helper.php';

$error = '';
$success = '';

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
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
        $error = 'Username, password, nama, dan nama perusahaan wajib diisi';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter';
    } else {
        // Send to API
        $data = [
            'username' => $username,
            'password' => $password,
            'nama' => $nama,
            'email' => $email,
            'telp' => $telp,
            'nama_perusahaan' => $nama_perusahaan,
            'alamat' => $alamat,
            'province_id' => $province_id ?: '',
            'regency_id' => $regency_id ?: '',
            'district_id' => $district_id ?: '',
            'village_id' => $village_id ?: ''
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, baseUrl('api/bos_registration.php?action=register'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($result && $result['success']) {
            $success = $result['data']['message'] ?? $result['message'] ?? 'Pendaftaran berhasil dikirim. Menunggu persetujuan App Owner.';
            $_POST = [];
        } else {
            $error = $result['message'] ?? $result['error'] ?? 'Gagal mendaftar';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Bos - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card mt-5">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-person-plus"></i> Pendaftaran Bos</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?>
                                <div class="mt-3">
                                    <a href="<?php echo baseUrl('login.php'); ?>" class="btn btn-primary">
                                        <i class="bi bi-box-arrow-in-right"></i> Login
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Username *</label>
                                            <input type="text" name="username" class="form-control" value="<?php echo $_POST['username'] ?? ''; ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Password *</label>
                                            <input type="password" name="password" class="form-control">
                                            <small class="form-text">Minimal 6 karakter</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Konfirmasi Password *</label>
                                            <input type="password" name="confirm_password" class="form-control">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Nama Lengkap *</label>
                                            <input type="text" name="nama" class="form-control" value="<?php echo $_POST['nama'] ?? ''; ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Nama Perusahaan *</label>
                                            <input type="text" name="nama_perusahaan" class="form-control" value="<?php echo $_POST['nama_perusahaan'] ?? ''; ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" class="form-control" value="<?php echo $_POST['email'] ?? ''; ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">No. Telepon</label>
                                            <input type="text" name="telp" class="form-control" value="<?php echo $_POST['telp'] ?? ''; ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Provinsi</label>
                                            <?php echo provinceDropdown('province_id', $_POST['province_id'] ?? '', 'onchange="loadRegencies(this.value)"', true); ?>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Kabupaten/Kota</label>
                                            <?php echo regencyDropdown('regency_id', $_POST['regency_id'] ?? '', $_POST['province_id'] ?? '', 'onchange="loadDistricts(this.value)"', true); ?>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Kecamatan</label>
                                            <?php echo districtDropdown('district_id', $_POST['district_id'] ?? '', $_POST['regency_id'] ?? '', 'onchange="loadVillages(this.value)"', true); ?>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Desa/Kelurahan</label>
                                            <?php echo villageDropdown('village_id', $_POST['village_id'] ?? '', $_POST['district_id'] ?? '', true); ?>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Alamat Lengkap</label>
                                            <textarea name="alamat" class="form-control" rows="2" placeholder="Nama jalan, nomor rumah, RT/RW"><?php echo $_POST['alamat'] ?? ''; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-end">
                                    <a href="<?php echo baseUrl('login.php'); ?>" class="btn btn-secondary me-2">Batal</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-send"></i> Daftar
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <small class="text-muted">Sudah punya akun? <a href="<?php echo baseUrl('login.php'); ?>">Login disini</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/kewer/includes/js/alamat-loader.js"></script>
</body>
</html>
