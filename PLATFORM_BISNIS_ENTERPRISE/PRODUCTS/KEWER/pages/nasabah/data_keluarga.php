<?php
/**
 * Page: Nasabah Data Keluarga
 * 
 * Halaman untuk nasabah melengkapi data keluarga dengan foto KK
 * Wajib diisi untuk nasabah yang sudah pernah mengajukan pinjaman (anti tukar kulit)
 * 
 * Access: nasabah only
 */

require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';

// Check if user is logged in and is nasabah
if (!isLoggedIn() || $_SESSION['role'] !== 'nasabah') {
    header('Location: ' . baseUrl('login.php'));
    exit();
}

$nasabah_id = $_SESSION['nasabah_id'] ?? null;
if (!$nasabah_id) {
    header('Location: ' . baseUrl('login.php?error=no_nasabah_id'));
    exit();
}

$page_title = 'Data Keluarga';
include BASE_PATH . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">
                <i class="bi bi-people-fill me-2"></i>
                Data Keluarga
            </h1>
            <p class="text-muted">Lengkapi data keluarga untuk pengajuan pinjaman berikutnya</p>
        </div>
    </div>

    <!-- Alert for requirement -->
    <div id="requirementAlert" class="alert alert-warning mb-4" style="display: none;">
        <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill me-2"></i>Data Keluarga Wajib</h5>
        <p class="mb-0">Anda sudah pernah mengajukan pinjaman. Untuk mengajukan pinjaman lagi, wajib melengkapi data keluarga dengan foto Kartu Keluarga (KK).</p>
    </div>

    <!-- Status Card -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card" id="statusCard">
                <div class="card-body text-center">
                    <div id="statusIcon" class="mb-3">
                        <i class="bi bi-hourglass-split fs-1 text-warning"></i>
                    </div>
                    <h5 class="card-title" id="statusTitle">Memuat...</h5>
                    <p class="card-text" id="statusDesc">Memuat status data keluarga...</p>
                    <span id="statusBadge" class="badge bg-secondary">-</span>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-info-circle me-2"></i>
                    Informasi Penting
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Data keluarga wajib diisi untuk nasabah yang sudah pernah mengajukan pinjaman</li>
                        <li>Foto Kartu Keluarga (KK) harus jelas dan terbaca</li>
                        <li>Data keluarga akan diverifikasi oleh petugas koperasi</li>
                        <li>Fitur ini bertujuan untuk mencegah "tukar kulit" (satu keluarga blacklist, keluarga lain mengajukan)</li>
                        <li>Semua anggota keluarga yang tercatat di KK wajib diinput</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Data Keluarga -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Form Data Keluarga
                </div>
                <div class="card-body">
                    <form id="keluargaForm">
                        <h5 class="mb-3 text-primary">Data Kartu Keluarga</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nomor Kartu Keluarga (KK) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="noKK" name="no_kk" required maxlength="16" placeholder="16 digit nomor KK">
                                <small class="text-muted">Nomor KK tertera di bagian atas Kartu Keluarga</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Kepala Keluarga <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="namaKepala" name="nama_kepala_keluarga" required placeholder="Nama lengkap kepala keluarga">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat Keluarga</label>
                            <textarea class="form-control" id="alamatKeluarga" name="alamat_keluarga" rows="2" placeholder="Alamat lengkap sesuai KK"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-2 mb-3">
                                <label class="form-label">RT</label>
                                <input type="text" class="form-control" id="rt" name="rt" maxlength="3">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">RW</label>
                                <input type="text" class="form-control" id="rw" name="rw" maxlength="3">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Desa/Kelurahan</label>
                                <input type="text" class="form-control" id="desa" name="desa_kelurahan">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Kecamatan</label>
                                <input type="text" class="form-control" id="kecamatan" name="kecamatan">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Kabupaten/Kota</label>
                                <input type="text" class="form-control" id="kabupaten" name="kabupaten">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Provinsi</label>
                                <input type="text" class="form-control" id="provinsi" name="provinsi">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Kode Pos</label>
                                <input type="text" class="form-control" id="kodePos" name="kode_pos" maxlength="5">
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3 text-primary">Foto Kartu Keluarga</h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Upload Foto KK <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="fotoKK" name="foto_kk" accept="image/jpeg,image/png,image/jpg">
                            <small class="text-muted">Format: JPG/PNG, Maksimal 5MB. Foto harus jelas dan terbaca.</small>
                        </div>

                        <div id="previewFotoKK" class="mb-3" style="display: none;">
                            <label class="form-label">Preview Foto KK:</label>
                            <div class="border p-2 rounded">
                                <img id="imgPreviewKK" src="" alt="Preview KK" class="img-fluid" style="max-height: 300px;">
                            </div>
                        </div>

                        <div id="existingFotoKK" class="mb-3" style="display: none;">
                            <label class="form-label">Foto KK Tersimpan:</label>
                            <div class="border p-2 rounded">
                                <img id="imgExistingKK" src="" alt="Foto KK" class="img-fluid" style="max-height: 300px;">
                                <p class="mt-2 mb-0 text-success"><i class="bi bi-check-circle me-1"></i>Foto KK sudah diupload</p>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" id="saveBtn">
                                <i class="bi bi-save me-2"></i>Simpan Data Keluarga
                            </button>
                            <a href="<?php echo baseUrl('pages/nasabah/dashboard.php'); ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Kembali ke Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Anggota Keluarga Section -->
    <div class="row mt-4" id="anggotaSection" style="display: none;">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-people me-2"></i>Daftar Anggota Keluarga</span>
                    <button type="button" class="btn btn-success btn-sm" onclick="showAddAnggotaModal()">
                        <i class="bi bi-plus me-1"></i>Tambah Anggota
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="anggotaTable">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Nama Lengkap</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tempat/Tgl Lahir</th>
                                    <th>Hubungan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="anggotaTableBody">
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        Belum ada anggota keluarga
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Anggota -->
<div class="modal fade" id="addAnggotaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Anggota Keluarga</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="anggotaForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIK <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="anggotaNIK" required maxlength="16" placeholder="16 digit NIK">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="anggotaNama" required placeholder="Nama lengkap sesuai KK">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jenis Kelamin</label>
                            <select class="form-select" id="anggotaJenisKelamin">
                                <option value="">Pilih...</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" class="form-control" id="anggotaTempatLahir">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control" id="anggotaTanggalLahir">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hubungan dengan KK <span class="text-danger">*</span></label>
                            <select class="form-select" id="anggotaHubungan" required>
                                <option value="">Pilih...</option>
                                <option value="kepala_keluarga">Kepala Keluarga</option>
                                <option value="suami">Suami</option>
                                <option value="istri">Istri</option>
                                <option value="anak">Anak</option>
                                <option value="menantu">Menantu</option>
                                <option value="cucu">Cucu</option>
                                <option value="orang_tua">Orang Tua</option>
                                <option value="mertua">Mertua</option>
                                <option value="famili_lain">Famili Lain</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status Perkawinan</label>
                            <select class="form-select" id="anggotaStatusKawin">
                                <option value="">Pilih...</option>
                                <option value="belum_kawin">Belum Kawin</option>
                                <option value="kawin">Kawin</option>
                                <option value="cerai_hidup">Cerai Hidup</option>
                                <option value="cerai_mati">Cerai Mati</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Pekerjaan</label>
                            <input type="text" class="form-control" id="anggotaPekerjaan">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Agama</label>
                            <input type="text" class="form-control" id="anggotaAgama">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Pendidikan</label>
                            <input type="text" class="form-control" id="anggotaPendidikan">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Ayah</label>
                            <input type="text" class="form-control" id="anggotaNamaAyah">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Ibu</label>
                            <input type="text" class="form-control" id="anggotaNamaIbu">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveAnggota()">Simpan Anggota</button>
            </div>
        </div>
    </div>
