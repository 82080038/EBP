<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/business_logic.php';
requireLogin();

$user      = getCurrentUser();
$cabang_id = getCurrentCabang();
$role      = $user['role'] ?? '';

// Baca satu notifikasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['baca_id'])) {
    bacaNotifikasi((int)$_POST['baca_id'], $user['id']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Baca semua
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['baca_semua'])) {
    query(
        "UPDATE notifikasi SET is_read = 1, read_at = NOW()
         WHERE is_read = 0
           AND (user_id = ? OR (target_role = ? AND (cabang_id = ? OR cabang_id IS NULL)))",
        [$user['id'], $role, $cabang_id]
    );
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Ambil semua notifikasi user
$notifikasi = query(
    "SELECT * FROM notifikasi
     WHERE (user_id = ? OR (target_role = ? AND (cabang_id = ? OR cabang_id IS NULL)))
     ORDER BY created_at DESC LIMIT 100",
    [$user['id'], $role, $cabang_id]
) ?: [];

$belum_baca = array_filter($notifikasi, fn($n) => !$n['is_read']);

$page_title = 'Notifikasi';
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/sidebar.php';

$tipe_icon = [
    'jatuh_tempo'     => ['icon' => 'bi-calendar-x', 'color' => 'warning'],
    'macet'           => ['icon' => 'bi-exclamation-octagon', 'color' => 'danger'],
    'kas_selisih'     => ['icon' => 'bi-cash-coin', 'color' => 'danger'],
    'pinjaman_baru'   => ['icon' => 'bi-file-earmark-plus', 'color' => 'primary'],
    'restrukturisasi' => ['icon' => 'bi-arrow-repeat', 'color' => 'info'],
    'write_off'       => ['icon' => 'bi-trash', 'color' => 'secondary'],
    'peringatan'      => ['icon' => 'bi-exclamation-triangle', 'color' => 'warning'],
    'info'            => ['icon' => 'bi-info-circle', 'color' => 'info'],
];
?>
<div class="content-area">
    <div class="container-fluid">
        <div class="row mb-3 align-items-center">
            <div class="col">
                <h4 class="fw-bold mb-0"><i class="bi bi-bell"></i> Notifikasi
                    <?php if (count($belum_baca) > 0): ?>
                        <span class="badge bg-danger"><?= count($belum_baca) ?> baru</span>
                    <?php endif; ?>
                </h4>
            </div>
            <?php if (count($belum_baca) > 0): ?>
            <div class="col-auto">
                <form method="POST" style="display:inline">
                    <button type="submit" name="baca_semua" value="1" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-check-all"></i> Tandai Semua Dibaca
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <?php if (empty($notifikasi)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-bell-slash" style="font-size:3rem"></i>
            <div class="mt-2">Tidak ada notifikasi</div>
        </div>
        <?php else: ?>
        <div class="list-group shadow-sm">
            <?php foreach ($notifikasi as $n):
                $ti    = $tipe_icon[$n['tipe']] ?? ['icon' => 'bi-dot', 'color' => 'secondary'];
                $style = $n['is_read'] ? 'bg-white' : 'bg-light border-start border-4 border-' . $ti['color'];
                $url   = '';
                if ($n['referensi_tabel'] === 'pinjaman')    $url = baseUrl('pages/pinjaman/detail.php?id=' . $n['referensi_id']);
                elseif ($n['referensi_tabel'] === 'nasabah') $url = baseUrl('pages/nasabah/detail.php?id=' . $n['referensi_id']);
                elseif ($n['referensi_tabel'] === 'kas_petugas') $url = baseUrl('pages/kas_petugas/index.php');
            ?>
            <div class="list-group-item list-group-item-action d-flex gap-3 align-items-start py-3 <?= $style ?>">
                <div class="text-<?= $ti['color'] ?> fs-4 pt-1">
                    <i class="bi <?= $ti['icon'] ?>"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <div class="fw-semibold <?= $n['is_read'] ? 'text-muted' : '' ?>">
                            <?= htmlspecialchars($n['judul']) ?>
                        </div>
                        <small class="text-muted ms-2 text-nowrap">
                            <?= date('d/m H:i', strtotime($n['created_at'])) ?>
                        </small>
                    </div>
                    <div class="small <?= $n['is_read'] ? 'text-muted' : '' ?>">
                        <?= htmlspecialchars($n['pesan']) ?>
                    </div>
                    <div class="mt-1 d-flex gap-2">
                        <?php if ($url): ?>
                            <a href="<?= $url ?>" class="btn btn-xs btn-outline-<?= $ti['color'] ?> btn-sm py-0">
                                <i class="bi bi-arrow-right"></i> Lihat
                            </a>
                        <?php endif; ?>
                        <?php if (!$n['is_read']): ?>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="baca_id" value="<?= $n['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-secondary py-0">
                                <i class="bi bi-check"></i> Tandai Dibaca
                            </button>
                        </form>
                        <?php else: ?>
                            <small class="text-muted"><i class="bi bi-check2"></i> Dibaca <?= $n['read_at'] ? date('d/m H:i', strtotime($n['read_at'])) : '' ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once BASE_PATH . '/includes/footer.php'; ?>
