<?php
/**
 * Fix remaining Database class usage in SimpleControllers and other files
 */

$files = [
    '/opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND/modules/CRM/Controllers/SimpleCustomerController.php',
    '/opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND/modules/Kitchen/Controllers/SimpleKitchenController.php',
    '/opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND/modules/Delivery/Controllers/SimpleDeliveryController.php',
    '/opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND/modules/SupplyChain/Controllers/SimpleSupplierController.php',
    '/opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND/modules/Inventory/Controllers/SimpleInventoryController.php',
    '/opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND/modules/Reservation/Controllers/SimpleReservationController.php',
    '/opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND/modules/Table/Controllers/SimpleTableController.php',
    '/opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND/modules/Menu/Controllers/SimpleMenuController.php',
    '/opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND/modules/Consumer/Controllers/ConsumerController.php',
    '/opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND/modules/HR/Controllers/SimpleEmployeeController.php',
    '/opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND/modules/User/Controllers/SimpleUserController.php',
    '/opt/lampp/htdocs/EBP/PLATFORM_BISNIS_ENTERPRISE/PRODUCTS/RESTAURANT_ERP/BACKEND/modules/Auth/Controllers/AuthController.php',
];

$pdoTemplate = <<<'PDO'
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $db = new PDO($dsn, $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
PDO;

$filesFixed = 0;

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "Skipped (not found): $file\n";
        continue;
    }

    $content = file_get_contents($file);
    $originalContent = $content;

    // Replace $database = new Database(); $db = $database->connect();
    $content = preg_replace(
        '/\$database\s*=\s*new\s+Database\(\);\s*\$db\s*=\s*\$database->connect\(\);/',
        $pdoTemplate,
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