</div>

<script>
let keluargaData = null;
let anggotaList = [];

// Load on page load
document.addEventListener('DOMContentLoaded', function() {
    checkRequirement();
    loadDataKeluarga();
    
    // Setup form submit
    document.getElementById('keluargaForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveDataKeluarga();
    });
    
    // Setup file preview
    document.getElementById('fotoKK').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imgPreviewKK').src = e.target.result;
                document.getElementById('previewFotoKK').style.display = 'block';
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    });
});

/**
 * Check if data keluarga is required
 */
async function checkRequirement() {
    try {
        const response = await fetch('<?php echo baseUrl("api/nasabah_portal.php?action=check_data_keluarga_required"); ?>');
        const result = await response.json();
        
        if (result.success && result.data.required) {
            document.getElementById('requirementAlert').style.display = 'block';
        }
        
        updateStatusCard(result.data);
    } catch (error) {
        console.error('Error checking requirement:', error);
    }
}

/**
 * Update status card
 */
function updateStatusCard(data) {
    const statusIcon = document.getElementById('statusIcon');
    const statusTitle = document.getElementById('statusTitle');
    const statusDesc = document.getElementById('statusDesc');
    const statusBadge = document.getElementById('statusBadge');
    
    if (data.has_data) {
        statusIcon.innerHTML = '<i class="bi bi-check-circle-fill fs-1 text-success"></i>';
        statusTitle.textContent = 'Data Lengkap';
        statusDesc.textContent = 'Data keluarga sudah lengkap dan terverifikasi.';
        statusBadge.className = 'badge bg-success';
        statusBadge.textContent = 'Terverifikasi';
        document.getElementById('anggotaSection').style.display = 'block';
    } else if (data.required) {
        statusIcon.innerHTML = '<i class="bi bi-exclamation-triangle-fill fs-1 text-warning"></i>';
        statusTitle.textContent = 'Data Wajib';
        statusDesc.textContent = 'Anda pernah pinjam. Wajib lengkapi data keluarga.';
        statusBadge.className = 'badge bg-warning';
        statusBadge.textContent = 'Wajib Diisi';
    } else {
        statusIcon.innerHTML = '<i class="bi bi-info-circle-fill fs-1 text-info"></i>';
        statusTitle.textContent = 'Data Belum Wajib';
        statusDesc.textContent = 'Data keluarga belum wajib untuk pengajuan pertama.';
        statusBadge.className = 'badge bg-info';
        statusBadge.textContent = 'Opsional';
    }
}

