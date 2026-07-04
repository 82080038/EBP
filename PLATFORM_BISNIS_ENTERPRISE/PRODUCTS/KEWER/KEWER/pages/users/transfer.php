<?php
/**
 * Halaman: Resign / Transfer Karyawan Antar Koperasi
 * Hanya bisa diakses oleh bos dan manager_pusat
 */
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';

requireLogin();

if (!hasPermission('manage_users')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$current_user = getCurrentUser();
$actor_bos_id = getOwnerBosId();

// Ambil daftar karyawan milik bos yang login (aktif saja)
$karyawan_list = query(
    "SELECT u.id, u.username, u.nama, u.role, u.cabang_id, c.nama_cabang
     FROM users u
     LEFT JOIN cabang c ON u.cabang_id = c.id
     WHERE u.owner_bos_id = ? AND u.status = 'aktif'
       AND u.role NOT IN ('bos','appOwner')
     ORDER BY c.nama_cabang, u.nama",
    [$actor_bos_id]
);
if (!is_array($karyawan_list)) $karyawan_list = [];

// Ambil daftar bos lain yang aktif (untuk pilihan tujuan transfer)
$bos_lain = query(
    "SELECT u.id, u.nama, u.username FROM users u
     WHERE u.role = 'bos' AND u.status = 'aktif' AND u.id != ?
     ORDER BY u.nama",
    [$actor_bos_id]
);
if (!is_array($bos_lain)) $bos_lain = [];

// Ambil riwayat resign/transfer dari audit log
$riwayat = query(
    "SELECT al.*, u.nama as nama_karyawan, u.username
     FROM audit_log al
     LEFT JOIN users u ON al.record_id = u.id
     WHERE al.action IN ('resign_karyawan','pindah_cabang_karyawan')
       AND al.user_id = ?
     ORDER BY al.created_at DESC
     LIMIT 30",
    [$current_user['id']]
);
if (!is_array($riwayat)) $riwayat = [];

$page_title = 'Resign / Pindah Cabang Karyawan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>
        
        <main class="content-area">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="bi bi-person-x"></i> Resign / Pindah Cabang Karyawan</h1>
            </div>
            
            <p class="text-muted mb-3">Proses resign atau pindah cabang karyawan. Identitas NIK tersimpan permanen di platform.</p>

            <div class="alert alert-info border-start border-4 border-info">
                <div class="fw-semibold mb-1"><i class="bi bi-lightbulb"></i> Cara Kerja Identitas Karyawan</div>
                <ul class="mb-0 small">
                    <li><strong>Resign / Dipecat:</strong> Karyawan dinonaktifkan. Tidak bisa login dan tidak bisa akses data koperasi ini.</li>
                    <li><strong>Pindah ke koperasi lain:</strong> Tidak perlu dilakukan di sini. Bos koperasi lain cukup mendaftarkan NIK yang sama &rarr; data identitas terisi otomatis.</li>
                    <li><strong>Pindah cabang (koperasi sama):</strong> Gunakan aksi &ldquo;Pindah Cabang&rdquo; di bawah.</li>
                    <li><strong>Data lama terlindungi:</strong> Setelah resign, karyawan <em>tidak bisa</em> melihat data koperasi lama meskipun bergabung ke koperasi lain.</li>
                </ul>
            </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="row">
            <!-- Form Resign / Transfer -->
            <div class="col-lg-5">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-danger text-white fw-semibold">
                        <i class="bi bi-person-dash"></i> Proses Resign / Transfer
                    </div>
                    <div class="card-body">
                        <form id="formTransfer">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Karyawan <span class="text-danger">*</span></label>
                                <select class="form-select" id="user_id" name="user_id" required>
                                    <option value="">-- Pilih karyawan --</option>
                                    <?php foreach ($karyawan_list as $k): ?>
                                        <option value="<?= $k['id'] ?>" data-cabang="<?= htmlspecialchars($k['nama_cabang'] ?? '-') ?>">
                                            <?= htmlspecialchars($k['nama']) ?> (<?= htmlspecialchars($k['username']) ?> · <?= htmlspecialchars($k['role']) ?> · <?= htmlspecialchars($k['nama_cabang'] ?? '-') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Aksi <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="action" id="aksi_resign" value="resign" checked>
                                        <label class="form-check-label" for="aksi_resign">
                                            <span class="badge bg-secondary">Resign / Pecat</span> — nonaktifkan
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="action" id="aksi_pindah" value="pindah_cabang">
                                        <label class="form-check-label" for="aksi_pindah">
                                            <span class="badge bg-primary">Pindah Cabang</span> — dalam koperasi ini
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Panel pindah cabang (hanya tampil jika action=pindah_cabang) -->
                            <div id="panelPindah" class="d-none">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Cabang Tujuan <span class="text-danger">*</span></label>
                                    <?php
                                    $cabang_milik_bos = query(
                                        "SELECT id, nama_cabang, is_headquarters FROM cabang WHERE owner_bos_id = ? AND status = 'aktif' ORDER BY is_headquarters DESC, nama_cabang",
                                        [$actor_bos_id]
                                    );
                                    if (!is_array($cabang_milik_bos)) $cabang_milik_bos = [];
                                    ?>
                                    <select class="form-select" id="target_cabang_id" name="target_cabang_id">
                                        <option value="">-- Pilih cabang tujuan --</option>
                                        <?php foreach ($cabang_milik_bos as $c): ?>
                                            <option value="<?= $c['id'] ?>">
                                                <?= htmlspecialchars($c['nama_cabang']) ?>
                                                <?= $c['is_headquarters'] ? '(Pusat)' : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text text-muted">Pindah ke koperasi lain: karyawan harus resign dulu, lalu bos lain mendaftarkan NIK-nya.</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Alasan <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="alasan" name="alasan" rows="3" placeholder="Jelaskan alasan resign / transfer..." required></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-danger" id="btnSubmit">
                                    <i class="bi bi-check-circle"></i> Proses
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Riwayat Resign / Transfer -->
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-header fw-semibold">
                        <i class="bi bi-clock-history"></i> Riwayat Resign / Transfer
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                        <th>Karyawan</th>
                                        <th>Detail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($riwayat)): ?>
                                        <tr><td colspan="4" class="text-center text-muted py-3">Belum ada riwayat</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($riwayat as $r):
                                            $new_val = json_decode($r['new_values'] ?? '{}', true);
                                            $badge = $r['action'] === 'resign_karyawan' ? 'bg-secondary' : 'bg-primary';
                                            $label = $r['action'] === 'resign_karyawan' ? 'Resign' : 'Pindah Cabang';
                                        ?>
                                        <tr>
                                            <td class="small"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
                                            <td><span class="badge <?= $badge ?>"><?= $label ?></span></td>
                                            <td class="small"><?= htmlspecialchars($r['nama_karyawan'] ?? '-') ?></td>
                                            <td class="small text-muted">
                                                <?php if ($r['action'] === 'pindah_cabang_karyawan'): ?>
                                                    → <?= htmlspecialchars($new_val['nama_cabang'] ?? '-') ?>
                                                <?php else: ?>
                                                    <?= htmlspecialchars($new_val['alasan'] ?? '-') ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
    </main>
</div>

<script>
// Toggle panel pindah cabang
document.querySelectorAll('input[name="action"]').forEach(r => {
    r.addEventListener('change', function() {
        document.getElementById('panelPindah').classList.toggle('d-none', this.value !== 'pindah_cabang');
    });
});

// Submit form
document.getElementById('formTransfer').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmit');
    const action = document.querySelector('input[name="action"]:checked').value;
    const userId = parseInt(document.getElementById('user_id').value);
    const alasan = document.getElementById('alasan').value.trim();

    if (!userId) { Swal.fire('Error', 'Pilih karyawan terlebih dahulu', 'error'); return; }
    if (!alasan) { Swal.fire('Error', 'Alasan wajib diisi', 'error'); return; }

    const payload = { action, user_id: userId, alasan };

    if (action === 'pindah_cabang') {
        payload.target_cabang_id = parseInt(document.getElementById('target_cabang_id')?.value || 0);
        if (!payload.target_cabang_id) {
            Swal.fire('Error', 'Pilih cabang tujuan', 'error');
            return;
        }
    }

    const titles = { resign: 'Konfirmasi Resign / Pecat', pindah_cabang: 'Konfirmasi Pindah Cabang' };
    const texts  = {
        resign: 'Karyawan akan dinonaktifkan dan tidak bisa login. NIK tetap tersimpan di platform — bos koperasi lain bisa mendaftarkan NIK yang sama.',
        pindah_cabang: 'Karyawan akan dipindah ke cabang yang dipilih dalam koperasi ini.',
    };

    const konfirm = await Swal.fire({
        title: titles[action] || 'Konfirmasi',
        text: texts[action] || '',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Proses',
        cancelButtonText: 'Batal',
    });
    if (!konfirm.isConfirmed) return;

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';

    try {
        const res = await fetch('<?= baseUrl('api/transfer_karyawan.php') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.success) {
            await Swal.fire('Berhasil', data.message, 'success');
            location.reload();
        } else {
            Swal.fire('Gagal', data.error || 'Terjadi kesalahan', 'error');
        }
    } catch(err) {
        Swal.fire('Error', 'Gagal menghubungi server: ' + err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle"></i> Proses';
    }
});
</script>
</body>
</html>
