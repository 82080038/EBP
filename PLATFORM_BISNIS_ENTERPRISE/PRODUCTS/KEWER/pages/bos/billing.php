<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();
$user = getCurrentUser();

if ($user['role'] !== 'bos') {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$page_title = 'Billing';

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
$error = '';

// Filter
$filter_status = $_GET['status'] ?? 'all';
$filter_month = (int)($_GET['bulan'] ?? date('n'));
$filter_year = (int)($_GET['tahun'] ?? date('Y'));

$where = "WHERE ki.bos_user_id = {$user['id']} AND ki.periode_bulan = $filter_month AND ki.periode_tahun = $filter_year";
if ($filter_status !== 'all') {
    $where .= " AND ki.status = '" . addslashes($filter_status) . "'";
}

$invoices = query("
    SELECT ki.*, bp.nama as plan_nama, bp.tipe as plan_tipe
    FROM koperasi_invoices ki
    JOIN koperasi_billing kb ON kb.id = ki.koperasi_billing_id
    JOIN billing_plans bp ON bp.id = kb.billing_plan_id
    $where
    ORDER BY ki.created_at DESC
");
if (!is_array($invoices)) $invoices = [];

// Get primary bank account for payment info
$primary_bank = query("SELECT * FROM platform_bank_accounts WHERE is_primary = 1 AND is_active = 1 LIMIT 1");
$primary_bank = (is_array($primary_bank) && !empty($primary_bank)) ? $primary_bank[0] : null;

// Summary
$sum_total = 0; $sum_paid = 0;
foreach ($invoices as $inv) {
    $sum_total += (float)$inv['total'];
    if ($inv['status'] === 'dibayar') $sum_paid += (float)$inv['total'];
}
?>
<?php include BASE_PATH . '/includes/header.php'; ?>

        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo htmlspecialchars($success); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <!-- Payment Method Info for Payment -->
        <?php if ($primary_bank): ?>
        <div class="alert alert-info alert-dismissible fade show mb-4">
            <h6 class="alert-heading"><i class="bi bi-bank"></i> Informasi Pembayaran</h6>
            <p class="mb-1">Silakan transfer pembayaran invoice ke:</p>
            <div class="mb-0">
                <?php
                $tipe_labels = [
                    'bank' => 'Bank Tradisional',
                    'mobile_banking' => 'Mobile Banking',
                    'ewallet' => 'E-Wallet',
                    'qris' => 'QRIS',
                    'virtual_account' => 'Virtual Account'
                ];
                ?>
                <span class="badge bg-primary"><?php echo $tipe_labels[$primary_bank['tipe_pembayaran']] ?? ucfirst($primary_bank['tipe_pembayaran']); ?></span>
                <strong><?php echo htmlspecialchars($primary_bank['nama_bank']); ?></strong><br>
                <?php if ($primary_bank['tipe_pembayaran'] === 'ewallet'): ?>
                Nomor HP: <code class="fs-5"><?php echo htmlspecialchars($primary_bank['nomor_hp']); ?></code><br>
                <?php elseif ($primary_bank['tipe_pembayaran'] === 'qris'): ?>
                <strong>QR Code</strong> - Scan kode QR untuk pembayaran<br>
                <?php if ($primary_bank['qris_code']): ?>
                <small class="text-muted">QR: <?php echo htmlspecialchars(substr($primary_bank['qris_code'], 0, 50)); ?>...</small><br>
                <?php endif; ?>
                <?php else: ?>
                No. Rekening/VA: <code class="fs-5"><?php echo htmlspecialchars($primary_bank['nomor_rekening']); ?></code><br>
                <?php endif; ?>
                <?php if ($primary_bank['nama_pemilik']): ?>a.n. <strong><?php echo htmlspecialchars($primary_bank['nama_pemilik']); ?></strong><br><?php endif; ?>
                <?php if ($primary_bank['cabang']): ?><small class="text-muted">Cabang: <?php echo htmlspecialchars($primary_bank['cabang']); ?></small><?php endif; ?>
                <?php if ($primary_bank['keterangan']): ?><br><small class="text-muted"><?php echo htmlspecialchars($primary_bank['keterangan']); ?></small><?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning mb-4">
            <strong><i class="bi bi-exclamation-triangle"></i> Informasi pembayaran belum tersedia.</strong>
            Hubungi admin untuk info metode pembayaran.
        </div>
        <?php endif; ?>

        <!-- Filter -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="d-flex gap-2 align-items-end">
                    <div>
                        <label class="form-label small mb-0">Bulan</label>
                        <select name="bulan" class="form-select form-select-sm" onchange="window.location.href='?bulan='+this.value+'&tahun='+document.getElementById('tahun').value+'&status=<?php echo $filter_status; ?>'">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>" <?php echo $m == $filter_month ? 'selected' : ''; ?>><?php echo date('F', mktime(0,0,0,$m)); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label small mb-0">Tahun</label>
                        <select id="tahun" name="tahun" class="form-select form-select-sm" onchange="window.location.href='?bulan=<?php echo $filter_month; ?>&tahun='+this.value+'&status=<?php echo $filter_status; ?>'">
                            <?php for ($y = 2024; $y <= date('Y') + 1; $y++): ?>
                            <option value="<?php echo $y; ?>" <?php echo $y == $filter_year ? 'selected' : ''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label small mb-0">Status</label>
                        <select class="form-select form-select-sm" onchange="window.location.href='?bulan=<?php echo $filter_month; ?>&tahun=<?php echo $filter_year; ?>&status='+this.value">
                            <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>Semua</option>
                            <option value="terbit" <?php echo $filter_status === 'terbit' ? 'selected' : ''; ?>>Terbit</option>
                            <option value="dibayar" <?php echo $filter_status === 'dibayar' ? 'selected' : ''; ?>>Dibayar</option>
                            <option value="overdue" <?php echo $filter_status === 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-inline-flex gap-2 align-items-center">
                    <span class="small text-muted">Total: <strong>Rp <?php echo number_format($sum_total, 0, ',', '.'); ?></strong></span>
                    <span class="small text-success">Dibayar: <strong>Rp <?php echo number_format($sum_paid, 0, ',', '.'); ?></strong></span>
                </div>
            </div>
        </div>

        <!-- Invoice list -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-receipt"></i> Invoice <?php echo date('F', mktime(0,0,0,$filter_month)); ?> <?php echo $filter_year; ?></h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Plan</th>
                                <th>Fixed</th>
                                <th>% Revenue</th>
                                <th>Usage</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Jatuh Tempo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($invoices)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-3">Belum ada invoice untuk periode ini</td></tr>
                            <?php else: foreach ($invoices as $inv): ?>
                            <tr>
                                <td><code class="small"><?php echo htmlspecialchars($inv['kode_invoice']); ?></code></td>
                                <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($inv['plan_nama']); ?></span></td>
                                <td class="text-end"><small><?php echo $inv['biaya_fixed'] > 0 ? number_format($inv['biaya_fixed'], 0, ',', '.') : '-'; ?></small></td>
                                <td class="text-end"><small><?php echo $inv['biaya_persentase'] > 0 ? number_format($inv['biaya_persentase'], 0, ',', '.') : '-'; ?></small></td>
                                <td class="text-end"><small><?php echo $inv['biaya_usage'] > 0 ? number_format($inv['biaya_usage'], 0, ',', '.') : '-'; ?></small></td>
                                <td class="text-end"><strong>Rp <?php echo number_format($inv['total'], 0, ',', '.'); ?></strong></td>
                                <td>
                                    <?php
                                    $sc = ['draft'=>'secondary','terbit'=>'warning','dibayar'=>'success','overdue'=>'danger','cancelled'=>'dark'];
                                    ?>
                                    <span class="badge bg-<?php echo $sc[$inv['status']] ?? 'secondary'; ?>"><?php echo ucfirst($inv['status']); ?></span>
                                </td>
                                <td><small><?php echo $inv['tanggal_jatuh_tempo'] ? date('d M Y', strtotime($inv['tanggal_jatuh_tempo'])) : '-'; ?></small></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
