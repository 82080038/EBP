<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/includes/alamat_helper.php';
require_once BASE_PATH . '/includes/people_helper.php';

$error = '';
$success = '';
$role = $_POST['role'] ?? '';
$cabang_list = query("SELECT id, nama_cabang FROM cabang WHERE status = 'aktif' ORDER BY is_headquarters DESC, nama_cabang ASC");
if (!is_array($cabang_list)) $cabang_list = [];

// Tentukan owner_bos_id dari user yang sedang login
$current_user = getCurrentUser();
$owner_bos_id = null;
if ($current_user && $current_user['role'] === 'bos') {
    $owner_bos_id = $current_user['id'];
} elseif ($current_user && $current_user['owner_bos_id']) {
    $owner_bos_id = $current_user['owner_bos_id'];
}

// Endpoint AJAX: lookup NIK ke db_orang
if (isset($_GET['lookup_ktp'])) {
    header('Content-Type: application/json');
    $ktp = trim($_GET['lookup_ktp'] ?? '');
    $person = findPersonByKtp($ktp);
    if ($person) {
        echo json_encode(['found' => true, 'nama' => $person['nama'], 'telp' => $person['telp'], 'email' => $person['email'], 'tanggal_lahir' => $person['tanggal_lahir']]);
    } else {
        echo json_encode(['found' => false]);
    }
    exit();
}

