<?php

if (!class_exists('Database')) {
    if (!class_exists('database')) {
    require_once __DIR__ . '/../../config/database.php';
}
}
if (!class_exists('Response')) {
    if (!class_exists('Response')) {
    require_once __DIR__ . '/../../core/Response.php';
}
}

class TenantService {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function registerTenant($tenantData, $companyData, $branchData, $userData, $additionalRoles, $tableConfig) {
        $pdo = $this->db->connect();
        
        try {
            $pdo->beginTransaction();

            // Insert tenant
            $stmt = $pdo->prepare("INSERT INTO tenants (tenant_code, tenant_name, business_type, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $tenantData['tenant_code'],
                $tenantData['tenant_name'],
                $tenantData['business_type'],
                $tenantData['status']
            ]);
            $tenantId = $pdo->lastInsertId();

            // Insert company
            $stmt = $pdo->prepare("INSERT INTO companies (tenant_id, company_code, company_name, address, phone, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $tenantId,
                $companyData['company_code'],
                $companyData['company_name'],
                $companyData['address'],
                $companyData['phone'],
                $companyData['status']
            ]);
            $companyId = $pdo->lastInsertId();

            // Insert branch
            $stmt = $pdo->prepare("INSERT INTO branches (tenant_id, company_id, branch_code, branch_name, address, phone, is_main, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $tenantId,
                $companyId,
                $branchData['branch_code'],
                $branchData['branch_name'],
                $branchData['address'],
                $branchData['phone'],
                $branchData['is_main'] ? 1 : 0,
                $branchData['status']
            ]);
            $branchId = $pdo->lastInsertId();

            // Create default roles based on restaurant type
            $roles = $this->createRolesForRestaurantType($pdo, $tenantId, $tenantData['business_type']);
            
            // Create admin role
            $stmt = $pdo->prepare("INSERT INTO roles (tenant_id, role_code, role_name, description, is_system, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$tenantId, 'MANAGER', 'Manager', 'Full system access', true, 'ACTIVE']);
            $managerRoleId = $pdo->lastInsertId();

            // Assign all permissions to manager
            $this->assignAllPermissionsToRole($pdo, $managerRoleId);

            // Create admin user
            $hashedPassword = password_hash($userData['password'], PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (tenant_id, branch_id, username, email, password, full_name, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $tenantId,
                $branchId,
                $userData['username'],
                $userData['email'],
                $hashedPassword,
                $userData['full_name'],
                $userData['status']
            ]);
            $userId = $pdo->lastInsertId();

            // Assign manager role to admin user
            $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id, assigned_at) VALUES (?, ?, NOW())");
            $stmt->execute([$userId, $managerRoleId]);

            // Create additional roles if requested
            foreach ($additionalRoles as $roleCode) {
                if (isset($roles[$roleCode])) {
                    $roleId = $roles[$roleCode];
                    // Assign permissions to this role
                    $this->assignPermissionsToRole($pdo, $roleId, $roleCode);
                }
            }

            // Create menu categories based on restaurant type
            $categories = $this->createMenuCategories($pdo, $tenantId, $tenantData['business_type']);

            // Create menu products based on restaurant type
            $products = $this->createMenuProducts($pdo, $tenantId, $categories, $tenantData['business_type']);

            // Create tables
            $this->createTables($pdo, $tenantId, $branchId, $tableConfig['table_count']);

            // Create inventory items linked to products
            $this->createInventory($pdo, $tenantId, $branchId, $products);

            $pdo->commit();

            return [
                'success' => true,
                'message' => 'Tenant registered successfully',
                'data' => [
                    'tenant_id' => $tenantId,
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'user_id' => $userId
                ]
            ];

        } catch (Exception $e) {
            $pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ];
        }
    }

    private function createRolesForRestaurantType($pdo, $tenantId, $restaurantType) {
        $roleConfigs = [
            'RESTAURANT' => ['CHEF', 'WAITER', 'CASHIER', 'INVENTORY_MANAGER'],
            'CAFE' => ['BARISTA', 'SERVER', 'CASHIER'],
            'BAR_PUB' => ['BARTENDER', 'SERVER', 'SECURITY', 'CASHIER'],
            'FOOD_COURT' => ['STALL_OPERATOR', 'CASHIER', 'CLEANER'],
            'CATERING' => ['CHEF', 'COORDINATOR', 'DELIVERY', 'SALES'],
            'FAST_FOOD' => ['CREW', 'DRIVE_THRU', 'CASHIER'],
            'FINE_DINING' => ['HEAD_CHEF', 'SOMMELIER', 'MAITRE_D', 'WAITER'],
            'COFFEE_SHOP' => ['BARISTA', 'ROASTER', 'SERVER']
        ];

        $roles = [];
        $roleList = $roleConfigs[$restaurantType] ?? [];

        foreach ($roleList as $roleCode) {
            $stmt = $pdo->prepare("INSERT INTO roles (tenant_id, role_code, role_name, description, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $tenantId,
                $roleCode,
                ucfirst(str_replace('_', ' ', strtolower($roleCode))),
                "Role for {$restaurantType}",
                'ACTIVE'
            ]);
            $roles[$roleCode] = $pdo->lastInsertId();
        }

        return $roles;
    }

    private function assignAllPermissionsToRole($pdo, $roleId) {
        $stmt = $pdo->query("SELECT permission_id FROM permissions");
        $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($permissions as $permissionId) {
            $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id, granted_at) VALUES (?, ?, NOW())");
            $stmt->execute([$roleId, $permissionId]);
        }
    }

    private function assignPermissionsToRole($pdo, $roleId, $roleCode) {
        $permissionMap = [
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

        $permissions = $permissionMap[$roleCode] ?? [];

        foreach ($permissions as $permCode) {
            $stmt = $pdo->prepare("SELECT permission_id FROM permissions WHERE permission_code = ?");
            $stmt->execute([$permCode]);
            $perm = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($perm) {
                $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id, granted_at) VALUES (?, ?, NOW())");
                $stmt->execute([$roleId, $perm['permission_id']]);
            }
        }
    }

    private function createMenuCategories($pdo, $tenantId, $restaurantType) {
        $categoryConfigs = [
            'RESTAURANT' => ['Appetizer', 'Main Course', 'Dessert', 'Beverage'],
            'CAFE' => ['Coffee', 'Tea', 'Pastries', 'Light Meals'],
            'BAR_PUB' => ['Cocktails', 'Beer', 'Wine', 'Snacks'],
            'FOOD_COURT' => ['Asian', 'Western', 'Local', 'Beverages'],
            'CATERING' => ['Buffet', 'Package A', 'Package B', 'Beverages'],
            'FAST_FOOD' => ['Burgers', 'Fries', 'Drinks', 'Desserts'],
            'FINE_DINING' => ['Appetizer', 'Soup', 'Main Course', 'Dessert', 'Wine'],
            'COFFEE_SHOP' => ['Espresso', 'Pour Over', 'Pastries', 'Light Food']
        ];

        $categories = [];
        $categoryList = $categoryConfigs[$restaurantType] ?? ['General'];

        foreach ($categoryList as $index => $categoryName) {
            $categoryCode = strtoupper(str_replace(' ', '_', $categoryName));
            $stmt = $pdo->prepare("INSERT INTO categories (tenant_id, category_code, category_name, description, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $tenantId,
                $categoryCode,
                $categoryName,
                "Category for {$restaurantType}",
                'ACTIVE'
            ]);
            $categories[$categoryName] = $pdo->lastInsertId();
        }

        return $categories;
    }

    private function createMenuProducts($pdo, $tenantId, $categories, $restaurantType) {
        $productConfigs = [
            'RESTAURANT' => [
                'Appetizer' => ['Spring Rolls', 'Soup of the Day', 'Caesar Salad'],
                'Main Course' => ['Nasi Goreng Spesial', 'Ayam Bakar', 'Sate Ayam'],
                'Dessert' => ['Es Teler', 'Pisang Goreng'],
                'Beverage' => ['Es Teh Manis', 'Jus Jeruk']
            ],
            'CAFE' => [
                'Coffee' => ['Espresso', 'Cappuccino', 'Latte'],
                'Tea' => ['Green Tea', 'Chai Latte'],
                'Pastries' => ['Croissant', 'Muffin'],
                'Light Meals' => ['Sandwich', 'Salad']
            ],
            'BAR_PUB' => [
                'Cocktails' => ['Mojito', 'Margarita'],
                'Beer' => ['Beer Draft', 'Bottled Beer'],
                'Wine' => ['Red Wine', 'White Wine'],
                'Snacks' => ['Nachos', 'Wings']
            ],
            'FOOD_COURT' => [
                'Asian' => ['Fried Rice', 'Noodle Soup'],
                'Western' => ['Burger', 'Pizza'],
                'Local' => ['Nasi Lemak', 'Mee Goreng'],
                'Beverages' => ['Soft Drink', 'Ice Tea']
            ],
            'CATERING' => [
                'Buffet' => ['Full Buffet Set', 'Mini Buffet'],
                'Package A' => ['Wedding Package A'],
                'Package B' => ['Corporate Package B'],
                'Beverages' => ['Drink Package']
            ],
            'FAST_FOOD' => [
                'Burgers' => ['Cheeseburger', 'Double Burger'],
                'Fries' => ['Regular Fries', 'Large Fries'],
                'Drinks' => ['Cola', 'Sprite'],
                'Desserts' => ['Ice Cream']
            ],
            'FINE_DINING' => [
                'Appetizer' => ['Foie Gras', 'Oysters'],
                'Soup' => ['French Onion', 'Lobster Bisque'],
                'Main Course' => ['Wagyu Steak', 'Lobster'],
                'Dessert' => ['Chocolate Souffle', 'Creme Brulee'],
                'Wine' => ['Champagne', 'Red Wine Selection']
            ],
            'COFFEE_SHOP' => [
                'Espresso' => ['Single Shot', 'Double Shot'],
                'Pour Over' => ['V60', 'Chemex'],
                'Pastries' => ['Croissant', 'Danish'],
                'Light Food' => ['Avocado Toast', 'Bagel']
            ]
        ];

        $products = [];
        $productList = $productConfigs[$restaurantType] ?? [];

        foreach ($productList as $category => $items) {
            if (!isset($categories[$category])) continue;

            $categoryId = $categories[$category];
            foreach ($items as $productName) {
                $productCode = strtoupper(str_replace(' ', '_', $productName));
                $price = $this->getRandomPrice($restaurantType);
                
                $stmt = $pdo->prepare("INSERT INTO products (tenant_id, category_id, product_code, product_name, description, price, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $tenantId,
                    $categoryId,
                    $productCode,
                    $productName,
                    "Menu item for {$restaurantType}",
                    $price,
                    'ACTIVE'
                ]);
                $products[$productName] = $pdo->lastInsertId();
            }
        }

        return $products;
    }

    private function getRandomPrice($restaurantType) {
        $priceRanges = [
            'RESTAURANT' => [45000, 150000],
            'CAFE' => [25000, 60000],
            'BAR_PUB' => [50000, 150000],
            'FOOD_COURT' => [20000, 50000],
            'CATERING' => [1000000, 5000000],
            'FAST_FOOD' => [15000, 80000],
            'FINE_DINING' => [200000, 1000000],
            'COFFEE_SHOP' => [20000, 60000]
        ];

        $range = $priceRanges[$restaurantType] ?? [30000, 100000];
        return rand($range[0], $range[1]);
    }

    private function createTables($pdo, $tenantId, $branchId, $tableCount) {
        for ($i = 1; $i <= $tableCount; $i++) {
            $capacity = rand(2, 8);
            $tableNumber = strval($i);
            
            $stmt = $pdo->prepare("INSERT INTO tables (tenant_id, branch_id, table_number, capacity, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$tenantId, $branchId, $tableNumber, $capacity, 'AVAILABLE']);
        }
    }

    private function createInventory($pdo, $tenantId, $branchId, $products) {
        foreach ($products as $productName => $productId) {
            $quantity = rand(50, 200);
            $unit = 'kg';
            $minStock = rand(10, 30);
            $maxStock = rand(200, 500);
            
            $stmt = $pdo->prepare("INSERT INTO inventory (tenant_id, branch_id, product_id, quantity, unit, minimum_stock, maximum_stock, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$tenantId, $branchId, $productId, $quantity, $unit, $minStock, $maxStock, 'ACTIVE']);
        }
    }

    public function getAllTenants() {
        $pdo = $this->db->connect();
        $stmt = $pdo->query("SELECT * FROM tenants WHERE deleted_at IS NULL ORDER BY created_at DESC");
        $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'message' => 'Tenants retrieved successfully',
            'data' => $tenants
        ];
    }

    public function getTenantById($tenantId) {
        $pdo = $this->db->connect();
        $stmt = $pdo->prepare("SELECT * FROM tenants WHERE tenant_id = ? AND deleted_at IS NULL");
        $stmt->execute([$tenantId]);
        $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tenant) {
            return [
                'success' => false,
                'message' => 'Tenant not found'
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Tenant retrieved successfully',
            'data' => $tenant
        ];
    }
}
