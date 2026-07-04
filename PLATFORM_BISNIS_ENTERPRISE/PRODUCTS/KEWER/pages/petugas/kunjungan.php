<?php
require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/feature_flags.php';

// Check GPS tracking feature flag
if (!isFeatureEnabled('gps_pembayaran')) {
    header('Location: /dashboard.php');
    exit;
}

require_once __DIR__ . '/../../src/Geo/GPSTracker.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

// Check permission
if (!hasPermission('visits_view')) {
    header('Location: /dashboard.php');
    exit;
}

$pageTitle = 'Kunjungan Petugas';
$petugas_id = $_SESSION['user_id'];
$cabang_id = $_SESSION['cabang_id'];

// Get filter parameters
$visit_type = $_GET['visit_type'] ?? '';
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Build query
$where = ["v.petugas_id = ?", "v.cabang_id = ?"];
$params = [$petugas_id, $cabang_id];

if ($visit_type) {
    $where[] = "v.visit_type = ?";
    $params[] = $visit_type;
}

if ($start_date) {
    $where[] = "DATE(v.visit_date) >= ?";
    $params[] = $start_date;
}

if ($end_date) {
    $where[] = "DATE(v.visit_date) <= ?";
    $params[] = $end_date;
}

$whereClause = implode(' AND ', $where);

// Get visits
$sql = "SELECT v.*, n.nama as nama_nasabah, n.ktp, u.nama as nama_petugas,
        c.nama_cabang
        FROM visits v
        LEFT JOIN nasabah n ON v.nasabah_id = n.id
        LEFT JOIN users u ON v.petugas_id = u.id
        LEFT JOIN cabang c ON v.cabang_id = c.id
        WHERE $whereClause
        ORDER BY v.visit_date DESC
        LIMIT 100";

$visits = query($sql, $params);

// Get statistics
$statsSql = "SELECT COUNT(*) as total_visits,
            SUM(CASE WHEN v.geofence_valid = 1 THEN 1 ELSE 0 END) as valid_geofence,
            AVG(v.distance_from_cabang) as avg_distance
            FROM visits v
            WHERE $whereClause";

$stats = query($statsSql, $params);
$stats = is_array($stats) && isset($stats[0]) ? $stats[0] : ['total_visits' => 0, 'valid_geofence' => 0, 'avg_distance' => 0];

require_once BASE_PATH . '/includes/header.php';
?>

<div class="main-container">
    <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>
    <main class="content-area">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="page-title">Kunjungan Petugas</h2>
                    <p class="text-muted">Log kunjungan lapangan dengan GPS tracking</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Kunjungan</h5>
                            <h3 class="display-4"><?php echo $stats['total_visits']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Valid Geofence</h5>
                            <h3 class="display-4"><?php echo $stats['valid_geofence']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Rata-rata Jarak</h5>
                            <h3 class="display-4"><?php echo round($stats['avg_distance'] ?? 0, 2); ?> m</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Tipe Kunjungan</label>
                            <select name="visit_type" class="form-select">
                                <option value="">Semua</option>
                                <option value="pembayaran" <?php echo $visit_type == 'pembayaran' ? 'selected' : ''; ?>>Pembayaran</option>
                                <option value="follow_up" <?php echo $visit_type == 'follow_up' ? 'selected' : ''; ?>>Follow-up</option>
                                <option value="survey" <?php echo $visit_type == 'survey' ? 'selected' : ''; ?>>Survey</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Akhir</label>
                            <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Visits Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Kunjungan</h5>
                    <button class="btn btn-success" onclick="showCaptureModal()">
                        <i class="fas fa-map-marker-alt"></i> Catat Kunjungan
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nasabah</th>
                                    <th>KTP</th>
                                    <th>Tipe</th>
                                    <th>Lokasi GPS</th>
                                    <th>Jarak</th>
                                    <th>Geofence</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (is_array($visits) && count($visits) > 0): ?>
                                    <?php foreach ($visits as $visit): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($visit['visit_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($visit['nama_nasabah'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($visit['ktp'] ?? '-'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $visit['visit_type'] == 'pembayaran' ? 'primary' : 'secondary'; ?>">
                                                    <?php echo ucfirst($visit['visit_type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($visit['latitude'] && $visit['longitude']): ?>
                                                    <a href="https://maps.google.com/?q=<?php echo $visit['latitude']; ?>,<?php echo $visit['longitude']; ?>" target="_blank" class="btn btn-sm btn-info">
                                                        <i class="fas fa-map"></i> Lihat Peta
                                                    </a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $visit['distance_from_cabang'] ? round($visit['distance_from_cabang'], 2) . ' m' : '-'; ?></td>
                                            <td>
                                                <?php if ($visit['geofence_valid']): ?>
                                                    <span class="badge bg-success">Valid</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Invalid</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($visit['notes'] ?? '-'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada data kunjungan</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Capture Modal -->
<div class="modal fade" id="captureModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Catat Kunjungan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="captureForm">
                    <div class="mb-3">
                        <label class="form-label">Nasabah</label>
                        <select name="nasabah_id" class="form-select" required>
                            <option value="">Pilih Nasabah</option>
                            <?php
                            $nasabahSql = "SELECT id, nama, ktp FROM nasabah WHERE cabang_id = ? AND status = 'aktif' ORDER BY nama";
                            $nasabahList = query($nasabahSql, [$cabang_id]);
                            if (is_array($nasabahList)):
                                foreach ($nasabahList as $n):
                                    ?>
                                    <option value="<?php echo $n['id']; ?>"><?php echo htmlspecialchars($n['nama'] . ' - ' . $n['ktp']); ?></option>
                                <?php endforeach; endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipe Kunjungan</label>
                        <select name="visit_type" class="form-select" required>
                            <option value="pembayaran">Pembayaran</option>
                            <option value="follow_up">Follow-up</option>
                            <option value="survey">Survey</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi GPS</label>
                        <div class="d-flex gap-2">
                            <input type="text" name="latitude" class="form-control" placeholder="Latitude" readonly>
                            <input type="text" name="longitude" class="form-control" placeholder="Longitude" readonly>
                            <button type="button" class="btn btn-primary" onclick="captureGPS()">
                                <i class="fas fa-crosshairs"></i> Ambil GPS
                            </button>
                        </div>
                        <small class="text-muted" id="gpsStatus"></small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveVisit()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function showCaptureModal() {
    new bootstrap.Modal(document.getElementById('captureModal')).show();
}

function captureGPS() {
    if (!navigator.geolocation) {
        document.getElementById('gpsStatus').textContent = 'Geolocation tidak didukung';
        return;
    }
    
    document.getElementById('gpsStatus').textContent = 'Mengambil lokasi...';
    
    navigator.geolocation.getCurrentPosition(
        function(position) {
            document.querySelector('[name="latitude"]').value = position.coords.latitude;
            document.querySelector('[name="longitude"]').value = position.coords.longitude;
            document.getElementById('gpsStatus').textContent = 'Akurasi: ' + Math.round(position.coords.accuracy) + ' meter';
        },
        function(error) {
            document.getElementById('gpsStatus').textContent = 'Gagal mengambil lokasi: ' + error.message;
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

function saveVisit() {
    const form = document.getElementById('captureForm');
    const formData = new FormData(form);
    
    if (!formData.get('nasabah_id') || !formData.get('latitude') || !formData.get('longitude')) {
        alert('Mohon lengkapi semua field');
        return;
    }
    
    fetch('/api/visits.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Kunjungan berhasil dicatat');
            location.reload();
        } else {
            alert('Gagal: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}
</script>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
