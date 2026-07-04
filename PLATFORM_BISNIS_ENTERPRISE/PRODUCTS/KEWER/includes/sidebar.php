<?php
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../config/path.php';
}
// Only load functions if not already loaded
if (!function_exists('getCurrentUser')) {
    require_once __DIR__ . '/functions.php';
}

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ' . baseUrl('login.php'));
    exit();
}

$user = getCurrentUser();
$role = $user['role'] ?? '';
$cabang_id = getCurrentCabang();
$current_page = $_SERVER['PHP_SELF'];

// Notification count will be handled by jQuery
$notif_count = 0;

// Helper function to check if current page matches a specific path
function isCurrentPage($current, $path) {
    // Normalize paths for Windows
    $current = str_replace('\\', '/', $current);
    $path = str_replace('\\', '/', $path);
    
    // Special case for dashboard at root
    if ($path === 'dashboard.php') {
        return basename($current) === 'dashboard.php';
    }
    
    // Check if current page contains the path
    return strpos($current, $path) !== false;
}

// Helper function to check if current page is exactly a specific page
function isExactPage($current, $page) {
    return basename($current) === $page;
}

// Define pusat roles for consolidated data access
$pusat_roles = ['bos', 'manager_pusat', 'admin_pusat', 'petugas_pusat'];