/**
 * Load data keluarga
 */
async function loadDataKeluarga() {
    try {
        const response = await fetch('<?php echo baseUrl("api/nasabah_portal.php?action=get_data_keluarga"); ?>');
        const result = await response.json();
        
        if (result.success && result.data) {
            keluargaData = result.data;
            populateForm(result.data);
            anggotaList = result.data.anggota || [];
            renderAnggotaTable();
            document.getElementById('anggotaSection').style.display = 'block';
            
            // Show existing foto if available
            if (result.data.foto_kk) {
                document.getElementById('imgExistingKK').src = '<?php echo baseUrl("uploads/kk/"); ?>' + result.data.foto_kk;
                document.getElementById('existingFotoKK').style.display = 'block';
            }
        }
    } catch (error) {
        console.error('Error loading data keluarga:', error);
    }
}

/**
 * Populate form with existing data
 */
function populateForm(data) {
    document.getElementById('noKK').value = data.no_kk || '';
    document.getElementById('namaKepala').value = data.nama_kepala_keluarga || '';
    document.getElementById('alamatKeluarga').value = data.alamat_keluarga || '';
    document.getElementById('rt').value = data.rt || '';
    document.getElementById('rw').value = data.rw || '';
    document.getElementById('desa').value = data.desa_kelurahan || '';
    document.getElementById('kecamatan').value = data.kecamatan || '';
    document.getElementById('kabupaten').value = data.kabupaten || '';
    document.getElementById('provinsi').value = data.provinsi || '';
    document.getElementById('kodePos').value = data.kode_pos || '';
}

/**
 * Save data keluarga
 */
async function saveDataKeluarga() {
    const saveBtn = document.getElementById('saveBtn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
    
    const data = {
        no_kk: document.getElementById('noKK').value,
        nama_kepala_keluarga: document.getElementById('namaKepala').value,
        alamat_keluarga: document.getElementById('alamatKeluarga').value,
        rt: document.getElementById('rt').value,
        rw: document.getElementById('rw').value,
        desa_kelurahan: document.getElementById('desa').value,
        kecamatan: document.getElementById('kecamatan').value,
        kabupaten: document.getElementById('kabupaten').value,
        provinsi: document.getElementById('provinsi').value,
        kode_pos: document.getElementById('kodePos').value
    };
    
    try {
        const response = await fetch('<?php echo baseUrl("api/nasabah_portal.php?action=save_data_keluarga"); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Upload foto KK if selected
            const fotoFile = document.getElementById('fotoKK').files[0];
            if (fotoFile) {
                await uploadFotoKK();
            }
            
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data keluarga berhasil disimpan. Silakan tambahkan anggota keluarga.',
                confirmButtonText: 'OK'
            }).then(() => {
                document.getElementById('anggotaSection').style.display = 'block';
                checkRequirement();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: result.error || 'Terjadi kesalahan',
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Terjadi kesalahan saat menyimpan data',
            confirmButtonText: 'OK'
        });
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="bi bi-save me-2"></i>Simpan Data Keluarga';
    }
}

/**
 * Upload foto KK
 */
