<?php
/**
 * Page: Nasabah Profile
 * 
 * Halaman profil untuk nasabah melihat dan mengupdate data pribadi
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

$page_title = 'Profil Saya';
include BASE_PATH . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">
                <i class="bi bi-person-circle me-2"></i>
                Profil Saya
            </h1>
            <p class="text-muted">Lihat dan kelola informasi pribadi Anda</p>
        </div>
    </div>

    <div class="row">
        <!-- Profile Card -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-person-circle fs-1 text-primary"></i>
                    </div>
                    <h5 class="card-title" id="profileNama">...</h5>
                    <p class="card-text text-muted" id="profileKode">...</p>
                    <span id="profileStatus" class="badge bg-secondary">...</span>
                    
                    <hr>
                    
                    <div class="text-start">
                        <p class="mb-1"><strong>Login:</strong></p>
                        <p class="text-muted small mb-0">Username: <span id="profileUsername">...</span></p>
                        <p class="text-muted small">Password: 6 digit terakhir KTP</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Form -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-info-circle me-2"></i>
                    Informasi Pribadi
                </div>
                <div class="card-body">
                    <form id="profileForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Kode Nasabah</label>
                                <input type="text" class="form-control" id="kodeNasabah" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. KTP</label>
                                <input type="text" class="form-control" id="ktp" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama" readonly>
                                <small class="text-muted">Hubungi petugas untuk mengubah nama</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">No. Telepon/WhatsApp</label>
                                <input type="tel" class="form-control" id="telp" name="telp" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jenis Usaha</label>
                                <input type="text" class="form-control" id="jenisUsaha" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Lokasi Pasar/Tempat Usaha</label>
                            <input type="text" class="form-control" id="lokasiPasar" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamatRumah" name="alamat_rumah" rows="2"></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Provinsi</label>
                                <input type="text" class="form-control" id="provinsi" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Kabupaten/Kota</label>
                                <input type="text" class="form-control" id="kabupaten" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Kecamatan</label>
                                <input type="text" class="form-control" id="kecamatan" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Desa/Kelurahan</label>
                                <input type="text" class="form-control" id="desa" readonly>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <div>
                                <small class="text-muted">Terdaftar sejak: <span id="createdAt">...</span></small>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Load profile on page load
document.addEventListener('DOMContentLoaded', function() {
    loadProfile();
    
    // Setup form submit
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveProfile();
    });
});

/**
 * Load profile data
 */
async function loadProfile() {
    try {
        const response = await fetch('<?php echo baseUrl("api/nasabah_portal.php?action=profile"); ?>');
        const result = await response.json();
        
        if (result.success) {
            updateProfileForm(result.data);
        } else {
            showAlert('error', result.error || 'Gagal memuat data profil');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Terjadi kesalahan saat memuat data');
    }
}

/**
 * Update profile form with data
 */
function updateProfileForm(data) {
    // Profile card
    document.getElementById('profileNama').textContent = data.nama;
    document.getElementById('profileKode').textContent = data.kode_nasabah;
    document.getElementById('profileUsername').textContent = data.kode_nasabah;
    
    // Status badge
    const statusBadge = document.getElementById('profileStatus');
    if (data.status === 'blacklist') {
        statusBadge.className = 'badge bg-danger';
        statusBadge.innerHTML = '<i class="bi bi-x-octagon me-1"></i> BLACKLIST';
    } else if (data.status === 'aktif') {
        statusBadge.className = 'badge bg-success';
        statusBadge.innerHTML = '<i class="bi bi-check-circle me-1"></i> AKTIF';
    } else {
        statusBadge.className = 'badge bg-secondary';
        statusBadge.textContent = data.status.toUpperCase();
    }
    
    // Form fields
    document.getElementById('kodeNasabah').value = data.kode_nasabah;
    document.getElementById('ktp').value = data.ktp;
    document.getElementById('nama').value = data.nama;
    document.getElementById('email').value = data.email || '';
    document.getElementById('telp').value = data.telp || '';
    document.getElementById('jenisUsaha').value = data.jenis_usaha || '-';
    document.getElementById('lokasiPasar').value = data.lokasi_pasar || '-';
    document.getElementById('alamatRumah').value = data.alamat_lengkap?.alamat_rumah || '';
    document.getElementById('provinsi').value = data.alamat_lengkap?.province || '-';
    document.getElementById('kabupaten').value = data.alamat_lengkap?.regency || '-';
    document.getElementById('kecamatan').value = data.alamat_lengkap?.district || '-';
    document.getElementById('desa').value = data.alamat_lengkap?.village || '-';
    document.getElementById('createdAt').textContent = formatDate(data.created_at);
}

/**
 * Save profile changes
 */
async function saveProfile() {
    const data = {
        email: document.getElementById('email').value,
        telp: document.getElementById('telp').value,
        alamat_rumah: document.getElementById('alamatRumah').value
    };
    
    try {
        const response = await fetch('<?php echo baseUrl("api/nasabah_portal.php?action=update_profile"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('success', 'Profil berhasil diperbarui');
        } else {
            showAlert('error', result.error || 'Gagal memperbarui profil');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Terjadi kesalahan saat menyimpan data');
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
        month: 'long',
        year: 'numeric'
    });
}

/**
 * Show alert
 */
function showAlert(type, message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: type,
            title: type === 'success' ? 'Berhasil!' : 'Error!',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    } else {
        alert(message);
    }
}
</script>

<?php include BASE_PATH . '/includes/footer.php'; ?>
