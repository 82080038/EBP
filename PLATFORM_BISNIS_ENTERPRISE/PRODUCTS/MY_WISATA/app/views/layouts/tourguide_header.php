<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= isset($description) ? View::e($description) : 'MyWisata Tour Guide Panel' ?>">
    <title><?= isset($title) ? View::e($title) : 'Tour Guide Panel - MyWisata' ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= View::asset('css/style.css') ?>" rel="stylesheet">
    
    <script>
        window.APP_URL = '<?= BASE_URL ?>';
    </script>
</head>
<body>
    <!-- Tour Guide Layout -->
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="bg-primary text-white" style="width: 250px; min-height: 100vh;">
            <div class="p-3">
                <h5 class="text-center mb-4">
                    <i class="fas fa-user-tie me-2"></i>Tour Guide
                </h5>
                <ul class="nav flex-column">
                    <li class="nav-item mb-2">
                        <a class="nav-link text-white <?= $_GET['url'] === 'tourguide/dashboard' ? 'active bg-white text-primary' : '' ?>" href="<?= View::url('tourguide/dashboard') ?>">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link text-white <?= strpos($_GET['url'] ?? '', 'tourguide/profile') === 0 ? 'active bg-white text-primary' : '' ?>" href="<?= View::url('tourguide/profile') ?>">
                            <i class="fas fa-user me-2"></i>Profil Saya
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link text-white <?= strpos($_GET['url'] ?? '', 'tourguide/skills') === 0 ? 'active bg-white text-primary' : '' ?>" href="<?= View::url('tourguide/skills') ?>">
                            <i class="fas fa-language me-2"></i>Bahasa & Spesialisasi
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link text-white <?= strpos($_GET['url'] ?? '', 'tourguide/bookings') === 0 ? 'active bg-white text-primary' : '' ?>" href="<?= View::url('tourguide/bookings') ?>">
                            <i class="fas fa-calendar-check me-2"></i>Booking
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link text-white <?= strpos($_GET['url'] ?? '', 'tourguide/earnings') === 0 ? 'active bg-white text-primary' : '' ?>" href="<?= View::url('tourguide/earnings') ?>">
                            <i class="fas fa-wallet me-2"></i>Pendapatan
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link text-white" href="<?= View::url('auth/logout') ?>">
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
                <span class="navbar-brand mb-0 h1"><?= isset($title) ? View::e($title) : 'Tour Guide Panel' ?></span>
                <div class="d-flex align-items-center">
                    <span class="me-3">
                        <i class="fas fa-user-circle me-1"></i>
                        <?= View::e(Session::get('user_name')) ?>
                    </span>
                    <span class="badge bg-primary">Tour Guide</span>
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
