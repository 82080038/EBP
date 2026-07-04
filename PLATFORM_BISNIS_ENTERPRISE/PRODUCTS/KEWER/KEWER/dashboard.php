<?php
require_once 'config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/feature_flags.php';
requireLogin();

$user = getCurrentUser();
$role = $user['role'];
$user_cabang_id = $user['cabang_id'] ?? null;

// appOwner has their own dashboard — redirect
if ($role === 'appOwner') {
    header('Location: ' . baseUrl('pages/app_owner/dashboard.php'));
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <?php // PWA disabled during development ?>
    <?php /* if (isFeatureEnabled('pwa')): ?>
    <link rel="manifest" href="/kewer/manifest.json">
    <meta name="theme-color" content="#2c3e50">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <?php endif; */ ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .border-left-primary { border-left: 4px solid #4e73df !important; }
        .border-left-success { border-left: 4px solid #1cc88a !important; }
        .border-left-info { border-left: 4px solid #36b9cc !important; }
        .border-left-warning { border-left: 4px solid #f6c23e !important; }
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>

        <main class="content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <?php if ($user['role'] === 'bos'): ?>
                        <select class="form-select" id="cabangSelector" style="width: 200px;">
                            <option value="">Semua Cabang</option>
                            <?php
                            // Only show cabangs owned by this bos
                            $owned_cabangs = getBosOwnedCabangIds();
                            if (!empty($owned_cabangs)) {
                                $placeholders = implode(',', array_fill(0, count($owned_cabangs), '?'));
                                $cabangs = query("SELECT * FROM cabang WHERE status = 'aktif' AND id IN ($placeholders)", $owned_cabangs);
                                if (is_array($cabangs)) {
                                    foreach ($cabangs as $cabang):
                                    ?>
                                        <option value="<?php echo $cabang['id']; ?>">
                                            <?php echo $cabang['nama_cabang']; ?>
                                        </option>
                                    <?php endforeach;
                                }
                            }
                            ?>
                        </select>
                    <?php endif; ?>
                </div>
                
                <!-- Stats Cards -->
                <div class="row mb-4" id="stats-container">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-center">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-center">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-center">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-center">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Stats for Petugas -->
                <div class="row mb-4" id="petugas-stats-container" style="display:none;">
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-center">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-center">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Stats for Bos/Manager -->
                <div class="row mb-4" id="manager-stats-container" style="display:none;">
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-center">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card border-left-danger shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-center">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Aktivitas Terbaru</h6>
                            </div>
                            <div class="card-body" id="recent-activities-container">
                                <div class="text-center">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-bar-chart"></i> Pinjaman per Bulan</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="pinjamanChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-pie-chart"></i> Status Pinjaman</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="statusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Dashboard Data Loading via JSON API
        $(document).ready(function() {
            loadDashboardData();
        });

        function loadDashboardData() {
            // Load main stats
            window.KewerAPI.getDashboardStats().done(response => {
                renderMainStats(response.data);
            });

            // Load role-specific stats
            const role = '<?php echo $role; ?>';
            if (['petugas_pusat', 'petugas_cabang'].includes(role)) {
                $('#petugas-stats-container').show();
                loadPetugasStats();
            } else if (['bos', 'manager_pusat'].includes(role)) {
                $('#manager-stats-container').show();
                loadManagerStats();
            }

            // Load recent activities
            window.KewerAPI.getDashboardRecent().done(response => {
                renderRecentActivities(response.data);
            });

            // Load charts
            loadCharts();
        }

        function renderMainStats(data) {
            const html = `
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Nasabah
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        ${data.total_nasabah || 0}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-people fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Pinjaman Aktif
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        ${data.total_pinjaman || 0}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-cash-stack fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Outstanding
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        Rp ${formatNumber(data.outstanding || 0)}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-currency-dollar fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Telat Bayar
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        ${data.late_payments || 0}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-exclamation-triangle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#stats-container').html(html);
        }

        function loadPetugasStats() {
            window.KewerAPI.getDashboardStats().done(response => {
                const html = `
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Pembayaran Hari Ini
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            ${response.data.today_payments || 0}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-calendar-check fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Aktivitas Lapangan
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            ${response.data.today_payments || 0} Kunjungan
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-geo-alt fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $('#petugas-stats-container').html(html);
            });
        }

        function loadManagerStats() {
            window.KewerAPI.getDashboardStats().done(response => {
                const html = `
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Menunggu Approval
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            ${response.data.pending_approvals || 0}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-clock fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card border-left-danger shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                            Total Cabang
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            ${response.data.total_cabang || 0}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-building fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $('#manager-stats-container').html(html);
            });
        }

        function renderRecentActivities(activities) {
            if (!activities || activities.length === 0) {
                $('#recent-activities-container').html('<p class="text-muted">Belum ada aktivitas.</p>');
                return;
            }

            let html = '<div class="list-group">';
            activities.forEach(activity => {
                html += `
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">${activity.activity || activity.action}</h6>
                            <small>${new Date(activity.created_at).toLocaleString('id-ID')}</small>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            $('#recent-activities-container').html(html);
        }

        function loadCharts() {
            window.KewerAPI.getDashboardCharts().done(response => {
                renderCharts(response.data);
            });
        }

        function renderCharts(data) {
            // Pinjaman Chart
            const pinjamanCtx = document.getElementById('pinjamanChart');
            if (pinjamanCtx) {
                new Chart(pinjamanCtx, {
                    type: 'bar',
                    data: {
                        labels: data.months || [],
                        datasets: [{
                            label: 'Pinjaman',
                            data: data.monthly_loans || [],
                            backgroundColor: 'rgba(78, 115, 223, 0.8)'
                        }]
                    }
                });
            }

            // Status Chart
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'pie',
                    data: {
                        labels: data.status_labels || [],
                        datasets: [{
                            data: data.status_data || [],
                            backgroundColor: ['#4e73df', '#1cc88a', '#f6c23e', '#e74a3b']
                        }]
                    }
                });
            }
        }

        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }

        // Cabang selector
        document.getElementById('cabangSelector')?.addEventListener('change', function() {
            const url = new URL(window.location);
            url.searchParams.set('cabang_id', this.value);
            window.location = url;
        });
    </script>
</body>
</html>
