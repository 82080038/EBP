<?php
require_once 'config/path.php';
require_once BASE_PATH . '/includes/functions.php';

// Check if user is logged in
if (isLoggedIn()) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
} else {
    header('Location: ' . baseUrl('login.php'));
    exit();
}
