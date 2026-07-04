<?php
/**
 * Page: Provinsi Activation Management
 * 
 * Halaman untuk mengelola provinsi yang aktif/non-aktif
 * untuk wilayah kerja bos koperasi
 * 
 * Access: appOwner only
 */

require_once __DIR__ . '/../../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/alamat_helper.php';

// Check if user is logged in and is appOwner
if (!isLoggedIn() || $_SESSION['role'] !== 'appOwner') {
    header('Location: ' . baseUrl('login.php'));
    exit();
}

$page_title = 'Manajemen Provinsi Wilayah Kerja';
include BASE_PATH . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="bi bi-geo-alt-fill me-2"></i>
                    Manajemen Provinsi Wilayah Kerja
                </h1>
                <div>
                    <span class="badge bg-info fs-6">
                        <i class="bi bi-info-circle me-1"></i>
                        Data Nasional: 38 Provinsi
                    </span>
                </div>
            </div>
            
            <!-- Info Card -->
            <div class="alert alert-info mb-4">
                <h5 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Informasi</h5>
                <p class="mb-0">
                    Halaman ini digunakan untuk mengaktifkan atau menonaktifkan provinsi untuk wilayah kerja bos koperasi. 
                    <strong>Catatan:</strong> Pembatasan ini hanya berlaku untuk wilayah kerja bos dan bawahannya, 
                    namun tidak membatasi input data alamat nasabah (data alamat tetap bisa menggunakan semua provinsi nasional).
                </p>
            </div>
            
            <!-- Stats Cards -->
            <div class="row mb-4" id="statsContainer">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-white-50">Total Provinsi</h6>
                                    <h2 class="mb-0" id="totalProvinces">-</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-map fs-1 text-white-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-white-50">Provinsi Aktif</h6>
                                    <h2 class="mb-0" id="activeProvinces">-</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-check-circle fs-1 text-white-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-secondary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-white-50">Provinsi Non-Aktif</h6>
                                    <h2 class="mb-0" id="inactiveProvinces">-</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-x-circle fs-1 text-white-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filter & Search -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="searchInput" 
                                       placeholder="Cari nama provinsi...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="statusFilter">
                                <option value="all">Semua Status</option>
                                <option value="active">Aktif</option>
                                <option value="inactive">Non-Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                                <i class="bi bi-arrow-clockwise me-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Provinces Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Provinsi</h5>
                    <div>
                        <button class="btn btn-success btn-sm me-2" onclick="activateAll()">
                            <i class="bi bi-check-all me-1"></i> Aktifkan Semua
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="deactivateAll()">
                            <i class="bi bi-x-square me-1"></i> Nonaktifkan Semua
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="provincesTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" width="60">No</th>
                                    <th>Kode</th>
                                    <th>Nama Provinsi</th>
                                    <th class="text-center">Status</th>
                                    <th>Diaktifkan Oleh</th>
                                    <th>Tanggal Aktivasi</th>
                                    <th class="text-center" width="150">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="provincesTableBody">
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Memuat data provinsi...</p>
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

<script>
let provincesData = [];

// Load provinces on page load
document.addEventListener('DOMContentLoaded', function() {
    loadProvinces();
    
    // Setup search
    document.getElementById('searchInput').addEventListener('input', debounce(function() {
        filterTable();
    }, 300));
    
    // Setup status filter
    document.getElementById('statusFilter').addEventListener('change', filterTable);
});

/**
 * Load provinces from API
 */
