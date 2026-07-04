<?php
require_once 'config/path.php';
require_once BASE_PATH . '/includes/functions.php';

// Destroy session
session_destroy();

// Redirect to login
header('Location: ' . baseUrl('login.php'));
exit();
?>
