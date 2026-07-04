<?php
// Shared header for appOwner pages — partial, must not be accessed directly
if (!defined('BASE_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}
if (!isset($user) || $user['role'] !== 'appOwner') {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$pending_count = query("SELECT COUNT(*) as c FROM bos_registrations WHERE status = 'pending'");
$pending_count = (is_array($pending_count) && isset($pending_count[0])) ? (int)$pending_count[0]['c'] : 0;

$current_ao_page = basename($_SERVER['PHP_SELF']);
$ao_nav = [
    'dashboard.php' => ['icon' => 'speedometer2', 'label' => 'Dashboard'],
    'approvals.php' => ['icon' => 'person-check', 'label' => 'Persetujuan Bos'],
    'koperasi.php' => ['icon' => 'building', 'label' => 'Koperasi'],
    'billing.php' => ['icon' => 'receipt', 'label' => 'Billing'],
    'usage.php' => ['icon' => 'bar-chart-line', 'label' => 'Usage'],
    'ai_advisor.php' => ['icon' => 'robot',       'label' => 'AI Advisor'],
    'features.php'  => ['icon' => 'toggles',      'label' => 'Feature Flags'],
    'settings.php'  => ['icon' => 'gear',          'label' => 'Settings'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'App Owner'; ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; }
        .app-header { background: linear-gradient(135deg, #1a1a2e, #16213e); color: white; padding: 0.75rem 0; }
        .ao-nav { background: white; border-bottom: 1px solid #dee2e6; padding: 0.5rem 0; position: sticky; top: 0; z-index: 100; }
        .ao-nav .nav-link { color: #495057; font-size: 0.85rem; padding: 0.4rem 0.8rem; border-radius: 6px; }
        .ao-nav .nav-link.active { background: #1a1a2e; color: white; }
        .ao-nav .nav-link:hover:not(.active) { background: #f0f2f5; }
        .stat-card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-2px); }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-approved { background: #d1e7dd; color: #0f5132; }
        .badge-rejected { background: #f8d7da; color: #842029; }
    </style>
    <?php if (isset($extra_css)) echo $extra_css; ?>
</head>
<body>
    <div class="app-header">
        <div class="container-fluid px-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><i class="bi bi-gear-wide-connected"></i> <?php echo APP_NAME; ?> <span class="opacity-50">Platform</span></h5>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <?php if ($pending_count > 0): ?>
                    <a href="<?php echo baseUrl('pages/app_owner/approvals.php'); ?>" class="btn btn-warning btn-sm">
                        <i class="bi bi-bell-fill"></i> <?php echo $pending_count; ?>
                    </a>
                    <?php endif; ?>
                    <span class="text-light small"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($user['nama']); ?> <span class="badge bg-secondary ms-1"><?php echo ucfirst($user['role']); ?></span></span>
                    <a href="<?php echo baseUrl('logout.php'); ?>" class="btn btn-outline-light btn-sm">Logout</a>
                </div>
            </div>
        </div>
    </div>
    <div class="ao-nav">
        <div class="container-fluid px-4">
            <div class="d-flex gap-1 flex-wrap">
                <?php foreach ($ao_nav as $file => $nav): ?>
                <a href="<?php echo baseUrl('pages/app_owner/' . $file); ?>" class="nav-link <?php echo $current_ao_page === $file ? 'active' : ''; ?>">
                    <i class="bi bi-<?php echo $nav['icon']; ?>"></i> <?php echo $nav['label']; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="container-fluid px-4 py-4">