// Helper function to check if user has permission safely
function safeHasPermission($permission_code) {
    try {
        return hasPermission($permission_code);
    } catch (Exception $e) {
        // If permission check fails, return false for safety
        return false;
    }
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo baseUrl('dashboard.php'); ?>"><?php echo defined('APP_NAME') ? APP_NAME : 'Kewer'; ?></a>
        <div class="navbar-nav ms-auto">
            <span class="navbar-text me-3">
                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($user['nama']); ?> <span class="badge bg-secondary ms-1"><?php echo ucfirst($user['role']); ?></span>
            </span>
            <a class="nav-link" href="<?php echo baseUrl('logout.php'); ?>">Logout</a>
        </div>
    </div>
</nav>

<style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow: hidden;
    }

    .navbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1030;
        height: 56px;
    }

    .main-container {
        margin-top: 56px;
        height: calc(100vh - 56px);
        overflow: hidden;
    }

    .sidebar {
        position: fixed;
        top: 56px;
        left: 0;
        height: calc(100vh - 56px);
        width: 16.666667%;
        overflow-y: auto;
        overflow-x: hidden;
        background-color: #f8f9fa;
        border-right: 1px solid #dee2e6;
        padding: 15px;
        scroll-behavior: smooth;
        z-index: 1020;
    }

    .sidebar .nav-link {
        color: #333;
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 5px;
        transition: all 0.3s ease;
    }

    .sidebar .nav-link:hover {
        background-color: #e9ecef;
        color: #000;
    }

    .sidebar .nav-link.active {
        background-color: #0d6efd;
        color: #fff;
        font-weight: 500;
        box-shadow: 0 2px 4px rgba(13, 110, 253, 0.3);
    }

    .sidebar .nav-link.active i {
        color: #fff;
    }

    .sidebar::-webkit-scrollbar {
        width: 8px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .content-area {
        margin-left: 16.666667%;
        height: calc(100vh - 56px);
        overflow-y: auto;
        overflow-x: hidden;
        padding: 20px;
        box-sizing: border-box;
    }

    .content-area::-webkit-scrollbar {
        width: 8px;
    }

    .content-area::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .content-area::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .content-area::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Ensure tables and other overflow elements don't break layout */
    .content-area .table-responsive {
        overflow-x: auto;
        overflow-y: auto;
        max-width: 100%;
    }

    .content-area table {
        max-width: 100%;
        width: 100%;
    }

    @media (max-width: 991.98px) {
        .sidebar {
            width: 25%;
        }
        .content-area {
            margin-left: 25%;
        }
    }
</style>
<nav class="sidebar">
    <ul class="nav flex-column">
            <?php if ($role !== 'appOwner'): ?>
            <li class="nav-item">
                <a class="nav-link position-relative <?php echo isCurrentPage($current_page, '/pages/notifikasi/index.php') ? 'active' : ''; ?>" href="<?php echo baseUrl('pages/notifikasi/index.php'); ?>">
                    <i class="bi bi-bell"></i> Notifikasi
                    <span id="notification-badge" class="badge bg-danger rounded-pill ms-1" style="font-size:10px; display:none;"></span>
                </a>
                <!-- Notification Dropdown -->
                <div id="notification-dropdown" class="dropdown-menu notification-dropdown" style="position: fixed; left: 16.666667%; top: 56px; width: 350px; max-height: 400px; overflow-y: auto; display: none; z-index: 1030;">
                    <div class="dropdown-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Notifikasi</h6>
                        <button id="mark-all-read" class="btn btn-sm btn-link text-decoration-none">Tandai semua dibaca</button>
                    </div>
                    <div class="dropdown-divider"></div>
                    <div id="notification-list" class="notification-list">
                        <div class="text-center p-3">
                            <div class="spinner-border spinner-border-sm" role="status"></div>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo baseUrl('pages/notifikasi/index.php'); ?>" class="dropdown-item text-center">Lihat semua notifikasi</a>
                </div>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($current_page, 'dashboard.php') !== false ? 'active' : ''; ?>" href="<?php echo baseUrl('dashboard.php'); ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <?php if (safeHasPermission('nasabah.read')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isCurrentPage($current_page, '/pages/nasabah/index.php') ? 'active' : ''; ?>" href="<?php echo baseUrl('pages/nasabah/index.php'); ?>">
                    <i class="bi bi-people"></i> Nasabah
                </a>
            </li>
            <?php endif; ?>
            <?php if (safeHasPermission('pinjaman.read')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isCurrentPage($current_page, '/pages/pinjaman/index.php') ? 'active' : ''; ?>" href="<?php echo baseUrl('pages/pinjaman/index.php'); ?>">
                    <i class="bi bi-cash-stack"></i> Pinjaman
                </a>
            </li>
            <?php endif; ?>
            <?php if (safeHasPermission('angsuran.read')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isCurrentPage($current_page, '/pages/angsuran/index.php') ? 'active' : ''; ?>" href="<?php echo baseUrl('pages/angsuran/index.php'); ?>">
                    <i class="bi bi-calendar-check"></i> Angsuran
                </a>
            </li>
            <?php endif; ?>
            <?php if (safeHasPermission('angsuran.read')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isCurrentPage($current_page, '/pages/field_activities/index.php') ? 'active' : ''; ?>" href="<?php echo baseUrl('pages/field_activities/index.php'); ?>">
                    <i class="bi bi-clipboard-data"></i> Aktivitas Lapangan
                </a>
            </li>
            <?php endif; ?>
            <?php if (safeHasPermission('kas_petugas.read')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isCurrentPage($current_page, '/pages/kas_petugas/index.php') ? 'active' : ''; ?>" href="<?php echo baseUrl('pages/kas_petugas/index.php'); ?>">
                    <i class="bi bi-cash-coin"></i> Kas Petugas
                </a>
            </li>
            <?php endif; ?>
            <?php if (safeHasPermission('kas.read')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isCurrentPage($current_page, '/pages/cash_reconciliation/index.php') ? 'active' : ''; ?>" href="<?php echo baseUrl('pages/cash_reconciliation/index.php'); ?>">
                    <i class="bi bi-calculator"></i> Rekonsiliasi Kas
                </a>
            </li>
            <?php endif; ?>
            <?php if (safeHasPermission('pinjaman.auto_confirm')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isCurrentPage($current_page, '/pages/auto_confirm/index.php') ? 'active' : ''; ?>" href="<?php echo baseUrl('pages/auto_confirm/index.php'); ?>">
                    <i class="bi bi-gear"></i> Auto-Confirm
                </a>
            </li>
            <?php endif; ?>
            <?php if (safeHasPermission('users.create') || safeHasPermission('users.read')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isCurrentPage($current_page, '/pages/users/index.php') ? 'active' : ''; ?>" href="<?php echo baseUrl('pages/users/index.php'); ?>">
                    <i class="bi bi-person-gear"></i> Users
                </a>
            </li>
            <?php endif; ?>
            <?php if (safeHasPermission('manage_users')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isCurrentPage($current_page, '/pages/users/transfer.php') ? 'active' : ''; ?>" href="<?php echo baseUrl('pages/users/transfer.php'); ?>">
                    <i class="bi bi-person-x"></i> Resign / Transfer
                </a>
            </li>
            <?php endif; ?>
            <?php if (safeHasPermission('cabang.read')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isCurrentPage($current_page, '/pages/cabang/index.php') ? 'active' : ''; ?>" href="<?php echo baseUrl('pages/cabang/index.php'); ?>">
                    <i class="bi bi-building"></i> Cabang
                </a>
            </li>
            <?php endif; ?>
            <?php if (safeHasPermission('manage_bunga') || safeHasPermission('view_settings')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isCurrentPage($current_page, '/pages/setting_bunga/index.php') ? 'active' : ''; ?>" href="<?php echo baseUrl('pages/setting_bunga/index.php'); ?>">
                    <i class="bi bi-percent"></i> Setting Bunga
                </a>
            </li>
            <?php endif; ?>
            <?php if (safeHasPermission('manage_pengeluaran') || safeHasPermission('view_pengeluaran')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isCurrentPage($current_page, '/pages/pengeluaran/index.php') ? 'active' : ''; ?>" href="<?php echo baseUrl('pages/pengeluaran/index.php'); ?>">
                    <i class="bi bi-wallet2"></i> Pengeluaran
                </a>
            </li>
            <?php endif; ?>
            <?php if (safeHasPermission('manage_kas_bon') || safeHasPermission('view_kas_bon')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isCurrentPage($current_page, '/pages/kas_bon/index.php') ? 'active' : ''; ?>" href="<?php echo baseUrl('pages/kas_bon/index.php'); ?>">
                    <i class="bi bi-receipt"></i> Kas Bon
                </a>
            </li>
            <?php endif; ?>
            <?php if (safeHasPermission('view_laporan')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isCurrentPage($current_page, '/pages/family_risk/index.php') ? 'active' : ''; ?>" href="<?php echo baseUrl('pages/family_risk/index.php'); ?>">
                    <i class="bi bi-diagram-3"></i> Family Risk
                </a>
            </li>
            <?php endif; ?>
            <?php if (safeHasPermission('manage_petugas') || safeHasPermission('view_petugas')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isCurrentPage($current_page, '/pages/petugas/index.php') ? 'active' : ''; ?>" href="<?php echo baseUrl('pages/petugas/index.php'); ?>">
                    <i class="bi bi-person-badge"></i> Petugas
                </a>
            </li>
            <?php endif; ?>
            <?php if (safeHasPermission('view_laporan') || in_array($role, $pusat_roles)): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isCurrentPage($current_page, '/pages/laporan/index.php') ? 'active' : ''; ?>" href="<?php echo baseUrl('pages/laporan/index.php'); ?>">
                    <i class="bi bi-file-earmark-bar-graph"></i> Laporan
                </a>
            </li>
            <?php endif; ?>
            <?php if (safeHasPermission('rute_harian.read')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isCurrentPage($current_page, '/pages/rute_harian/index.php') ? 'active' : ''; ?>" href="<?php echo baseUrl('pages/rute_harian/index.php'); ?>">
                    <i class="bi bi-map"></i> Rute Harian
                </a>
            </li>
            <?php endif; ?>
            <?php if (safeHasPermission('view_laporan')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isCurrentPage($current_page, '/pages/kinerja/index.php') ? 'active' : ''; ?>" href="<?php echo baseUrl('pages/kinerja/index.php'); ?>">
                    <i class="bi bi-bar-chart"></i> Kinerja Petugas
                </a>
            </li>
            <?php endif; ?>
            <?php if ($role === 'bos'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isCurrentPage($current_page, '/pages/bos/delegated_permissions.php') ? 'active' : ''; ?>" href="<?php echo baseUrl('pages/bos/delegated_permissions.php'); ?>">
                    <i class="bi bi-shield-lock"></i> Delegasi Permission
                </a>
            </li>
            <?php endif; ?>
            <?php /* Persetujuan Bos sekarang di pages/app_owner/approvals.php (khusus appOwner) */ ?>
            <?php if (safeHasPermission('view_laporan') || safeHasPermission('assign_permissions')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isCurrentPage($current_page, '/pages/audit/index.php') ? 'active' : ''; ?>" href="<?php echo baseUrl('pages/audit/index.php'); ?>">
                    <i class="bi bi-shield-check"></i> Audit Trail
                </a>
            </li>
            <?php endif; ?>
            <?php if (safeHasPermission('assign_permissions')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($current_page, 'permissions') !== false ? 'active' : ''; ?>" href="<?php echo baseUrl('pages/permissions/index.php'); ?>">
                    <i class="bi bi-shield-lock"></i> Permissions
                </a>
            </li>
            <?php endif; ?>
            <?php if (in_array($role, ['petugas_pusat','petugas_cabang']) && function_exists('isFeatureEnabled') && isFeatureEnabled('slip_harian')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isCurrentPage($current_page, '/pages/petugas/slip_harian.php') ? 'active' : ''; ?>" href="<?php echo baseUrl('pages/petugas/slip_harian.php'); ?>">
                    <i class="bi bi-receipt-cutoff"></i> Slip Harian
                </a>
            </li>
            <?php endif; ?>
            <?php if (in_array($role, ['bos','manager_pusat','manager_cabang','admin_pusat','appOwner']) && function_exists('isFeatureEnabled') && isFeatureEnabled('two_factor_auth')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo isCurrentPage($current_page, '/pages/users/settings_2fa.php') ? 'active' : ''; ?>" href="<?php echo baseUrl('pages/users/settings_2fa.php'); ?>">
                    <i class="bi bi-shield-shaded"></i> Pengaturan 2FA
                </a>
            </li>
            <?php endif; ?>
        </ul>
</nav>

<script>
    // Auto-center active menu item in sidebar
    document.addEventListener('DOMContentLoaded', function() {
        const activeLink = document.querySelector('.sidebar .nav-link.active');
        const sidebar = document.querySelector('.sidebar');
        
        if (activeLink && sidebar) {
            const sidebarHeight = sidebar.clientHeight;
            const linkTop = activeLink.offsetTop;
            const linkHeight = activeLink.clientHeight;
            
            // Calculate scroll position to center the link
            const scrollPosition = linkTop - (sidebarHeight / 2) + (linkHeight / 2);
            
            // Ensure scroll position is not negative
            const finalScrollPosition = Math.max(0, scrollPosition);
            
            // Always scroll to center the active link
            sidebar.scrollTo({
                top: finalScrollPosition,
                behavior: 'smooth'
            });
        }
    });

    // Scroll to active link on click
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        link.addEventListener('click', function() {
            setTimeout(() => {
                const sidebar = document.querySelector('.sidebar');
                if (sidebar) {
                    const linkTop = this.offsetTop;
                    const sidebarHeight = sidebar.clientHeight;
                    const linkHeight = this.clientHeight;
                    const scrollPosition = linkTop - (sidebarHeight / 2) + (linkHeight / 2);
                    const finalScrollPosition = Math.max(0, scrollPosition);
                    
                    sidebar.scrollTo({
                        top: finalScrollPosition,
                        behavior: 'smooth'
                    });
                }
            }, 100);
        });
    });
</script>
<!-- Include jQuery and Global JS Files -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?php echo baseUrl('includes/js/api.js'); ?>"></script>
<script src="<?php echo baseUrl('includes/js/app.js'); ?>"></script>
<script src="<?php echo baseUrl('includes/js/notifications.js'); ?>"></script>
<script>
// Toggle notification dropdown
document.querySelector('.sidebar .nav-link[href*="notifikasi"]').addEventListener('click', function(e) {
    e.preventDefault();
    const dropdown = document.getElementById('notification-dropdown');
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    
    if (dropdown.style.display === 'block') {
        window.KewerNotifications.loadNotifications('#notification-list');
    }
});

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('notification-dropdown');
    const notifLink = document.querySelector('.sidebar .nav-link[href*="notifikasi"]');
    
    if (dropdown.style.display === 'block' && !dropdown.contains(e.target) && !notifLink.contains(e.target)) {
        dropdown.style.display = 'none';
    }
});
</script>
