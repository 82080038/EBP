<?php
/**
 * Fix remaining Database class usage in Repositories
 */

$files = [
    '/opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND/modules/Accounting/Repositories/AccountingRepository.php',
    '/opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND/modules/Accounting/Repositories/AccountsPayableRepository.php',
    '/opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND/modules/Accounting/Repositories/AccountingPeriodRepository.php',
    '/opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND/modules/Accounting/Repositories/AccountsReceivableRepository.php',
];

$filesFixed = 0;

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "Skipped (not found): $file\n";
        continue;
    }

    $content = file_get_contents($file);
    $originalContent = $content;

    // Replace $database = new Database(); $this->db = $database->connect();
    $content = preg_replace(
        '/\$database\s*=\s*new\s+Database\(\);\s*\$this->db\s*=\s*\$database->connect\(\);/',
        '$host = \'localhost\';
        $dbname = \'ebp_restaurant_db\';
        $username = \'ebp_app\';
        $password = \'ebp_secure_password_2026\';
        $socket = \'/opt/lampp/var/mysql/mysql.sock\';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);',
        $content
    );

    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $filesFixed++;
        echo "Fixed: $file\n";
    } else {
        echo "No changes needed: $file\n";
    }
}

echo "\n=== Summary ===\n";
echo "Files fixed: $filesFixed\n";
