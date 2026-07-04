<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/includes/alamat_helper.php';
require_once BASE_PATH . '/includes/people_helper.php';
requireLogin();
// Only users with manage_cabang permission can edit cabang
if (!hasPermission('manage_cabang')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$id = $_GET['id'] ?? '';
if (!$id) {
    header('Location: ' . baseUrl('pages/cabang/index.php'));
    exit();
}

$cabang = query("SELECT * FROM cabang WHERE id = ?", [$id]);
if (!$cabang) {
    header('Location: index.php');
    exit();
}
$cabang = $cabang[0];

if ($_POST) {
    $nama_cabang = $_POST['nama_cabang'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $telp = $_POST['telp'] ?? '';
    $email = $_POST['email'] ?? '';
    $province_id = $_POST['province_id'] ?? '';
    $regency_id = $_POST['regency_id'] ?? '';
    $district_id = $_POST['district_id'] ?? '';
    $village_id = $_POST['village_id'] ?? '';
    $status = $_POST['status'] ?? 'aktif';
    
    $result = query("UPDATE cabang SET nama_cabang = ?, alamat = ?, telp = ?, email = ?, province_id = ?, regency_id = ?, district_id = ?, village_id = ?, status = ? WHERE id = ?", [
        $nama_cabang,
        $alamat,
        $telp,
        $email,
        $province_id ?: null,
        $regency_id ?: null,
        $district_id ?: null,
        $village_id ?: null,
        $status,
        $id
    ]);
    
    if ($result) {
        logAudit('UPDATE', 'cabang', $id, $cabang, ['nama_cabang' => $nama_cabang, 'status' => $status]);
        $_SESSION['success'] = 'Cabang berhasil diupdate';
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['error'] = 'Gagal mengupdate cabang';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Cabang - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>
        
        <main class="content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Edit Cabang</h1>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label>Kode Cabang</label>
                                <input type="text" class="form-control" value="<?= $cabang['kode_cabang'] ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label>Nama Cabang *</label>
                                <input type="text" name="nama_cabang" class="form-control" value="<?= $cabang['nama_cabang'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Telp</label>
                                <input type="text" name="telp" class="form-control" value="<?= $cabang['telp'] ?? '' ?>">
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="<?= $cabang['email'] ?? '' ?>">
                            </div>
                            <div class="mb-3">
                                <label>Provinsi</label>
                                <?php echo provinceDropdown('province_id', $cabang['province_id'] ?? '', 'onchange="loadRegencies(this.value)"'); ?>
                            </div>
                            <div class="mb-3">
                                <label>Kabupaten/Kota</label>
                                <?php echo regencyDropdown('regency_id', $cabang['regency_id'] ?? '', $cabang['province_id'] ?? '', 'onchange="loadDistricts(this.value)"'); ?>
                            </div>
                            <div class="mb-3">
                                <label>Kecamatan</label>
                                <?php echo districtDropdown('district_id', $cabang['district_id'] ?? '', $cabang['regency_id'] ?? '', 'onchange="loadVillages(this.value)"'); ?>
                            </div>
                            <div class="mb-3">
                                <label>Desa/Kelurahan</label>
                                <?php echo villageDropdown('village_id', $cabang['village_id'] ?? '', $cabang['district_id'] ?? ''); ?>
                            </div>
                            <div class="mb-3">
                                <label>Keterangan Tambahan (Opsional)</label>
                                <textarea name="alamat" class="form-control" rows="2" placeholder="Nama jalan, nomor rumah, RT/RW (opsional)"><?= $cabang['alamat'] ?? '' ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label>Status</label>
                                <select name="status" class="form-select">
                                    <option value="aktif" <?= $cabang['status'] == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="nonaktif" <?= $cabang['status'] == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                                </select>
                            </div>
                            <div class="text-end">
                                <a href="index.php" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/kewer/includes/js/alamat-loader.js"></script>
    <script>
        // Initialize province dropdown
    </script>
</body>
</html>