if ($_POST) {
    $username      = trim($_POST['username'] ?? '');
    $password      = $_POST['password'] ?? '';
    $ktp           = trim($_POST['ktp'] ?? '');
    $nama          = trim($_POST['nama'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $telp          = trim($_POST['telp'] ?? '');
    $role          = $_POST['role'] ?? '';
    $cabang_id_post = $_POST['cabang_id'] ?? '';
    $gaji          = $_POST['gaji'] ?? 0;
    $limit_kasbon  = $_POST['limit_kasbon'] ?? 0;
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $tanggal_masuk = $_POST['tanggal_masuk'] ?? '';

    // Validasi wajib
    if (empty($username) || empty($password) || empty($ktp) || empty($nama) || empty($role)) {
        $error = 'Username, password, NIK/KTP, nama, dan role wajib diisi';
    } elseif (strlen($ktp) !== 16 || !ctype_digit($ktp)) {
        $error = 'NIK/KTP harus 16 digit angka';
    } else {
        // Cek apakah NIK sudah AKTIF di koperasi ini (bos yang sama)
        $aktif_di_koperasi = isKtpActiveInKoperasi($ktp, $owner_bos_id);
        if ($aktif_di_koperasi) {
            $error = "NIK ini sudah terdaftar aktif sebagai \"{$aktif_di_koperasi['nama']}\" ({$aktif_di_koperasi['username']}) di koperasi Anda. Tidak boleh duplikat.";
        } else {
            // Cek duplikat username
            $check_username = query("SELECT id FROM users WHERE username = ?", [$username]);
            if ($check_username) {
                $error = 'Username sudah digunakan';
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $cabang_tujuan = $cabang_id_post ?: 1;

                // Simpan user ke kewer.users (termasuk kolom ktp baru)
                $result = query(
                    "INSERT INTO users (username, password, ktp, nama, email, telp, role, cabang_id, status, owner_bos_id, gaji, limit_kasbon, tanggal_lahir, tanggal_masuk)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'aktif', ?, ?, ?, ?, ?)",
                    [$username, $password_hash, $ktp, $nama, $email, $telp, $role, $cabang_tujuan, $owner_bos_id, $gaji, $limit_kasbon, $tanggal_lahir ?: null, $tanggal_masuk ?: null]
                );

                if ($result) {
                    $new_user_id = query("SELECT LAST_INSERT_ID() as id")[0]['id'];

                    // Buat/update record di db_orang.people
                    try {
                        $existing_person = findPersonByKtp($ktp);
                        if ($existing_person) {
                            // NIK dikenal (pernah bekerja di koperasi lain) — buat record baru dengan kewer_user_id baru
                            createPersonForReturningStaff($new_user_id, $ktp, $nama, [
                                'telp' => $telp, 'email' => $email, 'tanggal_lahir' => $tanggal_lahir ?: null,
                            ]);
                        } else {
                            // NIK baru di platform — buat record pertama kali
                            createPerson([
                                'kewer_user_id' => $new_user_id,
                                'nama'          => $nama,
                                'ktp'           => $ktp,
                                'telp'          => $telp,
                                'email'         => $email,
                                'tanggal_lahir' => $tanggal_lahir ?: null,
                                'pekerjaan'     => $role,
                            ]);
                        }

                        // Update db_orang_person_id di users
                        $person_row = query_orang("SELECT id FROM people WHERE kewer_user_id = ? ORDER BY id DESC LIMIT 1", [$new_user_id]);
                        if ($person_row && isset($person_row[0])) {
                            query("UPDATE users SET db_orang_person_id = ? WHERE id = ?", [$person_row[0]['id'], $new_user_id]);
                        }
                    } catch (Exception $e) {
                        error_log("people_helper error saat tambah petugas: " . $e->getMessage());
                    }

                    $success = 'Petugas berhasil ditambahkan';
                    $_POST = [];
                } else {
                    $error = 'Gagal menambahkan petugas';
                }
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
    <title>Tambah Petugas - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>
        
        <main class="content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Tambah Petugas</h1>
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
                        <form method="POST" id="formTambahPetugas">
                            <?= csrfField() ?>

                            <!-- NIK / KTP — wajib, lookup pertama -->
                            <div class="card border-primary mb-4">
                                <div class="card-header bg-primary text-white fw-semibold">
                                    <i class="bi bi-person-vcard"></i> Identitas KTP (Wajib)
                                </div>
                                <div class="card-body">
                                    <div class="row align-items-end">
                                        <div class="col-md-8">
                                            <label class="form-label fw-semibold">NIK / No. KTP <span class="text-danger">*</span></label>
                                            <input type="text" name="ktp" id="inputKtp" class="form-control form-control-lg font-monospace"
                                                   maxlength="16" inputmode="numeric" pattern="[0-9]{16}"
                                                   placeholder="16 digit angka"
                                                   value="<?= htmlspecialchars($_POST['ktp'] ?? '') ?>" required>
                                            <div class="form-text">NIK digunakan sebagai identitas global. Jika pernah terdaftar di koperasi lain, data akan diketahui otomatis.</div>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" class="btn btn-outline-primary w-100" id="btnLookupKtp">
                                                <i class="bi bi-search"></i> Cek NIK
                                            </button>
                                        </div>
                                    </div>
                                    <div id="ktpInfo" class="mt-3 d-none"></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Username <span class="text-danger">*</span></label>
                                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                                        <small class="form-text">Unik di seluruh platform</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Password <span class="text-danger">*</span></label>
                                        <input type="password" name="password" class="form-control" required>
                                        <small class="form-text">Minimal 6 karakter</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                        <input type="password" name="confirm_password" class="form-control" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" name="nama" id="inputNama" class="form-control" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="<?php echo $_POST['email'] ?? ''; ?>">
                                        <small class="form-text">Opsional</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Role *</label>
                                        <select name="role" class="form-select" id="roleSelect" required>
                                            <option value="">Pilih Role</option>
                                            <option value="bos" <?php echo $role === 'bos' ? 'selected' : ''; ?>>Bos</option>
                                            <option value="manager_pusat" <?php echo $role === 'manager_pusat' ? 'selected' : ''; ?>>Manager Pusat</option>
                                            <option value="manager_cabang" <?php echo $role === 'manager_cabang' ? 'selected' : ''; ?>>Manager Cabang</option>
                                            <option value="admin_pusat" <?php echo $role === 'admin_pusat' ? 'selected' : ''; ?>>Admin Pusat</option>
                                            <option value="admin_cabang" <?php echo $role === 'admin_cabang' ? 'selected' : ''; ?>>Admin Cabang</option>
                                            <option value="petugas_pusat" <?php echo $role === 'petugas_pusat' ? 'selected' : ''; ?>>Petugas Pusat</option>
                                            <option value="petugas_cabang" <?php echo $role === 'petugas_cabang' ? 'selected' : ''; ?>>Petugas Cabang</option>
                                            <option value="teller" <?php echo $role === 'teller' ? 'selected' : ''; ?>>Teller</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3" id="cabangField">
                                        <label class="form-label">Cabang *</label>
                                        <select name="cabang_id" class="form-select" id="cabangSelect">
                                            <option value="">Pilih Cabang</option>
                                            <?php foreach ($cabang_list as $c): ?>
                                                <option value="<?php echo $c['id']; ?>" <?php echo ($_POST['cabang_id'] ?? '') == $c['id'] ? 'selected' : ''; ?>>
                                                    <?php echo $c['nama_cabang']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="form-text">Tidak wajib untuk Bos</small>
                                    </div>
                                    
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6><i class="bi bi-info-circle"></i> Informasi Role</h6>
                                            <ul class="mb-0 small">
                                                <li><strong>Bos:</strong> Akses penuh ke semua fitur</li>
                                                <li><strong>Admin:</strong> Kelola cabang tertentu</li>
                                                <li><strong>Petugas:</strong> Input data dasar, tidak bisa approve pinjaman</li>
                                            </ul>
                                        </div>
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
    <script>
        // Toggle cabang field based on role
        document.getElementById('roleSelect').addEventListener('change', function() {
            const cabangField = document.getElementById('cabangField');
            const cabangSelect = document.getElementById('cabangSelect');
            const pusatRoles = ['bos', 'manager_pusat', 'admin_pusat', 'petugas_pusat'];
            if (pusatRoles.includes(this.value)) {
                cabangField.style.display = 'none';
                cabangSelect.required = false;
            } else {
                cabangField.style.display = 'block';
                cabangSelect.required = true;
            }
        });
        document.getElementById('roleSelect').dispatchEvent(new Event('change'));

        // Lookup NIK ke db_orang
        document.getElementById('btnLookupKtp').addEventListener('click', async function() {
            const ktp = document.getElementById('inputKtp').value.trim();
            const infoDiv = document.getElementById('ktpInfo');

            if (ktp.length !== 16 || !/^[0-9]{16}$/.test(ktp)) {
                infoDiv.className = 'mt-3 alert alert-warning';
                infoDiv.innerHTML = '<i class="bi bi-exclamation-triangle"></i> NIK harus 16 digit angka.';
                return;
            }

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mencari...';

            try {
                const res  = await fetch(`?lookup_ktp=${ktp}`);
                const data = await res.json();

                if (data.found) {
                    // Auto-fill data dari db_orang
                    if (data.nama)          document.getElementById('inputNama').value = data.nama;
                    if (data.telp)          document.querySelector('[name="telp"]').value = data.telp;
                    if (data.email)         document.querySelector('[name="email"]').value = data.email;
                    if (data.tanggal_lahir) document.querySelector('[name="tanggal_lahir"]')?.setAttribute('value', data.tanggal_lahir);

                    infoDiv.className = 'mt-3 alert alert-info';
                    infoDiv.innerHTML = `<i class="bi bi-info-circle"></i>
                        <strong>NIK dikenal di platform.</strong> Data identitas diisi otomatis.
                        Karyawan ini pernah terdaftar di koperasi lain — setelah disimpan,
                        ia <strong>tidak bisa melihat data koperasi lama</strong>.
                        Verifikasi data sebelum menyimpan.`;
                } else {
                    infoDiv.className = 'mt-3 alert alert-success';
                    infoDiv.innerHTML = '<i class="bi bi-check-circle"></i> NIK baru di platform. Silakan isi data lengkap.';
                }
            } catch(e) {
                infoDiv.className = 'mt-3 alert alert-danger';
                infoDiv.innerHTML = '<i class="bi bi-x-circle"></i> Gagal memeriksa NIK: ' + e.message;
            } finally {
                infoDiv.classList.remove('d-none');
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-search"></i> Cek NIK';
            }
        });

        // Validasi password match sebelum submit
        document.getElementById('formTambahPetugas').addEventListener('submit', function(e) {
            const p1 = this.querySelector('[name="password"]').value;
            const p2 = this.querySelector('[name="confirm_password"]').value;
            if (p1 !== p2) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak sama.');
            }
        });
    </script>
</body>
</html>
