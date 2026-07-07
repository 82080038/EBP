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
    
    // Define roles with their permissions (updated with granular permissions)
    $rolesConfig = [
        'ADMIN' => [
            'name' => 'Administrator',
            'description' => 'Full system access',
            'permissions' => [
                // Menu
                'MENU_CREATE', 'MENU_EDIT', 'MENU_DELETE', 'MENU_VIEW', 'MENU_EDIT_PRICE', 'MENU_MANAGE_MODIFIERS', 'MENU_VIEW_RECIPE',
                // Order
                'ORDER_CREATE', 'ORDER_EDIT', 'ORDER_DELETE', 'ORDER_VIEW', 'ORDER_PAYMENT', 'ORDER_DISCOUNT', 'ORDER_SPLIT_BILL', 'ORDER_MERGE', 'ORDER_VOID', 'ORDER_REFUND', 'ORDER_KITCHEN_STATUS', 'ORDER_TAB_OPEN', 'ORDER_TAB_CLOSE',
                // Table
                'TABLE_CREATE', 'TABLE_EDIT', 'TABLE_DELETE', 'TABLE_VIEW', 'TABLE_UPDATE_STATUS', 'TABLE_ASSIGN_ORDER', 'TABLE_MERGE', 'TABLE_SPLIT',
                // Inventory
                'INVENTORY_CREATE', 'INVENTORY_EDIT', 'INVENTORY_DELETE', 'INVENTORY_VIEW', 'INVENTORY_ADJUST', 'INVENTORY_STOCK_OPNAME', 'INVENTORY_CREATE_PO', 'INVENTORY_RECEIVE_PO', 'INVENTORY_VIEW_LOW_STOCK', 'INVENTORY_VIEW_EXPIRING',
                // Kitchen
                'KITCHEN_VIEW', 'KITCHEN_UPDATE_STATUS', 'KITCHEN_FIRE_COURSE', 'KITCHEN_CANCEL_ITEM',
                // Reservation
                'RESERVATION_CREATE', 'RESERVATION_EDIT', 'RESERVATION_DELETE', 'RESERVATION_VIEW', 'RESERVATION_CONFIRM', 'RESERVATION_WAITLIST', 'RESERVATION_VIEW_GUEST_NOTES',
                // Accounting
                'ACCOUNTING_VIEW_REVENUE', 'ACCOUNTING_VIEW_EXPENSES', 'ACCOUNTING_VIEW_PROFIT', 'ACCOUNTING_VIEW_TRANSACTIONS', 'ACCOUNTING_CREATE_JOURNAL', 'ACCOUNTING_VIEW_TAX', 'ACCOUNTING_MANAGE_PAYABLES', 'ACCOUNTING_MANAGE_RECEIVABLES',
                // CRM
                'CRM_VIEW_CUSTOMERS', 'CRM_VIEW_CUSTOMER_DETAIL', 'CRM_ADD_CUSTOMER', 'CRM_EDIT_CUSTOMER', 'CRM_MANAGE_LOYALTY', 'CRM_VIEW_HISTORY', 'CRM_VIEW_PREFERENCES', 'CRM_MARKETING',
                // Report
                'REPORT_SALES', 'REPORT_INVENTORY', 'REPORT_STAFF', 'REPORT_FINANCIAL', 'REPORT_CUSTOM', 'REPORT_EXPORT', 'REPORT_SCHEDULE',
                // HR
                'HR_VIEW_EMPLOYEES', 'HR_ADD_EMPLOYEE', 'HR_EDIT_EMPLOYEE', 'HR_DELETE_EMPLOYEE', 'HR_VIEW_PAYROLL', 'HR_MANAGE_PAYROLL', 'HR_VIEW_SCHEDULE', 'HR_CREATE_SCHEDULE', 'HR_PERFORMANCE',
                // Delivery
                'DELIVERY_VIEW', 'DELIVERY_CREATE', 'DELIVERY_EDIT', 'DELIVERY_ASSIGN_DRIVER', 'DELIVERY_UPDATE_STATUS', 'DELIVERY_TRACK',
                // Supply Chain
                'SUPPLYCHAIN_VIEW', 'SUPPLYCHAIN_MANAGE_SUPPLIERS', 'SUPPLYCHAIN_PURCHASE_PLANNING', 'SUPPLYCHAIN_QUALITY_CONTROL',
                // Quality
                'QUALITY_VIEW', 'QUALITY_MANAGE', 'QUALITY_CREATE_CHECK',
                // Loyalty
                'LOYALTY_VIEW', 'LOYALTY_MANAGE', 'LOYALTY_REDEEM',
                // Settings
                'SETTINGS_VIEW', 'SETTINGS_MANAGE', 'SETTINGS_TAX_CONFIG', 'SETTINGS_PAYMENT_CONFIG',
                // User
                'USER_VIEW', 'USER_CREATE', 'USER_EDIT', 'USER_DELETE', 'USER_ASSIGN_ROLE'
            ]
        ],
        'KASIR' => [
            'name' => 'Kasir',
            'description' => 'Cashier - handle sales and payments',
            'permissions' => [
                'ORDER_VIEW', 'ORDER_PAYMENT', 'ORDER_DISCOUNT', 'ORDER_SPLIT_BILL', 'ORDER_VOID', 'ORDER_REFUND',
                'TABLE_VIEW', 'TABLE_UPDATE_STATUS', 'TABLE_ASSIGN_ORDER',
                'MENU_VIEW',
                'ACCOUNTING_VIEW_REVENUE', 'ACCOUNTING_VIEW_TRANSACTIONS', 'ACCOUNTING_VIEW_TAX',
                'REPORT_SALES', 'REPORT_FINANCIAL',
                'CRM_ADD_CUSTOMER',
                'USER_VIEW_OWN_PROFILE'
            ]
        ],
        'KOKI' => [
            'name' => 'Koki',
            'description' => 'Chef - kitchen operations',
            'permissions' => [
                'KITCHEN_VIEW', 'KITCHEN_UPDATE_STATUS', 'KITCHEN_FIRE_COURSE',
                'ORDER_VIEW',
                'INVENTORY_VIEW', 'INVENTORY_VIEW_LOW_STOCK', 'INVENTORY_VIEW_EXPIRING',
                'MENU_VIEW', 'MENU_VIEW_RECIPE',
                'USER_VIEW_OWN_PROFILE', 'USER_VIEW_OWN_SCHEDULE'
            ]
        ],
        'WAITER' => [
            'name' => 'Waiter',
            'description' => 'Waiter - table service',
            'permissions' => [
                'TABLE_VIEW', 'TABLE_UPDATE_STATUS', 'TABLE_ASSIGN_ORDER',
                'RESERVATION_CREATE', 'RESERVATION_EDIT', 'RESERVATION_VIEW', 'RESERVATION_CONFIRM',
                'ORDER_CREATE', 'ORDER_EDIT', 'ORDER_VIEW',
                'MENU_VIEW',
                'CRM_ADD_CUSTOMER', 'CRM_VIEW_CUSTOMERS', 'CRM_VIEW_CUSTOMER_DETAIL',
                'USER_VIEW_OWN_PROFILE', 'USER_VIEW_OWN_SCHEDULE'
            ]
        ],
        'MANAGER' => [
            'name' => 'Manager',
            'description' => 'Manager - oversight and reports',
            'permissions' => [
                // Menu (limited)
                'MENU_VIEW', 'MENU_EDIT_PRICE',
                // Order
                'ORDER_CREATE', 'ORDER_EDIT', 'ORDER_VIEW', 'ORDER_PAYMENT', 'ORDER_DISCOUNT', 'ORDER_SPLIT_BILL', 'ORDER_VOID', 'ORDER_REFUND', 'ORDER_KITCHEN_STATUS',
                // Table
                'TABLE_VIEW', 'TABLE_UPDATE_STATUS', 'TABLE_ASSIGN_ORDER', 'TABLE_MERGE', 'TABLE_SPLIT',
                // Inventory
                'INVENTORY_VIEW', 'INVENTORY_EDIT', 'INVENTORY_ADJUST', 'INVENTORY_VIEW_LOW_STOCK', 'INVENTORY_VIEW_EXPIRING', 'INVENTORY_CREATE_PO', 'INVENTORY_RECEIVE_PO',
                // Kitchen
                'KITCHEN_VIEW', 'KITCHEN_UPDATE_STATUS', 'KITCHEN_FIRE_COURSE', 'KITCHEN_CANCEL_ITEM',
                // Reservation
                'RESERVATION_CREATE', 'RESERVATION_EDIT', 'RESERVATION_VIEW', 'RESERVATION_CONFIRM', 'RESERVATION_WAITLIST',
                // Accounting (read-only)
                'ACCOUNTING_VIEW_REVENUE', 'ACCOUNTING_VIEW_EXPENSES', 'ACCOUNTING_VIEW_PROFIT', 'ACCOUNTING_VIEW_TRANSACTIONS', 'ACCOUNTING_VIEW_TAX',
                // CRM
                'CRM_VIEW_CUSTOMERS', 'CRM_VIEW_CUSTOMER_DETAIL', 'CRM_ADD_CUSTOMER', 'CRM_EDIT_CUSTOMER', 'CRM_VIEW_HISTORY', 'CRM_VIEW_PREFERENCES', 'CRM_MARKETING',
                // Report
                'REPORT_SALES', 'REPORT_INVENTORY', 'REPORT_STAFF', 'REPORT_FINANCIAL', 'REPORT_CUSTOM', 'REPORT_EXPORT',
                // HR
                'HR_VIEW_EMPLOYEES', 'HR_EDIT_EMPLOYEE', 'HR_VIEW_PAYROLL', 'HR_MANAGE_PAYROLL', 'HR_VIEW_SCHEDULE', 'HR_CREATE_SCHEDULE', 'HR_PERFORMANCE',
                // Delivery
                'DELIVERY_VIEW', 'DELIVERY_CREATE', 'DELIVERY_EDIT', 'DELIVERY_ASSIGN_DRIVER', 'DELIVERY_UPDATE_STATUS', 'DELIVERY_TRACK',
                // Supply Chain
                'SUPPLYCHAIN_VIEW', 'SUPPLYCHAIN_MANAGE_SUPPLIERS', 'SUPPLYCHAIN_PURCHASE_PLANNING',
                // Quality
                'QUALITY_VIEW', 'QUALITY_MANAGE', 'QUALITY_CREATE_CHECK',
                // User (limited)
                'USER_VIEW', 'USER_VIEW_OWN_PROFILE', 'USER_VIEW_OWN_SCHEDULE'
            ]
        ],
        'STOK' => [
            'name' => 'Stok',
            'description' => 'Inventory Manager',
            'permissions' => [
                'INVENTORY_CREATE', 'INVENTORY_EDIT', 'INVENTORY_DELETE', 'INVENTORY_VIEW', 'INVENTORY_ADJUST', 'INVENTORY_STOCK_OPNAME', 'INVENTORY_CREATE_PO', 'INVENTORY_RECEIVE_PO', 'INVENTORY_VIEW_LOW_STOCK', 'INVENTORY_VIEW_EXPIRING',
                'SUPPLYCHAIN_VIEW', 'SUPPLYCHAIN_MANAGE_SUPPLIERS', 'SUPPLYCHAIN_PURCHASE_PLANNING', 'SUPPLYCHAIN_QUALITY_CONTROL',
                'QUALITY_VIEW', 'QUALITY_MANAGE', 'QUALITY_CREATE_CHECK',
                'ORDER_VIEW',
                'REPORT_INVENTORY',
                'MENU_VIEW', 'MENU_VIEW_RECIPE',
                'USER_VIEW_OWN_PROFILE', 'USER_VIEW_OWN_SCHEDULE'
            ]
        ],
        'BARTENDER' => [
            'name' => 'Bartender',
            'description' => 'Bartender - bar service',
            'permissions' => [
                'ORDER_VIEW', 'ORDER_CREATE', 'ORDER_TAB_OPEN', 'ORDER_TAB_CLOSE',
                'TABLE_VIEW', 'TABLE_UPDATE_STATUS', 'TABLE_ASSIGN_ORDER',
                'INVENTORY_VIEW', 'INVENTORY_VIEW_LOW_STOCK',
                'MENU_VIEW',
                'USER_VIEW_OWN_PROFILE', 'USER_VIEW_OWN_SCHEDULE'
            ]
        ],
        'BARISTA' => [
            'name' => 'Barista',
            'description' => 'Barista - coffee preparation',
            'permissions' => [
                'ORDER_VIEW', 'ORDER_CREATE',
                'INVENTORY_VIEW', 'INVENTORY_VIEW_LOW_STOCK',
                'MENU_VIEW',
                'LOYALTY_VIEW', 'LOYALTY_REDEEM',
                'USER_VIEW_OWN_PROFILE', 'USER_VIEW_OWN_SCHEDULE'
            ]
        ],
        'SOMMELIER' => [
            'name' => 'Sommelier',
            'description' => 'Sommelier - wine service (fine dining)',
            'permissions' => [
                'ORDER_VIEW',
                'MENU_VIEW',
                'INVENTORY_VIEW',
                'CRM_VIEW_CUSTOMERS', 'CRM_VIEW_CUSTOMER_DETAIL', 'CRM_ADD_CUSTOMER', 'CRM_EDIT_CUSTOMER', 'CRM_VIEW_HISTORY', 'CRM_VIEW_PREFERENCES',
                'RESERVATION_VIEW_GUEST_NOTES',
                'USER_VIEW_OWN_PROFILE', 'USER_VIEW_OWN_SCHEDULE'
            ]
        ],
        'HOST' => [
            'name' => 'Host/Hostess',
            'description' => 'Host/Hostess - guest reception',
            'permissions' => [
                'TABLE_VIEW', 'TABLE_UPDATE_STATUS', 'TABLE_ASSIGN_ORDER',
                'RESERVATION_CREATE', 'RESERVATION_EDIT', 'RESERVATION_VIEW', 'RESERVATION_CONFIRM', 'RESERVATION_WAITLIST', 'RESERVATION_VIEW_GUEST_NOTES',
                'ORDER_VIEW',
                'MENU_VIEW',
                'CRM_ADD_CUSTOMER', 'CRM_VIEW_CUSTOMERS', 'CRM_VIEW_CUSTOMER_DETAIL',
                'USER_VIEW_OWN_PROFILE', 'USER_VIEW_OWN_SCHEDULE'
            ]
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
    
    // Insert default permissions (granular permissions)
    $permissions = [
        // Menu
        'MENU_CREATE', 'MENU_EDIT', 'MENU_DELETE', 'MENU_VIEW', 'MENU_EDIT_PRICE', 'MENU_MANAGE_MODIFIERS', 'MENU_VIEW_RECIPE',
        // Order
        'ORDER_CREATE', 'ORDER_EDIT', 'ORDER_DELETE', 'ORDER_VIEW', 'ORDER_PAYMENT', 'ORDER_DISCOUNT', 'ORDER_SPLIT_BILL', 'ORDER_MERGE', 'ORDER_VOID', 'ORDER_REFUND', 'ORDER_KITCHEN_STATUS', 'ORDER_TAB_OPEN', 'ORDER_TAB_CLOSE',
        // Table
        'TABLE_CREATE', 'TABLE_EDIT', 'TABLE_DELETE', 'TABLE_VIEW', 'TABLE_UPDATE_STATUS', 'TABLE_ASSIGN_ORDER', 'TABLE_MERGE', 'TABLE_SPLIT',
        // Inventory
        'INVENTORY_CREATE', 'INVENTORY_EDIT', 'INVENTORY_DELETE', 'INVENTORY_VIEW', 'INVENTORY_ADJUST', 'INVENTORY_STOCK_OPNAME', 'INVENTORY_CREATE_PO', 'INVENTORY_RECEIVE_PO', 'INVENTORY_VIEW_LOW_STOCK', 'INVENTORY_VIEW_EXPIRING',
        // Kitchen
        'KITCHEN_VIEW', 'KITCHEN_UPDATE_STATUS', 'KITCHEN_FIRE_COURSE', 'KITCHEN_CANCEL_ITEM',
        // Reservation
        'RESERVATION_CREATE', 'RESERVATION_EDIT', 'RESERVATION_DELETE', 'RESERVATION_VIEW', 'RESERVATION_CONFIRM', 'RESERVATION_WAITLIST', 'RESERVATION_VIEW_GUEST_NOTES',
        // Accounting
        'ACCOUNTING_VIEW_REVENUE', 'ACCOUNTING_VIEW_EXPENSES', 'ACCOUNTING_VIEW_PROFIT', 'ACCOUNTING_VIEW_TRANSACTIONS', 'ACCOUNTING_CREATE_JOURNAL', 'ACCOUNTING_VIEW_TAX', 'ACCOUNTING_MANAGE_PAYABLES', 'ACCOUNTING_MANAGE_RECEIVABLES',
        // CRM
        'CRM_VIEW_CUSTOMERS', 'CRM_VIEW_CUSTOMER_DETAIL', 'CRM_ADD_CUSTOMER', 'CRM_EDIT_CUSTOMER', 'CRM_MANAGE_LOYALTY', 'CRM_VIEW_HISTORY', 'CRM_VIEW_PREFERENCES', 'CRM_MARKETING',
        // Report
        'REPORT_SALES', 'REPORT_INVENTORY', 'REPORT_STAFF', 'REPORT_FINANCIAL', 'REPORT_CUSTOM', 'REPORT_EXPORT', 'REPORT_SCHEDULE',
        // HR
        'HR_VIEW_EMPLOYEES', 'HR_ADD_EMPLOYEE', 'HR_EDIT_EMPLOYEE', 'HR_DELETE_EMPLOYEE', 'HR_VIEW_PAYROLL', 'HR_MANAGE_PAYROLL', 'HR_VIEW_SCHEDULE', 'HR_CREATE_SCHEDULE', 'HR_PERFORMANCE', 'HR_VIEW_OWN_PROFILE', 'HR_VIEW_OWN_SCHEDULE',
        // Delivery
        'DELIVERY_VIEW', 'DELIVERY_CREATE', 'DELIVERY_EDIT', 'DELIVERY_ASSIGN_DRIVER', 'DELIVERY_UPDATE_STATUS', 'DELIVERY_TRACK',
        // Supply Chain
        'SUPPLYCHAIN_VIEW', 'SUPPLYCHAIN_MANAGE_SUPPLIERS', 'SUPPLYCHAIN_PURCHASE_PLANNING', 'SUPPLYCHAIN_QUALITY_CONTROL',
        // Quality
        'QUALITY_VIEW', 'QUALITY_MANAGE', 'QUALITY_CREATE_CHECK',
        // Loyalty
        'LOYALTY_VIEW', 'LOYALTY_MANAGE', 'LOYALTY_REDEEM',
        // Settings
        'SETTINGS_VIEW', 'SETTINGS_MANAGE', 'SETTINGS_TAX_CONFIG', 'SETTINGS_PAYMENT_CONFIG',
        // User
        'USER_VIEW', 'USER_CREATE', 'USER_EDIT', 'USER_DELETE', 'USER_ASSIGN_ROLE'
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
        echo "Admin user already exists (ID: $userId) - updating password\n";
        // Update password for existing admin user
        $hashedPassword = password_hash('admin123', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->execute([$hashedPassword, $userId]);
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
