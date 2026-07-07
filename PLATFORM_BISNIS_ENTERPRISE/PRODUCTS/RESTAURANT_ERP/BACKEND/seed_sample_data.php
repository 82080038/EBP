<?php

/**
 * Seed Sample Data for RESTAURANT_ERP Testing
 * Creates sample products, tables, and inventory items
 */

require_once 'bootstrap.php';

echo "========================================\n";
echo "Seeding Sample Data for Testing\n";
echo "========================================\n\n";

try {
    $db = new Database();
    $pdo = $db->connect();
    
    // Get tenant and branch info
    $stmt = $pdo->query("SELECT tenant_id FROM tenants LIMIT 1");
    $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
    $tenantId = $tenant['tenant_id'] ?? 1;
    
    $stmt = $pdo->query("SELECT branch_id FROM branches LIMIT 1");
    $branch = $stmt->fetch(PDO::FETCH_ASSOC);
    $branchId = $branch['branch_id'] ?? 2;
    
    echo "Using Tenant ID: $tenantId\n";
    echo "Using Branch ID: $branchId\n\n";
    
    // Insert Categories
    echo "Inserting Categories...\n";
    $categories = [
        ['MAIN', 'Main Course', 'Main dishes'],
        ['APP', 'Appetizers', 'Starters'],
        ['BEV', 'Beverages', 'Drinks'],
        ['DES', 'Desserts', 'Sweet treats']
    ];
    
    foreach ($categories as $cat) {
        // Check if category already exists
        $stmt = $pdo->prepare("SELECT category_id FROM categories WHERE tenant_id = ? AND category_code = ?");
        $stmt->execute([$tenantId, $cat[0]]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO categories (tenant_id, category_code, category_name, description, status) VALUES (?, ?, ?, ?, 'ACTIVE')");
            $stmt->execute([$tenantId, $cat[0], $cat[1], $cat[2]]);
            echo "  ✓ Created category: {$cat[1]}\n";
        } else {
            echo "  ⊘ Category already exists: {$cat[1]}\n";
        }
    }
    
    // Get category IDs
    $stmt = $pdo->query("SELECT category_id, category_name FROM categories WHERE tenant_id = $tenantId ORDER BY category_id");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $categoryMap = [];
    foreach ($categories as $cat) {
        $categoryMap[$cat['category_name']] = $cat['category_id'];
    }
    
    // Insert Products
    echo "\nInserting Products...\n";
    $products = [
        ['Nasi Goreng', 25000, 'Fried rice with egg and vegetables', $categoryMap['Main Course']],
        ['Mie Goreng', 22000, 'Fried noodles with chicken', $categoryMap['Main Course']],
        ['Ayam Bakar', 35000, 'Grilled chicken with sambal', $categoryMap['Main Course']],
        ['Gado-Gado', 20000, 'Indonesian vegetable salad', $categoryMap['Main Course']],
        ['Sate Ayam', 30000, 'Chicken satay with peanut sauce', $categoryMap['Main Course']],
        ['Es Teh Manis', 5000, 'Sweet iced tea', $categoryMap['Beverages']],
        ['Es Jeruk', 6000, 'Fresh orange juice', $categoryMap['Beverages']],
        ['Kopi Susu', 12000, 'Coffee with milk', $categoryMap['Beverages']],
        ['Jus Alpukat', 15000, 'Avocado juice', $categoryMap['Beverages']],
        ['Pisang Goreng', 10000, 'Fried banana', $categoryMap['Desserts']],
        ['Es Krim', 15000, 'Vanilla ice cream', $categoryMap['Desserts']],
        ['Puding', 12000, 'Chocolate pudding', $categoryMap['Desserts']]
    ];
    
    foreach ($products as $prod) {
        $productCode = strtoupper(str_replace(' ', '_', $prod[0]));
        // Check if product already exists
        $stmt = $pdo->prepare("SELECT product_id FROM products WHERE tenant_id = ? AND product_code = ?");
        $stmt->execute([$tenantId, $productCode]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO products (tenant_id, category_id, product_code, product_name, price, description, is_available, status) VALUES (?, ?, ?, ?, ?, ?, 1, 'ACTIVE')");
            $stmt->execute([$tenantId, $prod[3], $productCode, $prod[0], $prod[1], $prod[2]]);
            echo "  ✓ Created product: {$prod[0]} (Rp {$prod[1]})\n";
        } else {
            echo "  ⊘ Product already exists: {$prod[0]}\n";
        }
    }
    
    // Insert Tables
    echo "\nInserting Tables...\n";
    $tables = [
        ['T-01', 'Table 1', 4],
        ['T-02', 'Table 2', 4],
        ['T-03', 'Table 3', 6],
        ['T-04', 'Table 4', 6],
        ['T-05', 'Table 5', 2],
        ['T-06', 'Table 6', 8],
        ['VIP-1', 'VIP Room 1', 10],
        ['VIP-2', 'VIP Room 2', 12]
    ];
    
    foreach ($tables as $table) {
        // Check if table already exists
        $stmt = $pdo->prepare("SELECT table_id FROM tables WHERE tenant_id = ? AND branch_id = ? AND table_number = ?");
        $stmt->execute([$tenantId, $branchId, $table[0]]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO tables (tenant_id, branch_id, table_number, table_name, capacity, status) VALUES (?, ?, ?, ?, ?, 'AVAILABLE')");
            $stmt->execute([$tenantId, $branchId, $table[0], $table[1], $table[2]]);
            echo "  ✓ Created table: {$table[0]} (Capacity: {$table[2]})\n";
        } else {
            echo "  ⊘ Table already exists: {$table[0]}\n";
        }
    }
    
    // Insert Inventory Items
    echo "\nInserting Inventory Items...\n";
    // First, get product IDs to link inventory to products
    $stmt = $pdo->query("SELECT product_id, product_name FROM products WHERE tenant_id = $tenantId LIMIT 10");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $inventory = [
        ['Rice', 50, 'kg', 10],
        ['Chicken', 30, 'kg', 5],
        ['Noodles', 100, 'pack', 20],
        ['Eggs', 200, 'piece', 50],
        ['Vegetables', 20, 'kg', 5],
        ['Sugar', 25, 'kg', 5],
        ['Tea', 10, 'kg', 2],
        ['Coffee', 5, 'kg', 1],
        ['Milk', 20, 'liter', 5],
        ['Banana', 15, 'kg', 3]
    ];
    
    foreach ($inventory as $index => $item) {
        if (isset($products[$index])) {
            $productId = $products[$index]['product_id'];
            // Check if inventory already exists
            $stmt = $pdo->prepare("SELECT inventory_id FROM inventory WHERE tenant_id = ? AND branch_id = ? AND product_id = ?");
            $stmt->execute([$tenantId, $branchId, $productId]);
            if (!$stmt->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO inventory (tenant_id, branch_id, product_id, quantity, unit, minimum_stock, status) VALUES (?, ?, ?, ?, ?, ?, 'ACTIVE')");
                $stmt->execute([$tenantId, $branchId, $productId, $item[1], $item[2], $item[3]]);
                echo "  ✓ Created inventory item: {$item[0]} ({$item[1]} {$item[2]}) linked to product ID {$productId}\n";
            } else {
                echo "  ⊘ Inventory already exists: {$item[0]}\n";
            }
        }
    }
    
    echo "\n========================================\n";
    echo "Sample Data Seeded Successfully!\n";
    echo "========================================\n";
    echo "Categories: " . count($categories) . "\n";
    echo "Products: " . count($products) . "\n";
    echo "Tables: " . count($tables) . "\n";
    echo "Inventory Items: " . count($inventory) . "\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
