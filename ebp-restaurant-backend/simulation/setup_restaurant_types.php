<?php

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$pdo = $db->connect();

echo "Setting up 7 restaurant types with 2 months of simulation data...\n\n";

// Restaurant type configurations
$restaurantTypes = [
    'RESTAURANT' => [
        'name' => 'Restoran Makanan',
        'roles' => ['MANAGER', 'CHEF', 'WAITER', 'CASHIER', 'INVENTORY_MANAGER'],
        'menu_categories' => ['Appetizer', 'Main Course', 'Dessert', 'Beverage'],
        'avg_order_value' => 150000,
        'daily_orders' => 80,
        'has_reservations' => true,
        'has_kitchen' => true
    ],
    'CAFE' => [
        'name' => 'Kafe',
        'roles' => ['MANAGER', 'BARISTA', 'SERVER', 'CASHIER'],
        'menu_categories' => ['Coffee', 'Tea', 'Pastries', 'Light Meals'],
        'avg_order_value' => 45000,
        'daily_orders' => 150,
        'has_reservations' => false,
        'has_kitchen' => false
    ],
    'BAR_PUB' => [
        'name' => 'Bar/Pub',
        'roles' => ['MANAGER', 'BARTENDER', 'SERVER', 'SECURITY', 'CASHIER'],
        'menu_categories' => ['Cocktails', 'Beer', 'Wine', 'Snacks'],
        'avg_order_value' => 120000,
        'daily_orders' => 100,
        'has_reservations' => false,
        'has_kitchen' => false
    ],
    'FOOD_COURT' => [
        'name' => 'Food Court',
        'roles' => ['MANAGER', 'STALL_OPERATOR', 'CASHIER', 'CLEANER'],
        'menu_categories' => ['Asian', 'Western', 'Local', 'Beverages'],
        'avg_order_value' => 35000,
        'daily_orders' => 300,
        'has_reservations' => false,
        'has_kitchen' => true
    ],
    'CATERING' => [
        'name' => 'Catering Service',
        'roles' => ['MANAGER', 'CHEF', 'COORDINATOR', 'DELIVERY', 'SALES'],
        'menu_categories' => ['Buffet', 'Package A', 'Package B', 'Beverages'],
        'avg_order_value' => 5000000,
        'daily_orders' => 5,
        'has_reservations' => true,
        'has_kitchen' => true
    ],
    'FAST_FOOD' => [
        'name' => 'Fast Food Restaurant',
        'roles' => ['MANAGER', 'CREW', 'DRIVE_THRU', 'CASHIER'],
        'menu_categories' => ['Burgers', 'Fries', 'Drinks', 'Desserts'],
        'avg_order_value' => 55000,
        'daily_orders' => 400,
        'has_reservations' => false,
        'has_kitchen' => true
    ],
    'FINE_DINING' => [
        'name' => 'Fine Dining',
        'roles' => ['MANAGER', 'HEAD_CHEF', 'SOMMELIER', 'MAITRE_D', 'WAITER'],
        'menu_categories' => ['Appetizer', 'Soup', 'Main Course', 'Dessert', 'Wine'],
        'avg_order_value' => 800000,
        'daily_orders' => 30,
        'has_reservations' => true,
        'has_kitchen' => true
    ],
    'COFFEE_SHOP' => [
        'name' => 'Coffee Shop',
        'roles' => ['MANAGER', 'BARISTA', 'ROASTER', 'SERVER'],
        'menu_categories' => ['Espresso', 'Pour Over', 'Pastries', 'Light Food'],
        'avg_order_value' => 40000,
        'daily_orders' => 120,
        'has_reservations' => false,
        'has_kitchen' => false
    ]
];

// Permission mappings for roles
$rolePermissions = [
    'MANAGER' => ['MENU_MANAGE', 'TABLE_MANAGE', 'RESERVATION_MANAGE', 'INVENTORY_MANAGE', 'KITCHEN_VIEW', 'USER_MANAGE', 'SETTINGS_MANAGE', 'REPORT_VIEW', 'SALES_MANAGE'],
    'CHEF' => ['MENU_MANAGE', 'INVENTORY_MANAGE', 'KITCHEN_VIEW'],
    'HEAD_CHEF' => ['MENU_MANAGE', 'INVENTORY_MANAGE', 'KITCHEN_VIEW', 'USER_MANAGE'],
    'WAITER' => ['TABLE_MANAGE', 'RESERVATION_MANAGE', 'SALES_MANAGE'],
    'MAITRE_D' => ['TABLE_MANAGE', 'RESERVATION_MANAGE', 'SALES_MANAGE', 'SETTINGS_MANAGE'],
    'CASHIER' => ['SALES_MANAGE', 'REPORT_VIEW'],
    'INVENTORY_MANAGER' => ['INVENTORY_MANAGE', 'REPORT_VIEW'],
    'BARISTA' => ['MENU_MANAGE', 'SALES_MANAGE'],
    'SERVER' => ['TABLE_MANAGE', 'SALES_MANAGE'],
    'BARTENDER' => ['MENU_MANAGE', 'INVENTORY_MANAGE', 'SALES_MANAGE'],
    'SECURITY' => ['TABLE_MANAGE'],
    'STALL_OPERATOR' => ['MENU_MANAGE', 'INVENTORY_MANAGE', 'SALES_MANAGE'],
    'CLEANER' => ['TABLE_MANAGE'],
    'COORDINATOR' => ['RESERVATION_MANAGE', 'SALES_MANAGE'],
    'DELIVERY' => ['SALES_MANAGE'],
    'SALES' => ['RESERVATION_MANAGE', 'SALES_MANAGE', 'REPORT_VIEW'],
    'CREW' => ['MENU_MANAGE', 'SALES_MANAGE'],
    'DRIVE_THRU' => ['SALES_MANAGE'],
    'SOMMELIER' => ['MENU_MANAGE', 'SALES_MANAGE'],
    'ROASTER' => ['INVENTORY_MANAGE']
];