async function uploadFotoKK() {
    const formData = new FormData();
    formData.append('foto_kk', document.getElementById('fotoKK').files[0]);
    
    const response = await fetch('<?php echo baseUrl("api/nasabah_portal.php?action=upload_foto_kk"); ?>', {
        method: 'POST',
        body: formData
    });
    
    return await response.json();
}

/**
 * Show add anggota modal
 */
function showAddAnggotaModal() {
    const modal = new bootstrap.Modal(document.getElementById('addAnggotaModal'));
    modal.show();
}

/**
 * Save anggota keluarga
 */
async function saveAnggota() {
    const data = {
        nik: document.getElementById('anggotaNIK').value,
        nama_lengkap: document.getElementById('anggotaNama').value,
        jenis_kelamin: document.getElementById('anggotaJenisKelamin').value,
        tempat_lahir: document.getElementById('anggotaTempatLahir').value,
        tanggal_lahir: document.getElementById('anggotaTanggalLahir').value,
        hubungan_dengan_kk: document.getElementById('anggotaHubungan').value,
        status_perkawinan: document.getElementById('anggotaStatusKawin').value,
        pekerjaan: document.getElementById('anggotaPekerjaan').value,
        agama: document.getElementById('anggotaAgama').value,
        pendidikan: document.getElementById('anggotaPendidikan').value,
        nama_ayah: document.getElementById('anggotaNamaAyah').value,
        nama_ibu: document.getElementById('anggotaNamaIbu').value
    };
    
    try {
        const response = await fetch('<?php echo baseUrl("api/nasabah_portal.php?action=add_anggota_keluarga"); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Anggota keluarga berhasil ditambahkan',
                confirmButtonText: 'OK',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            });
            
            // Reset form and close modal
            document.getElementById('anggotaForm').reset();
            bootstrap.Modal.getInstance(document.getElementById('addAnggotaModal')).hide();
            
            // Reload anggota list
            loadDataKeluarga();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: result.error || 'Terjadi kesalahan',
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Terjadi kesalahan saat menambahkan anggota',
            confirmButtonText: 'OK'
        });
    }
}

/**
 * Render anggota table
 */
function renderAnggotaTable() {
    const tbody = document.getElementById('anggotaTableBody');
    
    if (anggotaList.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4 text-muted">
                    Belum ada anggota keluarga. Silakan tambahkan anggota sesuai KK.
                </td>
            </tr>
        `;
        return;
    }
    
    const hubunganLabels = {
        'kepala_keluarga': 'Kepala Keluarga',
        'suami': 'Suami',
        'istri': 'Istri',
        'anak': 'Anak',
        'menantu': 'Menantu',
        'cucu': 'Cucu',
        'orang_tua': 'Orang Tua',
        'mertua': 'Mertua',
        'famili_lain': 'Famili Lain'
    };
    
    tbody.innerHTML = anggotaList.map((a, index) => `
        <tr>
            <td>${index + 1}</td>
            <td>${a.nik || '-'}</td>
            <td><strong>${a.nama_lengkap}</strong></td>
            <td>${a.jenis_kelamin === 'L' ? 'Laki-laki' : a.jenis_kelamin === 'P' ? 'Perempuan' : '-'}</td>
            <td>${a.tempat_lahir || '-'}${a.tanggal_lahir ? ', ' + formatDate(a.tanggal_lahir) : ''}</td>
            <td>${hubunganLabels[a.hubungan_dengan_kk] || a.hubungan_dengan_kk}</td>
            <td>
                ${a.status_blacklist ? 
                    '<span class="badge bg-danger">BLACKLIST</span>' : 
                    '<span class="badge bg-success">Aktif</span>'}
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="deleteAnggota(${a.id})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

/**
 * Delete anggota
 */
async function deleteAnggota(anggotaId) {
    const confirm = await Swal.fire({
        icon: 'warning',
        title: 'Hapus Anggota?',
        text: 'Anggota keluarga akan dihapus. Lanjutkan?',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    });
    
    if (!confirm.isConfirmed) return;
    
    try {
        const response = await fetch('<?php echo baseUrl("api/nasabah_portal.php?action=delete_anggota_keluarga"); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ anggota_id: anggotaId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Terhapus!',
                text: 'Anggota keluarga berhasil dihapus',
                confirmButtonText: 'OK',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            });
            loadDataKeluarga();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: result.error || 'Terjadi kesalahan',
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

/**
 * Format date
 */
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
}
</script>

<?php include BASE_PATH . '/includes/footer.php'; ?>
