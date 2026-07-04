<?php
/**
 * Standard page header.
 * Outputs <!DOCTYPE>, <head> with shared CSS/JS, opens <body> and main-container.
 *
 * Usage (in pages):
 *   $page_title = 'My Page';
 *   require_once BASE_PATH . '/includes/header.php';
 *   require_once BASE_PATH . '/includes/sidebar.php';
 *   // ...your content (typically wrapped in <div class="content-area">...)...
 *   require_once BASE_PATH . '/includes/footer.php';
 */

if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../config/path.php';
}
if (!function_exists('getCurrentUser')) {
    require_once __DIR__ . '/functions.php';
}

$__page_title = isset($page_title) ? $page_title : (defined('APP_NAME') ? APP_NAME : 'Kewer');
$__app_name   = defined('APP_NAME') ? APP_NAME : 'Kewer';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($__page_title) ?> - <?= htmlspecialchars($__app_name) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/themes/light.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if (isset($extra_head)) echo $extra_head; ?>
</head>
<body>
    <div class="main-container">