try {
    foreach ($restaurantTypes as $typeCode => $config) {
        echo "Setting up {$config['name']}...\n";
        
        // Create tenant
        $stmt = $pdo->prepare("SELECT tenant_id FROM tenants WHERE tenant_code = ?");
        $stmt->execute([$typeCode]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            $tenantId = $existing['tenant_id'];
            echo "  Tenant already exists (ID: $tenantId)\n";
        } else {
            $stmt = $pdo->prepare("INSERT INTO tenants (tenant_code, tenant_name, status) VALUES (?, ?, ?)");
            $stmt->execute([$typeCode, $config['name'], 'ACTIVE']);
            $tenantId = $pdo->lastInsertId();
            echo "  Created tenant (ID: $tenantId)\n";
        }
        
        // Create company
        $stmt = $pdo->prepare("SELECT company_id FROM companies WHERE tenant_id = ? AND company_code = ?");
        $stmt->execute([$tenantId, $typeCode]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            $companyId = $existing['company_id'];
            echo "  Company already exists (ID: $companyId)\n";
        } else {
            $stmt = $pdo->prepare("INSERT INTO companies (tenant_id, company_code, company_name, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$tenantId, $typeCode, $config['name'], 'ACTIVE']);
            $companyId = $pdo->lastInsertId();
            echo "  Created company (ID: $companyId)\n";
        }
        
        // Create main branch
        $stmt = $pdo->prepare("SELECT branch_id FROM branches WHERE tenant_id = ? AND branch_code = ?");
        $stmt->execute([$tenantId, 'MAIN']);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            $branchId = $existing['branch_id'];
            echo "  Branch already exists (ID: $branchId)\n";
        } else {
            $stmt = $pdo->prepare("INSERT INTO branches (tenant_id, company_id, branch_code, branch_name, address, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$tenantId, $companyId, 'MAIN', 'Main Branch', 'Main Street', 'ACTIVE']);
            $branchId = $pdo->lastInsertId();
            echo "  Created branch (ID: $branchId)\n";
        }
        
        // Create roles
        foreach ($config['roles'] as $roleCode) {
            $stmt = $pdo->prepare("SELECT role_id FROM roles WHERE tenant_id = ? AND role_code = ?");
            $stmt->execute([$tenantId, $roleCode]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existing) {
                $stmt = $pdo->prepare("INSERT INTO roles (tenant_id, role_code, role_name, description, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$tenantId, $roleCode, ucfirst(str_replace('_', ' ', strtolower($roleCode))), "Role for {$config['name']}", 'ACTIVE']);
                $roleId = $pdo->lastInsertId();
                echo "  Created role: $roleCode (ID: $roleId)\n";
                
                // Assign permissions to role
                if (isset($rolePermissions[$roleCode])) {
                    foreach ($rolePermissions[$roleCode] as $permCode) {
                        $stmt = $pdo->prepare("SELECT permission_id FROM permissions WHERE permission_code = ?");
                        $stmt->execute([$permCode]);
                        $perm = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($perm) {
                            $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id, granted_at) VALUES (?, ?, NOW())");
                            $stmt->execute([$roleId, $perm['permission_id']]);
                        }
                    }
                }
            }
        }
        
        // Create manager user
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE tenant_id = ? AND username = ?");
        $stmt->execute([$tenantId, strtolower($typeCode) . '_manager']);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existing) {
            $hashedPassword = password_hash('manager123', PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (tenant_id, branch_id, username, email, password, full_name, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$tenantId, $branchId, strtolower($typeCode) . '_manager', strtolower($typeCode) . '@restaurant.com', $hashedPassword, 'Manager', 'ACTIVE']);
            $userId = $pdo->lastInsertId();
            echo "  Created manager user: " . strtolower($typeCode) . "_manager\n";
            
            // Assign manager role
            $stmt = $pdo->prepare("SELECT role_id FROM roles WHERE tenant_id = ? AND role_code = ?");
            $stmt->execute([$tenantId, 'MANAGER']);
            $roleId = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($roleId) {
                $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id, assigned_at) VALUES (?, ?, NOW())");
                $stmt->execute([$userId, $roleId['role_id']]);
            }
        }
        
        echo "  {$config['name']} setup complete\n\n";
    }
    
    echo "All restaurant types setup complete!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
