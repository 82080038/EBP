<?php

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$pdo = $db->connect();

echo "Generating 2 months of simulation data for all restaurant types...\n\n";

// Restaurant type configurations
$restaurantTypes = [
    'RESTAURANT' => [
        'name' => 'Restoran Makanan',
        'menu_categories' => ['Appetizer', 'Main Course', 'Dessert', 'Beverage'],
        'menu_items' => [
            'Appetizer' => ['Spring Rolls', 'Soup of the Day', 'Caesar Salad', 'Garlic Bread'],
            'Main Course' => ['Nasi Goreng Spesial', 'Ayam Bakar', 'Sate Ayam', 'Ikan Bakar', 'Rendang'],
            'Dessert' => ['Es Teler', 'Pisang Goreng', 'Puding Coklat', 'Ice Cream'],
            'Beverage' => ['Es Teh Manis', 'Jus Jeruk', 'Kopi', 'Teh Tarik']
        ],
        'avg_order_value' => 150000,
        'daily_orders' => 80,
        'tables' => 20,
        'has_reservations' => true,
        'has_kitchen' => true
    ],
    'CAFE' => [
        'name' => 'Kafe',
        'menu_categories' => ['Coffee', 'Tea', 'Pastries', 'Light Meals'],
        'menu_items' => [
            'Coffee' => ['Espresso', 'Cappuccino', 'Latte', 'Americano', 'Mocha'],
            'Tea' => ['Green Tea', 'Chai Latte', 'Ice Tea', 'Matcha'],
            'Pastries' => ['Croissant', 'Muffin', 'Danish', 'Cake Slice'],
            'Light Meals' => ['Sandwich', 'Salad', 'Pasta', 'Panini']
        ],
        'avg_order_value' => 45000,
        'daily_orders' => 150,
        'tables' => 30,
        'has_reservations' => false,
        'has_kitchen' => false
    ],
    'BAR_PUB' => [
        'name' => 'Bar/Pub',
        'menu_categories' => ['Cocktails', 'Beer', 'Wine', 'Snacks'],
        'menu_items' => [
            'Cocktails' => ['Mojito', 'Margarita', 'Old Fashioned', 'Cosmopolitan'],
            'Beer' => ['Beer Draft', 'Bottled Beer', 'Craft Beer'],
            'Wine' => ['Red Wine', 'White Wine', 'Rose'],
            'Snacks' => ['Nachos', 'Wings', 'Fries', 'Popcorn']
        ],
        'avg_order_value' => 120000,
        'daily_orders' => 100,
        'tables' => 25,
        'has_reservations' => false,
        'has_kitchen' => false
    ],
    'FOOD_COURT' => [
        'name' => 'Food Court',
        'menu_categories' => ['Asian', 'Western', 'Local', 'Beverages'],
        'menu_items' => [
            'Asian' => ['Fried Rice', 'Noodle Soup', 'Dim Sum', 'Sushi'],
            'Western' => ['Burger', 'Pizza', 'Pasta', 'Steak'],
            'Local' => ['Nasi Lemak', 'Mee Goreng', 'Satay', 'Rendang'],
            'Beverages' => ['Soft Drink', 'Ice Tea', 'Coffee', 'Juice']
        ],
        'avg_order_value' => 35000,
        'daily_orders' => 300,
        'tables' => 50,
        'has_reservations' => false,
        'has_kitchen' => true
    ],
    'CATERING' => [
        'name' => 'Catering Service',
        'menu_categories' => ['Buffet', 'Package A', 'Package B', 'Beverages'],
        'menu_items' => [
            'Buffet' => ['Full Buffet Set', 'Mini Buffet', 'Premium Buffet'],
            'Package A' => ['Wedding Package A', 'Corporate Package A'],
            'Package B' => ['Wedding Package B', 'Corporate Package B'],
            'Beverages' => ['Drink Package', 'Coffee Service']
        ],
        'avg_order_value' => 5000000,
        'daily_orders' => 5,
        'tables' => 10,
        'has_reservations' => true,
        'has_kitchen' => true
    ],
    'FAST_FOOD' => [
        'name' => 'Fast Food Restaurant',
        'menu_categories' => ['Burgers', 'Fries', 'Drinks', 'Desserts'],
        'menu_items' => [
            'Burgers' => ['Cheeseburger', 'Double Burger', 'Chicken Burger', 'Veggie Burger'],
            'Fries' => ['Regular Fries', 'Large Fries', 'Curly Fries'],
            'Drinks' => ['Cola', 'Sprite', 'Orange Juice', 'Milkshake'],
            'Desserts' => ['Ice Cream', 'Apple Pie', 'Brownie']
        ],
        'avg_order_value' => 55000,
        'daily_orders' => 400,
        'tables' => 40,
        'has_reservations' => false,
        'has_kitchen' => true
    ],
    'FINE_DINING' => [
        'name' => 'Fine Dining',
        'menu_categories' => ['Appetizer', 'Soup', 'Main Course', 'Dessert', 'Wine'],
        'menu_items' => [
            'Appetizer' => ['Foie Gras', 'Oysters', 'Caviar', 'Truffle Soup'],
            'Soup' => ['French Onion', 'Lobster Bisque', 'Mushroom Soup'],
            'Main Course' => ['Wagyu Steak', 'Lobster', 'Duck Confit', 'Salmon'],
            'Dessert' => ['Chocolate Souffle', 'Creme Brulee', 'Tiramisu'],
            'Wine' => ['Champagne', 'Red Wine Selection', 'White Wine Selection']
        ],
        'avg_order_value' => 800000,
        'daily_orders' => 30,
        'tables' => 15,
        'has_reservations' => true,
        'has_kitchen' => true
    ],
    'COFFEE_SHOP' => [
        'name' => 'Coffee Shop',
        'menu_categories' => ['Espresso', 'Pour Over', 'Pastries', 'Light Food'],
        'menu_items' => [
            'Espresso' => ['Single Shot', 'Double Shot', 'Americano', 'Flat White'],
            'Pour Over' => ['V60', 'Chemex', 'Cold Brew'],
            'Pastries' => ['Croissant', 'Danish', 'Muffin', 'Cookie'],
            'Light Food' => ['Avocado Toast', 'Bagel', 'Sandwich']
        ],
        'avg_order_value' => 40000,
        'daily_orders' => 120,
        'tables' => 25,
        'has_reservations' => false,
        'has_kitchen' => false
    ]
];

