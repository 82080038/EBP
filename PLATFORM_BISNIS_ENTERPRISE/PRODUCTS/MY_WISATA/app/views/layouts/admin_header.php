<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= isset($description) ? View::e($description) : 'MyWisata Admin Panel' ?>">
    <title><?= isset($title) ? View::e($title) : 'Admin Panel - MyWisata' ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= View::asset('css/admin.css') ?>" rel="stylesheet">
    
    <script>
        window.APP_URL = '<?= BASE_URL ?>';
    </script>
</head>
<body>
    <!-- Admin Layout -->
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="bg-dark text-white" style="width: 250px; min-height: 100vh;">
            <div class="p-3">
                <h5 class="text-center mb-4">
                    <i class="fas fa-map-marked-alt me-2"></i>MyWisata Admin
                </h5>
                <ul class="nav flex-column">
                    <li class="nav-item mb-2">
                        <a class="nav-link text-white <?= $_GET['url'] === 'admin/dashboard' ? 'active bg-primary' : '' ?>" href="<?= View::url('admin/dashboard') ?>">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link text-white <?= strpos($_GET['url'] ?? '', 'admin/users') === 0 ? 'active bg-primary' : '' ?>" href="<?= View::url('admin/users') ?>">
                            <i class="fas fa-users me-2"></i>Pengguna
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link text-white <?= strpos($_GET['url'] ?? '', 'admin/guides') === 0 ? 'active bg-primary' : '' ?>" href="<?= View::url('admin/guides') ?>">
                            <i class="fas fa-user-tie me-2"></i>Tour Guide
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link text-white <?= strpos($_GET['url'] ?? '', 'admin/destinations') === 0 ? 'active bg-primary' : '' ?>" href="<?= View::url('admin/destinations') ?>">
                            <i class="fas fa-map-marked-alt me-2"></i>Destinasi
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link text-white" href="#">
                            <i class="fas fa-hotel me-2"></i>Hotel
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link text-white" href="#">
                            <i class="fas fa-utensils me-2"></i>Restoran
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link text-white" href="#">
                            <i class="fas fa-calendar-alt me-2"></i>Event
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link text-white" href="#">
                            <i class="fas fa-ticket-alt me-2"></i>Transaksi
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link text-white <?= strpos($_GET['url'] ?? '', 'admin/settings') === 0 ? 'active bg-primary' : '' ?>" href="<?= View::url('admin/settings') ?>">
                            <i class="fas fa-cog me-2"></i>Pengaturan
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link text-danger" href="<?= View::url('auth/logout') ?>">
                            <i class="fas fa-sign-out-alt me-2"></i>Keluar
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-grow-1" style="background-color: #f8f9fa;">
            <!-- Top Bar -->
            <nav class="navbar navbar-light bg-white border-bottom px-4">
                <span class="navbar-brand mb-0 h1"><?= isset($title) ? View::e($title) : 'Admin Panel' ?></span>
                <div class="d-flex align-items-center">
                    <span class="me-3">
                        <i class="fas fa-user-circle me-1"></i>
                        <?= View::e(Session::get('user_name')) ?>
                    </span>
                    <span class="badge bg-primary"><?= View::e(Session::get('role')) ?></span>
                </div>
            </nav>
            
            <!-- Flash Messages -->
            <?php if (Session::hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                <?= View::e(Session::getFlash('success')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if (Session::hasFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                <?= View::e(Session::getFlash('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <!-- Content -->
            <main>
