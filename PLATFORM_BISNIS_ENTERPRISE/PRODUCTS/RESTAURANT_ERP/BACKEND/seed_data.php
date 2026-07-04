<?php

require_once 'bootstrap.php';

$db = new Database();
$pdo = $db->connect();

echo "Seeding default data...\n";

try {
    // Check if tenant already exists
    $stmt = $pdo->prepare("SELECT tenant_id FROM tenants WHERE tenant_code = ?");
    $stmt->execute(['DEFAULT']);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        $tenantId = $existing['tenant_id'];
        echo "Tenant already exists (ID: $tenantId)\n";
    } else {
        // Insert default tenant
        $stmt = $pdo->prepare("INSERT INTO tenants (tenant_code, tenant_name, status) VALUES (?, ?, ?)");
        $stmt->execute(['DEFAULT', 'Default Tenant', 'ACTIVE']);
        $tenantId = $pdo->lastInsertId();
        echo "Created tenant (ID: $tenantId)\n";
    }
    
    // Check if company already exists
    $stmt = $pdo->prepare("SELECT company_id FROM companies WHERE tenant_id = ? AND company_code = ?");
    $stmt->execute([$tenantId, 'DEFAULT']);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        $companyId = $existing['company_id'];
        echo "Company already exists (ID: $companyId)\n";
    } else {
        // Insert default company
        $stmt = $pdo->prepare("INSERT INTO companies (tenant_id, company_code, company_name, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$tenantId, 'DEFAULT', 'Default Restaurant', 'ACTIVE']);
        $companyId = $pdo->lastInsertId();
        echo "Created company (ID: $companyId)\n";
    }
    
    // Check if branch already exists
    $stmt = $pdo->prepare("SELECT branch_id FROM branches WHERE tenant_id = ? AND branch_code = ?");
    $stmt->execute([$tenantId, 'MAIN']);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        $branchId = $existing['branch_id'];
        echo "Branch already exists (ID: $branchId)\n";
    } else {
        // Insert default branch
        $stmt = $pdo->prepare("INSERT INTO branches (tenant_id, company_id, branch_code, branch_name, address, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$tenantId, $companyId, 'MAIN', 'Main Branch', '123 Main Street', 'ACTIVE']);
        $branchId = $pdo->lastInsertId();
        echo "Created branch (ID: $branchId)\n";
    }
    
    // Define roles with their permissions
    $rolesConfig = [
        'ADMIN' => [
            'name' => 'Administrator',
            'description' => 'Full system access',
            'permissions' => ['MENU_MANAGE', 'TABLE_MANAGE', 'RESERVATION_MANAGE', 'INVENTORY_MANAGE',
                            'KITCHEN_VIEW', 'KITCHEN_MANAGE', 'USER_MANAGE', 'SETTINGS_MANAGE', 'REPORT_VIEW',
                            'SALES_MANAGE', 'SALES_VIEW', 'ORDER_MANAGE', 'PAYMENT_MANAGE', 'CUSTOMER_MANAGE']
        ],
        'KASIR' => [
            'name' => 'Kasir',
            'description' => 'Cashier - handle sales and payments',
            'permissions' => ['SALES_MANAGE', 'SALES_VIEW', 'ORDER_MANAGE', 'PAYMENT_MANAGE', 'CUSTOMER_MANAGE']
        ],
        'KOKI' => [
            'name' => 'Koki',
            'description' => 'Chef - kitchen operations',
            'permissions' => ['KITCHEN_VIEW', 'KITCHEN_MANAGE', 'ORDER_MANAGE']
        ],
        'WAITER' => [
            'name' => 'Waiter',
            'description' => 'Waiter - table service',
            'permissions' => ['TABLE_MANAGE', 'RESERVATION_MANAGE', 'ORDER_MANAGE', 'SALES_VIEW']
        ],
        'MANAGER' => [
            'name' => 'Manager',
            'description' => 'Manager - oversight and reports',
            'permissions' => ['REPORT_VIEW', 'SETTINGS_MANAGE', 'USER_MANAGE', 'INVENTORY_MANAGE', 'SALES_VIEW']
        ],
        'STOK' => [
            'name' => 'Stok',
            'description' => 'Inventory Manager',
            'permissions' => ['INVENTORY_MANAGE', 'MENU_MANAGE']
        ]
    ];

    $roleIds = [];

    foreach ($rolesConfig as $roleCode => $roleConfig) {
        $stmt = $pdo->prepare("SELECT role_id FROM roles WHERE tenant_id = ? AND role_code = ?");
        $stmt->execute([$tenantId, $roleCode]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $roleIds[$roleCode] = $existing['role_id'];
            echo "Role $roleCode already exists (ID: {$existing['role_id']})\n";
        } else {
            $stmt = $pdo->prepare("INSERT INTO roles (tenant_id, role_code, role_name, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$tenantId, $roleCode, $roleConfig['name'], $roleConfig['description']]);
            $roleIds[$roleCode] = $pdo->lastInsertId();
            echo "Created role $roleCode (ID: {$roleIds[$roleCode]})\n";
        }
    }
    
    // Insert default permissions
    $permissions = [
        'MENU_MANAGE', 'TABLE_MANAGE', 'RESERVATION_MANAGE', 'INVENTORY_MANAGE',
        'KITCHEN_VIEW', 'KITCHEN_MANAGE', 'USER_MANAGE', 'SETTINGS_MANAGE', 'REPORT_VIEW', 
        'SALES_MANAGE', 'SALES_VIEW', 'ORDER_MANAGE', 'PAYMENT_MANAGE', 'CUSTOMER_MANAGE'
    ];
    
    foreach ($permissions as $perm) {
        $stmt = $pdo->prepare("SELECT permission_id FROM permissions WHERE permission_code = ?");
        $stmt->execute([$perm]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existing) {
            $stmt = $pdo->prepare("INSERT INTO permissions (permission_code, permission_name, description) VALUES (?, ?, ?)");
            $stmt->execute([$perm, $perm, str_replace('_', ' ', $perm)]);
        }
    }
    echo "Created permissions\n";
    
    // Assign permissions to each role
    foreach ($rolesConfig as $roleCode => $roleConfig) {
        $roleId = $roleIds[$roleCode];

        foreach ($roleConfig['permissions'] as $permCode) {
            $stmt = $pdo->prepare("SELECT permission_id FROM permissions WHERE permission_code = ?");
            $stmt->execute([$permCode]);
            $perm = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($perm) {
                $stmt = $pdo->prepare("SELECT * FROM role_permissions WHERE role_id = ? AND permission_id = ?");
                $stmt->execute([$roleId, $perm['permission_id']]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$existing) {
                    $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                    $stmt->execute([$roleId, $perm['permission_id']]);
                }
            }
        }
        echo "Assigned permissions to role $roleCode\n";
    }
    
    // Check if admin user already exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE tenant_id = ? AND username = ?");
    $stmt->execute([$tenantId, 'admin']);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        $userId = $existing['user_id'];
        echo "Admin user already exists (ID: $userId)\n";
    } else {
        // Insert default admin user
        $hashedPassword = password_hash('admin123', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (tenant_id, branch_id, username, email, password, full_name, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$tenantId, $branchId, 'admin', 'admin@restaurant.com', $hashedPassword, 'System Administrator', 'ACTIVE']);
        $userId = $pdo->lastInsertId();
        echo "Created admin user (ID: $userId)\n";
    }
    
    // Check if user role already exists
    $stmt = $pdo->prepare("SELECT * FROM user_roles WHERE user_id = ? AND role_id = ?");
    $stmt->execute([$userId, $roleId]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing) {
        // Assign admin role to user
        $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
        $stmt->execute([$userId, $roleId]);
        echo "Assigned admin role to user\n";
    } else {
        echo "User role already assigned\n";
    }
    
    echo "\nDefault data seeded successfully!\n";
    echo "Login credentials:\n";
    echo "Username: admin\n";
    echo "Password: admin123\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
