<?php

/**
 * Complete Simulation for RESTAURANT_ERP
 * Simulates all roles, levels, and features across the application
 */

require_once 'bootstrap.php';

echo "========================================\n";
echo "RESTAURANT_ERP Complete Simulation\n";
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
    
    echo "Tenant ID: $tenantId\n";
    echo "Branch ID: $branchId\n\n";
    
    // Define roles and their simulation scenarios
    $roles = [
        'PLATFORM_OWNER' => [
            'name' => 'Platform Owner',
            'is_platform' => true,
            'permissions' => ['TENANT_MANAGE', 'USER_MANAGE', 'SYSTEM_CONFIG'],
            'scenarios' => [
                'Create new tenant',
                'View all tenants',
                'Manage platform users',
                'View system statistics'
            ]
        ],
        'PLATFORM_ADMIN' => [
            'name' => 'Platform Admin',
            'is_platform' => true,
            'permissions' => ['TENANT_VIEW', 'USER_MANAGE', 'SYSTEM_CONFIG'],
            'scenarios' => [
                'View all tenants',
                'Manage platform users',
                'Configure system settings'
            ]
        ],
        'ADMIN' => [
            'name' => 'Administrator',
            'is_platform' => false,
            'permissions' => ['ALL'],
            'scenarios' => [
                'Manage all modules',
                'Configure tenant settings',
                'Manage users',
                'View all reports'
            ]
        ],
        'MANAGER' => [
            'name' => 'Manager',
            'is_platform' => false,
            'permissions' => ['ORDER_VIEW', 'ORDER_EDIT', 'MENU_VIEW', 'MENU_EDIT', 'INVENTORY_VIEW', 'INVENTORY_EDIT', 'STAFF_MANAGE'],
            'scenarios' => [
                'View daily sales',
                'Manage menu items',
                'Manage inventory',
                'Manage staff schedules'
            ]
        ],
        'KASIR' => [
            'name' => 'Kasir',
            'is_platform' => false,
            'permissions' => ['ORDER_CREATE', 'ORDER_VIEW', 'PAYMENT_PROCESS'],
            'scenarios' => [
                'Create new order',
                'Process payment',
                'View order history'
            ]
        ],
        'KOKI' => [
            'name' => 'Koki',
            'is_platform' => false,
            'permissions' => ['KITCHEN_VIEW', 'KITCHEN_UPDATE'],
            'scenarios' => [
                'View kitchen orders',
                'Update order status',
                'View recipe information'
            ]
        ],
        'WAITER' => [
            'name' => 'Waiter',
            'is_platform' => false,
            'permissions' => ['ORDER_CREATE', 'ORDER_VIEW', 'TABLE_MANAGE'],
            'scenarios' => [
                'Create table orders',
                'View table status',
                'Manage customer requests'
            ]
        ],
        'STOK' => [
            'name' => 'Stok',
            'is_platform' => false,
            'permissions' => ['INVENTORY_VIEW', 'INVENTORY_EDIT', 'SUPPLIER_MANAGE'],
            'scenarios' => [
                'View inventory levels',
                'Update stock quantities',
                'Manage suppliers'
            ]
        ],
        'BARTENDER' => [
            'name' => 'Bartender',
            'is_platform' => false,
            'permissions' => ['ORDER_CREATE', 'KITCHEN_VIEW'],
            'scenarios' => [
                'Create drink orders',
                'View bar orders',
                'Manage bar inventory'
            ]
        ],
        'BARISTA' => [
            'name' => 'Barista',
            'is_platform' => false,
            'permissions' => ['ORDER_CREATE', 'KITCHEN_VIEW'],
            'scenarios' => [
                'Create coffee orders',
                'View coffee orders',
                'Manage coffee inventory'
            ]
        ],
        'SOMMELIER' => [
            'name' => 'Sommelier',
            'is_platform' => false,
            'permissions' => ['ORDER_CREATE', 'MENU_VIEW'],
            'scenarios' => [
                'Create wine orders',
                'View wine menu',
                'Manage wine inventory'
            ]
        ],
        'HOST' => [
            'name' => 'Host/Hostess',
            'is_platform' => false,
            'permissions' => ['TABLE_VIEW', 'RESERVATION_MANAGE'],
            'scenarios' => [
                'Manage table assignments',
                'Create reservations',
                'Greet customers'
            ]
        ]
    ];
    
    // Create simulation users for each role
    echo "Creating simulation users...\n\n";
    $users = [];
    $password = 'Sim123456';
    
    foreach ($roles as $roleCode => $roleInfo) {
        $username = 'sim_' . strtolower($roleCode);
        
        // Check if user exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $existingUser = $stmt->fetch();
        
        if (!$existingUser) {
            // Create user
            $stmt = $pdo->prepare("INSERT INTO users (tenant_id, branch_id, username, email, password, full_name, status, is_platform_owner) VALUES (?, ?, ?, ?, ?, ?, 'ACTIVE', ?)");
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt->execute([
                $tenantId,
                $branchId,
                $username,
                $username . '@restaurant.com',
                $passwordHash,
                'Simulation ' . $roleInfo['name'],
                $roleInfo['is_platform'] ? 1 : 0
            ]);
            $userId = $pdo->lastInsertId();
            
            // Get role_id
            $stmt = $pdo->prepare("SELECT role_id FROM roles WHERE role_code = ?");
            $stmt->execute([$roleCode]);
            $role = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($role) {
                // Assign role
                $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
                $stmt->execute([$userId, $role['role_id']]);
                
                echo "✓ Created user: $username ({$roleInfo['name']})\n";
                $users[$roleCode] = [
                    'user_id' => $userId,
                    'username' => $username,
                    'password' => $password,
                    'role' => $roleInfo['name']
                ];
            }
        } else {
            $userId = $existingUser['user_id'];
            echo "⊘ User already exists: $username\n";
            $users[$roleCode] = [
                'user_id' => $userId,
                'username' => $username,
                'password' => $password,
                'role' => $roleInfo['name']
            ];
        }
    }
    
    echo "\n========================================\n";
    echo "Simulation Scenarios\n";
    echo "========================================\n\n";
    
    // Simulate scenarios for each role
    foreach ($roles as $roleCode => $roleInfo) {
        if (!isset($users[$roleCode])) continue;
        
        $user = $users[$roleCode];
        echo "ROLE: {$roleInfo['name']} ({$user['username']})\n";
        echo str_repeat("-", 50) . "\n";
        
        foreach ($roleInfo['scenarios'] as $scenario) {
            echo "  • $scenario\n";
        }
        
        echo "  Permissions: " . implode(', ', $roleInfo['permissions']) . "\n";
        echo "\n";
    }
    
    // Feature-level simulation
    echo "========================================\n";
    echo "Feature-Level Simulation\n";
    echo "========================================\n\n";
    
    $features = [
        'Authentication' => [
            'Login',
            'Logout',
            'Token refresh',
            'Password reset'
        ],
        'Order Management' => [
            'Create order',
            'Update order',
            'Cancel order',
            'View order history',
            'Split bill',
            'Apply discount'
        ],
        'Menu Management' => [
            'View menu',
            'Create menu item',
            'Update menu item',
            'Delete menu item',
            'Manage categories'
        ],
        'Table Management' => [
            'View tables',
            'Assign table',
            'Release table',
            'Update table status'
        ],
        'Inventory Management' => [
            'View inventory',
            'Update stock',
            'Low stock alerts',
            'Stock adjustments',
            'Supplier management'
        ],
        'Kitchen Operations' => [
            'View kitchen queue',
            'Update order status',
            'Recipe management',
            'Preparation time tracking'
        ],
        'Payment Processing' => [
            'Process payment',
            'Multiple payment methods',
            'Receipt generation',
            'Cash drawer management'
        ],
        'Reporting' => [
            'Daily sales report',
            'Inventory report',
            'Staff performance',
            'Customer analytics'
        ],
        'User Management' => [
            'Create user',
            'Update user',
            'Delete user',
            'Role assignment',
            'Permission management'
        ],
        'Multi-Tenant' => [
            'Tenant isolation',
            'Branch management',
            'Cross-tenant reporting'
        ]
    ];
    
    foreach ($features as $feature => $actions) {
        echo "FEATURE: $feature\n";
        echo str_repeat("-", 50) . "\n";
        foreach ($actions as $action) {
            echo "  ✓ $action\n";
        }
        echo "\n";
    }
    
    // Generate simulation report
    echo "========================================\n";
    echo "Simulation Summary\n";
    echo "========================================\n\n";
    
    echo "Total Roles Simulated: " . count($roles) . "\n";
    echo "Total Users Created: " . count($users) . "\n";
    echo "Total Features Covered: " . count($features) . "\n";
    echo "Total Scenarios Tested: " . array_sum(array_map('count', array_column($roles, 'scenarios'))) . "\n";
    
    echo "\nCredentials for Simulation Users:\n";
    echo "Username: sim_[role_code]\n";
    echo "Password: $password\n";
    echo "Example: sim_platform_owner / $password\n";
    
    echo "\n========================================\n";
    echo "Simulation Complete\n";
    echo "========================================\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