async function loadProvinces() {
    try {
        const response = await fetch('<?php echo baseUrl("api/provinsi_activation.php"); ?>');
        const result = await response.json();
        
        if (result.success) {
            provincesData = result.data;
            updateStats(result.stats);
            renderTable(provincesData);
        } else {
            showAlert('error', result.error || 'Gagal memuat data provinsi');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Terjadi kesalahan saat memuat data');
    }
}

/**
 * Update statistics
 */
function updateStats(stats) {
    document.getElementById('totalProvinces').textContent = stats.total;
    document.getElementById('activeProvinces').textContent = stats.active;
    document.getElementById('inactiveProvinces').textContent = stats.inactive;
}

/**
 * Render table
 */
function renderTable(data) {
    const tbody = document.getElementById('provincesTableBody');
    
    if (data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    Tidak ada data provinsi
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = data.map((province, index) => `
        <tr>
            <td class="text-center">${index + 1}</td>
            <td><span class="badge bg-light text-dark">${String(province.province_id).padStart(2, '0')}</span></td>
            <td><strong>${province.province_name}</strong></td>
            <td class="text-center">
                ${province.is_active 
                    ? '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Aktif</span>'
                    : '<span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i> Non-Aktif</span>'
                }
            </td>
            <td><small>${province.activated_by_name || '-'}</small></td>
            <td><small>${formatDate(province.activated_at)}</small></td>
            <td class="text-center">
                <div class="form-check form-switch d-inline-block">
                    <input class="form-check-input" type="checkbox" 
                           ${province.is_active ? 'checked' : ''}
                           onchange="toggleProvince(${province.province_id}, this.checked)"
                           style="width: 3em; height: 1.5em; cursor: pointer;">
                </div>
            </td>
        </tr>
    `).join('');
}

/**
 * Toggle province activation
 */
async function toggleProvince(provinceId, isActive) {
    try {
        const response = await fetch('<?php echo baseUrl("api/provinsi_activation.php"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                province_id: provinceId,
                is_active: isActive,
                notes: isActive ? 'Activated via appOwner panel' : 'Deactivated via appOwner panel'
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('success', result.message);
            loadProvinces(); // Reload to refresh data
        } else {
            showAlert('error', result.error || 'Gagal mengubah status provinsi');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Terjadi kesalahan saat mengubah status');
    }
}

/**
 * Filter table
 */
function filterTable() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const status = document.getElementById('statusFilter').value;
    
    let filtered = provincesData.filter(p => {
        const matchSearch = p.province_name.toLowerCase().includes(search);
        const matchStatus = status === 'all' || 
                          (status === 'active' && p.is_active) ||
                          (status === 'inactive' && !p.is_active);
        return matchSearch && matchStatus;
    });
    
    renderTable(filtered);
}

/**
 * Reset filters
 */
function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = 'all';
    renderTable(provincesData);
}

/**
 * Activate all provinces
 */
async function activateAll() {
    if (!confirm('Apakah Anda yakin ingin mengaktifkan SEMUA provinsi?')) {
        return;
    }
    
    try {
        const inactiveProvinces = provincesData.filter(p => !p.is_active);
        
        for (const province of inactiveProvinces) {
            await fetch('<?php echo baseUrl("api/provinsi_activation.php"); ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    province_id: province.province_id,
                    is_active: true,
                    notes: 'Bulk activated via appOwner panel'
                })
            });
        }
        
        showAlert('success', 'Semua provinsi berhasil diaktifkan');
        loadProvinces();
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Terjadi kesalahan saat mengaktifkan provinsi');
    }
}

/**
 * Deactivate all provinces
 */
async function deactivateAll() {
    if (!confirm('Apakah Anda yakin ingin menonaktifkan SEMUA provinsi?\n\nPeringatan: Ini akan membatasi seluruh wilayah kerja bos koperasi!')) {
        return;
    }
    
    try {
        const activeProvinces = provincesData.filter(p => p.is_active);
        
        for (const province of activeProvinces) {
            await fetch('<?php echo baseUrl("api/provinsi_activation.php"); ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    province_id: province.province_id,
                    is_active: false,
                    notes: 'Bulk deactivated via appOwner panel'
                })
            });
        }
        
        showAlert('success', 'Semua provinsi berhasil dinonaktifkan');
        loadProvinces();
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Terjadi kesalahan saat menonaktifkan provinsi');
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
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Show alert
 */
function showAlert(type, message) {
    // Use SweetAlert2 if available, otherwise fallback to alert
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
