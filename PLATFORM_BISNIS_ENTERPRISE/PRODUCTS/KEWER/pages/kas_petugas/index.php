<?php
require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Permission check
if (!hasPermission('kas_petugas.read')) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$user = getCurrentUser();
$cabang_id = getCurrentCabang();
$role = $user['role'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kas Petugas - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/themes/light.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="main-container">
        <?php require_once BASE_PATH . '/includes/sidebar.php'; ?>
        
        <main class="content-area">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-cash-coin"></i> Tracking Dana Petugas</h2>
                    <a href="../../dashboard.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    Tracking dana yang dipegang petugas lapangan: saldo awal, total dikutip, total disetor, dan saldo akhir.
                </div>

                <!-- Date Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label>Tanggal</label>
                                <input type="date" class="form-control" id="tanggal" value="<?= $tanggal ?>" onchange="filterByDate()">
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-primary" onclick="filterByDate()">
                                    <i class="bi bi-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <?php
                $total_selisih = array_sum(array_column($kas_harian, 'selisih'));
                $ada_selisih   = count(array_filter($kas_harian, fn($k) => abs($k['selisih'] ?? 0) > 10000));
                $belum_verify  = count(array_filter($kas_harian, fn($k) => !$k['verified_by']));
                ?>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6>Petugas Hari Ini</h6>
                                <h3><?= count($kas_harian) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6>Total Disetor</h6>
                                <h3>Rp <?= number_format(array_sum(array_column($kas_harian, 'total_disetor')), 0, ',', '.') ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card <?= $ada_selisih ? 'bg-danger' : 'bg-secondary' ?> text-white">
                            <div class="card-body">
                                <h6>Petugas Selisih</h6>
                                <h3><?= $ada_selisih ?>
                                    <?php if ($ada_selisih): ?><small style="font-size:12px">(Rp <?= number_format(abs($total_selisih), 0, ',', '.') ?>)</small><?php endif; ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <h6>Belum Diverifikasi</h6>
                                <h3><?= $belum_verify ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Setoran Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Setoran Kas Petugas</h5>
                        <?php if (in_array($role, ['petugas_pusat', 'petugas_cabang'])): ?>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus"></i> Catat Setoran
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Petugas</label>
                                <select class="form-select" id="filterPetugas">
                                    <option value="">Semua Petugas</option>
                                    <?php foreach ($petugasList as $p): ?>
                                    <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nama']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="filterStatus">
                                    <option value="">Semua Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="kasPetugasTable">
                                <thead>
                                    <tr>
                                        <th>Petugas</th>
                                        <th>Saldo Awal</th>
                                        <th>Total Terima</th>
                                        <th>Total Disetor</th>
                                        <th>Saldo Akhir</th>
                                        <th>Selisih</th>
                                        <th>Terverifikasi</th>
                                        <th>Dikunci</th>
                                        <?php if (hasPermission('manage_kas')): ?><th>Aksi</th><?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody id="kaspetugas-table-body">
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <div class="spinner-border spinner-border-sm" role="status"></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Panel Pengganti Petugas -->
                <?php if (hasPermission('manage_kas') || hasPermission('manage_users')): ?>
                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-person-badge"></i> Pengganti Petugas Hari Ini</h5>
                        <button class="btn btn-sm btn-outline-primary" onclick="modalPengganti()">
                            <i class="bi bi-plus"></i> Assign Pengganti
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pengganti_hari)): ?>
                            <p class="text-muted mb-0">Tidak ada pengganti petugas hari ini</p>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>Petugas Asli</th><th>Pengganti</th><th>Alasan</th><th>Periode</th></tr></thead>
                                <tbody>
                                <?php foreach ($pengganti_hari as $pp): ?>
                                <tr>
                                    <td><?= htmlspecialchars($pp['petugas_nama']) ?></td>
                                    <td><?= htmlspecialchars($pp['pengganti_nama']) ?></td>
                                    <td><?= htmlspecialchars($pp['alasan_ketidakhadiran']) ?></td>
                                    <td><?= date('d/m', strtotime($pp['tanggal_mulai'])) ?> — <?= date('d/m', strtotime($pp['tanggal_selesai'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- Add Modal (for petugas) -->
    <?php if (in_array($role, ['petugas_pusat', 'petugas_cabang'])): ?>
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Catat Setoran Kas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="setoranForm">
                        <div class="mb-3">
                            <label class="form-label">Tanggal *</label>
                            <input type="date" class="form-control" name="tanggal" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Total Kas Petugas *</label>
                                <input type="number" class="form-control" name="total_kas_petugas" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Total Setoran *</label>
                                <input type="number" class="form-control" name="total_setoran" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea class="form-control" name="keterangan" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveSetoran()">Simpan</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal Pengganti Petugas -->
    <?php if (hasPermission('manage_kas') || hasPermission('manage_users')): ?>
    <?php
    $semua_petugas = query("SELECT id, nama FROM users WHERE cabang_id = ? AND role IN ('petugas_pusat','petugas_cabang') AND status = 'aktif' ORDER BY nama", [$cabang_id]) ?: [];
    ?>
    <div class="modal fade" id="penggantiModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-badge"></i> Assign Pengganti Petugas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Petugas yang Absen *</label>
                        <select class="form-select" id="pg_petugas_id" required>
                            <option value="">Pilih Petugas</option>
                            <?php foreach ($semua_petugas as $sp): ?>
                            <option value="<?= $sp['id'] ?>"><?= htmlspecialchars($sp['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pengganti *</label>
                        <select class="form-select" id="pg_pengganti_id" required>
                            <option value="">Pilih Pengganti</option>
                            <?php foreach ($semua_petugas as $sp): ?>
                            <option value="<?= $sp['id'] ?>"><?= htmlspecialchars($sp['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Ketidakhadiran</label>
                        <select class="form-select" id="pg_alasan">
                            <option value="sakit">Sakit</option>
                            <option value="izin">Izin</option>
                            <option value="cuti">Cuti</option>
                            <option value="darurat">Keperluan Darurat</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="pg_mulai" value="<?= $tanggal ?>">
                        </div>
                        <div class="col mb-3">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control" id="pg_selesai" value="<?= $tanggal ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea class="form-control" id="pg_catatan" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="savePengganti()">Simpan</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/id.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/id.js"></script>
    <script>
        $(document).ready(function() {
            // Only initialize DataTable if there's data
            var hasData = <?php echo !empty($setoranList) ? 'true' : 'false'; ?>;

            if (hasData) {
                try {
                    var table = $('#kasPetugasTable').DataTable({
                        language: {
                            search: "Cari:",
                            lengthMenu: "Tampilkan _MENU_ data per halaman",
                            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                            infoFiltered: "(difilter dari _MAX_ total data)",
                            paginate: {
                                first: "Pertama",
                                last: "Terakhir",
                                next: "Selanjutnya",
                                previous: "Sebelumnya"
                            },
                            emptyTable: "Tidak ada data tersedia",
                            zeroRecords: "Tidak ada data yang cocok"
                        },
                        pageLength: 25,
                        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                        responsive: true,
                        order: [[0, 'desc']]
                    });
                } catch (e) {
                    console.error('DataTables initialization error:', e);
                    $('#kasPetugasTable').removeClass('table-striped table-hover');
                }
            } else {
                $('#kasPetugasTable').removeClass('table-striped table-hover');
            }
            
            // Filter functionality
            $('#filterPetugas, #filterStatus').on('change', function() {
                const petugas = $('#filterPetugas').val();
                const status = $('#filterStatus').val();
                
                $('#kasPetugasTable tbody tr').each(function() {
                    const row = $(this);
                    const show = true;
                    
                    if (petugas && row.data('petugas') != petugas) {
                        row.hide();
                        return;
                    }
                    
                    if (status && row.data('status') !== status) {
                        row.hide();
                        return;
                    }
                    
                    row.show();
                });
            });
        });
        
        const API_BUSINESS = '<?= baseUrl('api/business.php') ?>';

        function filterByDate() {
            window.location.href = '?tanggal=' + document.getElementById('tanggal').value;
        }

        async function verifikasiKas(kas_id, nama_petugas, selisih) {
            const ada_selisih = Math.abs(selisih) > 10000;
            const { value: keterangan } = await Swal.fire({
                title: 'Verifikasi Kas ' + nama_petugas,
                icon: ada_selisih ? 'warning' : 'question',
                html: ada_selisih
                    ? `<div class="alert alert-warning">Ditemukan selisih <strong>Rp ${Math.abs(selisih).toLocaleString('id-ID')}</strong>. Wajib beri keterangan.</div>
                       <textarea class="form-control" id="ket_selisih" rows="3" placeholder="Keterangan selisih (wajib jika ada selisih)..."></textarea>`
                    : `<p>Kas petugas tidak ada selisih. Konfirmasi verifikasi?</p>
                       <textarea class="form-control" id="ket_selisih" rows="2" placeholder="Catatan opsional..."></textarea>`,
                showCancelButton: true, confirmButtonText: 'Verifikasi', cancelButtonText: 'Batal',
                confirmButtonColor: '#198754',
                preConfirm: () => {
                    const ket = document.getElementById('ket_selisih').value.trim();
                    if (ada_selisih && !ket) { Swal.showValidationMessage('Keterangan wajib diisi jika ada selisih'); return false; }
                    return ket;
                }
            });
        }
        
        // Load kas petugas data via JSON API
        $(document).ready(function() {
            loadKasPetugasData();
        });

        function loadKasPetugasData() {
            const tanggal = '<?php echo $_GET['tanggal'] ?? date('Y-m-d'); ?>';
            
            window.KewerAPI.getKasPetugas({ tanggal }).done(response => {
                if (response.success) {
                    if (response.stats) {
                        $('#total-setoran').text('Rp ' + formatRupiah(response.stats.total_setoran || 0));
                        $('#belum-verify').text(response.stats.belum_verify || 0);
                    }
                    renderKasPetugasTable(response.data);
                } else {
                    $('#kaspetugas-table-body').html('<tr><td colspan="9" class="text-center text-danger">Gagal memuat data</td></tr>');
                }
            }).fail(error => {
                $('#kaspetugas-table-body').html('<tr><td colspan="9" class="text-center text-danger">Gagal memuat data</td></tr>');
            });
        }

        function renderKasPetugasTable(data) {
            if (!data || data.length === 0) {
                $('#kaspetugas-table-body').html('<tr><td colspan="9" class="text-center text-muted">Belum ada data kas untuk tanggal ini</td></tr>');
                return;
            }

            let html = '';
            data.forEach(kp => {
                const selisihAbs = Math.abs(kp.selisih || 0);
                const adaMasalah = selisihAbs > 10000;
                const rowClass = adaMasalah && !kp.verified_by ? 'table-danger' : '';

                html += `
                    <tr class="${rowClass}">
                        <td><strong>${kp.petugas_nama || '-'}</strong></td>
                        <td>Rp ${formatRupiah(kp.saldo_awal)}</td>
                        <td>Rp ${formatRupiah(kp.total_terima)}</td>
                        <td>Rp ${formatRupiah(kp.total_disetor)}</td>
                        <td>Rp ${formatRupiah(kp.saldo_akhir)}</td>
                        <td class="${adaMasalah ? 'text-danger fw-bold' : 'text-success'}">
                            ${adaMasalah ? '⚠ ' : ''}Rp ${formatRupiah(kp.selisih)}
                            ${kp.selisih_keterangan ? `<br><small class="text-muted">${kp.selisih_keterangan}</small>` : ''}
                        </td>
                        <td>
                            ${kp.verified_by 
                                ? `<span class="badge bg-success"><i class="bi bi-check2"></i> ${kp.verified_nama || '-'}</span><br><small class="text-muted">${formatDate(kp.verified_at)}</small>`
                                : `<span class="badge bg-warning text-dark">Belum</span>`
                            }
                        </td>
                        <td>${kp.is_locked ? '<span class="badge bg-secondary"><i class="bi bi-lock"></i> Terkunci</span>' : '<span class="badge bg-light text-dark">Terbuka</span>'}</td>
                        <td>
                            ${!kp.verified_by && !kp.is_locked 
                                ? `<button class="btn btn-sm btn-success" onclick="verifikasiKas(${kp.id}, '${kp.petugas_nama}', ${kp.selisih})"><i class="bi bi-shield-check"></i> Verifikasi</button>`
                                : ''
                            }
                            ${kp.verified_by && !kp.is_locked
                                ? `<button class="btn btn-sm btn-outline-secondary" onclick="kunciKas(${kp.id})"><i class="bi bi-lock"></i> Kunci</button>`
                                : ''
                            }
                        </td>
                    </tr>
                `;
            });

            $('#kaspetugas-table-body').html(html);
        }

        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka || 0);
        }

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        }

        async function verifikasiKas(kas_id, petugas_nama, selisih) {
            const ada_selisih = Math.abs(selisih) > 10000;
            const keterangan = await Swal.fire({
                title: 'Verifikasi Kas Petugas',
                html: ada_selisih
                    ? `<p>Ada selisih Rp ${formatRupiah(selisih)}. Keterangan wajib diisi.</p>
                       <textarea class="form-control" id="ket_selisih" rows="2" placeholder="Keterangan selisih..."></textarea>`
                    : `<p>Kas petugas tidak ada selisih. Konfirmasi verifikasi?</p>
                       <textarea class="form-control" id="ket_selisih" rows="2" placeholder="Catatan opsional..."></textarea>`,
                showCancelButton: true, confirmButtonText: 'Verifikasi', cancelButtonText: 'Batal',
                confirmButtonColor: '#198754',
                preConfirm: () => {
                    const ket = document.getElementById('ket_selisih').value.trim();
                    if (ada_selisih && !ket) { Swal.showValidationMessage('Keterangan wajib diisi jika ada selisih'); return false; }
                    return ket;
                }
            });
            if (keterangan === undefined) return;
            const resp = await fetch(API_BUSINESS, { method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ action: 'verifikasi_kas', kas_id, keterangan_selisih: keterangan || null }) });
            const r = await resp.json();
            if (r.success) { Swal.fire('Terverifikasi', 'Kas berhasil diverifikasi.', 'success').then(() => location.reload()); }
            else { Swal.fire('Gagal', r.error || 'Terjadi kesalahan', 'error'); }
        }

        async function kunciKas(kas_id) {
            const ok = await Swal.fire({ title: 'Kunci Kas?', text: 'Kas yang terkunci tidak bisa diedit lagi.', icon: 'warning',
                showCancelButton: true, confirmButtonText: 'Kunci', confirmButtonColor: '#6c757d' });
            if (!ok.isConfirmed) return;
            const resp = await fetch(API_BUSINESS, { method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ action: 'kunci_kas', kas_id }) });
            const r = await resp.json();
            if (r.success) { Swal.fire('Terkunci', 'Kas berhasil dikunci.', 'success').then(() => location.reload()); }
            else { Swal.fire('Gagal', r.error || 'Terjadi kesalahan', 'error'); }
        }

        function modalPengganti() {
            new bootstrap.Modal(document.getElementById('penggantiModal')).show();
        }

        async function savePengganti() {
            const petugas_id  = document.getElementById('pg_petugas_id').value;
            const pengganti_id = document.getElementById('pg_pengganti_id').value;
            if (!petugas_id || !pengganti_id) { Swal.fire('Lengkapi Data', 'Petugas dan pengganti wajib dipilih', 'warning'); return; }
            if (petugas_id === pengganti_id) { Swal.fire('Tidak Valid', 'Petugas dan pengganti tidak boleh sama', 'warning'); return; }
            const resp = await fetch(API_BUSINESS, { method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({
                    action: 'assign_pengganti',
                    petugas_id, pengganti_id,
                    alasan_ketidakhadiran: document.getElementById('pg_alasan').value,
                    tanggal_mulai: document.getElementById('pg_mulai').value,
                    tanggal_selesai: document.getElementById('pg_selesai').value,
                    catatan: document.getElementById('pg_catatan').value || null,
                    cabang_id: <?= $cabang_id ?>
                }) });
            const r = await resp.json();
            if (r.success) { Swal.fire('Berhasil', 'Pengganti berhasil di-assign.', 'success').then(() => location.reload()); }
            else { Swal.fire('Gagal', r.error || 'Terjadi kesalahan', 'error'); }
        }

        function saveSetoran() {
            const form = document.getElementById('setoranForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            fetch('<?php echo baseUrl('api/kas_petugas_setoran.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    Swal.fire('Sukses', result.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Swal.fire('Error', result.error || 'Gagal menyimpan setoran', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Terjadi kesalahan', 'error');
            });
        }
        
        function approveSetoran(id) {
            Swal.fire({
                title: 'Approve Setoran?',
                text: 'Anda yakin ingin menyetujui setoran ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Approve',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('<?php echo baseUrl('api/kas_petugas_setoran.php'); ?>?id=' + id, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ status: 'approved' })
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            Swal.fire('Sukses', result.message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            Swal.fire('Error', result.error || 'Gagal approve setoran', 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Terjadi kesalahan', 'error');
                    });
                }
            });
        }
        
        function rejectSetoran(id) {
            Swal.fire({
                title: 'Reject Setoran?',
                text: 'Anda yakin ingin menolak setoran ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Reject',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('<?php echo baseUrl('api/kas_petugas_setoran.php'); ?>?id=' + id, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ status: 'rejected' })
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            Swal.fire('Sukses', result.message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            Swal.fire('Error', result.error || 'Gagal reject setoran', 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Terjadi kesalahan', 'error');
                    });
                }
            });
        }
    </script>
</body>
</html>