try {
    $startDate = new DateTime('2024-01-01');
    $endDate = new DateTime('2024-02-29'); // 2 months (Jan + Feb 2024)
    
    foreach ($restaurantTypes as $typeCode => $config) {
        echo "Generating data for {$config['name']}...\n";
        
        // Get tenant info
        $stmt = $pdo->prepare("SELECT t.tenant_id, c.company_id, b.branch_id FROM tenants t JOIN companies c ON t.tenant_id = c.tenant_id JOIN branches b ON c.company_id = b.company_id WHERE t.tenant_code = ?");
        $stmt->execute([$typeCode]);
        $tenantInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tenantInfo) {
            echo "  Tenant not found, skipping...\n\n";
            continue;
        }
        
        $tenantId = $tenantInfo['tenant_id'];
        $companyId = $tenantInfo['company_id'];
        $branchId = $tenantInfo['branch_id'];
        
        // Create menu categories
        $categoryMap = [];
        foreach ($config['menu_categories'] as $index => $categoryName) {
            $categoryCode = strtoupper(str_replace(' ', '_', $categoryName));
            $stmt = $pdo->prepare("SELECT category_id FROM categories WHERE tenant_id = ? AND category_code = ?");
            $stmt->execute([$tenantId, $categoryCode]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existing) {
                $stmt = $pdo->prepare("INSERT INTO categories (tenant_id, category_code, category_name, description, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$tenantId, $categoryCode, $categoryName, "Category for {$config['name']}", 'ACTIVE']);
                $categoryMap[$categoryName] = $pdo->lastInsertId();
            } else {
                $categoryMap[$categoryName] = $existing['category_id'];
            }
        }
        echo "  Created " . count($categoryMap) . " menu categories\n";
        
        // Create menu products
        $productMap = [];
        foreach ($config['menu_items'] as $category => $items) {
            foreach ($items as $itemName) {
                $price = rand($config['avg_order_value'] * 0.3, $config['avg_order_value'] * 0.8);
                $productCode = strtoupper(str_replace(' ', '_', $itemName));
                
                $stmt = $pdo->prepare("SELECT product_id FROM products WHERE tenant_id = ? AND product_code = ?");
                $stmt->execute([$tenantId, $productCode]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$existing) {
                    $stmt = $pdo->prepare("INSERT INTO products (tenant_id, category_id, product_code, product_name, description, price, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$tenantId, $categoryMap[$category], $productCode, $itemName, "Menu item for {$config['name']}", $price, 'ACTIVE']);
                    $productMap[$itemName] = $pdo->lastInsertId();
                } else {
                    $productMap[$itemName] = $existing['product_id'];
                }
            }
        }
        echo "  Created " . count($productMap) . " menu products\n";
        
        // Create tables
        for ($i = 1; $i <= $config['tables']; $i++) {
            $capacity = rand(2, 8);
            $tableNumber = strval($i);
            $stmt = $pdo->prepare("SELECT table_id FROM tables WHERE tenant_id = ? AND branch_id = ? AND table_number = ?");
            $stmt->execute([$tenantId, $branchId, $tableNumber]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existing) {
                $stmt = $pdo->prepare("INSERT INTO tables (tenant_id, branch_id, table_number, capacity, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$tenantId, $branchId, $tableNumber, $capacity, 'AVAILABLE']);
            }
        }
        echo "  Created {$config['tables']} tables\n";
        
        // Generate orders for 2 months
        $currentDate = clone $startDate;
        $orderCount = 0;
        
        while ($currentDate <= $endDate) {
            // Skip some days for weekend patterns
            if ($currentDate->format('N') >= 6) {
                $dailyOrders = floor($config['daily_orders'] * 0.7);
            } else {
                $dailyOrders = $config['daily_orders'];
            }
            
            for ($i = 0; $i < $dailyOrders; $i++) {
                // Random order time
                $hour = rand(10, 22);
                $minute = rand(0, 59);
                $orderDateTime = clone $currentDate;
                $orderDateTime->setTime($hour, $minute);
                
                // Random table
                $stmt = $pdo->prepare("SELECT table_id FROM tables WHERE tenant_id = ? AND branch_id = ? AND table_number = ?");
                $tableNumber = strval(rand(1, $config['tables']));
                $stmt->execute([$tenantId, $branchId, $tableNumber]);
                $table = $stmt->fetch(PDO::FETCH_ASSOC);
                $tableId = $table ? $table['table_id'] : null;
                
                // Random products (2-5 items per order)
                $numItems = rand(2, 5);
                $productKeys = array_keys($productMap);
                $totalAmount = 0;
                
                $stmt = $pdo->prepare("INSERT INTO orders (tenant_id, branch_id, table_id, order_number, subtotal, total_amount, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $orderNumber = "ORD-" . $currentDate->format('Ymd') . "-" . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
                $stmt->execute([$tenantId, $branchId, $tableId, $orderNumber, 0, 0, 'COMPLETED', $orderDateTime->format('Y-m-d H:i:s')]);
                $orderId = $pdo->lastInsertId();
                
                // Add order items
                for ($j = 0; $j < $numItems; $j++) {
                    $randomProduct = $productKeys[array_rand($productKeys)];
                    $productId = $productMap[$randomProduct];
                    $quantity = rand(1, 3);
                    
                    // Get product price
                    $stmt = $pdo->prepare("SELECT price FROM products WHERE product_id = ?");
                    $stmt->execute([$productId]);
                    $product = $stmt->fetch(PDO::FETCH_ASSOC);
                    $itemPrice = $product['price'] * $quantity;
                    $totalAmount += $itemPrice;
                    
                    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$orderId, $productId, $quantity, $product['price'], $itemPrice]);
                }
                
                // Update order total
                $stmt = $pdo->prepare("UPDATE orders SET subtotal = ?, total_amount = ? WHERE order_id = ?");
                $stmt->execute([$totalAmount, $totalAmount, $orderId]);
                
                $orderCount++;
            }
            
            $currentDate->modify('+1 day');
        }
        
        echo "  Generated $orderCount orders over 2 months\n";
        
        // Generate reservations if applicable
        if ($config['has_reservations']) {
            $reservationCount = 0;
            $currentDate = clone $startDate;
            
            while ($currentDate <= $endDate) {
                // 5-10 reservations per day
                $dailyReservations = rand(5, 10);
                
                for ($i = 0; $i < $dailyReservations; $i++) {
                    $hour = rand(12, 20);
                    $minute = rand(0, 59) < 30 ? 0 : 30;
                    $reservationDate = clone $currentDate;
                    $reservationTime = sprintf('%02d:%02d', $hour, $minute);
                    
                    // Get random table
                    $tableNumber = strval(rand(1, $config['tables']));
                    $stmt = $pdo->prepare("SELECT table_id FROM tables WHERE tenant_id = ? AND branch_id = ? AND table_number = ?");
                    $stmt->execute([$tenantId, $branchId, $tableNumber]);
                    $table = $stmt->fetch(PDO::FETCH_ASSOC);
                    $tableId = $table ? $table['table_id'] : null;
                    
                    $guests = rand(2, 8);
                    $name = "Guest " . rand(1000, 9999);
                    $phone = "08" . rand(100000000, 999999999);
                    $reservationNumber = "RES-" . $currentDate->format('Ymd') . "-" . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
                    
                    $stmt = $pdo->prepare("INSERT INTO reservations (tenant_id, branch_id, table_id, reservation_number, customer_name, customer_phone, reservation_date, reservation_time, party_size, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$tenantId, $branchId, $tableId, $reservationNumber, $name, $phone, $currentDate->format('Y-m-d'), $reservationTime, $guests, 'CONFIRMED']);
                    $reservationCount++;
                }
                
                $currentDate->modify('+1 day');
            }
            
            echo "  Generated $reservationCount reservations\n";
        }
        
        // Generate inventory items (link to products)
        $inventoryCount = 0;
        foreach ($productMap as $productName => $productId) {
            $quantity = rand(50, 200);
            $unit = 'kg';
            $minStock = rand(10, 30);
            $maxStock = rand(200, 500);
            
            $stmt = $pdo->prepare("SELECT inventory_id FROM inventory WHERE tenant_id = ? AND branch_id = ? AND product_id = ?");
            $stmt->execute([$tenantId, $branchId, $productId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existing) {
                $stmt = $pdo->prepare("INSERT INTO inventory (tenant_id, branch_id, product_id, quantity, unit, minimum_stock, maximum_stock, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$tenantId, $branchId, $productId, $quantity, $unit, $minStock, $maxStock, 'ACTIVE']);
                $inventoryCount++;
            }
        }
        echo "  Created $inventoryCount inventory items\n";
        
        echo "  {$config['name']} data generation complete\n\n";
    }
    
    echo "All simulation data generated successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
