<?php
/**
 * Feature Flags — halaman kelola fitur v2.3.0
 * Hanya appOwner yang bisa akses dan toggle
 */
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/feature_flags.php';

requireLogin();
$user       = getCurrentUser();
$page_title = 'Feature Flags';
require_once __DIR__ . '/_header.php';

$features = getAllFeatures();

// Kelompokkan per kategori
$grouped = [];
foreach ($features as $f) {
    $grouped[$f['category']][] = $f;
}

$category_labels = [
    'wa'       => ['label' => 'WhatsApp Notifikasi', 'icon' => 'whatsapp',       'color' => 'success'],
    'auth'     => ['label' => 'Autentikasi',          'icon' => 'shield-lock',    'color' => 'danger'],
    'pwa'      => ['label' => 'PWA / Offline',        'icon' => 'phone',          'color' => 'info'],
    'laporan'  => ['label' => 'Laporan & Export',     'icon' => 'file-earmark-bar-graph', 'color' => 'primary'],
    'lapangan' => ['label' => 'Fitur Lapangan',       'icon' => 'geo-alt',        'color' => 'warning'],
    'system'   => ['label' => 'Sistem & Cron',        'icon' => 'gear-wide-connected', 'color' => 'secondary'],
    'general'  => ['label' => 'Umum',                 'icon' => 'toggles',        'color' => 'dark'],
];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="bi bi-toggles"></i> Feature Flags</h2>
            <p class="text-muted mb-0 small">Aktifkan atau nonaktifkan fitur v2.3.0. Perubahan langsung berlaku tanpa restart.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-danger" onclick="toggleAll(false)">
                <i class="bi bi-toggle-off"></i> Matikan Semua
            </button>
            <button class="btn btn-sm btn-outline-success" onclick="toggleAll(true)">
                <i class="bi bi-toggle-on"></i> Aktifkan Semua
            </button>
        </div>
    </div>

    <?php if (empty($features)): ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i>
        Tabel <code>platform_features</code> belum ada. Jalankan migration:
        <code>database/migrations/009_platform_features.sql</code>
    </div>
    <?php else: ?>

    <div class="row g-3">
        <?php foreach ($category_labels as $cat_key => $cat): ?>
        <?php if (empty($grouped[$cat_key])) continue; ?>
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-<?= $cat['color'] ?> bg-opacity-10 border-<?= $cat['color'] ?> border-start border-3">
                    <h6 class="mb-0 text-<?= $cat['color'] ?>">
                        <i class="bi bi-<?= $cat['icon'] ?>"></i> <?= $cat['label'] ?>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:260px">Fitur</th>
                                <th>Deskripsi</th>
                                <th style="width:90px" class="text-center">Status</th>
                                <th style="width:130px" class="text-center">Toggle</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($grouped[$cat_key] as $f): ?>
                        <tr id="row-<?= $f['feature_key'] ?>">
                            <td>
                                <strong><?= htmlspecialchars($f['label']) ?></strong><br>
                                <code class="small text-muted"><?= $f['feature_key'] ?></code>
                            </td>
                            <td class="small text-muted"><?= htmlspecialchars($f['description'] ?? '') ?></td>
                            <td class="text-center">
                                <span class="badge bg-<?= $f['is_enabled'] ? 'success' : 'secondary' ?> status-badge-<?= $f['feature_key'] ?>">
                                    <?= $f['is_enabled'] ? 'ON' : 'OFF' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-flex justify-content-center mb-0">
                                    <input class="form-check-input fs-5" type="checkbox"
                                           id="ff-<?= $f['feature_key'] ?>"
                                           data-key="<?= $f['feature_key'] ?>"
                                           <?= $f['is_enabled'] ? 'checked' : '' ?>
                                           onchange="toggleFeature(this)">
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-3 text-muted small">
        <i class="bi bi-info-circle"></i>
        Fitur yang dimatikan: API-nya akan mengembalikan <code>403</code>, UI-nya tidak akan ditampilkan.
        Tidak ada data yang hilang — hanya akses yang dibatasi.
        <?php if (!empty($features)): ?>
        <br>Terakhir diubah:
        <?php
        $last = array_filter($features, fn($x) => !empty($x['changed_at']));
        if ($last) {
            usort($last, fn($a, $b) => strcmp($b['changed_at'], $a['changed_at']));
            $l = $last[0];
            echo htmlspecialchars($l['label']) . ' — ' . date('d/m/Y H:i', strtotime($l['changed_at']));
        }
        ?>
        <?php endif; ?>
    </div>

    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
const API = '../../api/feature_flags.php';

async function toggleFeature(el) {
    const key     = el.dataset.key;
    const enabled = el.checked;

    el.disabled = true;
    try {
        const resp = await fetch(API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ key, enabled })
        });
        const r = await resp.json();
        if (r.success) {
            const badge = document.querySelector(`.status-badge-${key}`);
            if (badge) {
                badge.textContent = enabled ? 'ON' : 'OFF';
                badge.className = `badge bg-${enabled ? 'success' : 'secondary'} status-badge-${key}`;
            }
            // Toast kecil tanpa blocking
            Swal.fire({ toast: true, position: 'top-end', icon: 'success',
                title: `${enabled ? '✅ Diaktifkan' : '🔴 Dimatikan'}: ${key}`,
                showConfirmButton: false, timer: 1800, timerProgressBar: true });
        } else {
            el.checked = !enabled; // rollback
            Swal.fire('Gagal', r.error || 'Terjadi kesalahan', 'error');
        }
    } catch (e) {
        el.checked = !enabled;
        Swal.fire('Error', 'Tidak dapat menghubungi server', 'error');
    }
    el.disabled = false;
}

async function toggleAll(enable) {
    const label = enable ? 'mengaktifkan SEMUA fitur' : 'mematikan SEMUA fitur';
    const confirm = await Swal.fire({
        title: `${enable ? 'Aktifkan' : 'Matikan'} semua?`,
        text: `Anda akan ${label}.`,
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: enable ? '#198754' : '#dc3545',
        confirmButtonText: 'Ya, lanjutkan'
    });
    if (!confirm.isConfirmed) return;

    const switches = document.querySelectorAll('input[data-key]');
    const bulk = Array.from(switches).map(el => ({ key: el.dataset.key, enabled: enable }));

    const resp = await fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ bulk })
    });
    const r = await resp.json();
    if (r.success) {
        location.reload();
    } else {
        Swal.fire('Gagal', r.error || 'Terjadi kesalahan', 'error');
    }
}
</script>

<?php require_once __DIR__ . '/_footer.php'; ?>
