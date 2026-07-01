<?php

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$pdo = $db->connect();

echo "Running comprehensive simulation for all restaurant types...\n\n";

// Restaurant type configurations
$restaurantTypes = [
    'RESTAURANT' => 'Restoran Makanan',
    'CAFE' => 'Kafe',
    'BAR_PUB' => 'Bar/Pub',
    'FOOD_COURT' => 'Food Court',
    'CATERING' => 'Catering Service',
    'FAST_FOOD' => 'Fast Food Restaurant',
    'FINE_DINING' => 'Fine Dining',
    'COFFEE_SHOP' => 'Coffee Shop'
];

try {
    foreach ($restaurantTypes as $typeCode => $typeName) {
        echo "=== $typeName ===\n";
        
        // Get tenant info
        $stmt = $pdo->prepare("SELECT t.tenant_id, c.company_id, b.branch_id FROM tenants t JOIN companies c ON t.tenant_id = c.tenant_id JOIN branches b ON c.company_id = b.company_id WHERE t.tenant_code = ?");
        $stmt->execute([$typeCode]);
        $tenantInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tenantInfo) {
            echo "Tenant not found\n\n";
            continue;
        }
        
        $tenantId = $tenantInfo['tenant_id'];
        $branchId = $tenantInfo['branch_id'];
        
        // Count categories
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM categories WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        $categories = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Count products
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        $products = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Count tables
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tables WHERE tenant_id = ? AND branch_id = ?");
        $stmt->execute([$tenantId, $branchId]);
        $tables = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Count orders
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE tenant_id = ? AND branch_id = ?");
        $stmt->execute([$tenantId, $branchId]);
        $orders = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Calculate total revenue
        $stmt = $pdo->prepare("SELECT SUM(total_amount) as total FROM orders WHERE tenant_id = ? AND branch_id = ? AND status = 'COMPLETED'");
        $stmt->execute([$tenantId, $branchId]);
        $revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Count reservations
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reservations WHERE tenant_id = ? AND branch_id = ?");
        $stmt->execute([$tenantId, $branchId]);
        $reservations = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Count inventory items
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM inventory i JOIN products p ON i.product_id = p.product_id WHERE p.tenant_id = ? AND i.branch_id = ?");
        $stmt->execute([$tenantId, $branchId]);
        $inventory = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Count roles
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM roles WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        $roles = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Count users
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        $users = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        echo "Categories: $categories\n";
        echo "Products: $products\n";
        echo "Tables: $tables\n";
        echo "Orders (2 months): $orders\n";
        echo "Total Revenue: Rp " . number_format($revenue, 0, ',', '.') . "\n";
        echo "Reservations: $reservations\n";
        echo "Inventory Items: $inventory\n";
        echo "Roles: $roles\n";
        echo "Users: $users\n";
        
        // Show recent orders
        echo "\nRecent Orders:\n";
        $stmt = $pdo->prepare("SELECT order_number, total_amount, created_at FROM orders WHERE tenant_id = ? AND branch_id = ? ORDER BY created_at DESC LIMIT 5");
        $stmt->execute([$tenantId, $branchId]);
        $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($recentOrders as $order) {
            echo "  {$order['order_number']} - Rp " . number_format($order['total_amount'], 0, ',', '.') . " - {$order['created_at']}\n";
        }
        
        echo "\n";
    }
    
    echo "Simulation complete!\n";
    echo "Total restaurant types: " . count($restaurantTypes) . "\n";
    echo "Simulation period: January - February 2024 (2 months)\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
