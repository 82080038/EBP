<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

$user = getCurrentUser();
$role = $user['role'];
$user_cabang_id = $user['cabang_id'] ?? null;
$pusat_roles = ['bos'];

// Permission check
if (!hasPermission('view_laporan') && !in_array($role, $pusat_roles)) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

// Get cabang filter for reports (using shared function from includes/functions.php)
$cabang_filter_ids = getReportCabangFilter($role, $user_cabang_id, $user['id']);
$kantor_id = $cabang_filter_ids ? implode(',', $cabang_filter_ids) : 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .report-card { transition: transform 0.2s; }
        .report-card:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>

        <main class="content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-file-earmark-bar-graph"></i> Laporan</h1>
                    <div class="btn-group">
                        <button class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="bi bi-printer"></i> Cetak
                        </button>
                        <?php
                        require_once BASE_PATH . '/includes/feature_flags.php';
                        if (isFeatureEnabled('export_laporan')): ?>
                        <a id="btnExportCsv" href="#" class="btn btn-outline-success">
                            <i class="bi bi-filetype-csv"></i> Export CSV
                        </a>
                        <a id="btnExportPdf" href="#" class="btn btn-outline-danger">
                            <i class="bi bi-filetype-pdf"></i> Export PDF
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Jenis Laporan</label>
                                <select name="jenis_laporan" class="form-select">
                                    <option value="comprehensive" <?php echo $report_type === 'comprehensive' ? 'selected' : ''; ?>>Komprehensif</option>
                                    <option value="financial" <?php echo $report_type === 'financial' ? 'selected' : ''; ?>>Keuangan</option>
                                    <option value="loan_performance" <?php echo $report_type === 'loan_performance' ? 'selected' : ''; ?>>Kinerja Pinjaman</option>
                                    <option value="customer" <?php echo $report_type === 'customer' ? 'selected' : ''; ?>>Nasabah</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" class="form-control datepicker flatpickr-date" value="<?php echo $start_date; ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" class="form-control datepicker flatpickr-date" value="<?php echo $end_date; ?>">
                            </div>
                            <?php if (in_array($role, $pusat_roles)): ?>
                            <div class="col-md-3">
                                <label class="form-label">Cabang</label>
                                <select name="cabang_id" class="form-select">
                                    <option value="">Semua Cabang</option>
                                    <?php foreach ($branches as $branch): ?>
                                        <option value="<?php echo $branch['id']; ?>" <?php echo ($report_cabang == $branch['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($branch['nama_cabang']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> Tampilkan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Periode Laporan -->
                <div class="alert alert-info">
                    <i class="bi bi-calendar3"></i> 
                    Periode: <strong id="periode-start">-</strong> s/d <strong id="periode-end">-</strong>
                    | Cabang: <strong id="cabang-name">Semua Cabang</strong>
                </div>

                <!-- Laporan Container -->
                <div id="report-container">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Memuat laporan...</p>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <script>
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka);
        }

        function formatDate(dateStr, format = 'd F Y') {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const d = date.getDate();
            const m = months[date.getMonth()];
            const y = date.getFullYear();
            return format.replace('d', d).replace('F', m).replace('Y', y);
        }

        // Load laporan data via JSON API
        $(document).ready(function() {
            loadLaporanData();
        });

        function loadLaporanData() {
            const start_date = '<?php echo $_GET['tanggal_mulai'] ?? date('Y-m-01'); ?>';
            const end_date = '<?php echo $_GET['tanggal_selesai'] ?? date('Y-m-t'); ?>';
            const jenis_laporan = '<?php echo $_GET['jenis_laporan'] ?? 'comprehensive'; ?>';
            const cabang_id = '<?php echo $_GET['cabang_id'] ?? ''; ?>';

            $('#periode-start').text(formatDate(start_date));
            $('#periode-end').text(formatDate(end_date));

            window.KewerAPI.getLaporan({ start_date, end_date, jenis_laporan, cabang_id }).done(response => {
                if (response.success) {
                    if (cabang_id && response.cabang_name) {
                        $('#cabang-name').text(response.cabang_name);
                    }
                    renderLaporan(response.data, jenis_laporan);
                } else {
                    $('#report-container').html('<div class="alert alert-danger">Gagal memuat laporan</div>');
                }
            }).fail(error => {
                $('#report-container').html('<div class="alert alert-danger">Gagal memuat laporan</div>');
            });
        }

        function renderLaporan(data, jenis) {
            let html = '';
            
            if (jenis === 'comprehensive' || jenis === 'financial') {
                html += renderFinancialSection(data.financial || data);
            }
            
            if (jenis === 'comprehensive' || jenis === 'loan_performance') {
                html += renderLoanPerformanceSection(data.loan_performance || data);
            }
            
            if (jenis === 'comprehensive' || jenis === 'customer') {
                html += renderCustomerSection(data.customer || data);
            }

            $('#report-container').html(html);
        }

        function renderFinancialSection(fin) {
            return `
                <h4 class="mb-3"><i class="bi bi-currency-exchange"></i> Laporan Keuangan</h4>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card report-card border-primary">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Total Pinjaman Disalurkan</h6>
                                <h3 class="text-primary">Rp ${formatRupiah(fin.loans_disbursed?.total || 0)}</h3>
                                <small class="text-muted">${fin.loans_disbursed?.count || 0} pinjaman</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card report-card border-success">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Total Penagihan</h6>
                                <h3 class="text-success">Rp ${formatRupiah(fin.collections?.total || 0)}</h3>
                                <small class="text-muted">${fin.collections?.count || 0} pembayaran</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card report-card border-info">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Saldo Outstanding</h6>
                                <h3 class="text-info">Rp ${formatRupiah(fin.outstanding_balance || 0)}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card report-card border-danger">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Tunggakan</h6>
                                <h3 class="text-danger">Rp ${formatRupiah(fin.overdue?.total || 0)}</h3>
                                <small class="text-muted">${fin.overdue?.count || 0} angsuran</small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function renderLoanPerformanceSection(loan) {
            const statusClass = { 'aktif': 'success', 'lunas': 'primary', 'macet': 'danger' };
            
            let statusRows = '';
            if (loan.status_distribution) {
                loan.status_distribution.forEach(status => {
                    const cls = statusClass[status.status] || 'secondary';
                    statusRows += `
                        <tr>
                            <td><span class="badge bg-${cls}">${status.status ? status.status.charAt(0).toUpperCase() + status.status.slice(1) : '-'}</span></td>
                            <td class="text-end">${status.count || 0}</td>
                            <td class="text-end">Rp ${formatRupiah(status.total || 0)}</td>
                        </tr>
                    `;
                });
            }

            const perf = loan.payment_performance || {};

            return `
                <h4 class="mb-3"><i class="bi bi-graph-up"></i> Kinerja Pinjaman</h4>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card report-card">
                            <div class="card-header"><strong>Distribusi Status Pinjaman</strong></div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th class="text-end">Jumlah</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>${statusRows || '<tr><td colspan="3" class="text-center text-muted">Tidak ada data</td></tr>'}</tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card report-card">
                            <div class="card-header"><strong>Kinerja Pembayaran</strong></div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <h4 class="text-success">${perf.on_time || 0}</h4>
                                        <small class="text-muted">Tepat Waktu</small>
                                    </div>
                                    <div class="col-4">
                                        <h4 class="text-danger">${perf.late || 0}</h4>
                                        <small class="text-muted">Terlambat</small>
                                    </div>
                                    <div class="col-4">
                                        <h4 class="text-primary">${perf.total || 0}</h4>
                                        <small class="text-muted">Total</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function renderCustomerSection(cust) {
            return `
                <h4 class="mb-3"><i class="bi bi-people"></i> Laporan Nasabah</h4>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card report-card">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Total Nasabah</h6>
                                <h3 class="text-primary">${cust.total_nasabah || 0}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card report-card">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Nasabah Aktif</h6>
                                <h3 class="text-success">${cust.nasabah_aktif || 0}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card report-card">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Nasabah Baru</h6>
                                <h3 class="text-info">${cust.nasabah_baru || 0}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        flatpickr('input[type="date"]', {
            locale: 'id',
            dateFormat: 'Y-m-d',
            allowInput: true,
            altInput: true,
            altFormat: 'd F Y',
            theme: 'light'
        });
    </script>
</body>
</html>
