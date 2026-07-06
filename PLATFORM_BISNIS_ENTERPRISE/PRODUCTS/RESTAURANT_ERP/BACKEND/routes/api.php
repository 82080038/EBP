<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../bootstrap.php';

// Upload Module
if (!class_exists('UploadController')) {
    require_once __DIR__ . '/../modules/Upload/Controllers/UploadController.php';
}

// Auth Module
if (!class_exists('AuthController')) {
    require_once __DIR__ . '/../modules/Auth/Controllers/AuthController.php';
}

// Simple Menu Module (for testing without complex middleware)
if (!class_exists('SimpleMenuController')) {
    require_once __DIR__ . '/../modules/Menu/Controllers/SimpleMenuController.php';
}

// Simple Table Module (for testing without complex middleware)
if (!class_exists('SimpleTableController')) {
    require_once __DIR__ . '/../modules/Table/Controllers/SimpleTableController.php';
}

// Simple Order Module (for testing without complex middleware)
if (!class_exists('SimpleOrderController')) {
    require_once __DIR__ . '/../modules/Sales/Controllers/SimpleOrderController.php';
}

// Simple User Module (for testing without complex middleware)
if (!class_exists('SimpleUserController')) {
    require_once __DIR__ . '/../modules/User/Controllers/SimpleUserController.php';
}

// Simple Inventory Module (for testing without complex middleware)
if (!class_exists('SimpleInventoryController')) {
    require_once __DIR__ . '/../modules/Inventory/Controllers/SimpleInventoryController.php';
}

// Simple Kitchen Module (for testing without complex middleware)
if (!class_exists('SimpleKitchenController')) {
    require_once __DIR__ . '/../modules/Kitchen/Controllers/SimpleKitchenController.php';
}

// Simple Reservation Module (for testing without complex middleware)
if (!class_exists('SimpleReservationController')) {
    require_once __DIR__ . '/../modules/Reservation/Controllers/SimpleReservationController.php';
}

// Simple Customer Module (for testing without complex middleware)
if (!class_exists('SimpleCustomerController')) {
    require_once __DIR__ . '/../modules/CRM/Controllers/SimpleCustomerController.php';
}

// Simple Employee Module (for testing without complex middleware)
if (!class_exists('SimpleEmployeeController')) {
    require_once __DIR__ . '/../modules/HR/Controllers/SimpleEmployeeController.php';
}

// Simple Delivery Module (for testing without complex middleware)
if (!class_exists('SimpleDeliveryController')) {
    require_once __DIR__ . '/../modules/Delivery/Controllers/SimpleDeliveryController.php';
}

// Simple Supplier Module (for testing without complex middleware)
if (!class_exists('SimpleSupplierController')) {
    require_once __DIR__ . '/../modules/SupplyChain/Controllers/SimpleSupplierController.php';
}

// Sales Module
if (!class_exists('OrderController')) {
    require_once __DIR__ . '/../modules/Sales/Controllers/OrderController.php';
}
if (!class_exists('PaymentManagementController')) {
    require_once __DIR__ . '/../modules/Sales/Controllers/PaymentManagementController.php';
}

// Menu Module
if (!class_exists('MenuController')) {
    require_once __DIR__ . '/../modules/Menu/Controllers/MenuController.php';
}
if (!class_exists('ProductVariantController')) {
    require_once __DIR__ . '/../modules/Menu/Controllers/ProductVariantController.php';
}
if (!class_exists('ProductModifierController')) {
    require_once __DIR__ . '/../modules/Menu/Controllers/ProductModifierController.php';
}
if (!class_exists('ComboController')) {
    require_once __DIR__ . '/../modules/Menu/Controllers/ComboController.php';
}

// Table Module
if (!class_exists('TableController')) {
    require_once __DIR__ . '/../modules/Table/Controllers/TableController.php';
}

// Reservation Module
if (!class_exists('ReservationController')) {
    require_once __DIR__ . '/../modules/Reservation/Controllers/ReservationController.php';
}

// Inventory Module
if (!class_exists('InventoryController')) {
    require_once __DIR__ . '/../modules/Inventory/Controllers/InventoryController.php';
}
if (!class_exists('SupplierController')) {
    require_once __DIR__ . '/../modules/Inventory/Controllers/SupplierController.php';
}
if (!class_exists('StockAdjustmentController')) {
    require_once __DIR__ . '/../modules/Inventory/Controllers/StockAdjustmentController.php';
}
if (!class_exists('StockOpnameController')) {
    require_once __DIR__ . '/../modules/Inventory/Controllers/StockOpnameController.php';
}
if (!class_exists('PurchaseOrderController')) {
    require_once __DIR__ . '/../modules/Inventory/Controllers/PurchaseOrderController.php';
}
if (!class_exists('GoodsReceiptController')) {
    require_once __DIR__ . '/../modules/Inventory/Controllers/GoodsReceiptController.php';
}

// Kitchen Module
if (!class_exists('KitchenController')) {
    require_once __DIR__ . '/../modules/Kitchen/Controllers/KitchenController.php';
}

// Location Module
if (!class_exists('LocationController')) {
    require_once __DIR__ . '/../modules/Location/Controllers/LocationController.php';
}

// CRM Module
if (!class_exists('CustomerController')) {
    require_once __DIR__ . '/../modules/CRM/Controllers/CustomerController.php';
}

// AI Module
if (!class_exists('AIController')) {
    require_once __DIR__ . '/../modules/AI/Controllers/AIController.php';
}

// Recipe Module
if (!class_exists('RecipeController')) {
    require_once __DIR__ . '/../modules/Recipe/Controllers/RecipeController.php';
}

// Menu Engineering Module
if (!class_exists('MenuEngineeringController')) {
    require_once __DIR__ . '/../modules/MenuEngineering/Controllers/MenuEngineeringController.php';
}

// Food Waste Module
if (!class_exists('FoodWasteController')) {
    require_once __DIR__ . '/../modules/FoodWaste/Controllers/FoodWasteController.php';
}

// Staff Scheduling Module
if (!class_exists('StaffSchedulingController')) {
    require_once __DIR__ . '/../modules/StaffScheduling/Controllers/StaffSchedulingController.php';
}

// Tip Management Module
if (!class_exists('TipManagementController')) {
    require_once __DIR__ . '/../modules/TipManagement/Controllers/TipManagementController.php';
}

// Daily Reports Module
if (!class_exists('DailyReportsController')) {
    require_once __DIR__ . '/../modules/DailyReports/Controllers/DailyReportsController.php';
}

// Delivery Module
if (!class_exists('DeliveryController')) {
    require_once __DIR__ . '/../modules/Delivery/Controllers/DeliveryController.php';
}

// HR Module
if (!class_exists('EmployeeController')) {
    require_once __DIR__ . '/../modules/HR/Controllers/EmployeeController.php';
}

// Accounting Module
if (!class_exists('AccountingController')) {
    require_once __DIR__ . '/../modules/Accounting/Controllers/AccountingController.php';
}

if (!class_exists('GeneralLedgerController')) {
    require_once __DIR__ . '/../modules/Accounting/Controllers/GeneralLedgerController.php';
}

if (!class_exists('AccountsReceivableController')) {
    require_once __DIR__ . '/../modules/Accounting/Controllers/AccountsReceivableController.php';
}

if (!class_exists('AccountsPayableController')) {
    require_once __DIR__ . '/../modules/Accounting/Controllers/AccountsPayableController.php';
}

if (!class_exists('BankReconciliationController')) {
    require_once __DIR__ . '/../modules/Accounting/Controllers/BankReconciliationController.php';
}

if (!class_exists('FixedAssetsController')) {
    require_once __DIR__ . '/../modules/Accounting/Controllers/FixedAssetsController.php';
}

if (!class_exists('BudgetController')) {
    require_once __DIR__ . '/../modules/Accounting/Controllers/BudgetController.php';
}

if (!class_exists('AccountingPeriodController')) {
    require_once __DIR__ . '/../modules/Accounting/Controllers/AccountingPeriodController.php';
}

// Supply Chain Module
if (!class_exists('SupplyChainController')) {
    require_once __DIR__ . '/../modules/SupplyChain/Controllers/SupplyChainController.php';
}

// Maintenance Module
if (!class_exists('MaintenanceController')) {
    require_once __DIR__ . '/../modules/Maintenance/Controllers/MaintenanceController.php';
}

// Quality Module
if (!class_exists('QualityController')) {
    require_once __DIR__ . '/../modules/Quality/Controllers/QualityController.php';
}

// Sustainability Module
if (!class_exists('SustainabilityController')) {
    require_once __DIR__ . '/../modules/Sustainability/Controllers/SustainabilityController.php';
}

// Integration Module
if (!class_exists('IntegrationController')) {
    require_once __DIR__ . '/../modules/Integration/Controllers/IntegrationController.php';
}

// Offline Sync Module
if (!class_exists('OfflineSyncController')) {
    require_once __DIR__ . '/../modules/Offline/Controllers/OfflineSyncController.php';
}

// Inventory Advanced Module
if (!class_exists('InventoryAdvancedController')) {
    require_once __DIR__ . '/../modules/Inventory/Controllers/InventoryAdvancedController.php';
}

// Kitchen Performance Module
if (!class_exists('KitchenPerformanceController')) {
    require_once __DIR__ . '/../modules/Kitchen/Controllers/KitchenPerformanceController.php';
}

// Customer Advanced Module
if (!class_exists('CustomerAdvancedController')) {
    require_once __DIR__ . '/../modules/CRM/Controllers/CustomerAdvancedController.php';
}

// Cost Center Module
if (!class_exists('CostCenterController')) {
    require_once __DIR__ . '/../modules/Accounting/Controllers/CostCenterController.php';
}

// WhatsApp Module
if (!class_exists('WhatsAppController')) {
    require_once __DIR__ . '/../modules/WhatsApp/Controllers/WhatsAppController.php';
}

// Tax Calculation Module
if (!class_exists('TaxCalculationController')) {
    require_once __DIR__ . '/../modules/Accounting/Controllers/TaxCalculationController.php';
}

// Supply Chain Module
if (!class_exists('PurchasePlanningController')) {
    require_once __DIR__ . '/../modules/SupplyChain/Controllers/PurchasePlanningController.php';
}
if (!class_exists('QualityControlController')) {
    require_once __DIR__ . '/../modules/SupplyChain/Controllers/QualityControlController.php';
}
if (!class_exists('SupplierPerformanceController')) {
    require_once __DIR__ . '/../modules/SupplyChain/Controllers/SupplierPerformanceController.php';
}

// Currency Module
if (!class_exists('CurrencyController')) {
    require_once __DIR__ . '/../modules/Settings/Controllers/CurrencyController.php';
}

// AI Module
if (!class_exists('SmartProcurementController')) {
    require_once __DIR__ . '/../modules/AI/Controllers/SmartProcurementController.php';
}
if (!class_exists('KitchenIntelligenceController')) {
    require_once __DIR__ . '/../modules/AI/Controllers/KitchenIntelligenceController.php';
}
if (!class_exists('CustomerIntelligenceController')) {
    require_once __DIR__ . '/../modules/AI/Controllers/CustomerIntelligenceController.php';
}
if (!class_exists('DynamicPricingController')) {
    require_once __DIR__ . '/../modules/AI/Controllers/DynamicPricingController.php';
}
if (!class_exists('WasteReductionController')) {
    require_once __DIR__ . '/../modules/AI/Controllers/WasteReductionController.php';
}

// Maintenance Module
if (!class_exists('PredictiveMaintenanceController')) {
    require_once __DIR__ . '/../modules/Maintenance/Controllers/PredictiveMaintenanceController.php';
}
if (!class_exists('WorkOrderController')) {
    require_once __DIR__ . '/../modules/Maintenance/Controllers/WorkOrderController.php';
}
if (!class_exists('EquipmentHistoryController')) {
    require_once __DIR__ . '/../modules/Maintenance/Controllers/EquipmentHistoryController.php';
}

// Offline Module
if (!class_exists('OfflineStatusController')) {
    require_once __DIR__ . '/../modules/Offline/Controllers/OfflineStatusController.php';
}

// Kiosk Module
if (!class_exists('KioskController')) {
    require_once __DIR__ . '/../modules/Kiosk/Controllers/KioskController.php';
}

// Mobile Module
if (!class_exists('MobileOrderController')) {
    require_once __DIR__ . '/../modules/Mobile/Controllers/MobileOrderController.php';
}

// Consumer Module
if (!class_exists('ConsumerController')) {
    require_once __DIR__ . '/../modules/Consumer/Controllers/ConsumerController.php';
}

// WhatsApp Ordering Module
if (!class_exists('WhatsAppOrderingController')) {
    require_once __DIR__ . '/../modules/WhatsApp/Controllers/WhatsAppOrderingController.php';
}

// Quality Compliance Module
if (!class_exists('QualityComplianceController')) {
    require_once __DIR__ . '/../modules/Quality/Controllers/QualityComplianceController.php';
}

// User Module
if (!class_exists('UserController')) {
    require_once __DIR__ . '/../modules/User/Controllers/UserController.php';
}

// Settings Module
if (!class_exists('SettingController')) {
    require_once __DIR__ . '/../modules/Settings/Controllers/SettingController.php';
}

// Report Module
if (!class_exists('ReportController')) {
    require_once __DIR__ . '/../modules/Report/Controllers/ReportController.php';
}

// Tenant Module
if (!class_exists('TenantController')) {
    require_once __DIR__ . '/../modules/Tenant/Controllers/TenantController.php';
}

// AI Advanced Module
if (!class_exists('AdvancedAIController')) {
    require_once __DIR__ . '/../modules/AI/Controllers/AdvancedAIController.php';
}

// Enterprise Module
if (!class_exists('EnterpriseController')) {
    require_once __DIR__ . '/../modules/Enterprise/Controllers/EnterpriseController.php';
}

// Credit Module
if (!class_exists('CreditController')) {
    require_once __DIR__ . '/../modules/CRM/Controllers/CreditController.php';
}

// Customer Pricing Module
if (!class_exists('CustomerPricingController')) {
    require_once __DIR__ . '/../modules/CRM/Controllers/CustomerPricingController.php';
}

// Bonus Module
if (!class_exists('BonusController')) {
    require_once __DIR__ . '/../modules/HR/Controllers/BonusController.php';
}

// Tip Module
if (!class_exists('TipController')) {
    require_once __DIR__ . '/../modules/HR/Controllers/TipController.php';
}

// Commission Module
if (!class_exists('CommissionController')) {
    require_once __DIR__ . '/../modules/HR/Controllers/CommissionController.php';
}

// Loyalty Module
if (!class_exists('LoyaltyController')) {
    require_once __DIR__ . '/../modules/Loyalty/Controllers/LoyaltyController.php';
}

// Initialize dependencies
$db = new Database();
$router = new Router();
$permissionMiddleware = new PermissionMiddleware();
$authMiddleware = new AuthMiddleware();

// Helper function to apply auth and permission middleware
function withAuthAndPermission($handler, $permission, $permissionMiddleware, $authMiddleware) {
    return function($request) use ($handler, $permission, $permissionMiddleware, $authMiddleware) {
        try {
            // Apply auth middleware
            $request = $authMiddleware->handle($request);

            // Apply permission middleware
            $userId = $request['user_id'] ?? null;
            $isPlatformOwner = $request['is_platform_owner'] ?? false;
            $isTenantOwner = $request['is_tenant_owner'] ?? false;

            if ($userId && !$permissionMiddleware->check($userId, $permission, $isPlatformOwner, $isTenantOwner)) {
                return Response::error("You don't have permission to access this resource", 403);
            }

            // Call the actual handler
            return $handler($request);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), $e->getCode() ?: 401);
        }
    };
}

// Helper function to apply auth middleware only
function withAuth($handler, $authMiddleware) {
    return function($request) use ($handler, $authMiddleware) {
        try {
            // Apply auth middleware
            $request = $authMiddleware->handle($request);
            
            // Call the actual handler
            return $handler($request);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), $e->getCode() ?: 401);
        }
    };
}

// Initialize controllers
$simpleMenuController = new SimpleMenuController();
$simpleOrderController = new SimpleOrderController();
$simpleUserController = new SimpleUserController();
$simpleTableController = new SimpleTableController();
$simpleInventoryController = new SimpleInventoryController();
$simpleKitchenController = new SimpleKitchenController();
$simpleReservationController = new SimpleReservationController();
$simpleCustomerController = new SimpleCustomerController();
$simpleEmployeeController = new SimpleEmployeeController();
$simpleDeliveryController = new SimpleDeliveryController();
$simpleSupplierController = new SimpleSupplierController();
$authController = new AuthController();
$orderController = new OrderController();
$paymentManagementController = new PaymentManagementController();
$menuController = new MenuController();
$productVariantController = new ProductVariantController();
$productModifierController = new ProductModifierController();
$comboController = new ComboController();
$recipeController = new RecipeController();
$menuEngineeringController = new MenuEngineeringController();
$foodWasteController = new FoodWasteController();
$staffSchedulingController = new StaffSchedulingController();
$tipManagementController = new TipManagementController();
$dailyReportsController = new DailyReportsController();
$tableController = new TableController();
$reservationController = new ReservationController();
$inventoryController = new InventoryController();
$supplierController = new SupplierController();
$stockAdjustmentController = new StockAdjustmentController();
$stockOpnameController = new StockOpnameController();
$purchaseOrderController = new PurchaseOrderController();
$goodsReceiptController = new GoodsReceiptController();
$kitchenController = new KitchenController();
$locationController = new LocationController();
$customerController = new CustomerController();
$uploadController = new UploadController();
$aiController = new AIController();
$deliveryController = new DeliveryController();
$employeeController = new EmployeeController();
$accountingController = new AccountingController();
$generalLedgerController = new GeneralLedgerController();
$accountsReceivableController = new AccountsReceivableController();
$accountsPayableController = new AccountsPayableController();
$bankReconciliationController = new BankReconciliationController();
$fixedAssetsController = new FixedAssetsController();
$budgetController = new BudgetController();
$accountingPeriodController = new AccountingPeriodController();
$supplyChainController = new SupplyChainController();
$maintenanceController = new MaintenanceController();
$qualityController = new QualityController();
$sustainabilityController = new SustainabilityController();
$integrationController = new IntegrationController();
$advancedAIController = new AdvancedAIController();
$enterpriseController = new EnterpriseController();
$offlineSyncController = new OfflineSyncController();
$creditController = new CreditController();
$customerPricingController = new CustomerPricingController();
$bonusController = new BonusController();
$tipController = new TipController();
$commissionController = new CommissionController();
$inventoryAdvancedController = new InventoryAdvancedController();
$kitchenPerformanceController = new KitchenPerformanceController();
$customerAdvancedController = new CustomerAdvancedController();
$costCenterController = new CostCenterController();
$whatsappController = new WhatsAppController();
$taxCalculationController = new TaxCalculationController();
$purchasePlanningController = new PurchasePlanningController();
$qualityControlController = new QualityControlController();
$supplierPerformanceController = new SupplierPerformanceController();
$currencyController = new CurrencyController();
$smartProcurementController = new SmartProcurementController();
$kitchenIntelligenceController = new KitchenIntelligenceController();
$customerIntelligenceController = new CustomerIntelligenceController();
$dynamicPricingController = new DynamicPricingController();
$wasteReductionController = new WasteReductionController();
$predictiveMaintenanceController = new PredictiveMaintenanceController();
$workOrderController = new WorkOrderController();
$equipmentHistoryController = new EquipmentHistoryController();
$offlineStatusController = new OfflineStatusController();
$kioskController = new KioskController();
$mobileOrderController = new MobileOrderController();
$consumerController = new ConsumerController();
$whatsAppOrderingController = new WhatsAppOrderingController();
$qualityComplianceController = new QualityComplianceController();
$userController = new UserController();
$settingController = new SettingController();
$reportController = new ReportController();
$tenantController = new TenantController();

// Loyalty controller instantiated per route to avoid dependency issues

// Auth Routes
$router->addRoute('POST', '/api/v1/auth/login', function($request) use ($authController) {
    return $authController->login($request);
});

// Public Menu Routes (without authentication)
$router->addRoute('GET', '/api/v1/public/menu/categories', function($request) use ($simpleMenuController) {
    return $simpleMenuController->getCategories($request);
});
$router->addRoute('GET', '/api/v1/public/menu/products', function($request) use ($simpleMenuController) {
    return $simpleMenuController->getProducts($request);
});

// Simple Orders Route (without middleware for testing)
$router->addRoute('GET', '/api/v1/public/orders', function($request) use ($simpleOrderController) {
    return $simpleOrderController->getAll($request);
});

// Simple Users Route (without middleware for testing)
$router->addRoute('GET', '/api/v1/public/users', function($request) use ($simpleUserController) {
    return $simpleUserController->getUsers($request);
});

// Simple Tables Route (without middleware for testing)
$router->addRoute('GET', '/api/v1/public/tables', function($request) use ($simpleTableController) {
    return $simpleTableController->getTables($request);
});

// Simple Inventory Routes (without middleware for testing)
$router->addRoute('GET', '/api/v1/public/inventory', function($request) use ($simpleInventoryController) {
    return $simpleInventoryController->getInventory($request);
});
$router->addRoute('GET', '/api/v1/public/inventory/low-stock', function($request) use ($simpleInventoryController) {
    return $simpleInventoryController->getLowStock($request);
});

// Simple Kitchen Routes (without middleware for testing)
$router->addRoute('GET', '/api/v1/public/kitchen/orders', function($request) use ($simpleKitchenController) {
    return $simpleKitchenController->getKitchenOrders($request);
});

// Simple Reservation Routes (without middleware for testing)
$router->addRoute('GET', '/api/v1/public/reservations', function($request) use ($simpleReservationController) {
    return $simpleReservationController->getReservations($request);
});

// Simple Customer Routes (without middleware for testing)
$router->addRoute('GET', '/api/v1/public/customers', function($request) use ($simpleCustomerController) {
    return $simpleCustomerController->getCustomers($request);
});

// Simple Employee Routes (without middleware for testing)
$router->addRoute('GET', '/api/v1/public/employees', function($request) use ($simpleEmployeeController) {
    return $simpleEmployeeController->getEmployees($request);
});

// Simple Delivery Routes (without middleware for testing)
$router->addRoute('GET', '/api/v1/public/deliveries', function($request) use ($simpleDeliveryController) {
    return $simpleDeliveryController->getDeliveries($request);
});

// Simple Supplier Routes (without middleware for testing)
$router->addRoute('GET', '/api/v1/public/suppliers', function($request) use ($simpleSupplierController) {
    return $simpleSupplierController->getSuppliers($request);
});

// Upload Routes
$router->addRoute('POST', '/api/v1/upload/image', function($request) use ($uploadController) {
    return $uploadController->uploadImage($request);
});
$router->addRoute('DELETE', '/api/v1/upload/image', function($request) use ($uploadController) {
    return $uploadController->deleteImage($request);
});

// Menu Routes (with authentication and permission check)
$router->addRoute('GET', '/api/v1/menu/categories', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->getCategories($request);
    },
    'MENU_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/menu/products', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->getProducts($request);
    },
    'MENU_VIEW',
    $permissionMiddleware,
    $authMiddleware
));

// Table Routes (with authentication and permission check)
$router->addRoute('GET', '/api/v1/tables', withAuthAndPermission(
    function($request) use ($tableController) {
        return $tableController->getTables($request);
    },
    'TABLE_VIEW',
    $permissionMiddleware,
    $authMiddleware
));

// Tenant Routes
$router->addRoute('POST', '/api/v1/tenant/register', function($request) use ($tenantController) {
    return $tenantController->register($request);
});
$router->addRoute('GET', '/api/v1/tenants', function($request) use ($tenantController) {
    return $tenantController->getTenants($request);
});
$router->addRoute('GET', '/api/v1/tenants/{id}', function($request) use ($tenantController) {
    return $tenantController->getTenant($request);
});
$router->addRoute('POST', '/api/v1/tenant/configure', function($request) use ($tenantController) {
    return $tenantController->configure($request);
});

// Sales Routes
$router->addRoute('POST', '/api/v1/sales/orders', function($request) use ($orderController) {
    return $orderController->create($request);
});

// Orders Routes (alias for frontend compatibility with permission check)
$router->addRoute('GET', '/api/v1/orders', withAuthAndPermission(
    function($request) use ($orderController) {
        return $orderController->getAll($request);
    },
    'ORDER_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/orders/recent', withAuthAndPermission(
    function($request) use ($orderController) {
        // Support limit and sort parameters
        $limit = $request['limit'] ?? 5;
        $sort = $request['sort'] ?? 'created_at';
        $order = $request['order'] ?? 'DESC';
        $request['limit'] = $limit;
        $request['sort'] = $sort;
        $request['order'] = $order;
        return $orderController->getAll($request);
    },
    'ORDER_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/orders/{id}', withAuthAndPermission(
    function($request) use ($orderController) {
        return $orderController->get($request);
    },
    'ORDER_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/orders', withAuthAndPermission(
    function($request) use ($orderController) {
        return $orderController->create($request);
    },
    'ORDER_CREATE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/orders/{id}', withAuthAndPermission(
    function($request) use ($orderController) {
        return $orderController->update($request);
    },
    'ORDER_EDIT',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/sales/orders/{id}', function($request) use ($orderController) {
    return $orderController->update($request);
});
$router->addRoute('POST', '/api/v1/sales/orders/{id}/close', function($request) use ($orderController) {
    return $orderController->close($request);
});
$router->addRoute('POST', '/api/v1/sales/orders/{id}/hold', function($request) use ($orderController) {
    return $orderController->hold($request);
});
$router->addRoute('POST', '/api/v1/sales/orders/{id}/recall', function($request) use ($orderController) {
    return $orderController->recall($request);
});
$router->addRoute('POST', '/api/v1/sales/orders/{id}/priority', function($request) use ($orderController) {
    return $orderController->setPriority($request);
});
$router->addRoute('POST', '/api/v1/sales/orders/{id}/split-bill', function($request) use ($orderController) {
    return $orderController->splitBill($request);
});
$router->addRoute('POST', '/api/v1/sales/orders/{id}/payment', function($request) use ($orderController) {
    return $orderController->addPayment($request);
});

// Payment Management Routes
$router->addRoute('POST', '/api/v1/sales/credit-notes', function($request) use ($paymentManagementController) {
    return $paymentManagementController->createCreditNote($request);
});
$router->addRoute('POST', '/api/v1/sales/vouchers', function($request) use ($paymentManagementController) {
    return $paymentManagementController->createVoucher($request);
});
$router->addRoute('POST', '/api/v1/sales/vouchers/apply', function($request) use ($paymentManagementController) {
    return $paymentManagementController->applyVoucher($request);
});
$router->addRoute('POST', '/api/v1/sales/cash-drawers/{id}/open', function($request) use ($paymentManagementController) {
    return $paymentManagementController->openCashDrawer($request);
});
$router->addRoute('POST', '/api/v1/sales/cash-drawers/{id}/close', function($request) use ($paymentManagementController) {
    return $paymentManagementController->closeCashDrawer($request);
});

// Menu Routes - Categories (with permission check)
$router->addRoute('GET', '/api/v1/menu/categories', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->getCategories($request);
    },
    'MENU_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/menu/categories/{id}', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->getCategory($request);
    },
    'MENU_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/menu/categories', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->createCategory($request);
    },
    'MENU_CREATE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/menu/categories/{id}', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->updateCategory($request);
    },
    'MENU_EDIT',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('DELETE', '/api/v1/menu/categories/{id}', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->deleteCategory($request);
    },
    'MENU_DELETE',
    $permissionMiddleware,
    $authMiddleware
));

// Menu Routes - Products (with permission check)
$router->addRoute('GET', '/api/v1/menu/products', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->getProducts($request);
    },
    'MENU_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/menu/products/{id}', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->getProduct($request);
    },
    'MENU_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/menu/products', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->createProduct($request);
    },
    'MENU_CREATE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/menu/products/{id}', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->updateProduct($request);
    },
    'MENU_EDIT',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('DELETE', '/api/v1/menu/products/{id}', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->deleteProduct($request);
    },
    'MENU_DELETE',
    $permissionMiddleware,
    $authMiddleware
));

// Menu Routes - Product Variants
$router->addRoute('POST', '/api/v1/menu/products/{id}/variants', function($request) use ($productVariantController) {
    return $productVariantController->create($request);
});
$router->addRoute('GET', '/api/v1/menu/products/{id}/variants', function($request) use ($productVariantController) {
    return $productVariantController->getByProduct($request);
});
$router->addRoute('PUT', '/api/v1/menu/variants/{id}', function($request) use ($productVariantController) {
    return $productVariantController->update($request);
});
$router->addRoute('DELETE', '/api/v1/menu/variants/{id}', function($request) use ($productVariantController) {
    return $productVariantController->delete($request);
});

// Menu Routes - Product Modifiers
$router->addRoute('POST', '/api/v1/menu/modifier-groups', function($request) use ($productModifierController) {
    return $productModifierController->createGroup($request);
});
$router->addRoute('GET', '/api/v1/menu/modifier-groups', function($request) use ($productModifierController) {
    return $productModifierController->getGroups($request);
});
$router->addRoute('POST', '/api/v1/menu/modifier-groups/{id}/modifiers', function($request) use ($productModifierController) {
    return $productModifierController->createModifier($request);
});
$router->addRoute('GET', '/api/v1/menu/modifier-groups/{id}/modifiers', function($request) use ($productModifierController) {
    return $productModifierController->getModifiersByGroup($request);
});
$router->addRoute('POST', '/api/v1/menu/products/{id}/modifiers', function($request) use ($productModifierController) {
    return $productModifierController->assignToProduct($request);
});
$router->addRoute('GET', '/api/v1/menu/products/{id}/modifiers', function($request) use ($productModifierController) {
    return $productModifierController->getProductModifiers($request);
});

// Menu Routes - Combos
$router->addRoute('POST', '/api/v1/menu/combos', function($request) use ($comboController) {
    return $comboController->create($request);
});
$router->addRoute('GET', '/api/v1/menu/combos', function($request) use ($comboController) {
    return $comboController->getAll($request);
});
$router->addRoute('POST', '/api/v1/menu/combos/{id}/calculate-price', function($request) use ($comboController) {
    return $comboController->calculatePrice($request);
});

// Menu Routes - Recipes
$router->addRoute('GET', '/api/v1/menu/recipes', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->getRecipes($request);
    },
    'MENU_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/menu/recipes/{id}', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->getRecipe($request);
    },
    'MENU_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/menu/recipes', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->createRecipe($request);
    },
    'MENU_CREATE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/menu/recipes/{id}', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->updateRecipe($request);
    },
    'MENU_EDIT',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('DELETE', '/api/v1/menu/recipes/{id}', withAuthAndPermission(
    function($request) use ($menuController) {
        return $menuController->deleteRecipe($request);
    },
    'MENU_DELETE',
    $permissionMiddleware,
    $authMiddleware
));

// Recipe Management Routes (New Module)
$router->addRoute('GET', '/api/v1/recipes', withAuth(
    function($request) use ($recipeController) {
        return $recipeController->index($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/recipes/{id}', withAuth(
    function($request) use ($recipeController) {
        return $recipeController->show($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/recipes', withAuth(
    function($request) use ($recipeController) {
        return $recipeController->create($request);
    },
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/recipes/{id}', withAuth(
    function($request) use ($recipeController) {
        return $recipeController->update($request);
    },
    $authMiddleware
));
$router->addRoute('DELETE', '/api/v1/recipes/{id}', withAuth(
    function($request) use ($recipeController) {
        return $recipeController->delete($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/recipes/{id}/cost-analysis', withAuth(
    function($request) use ($recipeController) {
        return $recipeController->costAnalysis($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/recipes/{id}/clone', withAuth(
    function($request) use ($recipeController) {
        return $recipeController->clone($request);
    },
    $authMiddleware
));

// Menu Engineering Routes
$router->addRoute('GET', '/api/v1/menu-engineering/profitability/{product_id}', withAuth(
    function($request) use ($menuEngineeringController) {
        return $menuEngineeringController->getProfitability($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/menu-engineering/menu-mix', withAuth(
    function($request) use ($menuEngineeringController) {
        return $menuEngineeringController->getMenuMix($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/menu-engineering/category-performance', withAuth(
    function($request) use ($menuEngineeringController) {
        return $menuEngineeringController->getCategoryPerformance($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/menu-engineering/recommendations', withAuth(
    function($request) use ($menuEngineeringController) {
        return $menuEngineeringController->getRecommendations($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/menu-engineering/food-cost-variance', withAuth(
    function($request) use ($menuEngineeringController) {
        return $menuEngineeringController->getFoodCostVariance($request);
    },
    $authMiddleware
));

// Food Waste Routes
$router->addRoute('POST', '/api/v1/food-waste', withAuth(
    function($request) use ($foodWasteController) {
        return $foodWasteController->create($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/food-waste', withAuth(
    function($request) use ($foodWasteController) {
        return $foodWasteController->index($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/food-waste/analysis', withAuth(
    function($request) use ($foodWasteController) {
        return $foodWasteController->analysis($request);
    },
    $authMiddleware
));

// Staff Scheduling Routes
$router->addRoute('POST', '/api/v1/staff-scheduling/shifts', withAuth(
    function($request) use ($staffSchedulingController) {
        return $staffSchedulingController->createShift($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/staff-scheduling/shifts', withAuth(
    function($request) use ($staffSchedulingController) {
        return $staffSchedulingController->getShifts($request);
    },
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/staff-scheduling/schedules', withAuth(
    function($request) use ($staffSchedulingController) {
        return $staffSchedulingController->createSchedule($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/staff-scheduling/schedules', withAuth(
    function($request) use ($staffSchedulingController) {
        return $staffSchedulingController->getSchedules($request);
    },
    $authMiddleware
));

// Tip Management Routes
$router->addRoute('POST', '/api/v1/tips', withAuth(
    function($request) use ($tipManagementController) {
        return $tipManagementController->create($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/tips', withAuth(
    function($request) use ($tipManagementController) {
        return $tipManagementController->index($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/tips/summary', withAuth(
    function($request) use ($tipManagementController) {
        return $tipManagementController->summary($request);
    },
    $authMiddleware
));

// Daily Reports Routes
$router->addRoute('GET', '/api/v1/daily-reports/sales', withAuth(
    function($request) use ($dailyReportsController) {
        return $dailyReportsController->getSalesReport($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/daily-reports/table-turnover', withAuth(
    function($request) use ($dailyReportsController) {
        return $dailyReportsController->getTableTurnover($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/daily-reports/server-performance', withAuth(
    function($request) use ($dailyReportsController) {
        return $dailyReportsController->getServerPerformance($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/daily-reports/peak-hours', withAuth(
    function($request) use ($dailyReportsController) {
        return $dailyReportsController->getPeakHours($request);
    },
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/daily-reports/comprehensive', withAuth(
    function($request) use ($dailyReportsController) {
        return $dailyReportsController->getComprehensive($request);
    },
    $authMiddleware
));

// Table Routes
$router->addRoute('GET', '/api/v1/tables', withAuthAndPermission(
    function($request) use ($tableController) {
        return $tableController->getTables($request);
    },
    'TABLE_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/tables/available', withAuthAndPermission(
    function($request) use ($tableController) {
        return $tableController->getAvailableTables($request);
    },
    'TABLE_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/tables/{id}', withAuthAndPermission(
    function($request) use ($tableController) {
        return $tableController->getTable($request);
    },
    'TABLE_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/tables', withAuthAndPermission(
    function($request) use ($tableController) {
        return $tableController->createTable($request);
    },
    'TABLE_CREATE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/tables/{id}', withAuthAndPermission(
    function($request) use ($tableController) {
        return $tableController->updateTable($request);
    },
    'TABLE_EDIT',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PATCH', '/api/v1/tables/{id}/status', withAuthAndPermission(
    function($request) use ($tableController) {
        return $tableController->updateTableStatus($request);
    },
    'TABLE_EDIT',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('DELETE', '/api/v1/tables/{id}', withAuthAndPermission(
    function($request) use ($tableController) {
        return $tableController->deleteTable($request);
    },
    'TABLE_DELETE',
    $permissionMiddleware,
    $authMiddleware
));

// Reservation Routes
$router->addRoute('GET', '/api/v1/reservations', withAuthAndPermission(
    function($request) use ($reservationController) {
        return $reservationController->getReservations($request);
    },
    'RESERVATION_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reservations/date/{date}', withAuthAndPermission(
    function($request) use ($reservationController) {
        return $reservationController->getReservationsByDate($request);
    },
    'RESERVATION_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reservations/{id}', withAuthAndPermission(
    function($request) use ($reservationController) {
        return $reservationController->getReservation($request);
    },
    'RESERVATION_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reservations/check-availability', withAuthAndPermission(
    function($request) use ($reservationController) {
        return $reservationController->checkAvailability($request);
    },
    'RESERVATION_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/reservations', withAuthAndPermission(
    function($request) use ($reservationController) {
        return $reservationController->createReservation($request);
    },
    'RESERVATION_CREATE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/reservations/{id}', withAuthAndPermission(
    function($request) use ($reservationController) {
        return $reservationController->updateReservation($request);
    },
    'RESERVATION_EDIT',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PATCH', '/api/v1/reservations/{id}/status', withAuthAndPermission(
    function($request) use ($reservationController) {
        return $reservationController->updateReservationStatus($request);
    },
    'RESERVATION_EDIT',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('DELETE', '/api/v1/reservations/{id}', withAuthAndPermission(
    function($request) use ($reservationController) {
        return $reservationController->deleteReservation($request);
    },
    'RESERVATION_DELETE',
    $permissionMiddleware,
    $authMiddleware
));

// Location Routes
$router->addRoute('POST', '/api/v1/location/nearby-branches', function($request) use ($locationController) {
    return $locationController->findNearbyBranches($request);
});
$router->addRoute('POST', '/api/v1/location/detect-branch', function($request) use ($locationController) {
    return $locationController->detectNearbyBranch($request);
});
$router->addRoute('POST', '/api/v1/location/branches/{id}/delivery-check', function($request) use ($locationController) {
    return $locationController->checkDeliveryAvailability($request);
});
$router->addRoute('GET', '/api/v1/location/branches/{id}', function($request) use ($locationController) {
    return $locationController->getBranchLocation($request);
});
$router->addRoute('PUT', '/api/v1/location/branches/{id}', function($request) use ($locationController) {
    return $locationController->updateBranchLocation($request);
});

// Inventory Routes
$router->addRoute('GET', '/api/v1/inventory', withAuthAndPermission(
    function($request) use ($inventoryController) {
        return $inventoryController->getInventory($request);
    },
    'INVENTORY_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/inventory/low-stock', withAuthAndPermission(
    function($request) use ($inventoryController) {
        return $inventoryController->getLowStock($request);
    },
    'INVENTORY_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/inventory/{id}', withAuthAndPermission(
    function($request) use ($inventoryController) {
        return $inventoryController->getInventoryItem($request);
    },
    'INVENTORY_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/inventory', withAuthAndPermission(
    function($request) use ($inventoryController) {
        return $inventoryController->createInventory($request);
    },
    'INVENTORY_CREATE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/inventory/{id}', withAuthAndPermission(
    function($request) use ($inventoryController) {
        return $inventoryController->updateInventory($request);
    },
    'INVENTORY_EDIT',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/inventory/adjust', withAuthAndPermission(
    function($request) use ($inventoryController) {
        return $inventoryController->adjustStock($request);
    },
    'INVENTORY_EDIT',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('DELETE', '/api/v1/inventory/{id}', withAuthAndPermission(
    function($request) use ($inventoryController) {
        return $inventoryController->deleteInventory($request);
    },
    'INVENTORY_DELETE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/inventory/transactions', withAuthAndPermission(
    function($request) use ($inventoryController) {
        return $inventoryController->getTransactions($request);
    },
    'INVENTORY_VIEW',
    $permissionMiddleware,
    $authMiddleware
));

// Supplier Routes
$router->addRoute('POST', '/api/v1/inventory/suppliers', function($request) use ($supplierController) {
    return $supplierController->create($request);
});
$router->addRoute('GET', '/api/v1/inventory/suppliers', function($request) use ($supplierController) {
    return $supplierController->getAll($request);
});
$router->addRoute('PUT', '/api/v1/inventory/suppliers/{id}', function($request) use ($supplierController) {
    return $supplierController->update($request);
});
$router->addRoute('DELETE', '/api/v1/inventory/suppliers/{id}', function($request) use ($supplierController) {
    return $supplierController->delete($request);
});

// Stock Adjustment Routes
$router->addRoute('POST', '/api/v1/inventory/stock-adjustments', function($request) use ($stockAdjustmentController) {
    return $stockAdjustmentController->create($request);
});
$router->addRoute('GET', '/api/v1/inventory/stock-adjustments', function($request) use ($stockAdjustmentController) {
    return $stockAdjustmentController->getAll($request);
});
$router->addRoute('POST', '/api/v1/inventory/stock-adjustments/{id}/approve', function($request) use ($stockAdjustmentController) {
    return $stockAdjustmentController->approve($request);
});

// Stock Opname Routes
$router->addRoute('POST', '/api/v1/inventory/stock-opname', function($request) use ($stockOpnameController) {
    return $stockOpnameController->create($request);
});
$router->addRoute('GET', '/api/v1/inventory/stock-opname', function($request) use ($stockOpnameController) {
    return $stockOpnameController->getAll($request);
});
$router->addRoute('POST', '/api/v1/inventory/stock-opname/{id}/items', function($request) use ($stockOpnameController) {
    return $stockOpnameController->addItem($request);
});
$router->addRoute('POST', '/api/v1/inventory/stock-opname/{id}/complete', function($request) use ($stockOpnameController) {
    return $stockOpnameController->complete($request);
});

// Purchase Order Routes
$router->addRoute('POST', '/api/v1/inventory/purchase-orders', function($request) use ($purchaseOrderController) {
    return $purchaseOrderController->create($request);
});
$router->addRoute('GET', '/api/v1/inventory/purchase-orders', function($request) use ($purchaseOrderController) {
    return $purchaseOrderController->getAll($request);
});
$router->addRoute('POST', '/api/v1/inventory/purchase-orders/{id}/approve', function($request) use ($purchaseOrderController) {
    return $purchaseOrderController->approve($request);
});

// Goods Receipt Routes
$router->addRoute('POST', '/api/v1/inventory/goods-receipts', function($request) use ($goodsReceiptController) {
    return $goodsReceiptController->create($request);
});
$router->addRoute('GET', '/api/v1/inventory/goods-receipts', function($request) use ($goodsReceiptController) {
    return $goodsReceiptController->getAll($request);
});
$router->addRoute('POST', '/api/v1/inventory/goods-receipts/{id}/complete', function($request) use ($goodsReceiptController) {
    return $goodsReceiptController->complete($request);
});

// CRM Routes
$router->addRoute('POST', '/api/v1/crm/customers', function($request) use ($customerController) {
    return $customerController->create($request);
});
$router->addRoute('GET', '/api/v1/crm/customers', function($request) use ($customerController) {
    return $customerController->getAll($request);
});
$router->addRoute('PUT', '/api/v1/crm/customers/{id}', function($request) use ($customerController) {
    return $customerController->update($request);
});
$router->addRoute('POST', '/api/v1/crm/customers/{id}/loyalty-points', function($request) use ($customerController) {
    return $customerController->addLoyaltyPoints($request);
});
$router->addRoute('POST', '/api/v1/crm/customers/{id}/visit', function($request) use ($customerController) {
    return $customerController->recordVisit($request);
});

// Kitchen Routes
$router->addRoute('GET', '/api/v1/kitchen/orders', withAuthAndPermission(
    function($request) use ($kitchenController) {
        return $kitchenController->getKitchenOrders($request);
    },
    'KITCHEN_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/kitchen/orders/pending', withAuthAndPermission(
    function($request) use ($kitchenController) {
        return $kitchenController->getPendingOrders($request);
    },
    'KITCHEN_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/kitchen/orders/in-progress', withAuthAndPermission(
    function($request) use ($kitchenController) {
        return $kitchenController->getInProgressOrders($request);
    },
    'KITCHEN_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/kitchen/orders/ready', withAuthAndPermission(
    function($request) use ($kitchenController) {
        return $kitchenController->getReadyOrders($request);
    },
    'KITCHEN_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/kitchen/orders/{id}', withAuthAndPermission(
    function($request) use ($kitchenController) {
        return $kitchenController->getKitchenOrder($request);
    },
    'KITCHEN_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/kitchen/orders', withAuthAndPermission(
    function($request) use ($kitchenController) {
        return $kitchenController->createKitchenOrder($request);
    },
    'KITCHEN_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PATCH', '/api/v1/kitchen/orders/{id}/status', withAuthAndPermission(
    function($request) use ($kitchenController) {
        return $kitchenController->updateKitchenOrderStatus($request);
    },
    'KITCHEN_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PATCH', '/api/v1/kitchen/orders/{id}/priority', withAuthAndPermission(
    function($request) use ($kitchenController) {
        return $kitchenController->updateKitchenOrderPriority($request);
    },
    'KITCHEN_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PATCH', '/api/v1/kitchen/items/{id}/status', withAuthAndPermission(
    function($request) use ($kitchenController) {
        return $kitchenController->updateKitchenItemStatus($request);
    },
    'KITCHEN_VIEW',
    $permissionMiddleware,
    $authMiddleware
));

// User Routes
$router->addRoute('GET', '/api/v1/users', withAuthAndPermission(
    function($request) use ($userController) {
        return $userController->getUsers($request);
    },
    'USER_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/users/{id}', withAuthAndPermission(
    function($request) use ($userController) {
        return $userController->getUser($request);
    },
    'USER_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/users', withAuthAndPermission(
    function($request) use ($userController) {
        return $userController->createUser($request);
    },
    'USER_CREATE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/users/{id}', withAuthAndPermission(
    function($request) use ($userController) {
        return $userController->updateUser($request);
    },
    'USER_EDIT',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/users/{id}/change-password', withAuthAndPermission(
    function($request) use ($userController) {
        return $userController->changePassword($request);
    },
    'USER_EDIT',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('DELETE', '/api/v1/users/{id}', withAuthAndPermission(
    function($request) use ($userController) {
        return $userController->deleteUser($request);
    },
    'USER_DELETE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/users/with-role', withAuthAndPermission(
    function($request) use ($userController) {
        return $userController->createUserWithRole($request);
    },
    'USER_CREATE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/users/roles', withAuthAndPermission(
    function($request) use ($userController) {
        return $userController->getAvailableRoles($request);
    },
    'USER_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/users/{id}/permissions', withAuthAndPermission(
    function($request) use ($userController) {
        return $userController->getUserPermissions($request);
    },
    'USER_VIEW',
    $permissionMiddleware,
    $authMiddleware
));

// Settings Routes
$router->addRoute('GET', '/api/v1/settings', withAuthAndPermission(
    function($request) use ($settingController) {
        return $settingController->getSettings($request);
    },
    'SETTINGS_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/settings/{key}', withAuthAndPermission(
    function($request) use ($settingController) {
        return $settingController->getSetting($request);
    },
    'SETTINGS_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/settings/group/{prefix}', withAuthAndPermission(
    function($request) use ($settingController) {
        return $settingController->getSettingGroup($request);
    },
    'SETTINGS_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/settings', withAuthAndPermission(
    function($request) use ($settingController) {
        return $settingController->createSetting($request);
    },
    'SETTINGS_MANAGE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/settings/{id}', withAuthAndPermission(
    function($request) use ($settingController) {
        return $settingController->updateSetting($request);
    },
    'SETTINGS_MANAGE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/settings/upsert', withAuthAndPermission(
    function($request) use ($settingController) {
        return $settingController->upsertSetting($request);
    },
    'SETTINGS_MANAGE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('DELETE', '/api/v1/settings/{id}', withAuthAndPermission(
    function($request) use ($settingController) {
        return $settingController->deleteSetting($request);
    },
    'SETTINGS_MANAGE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/settings/initialize', withAuthAndPermission(
    function($request) use ($settingController) {
        return $settingController->initializeSettings($request);
    },
    'SETTINGS_MANAGE',
    $permissionMiddleware,
    $authMiddleware
));

// Report Routes
$router->addRoute('GET', '/api/v1/reports/sales', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getSalesReport($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/top-products', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getTopSellingProducts($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/inventory', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getInventoryReport($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/stock-movement', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getStockMovementReport($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/kitchen-performance', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getKitchenPerformanceReport($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/reservations', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getReservationReport($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/financial', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getFinancialReport($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/dashboard', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getDashboardSummary($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/profit-loss', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getProfitLossReport($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/cost-analysis', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getCostAnalysisReport($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/food-cost', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getFoodCostPercentage($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/sales-by-hour', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getSalesByHour($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/payment-breakdown', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getPaymentMethodBreakdown($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/inventory-usage', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getInventoryUsageReport($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/labor-cost', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getLaborCostAnalysis($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/reports/tax', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->getTaxReport($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));

// AI Routes
$router->addRoute('POST', '/api/v1/ai/sales-forecast', function($request) use ($aiController) {
    return $aiController->generateSalesForecast($request);
});
$router->addRoute('POST', '/api/v1/ai/inventory-prediction/{id}', function($request) use ($aiController) {
    return $aiController->generateInventoryPrediction($request);
});

// Delivery Routes
$router->addRoute('POST', '/api/v1/delivery/orders', function($request) use ($deliveryController) {
    return $deliveryController->create($request);
});
$router->addRoute('GET', '/api/v1/delivery/orders', function($request) use ($deliveryController) {
    return $deliveryController->getAll($request);
});
$router->addRoute('POST', '/api/v1/delivery/orders/{id}/assign-driver', function($request) use ($deliveryController) {
    return $deliveryController->assignDriver($request);
});
$router->addRoute('POST', '/api/v1/delivery/orders/{id}/status', function($request) use ($deliveryController) {
    return $deliveryController->updateStatus($request);
});

// HR Routes
$router->addRoute('POST', '/api/v1/hr/employees', function($request) use ($employeeController) {
    return $employeeController->create($request);
});
$router->addRoute('GET', '/api/v1/hr/employees', function($request) use ($employeeController) {
    return $employeeController->getAll($request);
});
$router->addRoute('POST', '/api/v1/hr/employees/{id}/attendance', function($request) use ($employeeController) {
    return $employeeController->recordAttendance($request);
});
$router->addRoute('POST', '/api/v1/hr/payroll/calculate', function($request) use ($employeeController) {
    return $employeeController->calculatePayroll($request);
});

// Accounting Routes
$router->addRoute('POST', '/api/v1/accounting/journal-entries', function($request) use ($accountingController) {
    return $accountingController->createJournalEntry($request);
});
$router->addRoute('GET', '/api/v1/accounting/trial-balance', function($request) use ($accountingController) {
    return $accountingController->getTrialBalance($request);
});
$router->addRoute('GET', '/api/v1/accounting/balance-sheet', function($request) use ($accountingController) {
    return $accountingController->getBalanceSheet($request);
});
$router->addRoute('GET', '/api/v1/accounting/profit-loss', function($request) use ($accountingController) {
    return $accountingController->getProfitLoss($request);
});

// General Ledger Routes
$router->addRoute('GET', '/api/v1/accounting/general-ledger', function($request) use ($generalLedgerController) {
    return $generalLedgerController->getLedger($request);
});
$router->addRoute('GET', '/api/v1/accounting/general-ledger/accounts/{id}/balance', function($request) use ($generalLedgerController) {
    return $generalLedgerController->getAccountBalance($request);
});
$router->addRoute('GET', '/api/v1/accounting/cash-flow', function($request) use ($generalLedgerController) {
    return $generalLedgerController->getCashFlowStatement($request);
});

// Accounts Receivable Routes
$router->addRoute('POST', '/api/v1/accounting/accounts-receivable', function($request) use ($accountsReceivableController) {
    return $accountsReceivableController->createInvoice($request);
});
$router->addRoute('GET', '/api/v1/accounting/accounts-receivable', function($request) use ($accountsReceivableController) {
    return $accountsReceivableController->getInvoices($request);
});
$router->addRoute('POST', '/api/v1/accounting/accounts-receivable/invoices', function($request) use ($accountsReceivableController) {
    return $accountsReceivableController->createInvoice($request);
});
$router->addRoute('GET', '/api/v1/accounting/accounts-receivable/invoices', function($request) use ($accountsReceivableController) {
    return $accountsReceivableController->getInvoices($request);
});
$router->addRoute('GET', '/api/v1/accounting/accounts-receivable/invoices/{id}', function($request) use ($accountsReceivableController) {
    return $accountsReceivableController->getInvoice($request);
});
$router->addRoute('POST', '/api/v1/accounting/accounts-receivable/payments', function($request) use ($accountsReceivableController) {
    return $accountsReceivableController->addPayment($request);
});
$router->addRoute('GET', '/api/v1/accounting/accounts-receivable/aging-report', function($request) use ($accountsReceivableController) {
    return $accountsReceivableController->getAgingReport($request);
});

// Accounts Payable Routes
$router->addRoute('POST', '/api/v1/accounting/accounts-payable', function($request) use ($accountsPayableController) {
    return $accountsPayableController->createBill($request);
});
$router->addRoute('GET', '/api/v1/accounting/accounts-payable', function($request) use ($accountsPayableController) {
    return $accountsPayableController->getBills($request);
});
$router->addRoute('POST', '/api/v1/accounting/accounts-payable/bills', function($request) use ($accountsPayableController) {
    return $accountsPayableController->createBill($request);
});
$router->addRoute('GET', '/api/v1/accounting/accounts-payable/bills', function($request) use ($accountsPayableController) {
    return $accountsPayableController->getBills($request);
});
$router->addRoute('GET', '/api/v1/accounting/accounts-payable/bills/{id}', function($request) use ($accountsPayableController) {
    return $accountsPayableController->getBill($request);
});
$router->addRoute('POST', '/api/v1/accounting/accounts-payable/payments', function($request) use ($accountsPayableController) {
    return $accountsPayableController->addPayment($request);
});
$router->addRoute('GET', '/api/v1/accounting/accounts-payable/aging-report', function($request) use ($accountsPayableController) {
    return $accountsPayableController->getAgingReport($request);
});

// Bank Reconciliation Routes
$router->addRoute('POST', '/api/v1/accounting/bank-reconciliations', function($request) use ($bankReconciliationController) {
    return $bankReconciliationController->createReconciliation($request);
});
$router->addRoute('GET', '/api/v1/accounting/bank-reconciliations', function($request) use ($bankReconciliationController) {
    return $bankReconciliationController->getReconciliations($request);
});
$router->addRoute('GET', '/api/v1/accounting/bank-reconciliations/{id}', function($request) use ($bankReconciliationController) {
    return $bankReconciliationController->getReconciliation($request);
});
$router->addRoute('POST', '/api/v1/accounting/bank-reconciliations/items', function($request) use ($bankReconciliationController) {
    return $bankReconciliationController->addItem($request);
});
$router->addRoute('POST', '/api/v1/accounting/bank-reconciliations/{id}/reconcile', function($request) use ($bankReconciliationController) {
    return $bankReconciliationController->reconcile($request);
});
$router->addRoute('GET', '/api/v1/accounting/bank-accounts', function($request) use ($bankReconciliationController) {
    return $bankReconciliationController->getBankAccounts($request);
});
$router->addRoute('POST', '/api/v1/accounting/bank-accounts', function($request) use ($bankReconciliationController) {
    return $bankReconciliationController->createBankAccount($request);
});

// Fixed Assets Routes
$router->addRoute('POST', '/api/v1/accounting/fixed-assets', function($request) use ($fixedAssetsController) {
    return $fixedAssetsController->createAsset($request);
});
$router->addRoute('GET', '/api/v1/accounting/fixed-assets', function($request) use ($fixedAssetsController) {
    return $fixedAssetsController->getAssets($request);
});
$router->addRoute('GET', '/api/v1/accounting/fixed-assets/{id}', function($request) use ($fixedAssetsController) {
    return $fixedAssetsController->getAsset($request);
});
$router->addRoute('POST', '/api/v1/accounting/fixed-assets/depreciation', function($request) use ($fixedAssetsController) {
    return $fixedAssetsController->calculateDepreciation($request);
});
$router->addRoute('GET', '/api/v1/accounting/fixed-assets/{id}/depreciation-schedule', function($request) use ($fixedAssetsController) {
    return $fixedAssetsController->getDepreciationSchedule($request);
});
$router->addRoute('POST', '/api/v1/accounting/fixed-assets/{id}/dispose', function($request) use ($fixedAssetsController) {
    return $fixedAssetsController->disposeAsset($request);
});

// Budget Management Routes
$router->addRoute('POST', '/api/v1/accounting/budgets', function($request) use ($budgetController) {
    return $budgetController->createBudget($request);
});
$router->addRoute('GET', '/api/v1/accounting/budgets', function($request) use ($budgetController) {
    return $budgetController->getBudgets($request);
});
$router->addRoute('GET', '/api/v1/accounting/budgets/{id}', function($request) use ($budgetController) {
    return $budgetController->getBudget($request);
});
$router->addRoute('POST', '/api/v1/accounting/budgets/items', function($request) use ($budgetController) {
    return $budgetController->addBudgetItem($request);
});
$router->addRoute('POST', '/api/v1/accounting/budgets/{id}/approve', function($request) use ($budgetController) {
    return $budgetController->approveBudget($request);
});
$router->addRoute('GET', '/api/v1/accounting/budgets/{id}/variance', function($request) use ($budgetController) {
    return $budgetController->getBudgetVariance($request);
});

// Accounting Period Routes
$router->addRoute('POST', '/api/v1/accounting/periods', function($request) use ($accountingPeriodController) {
    return $accountingPeriodController->createPeriod($request);
});
$router->addRoute('GET', '/api/v1/accounting/periods', function($request) use ($accountingPeriodController) {
    return $accountingPeriodController->getPeriods($request);
});
$router->addRoute('GET', '/api/v1/accounting/periods/current', function($request) use ($accountingPeriodController) {
    return $accountingPeriodController->getCurrentPeriod($request);
});
$router->addRoute('POST', '/api/v1/accounting/periods/{id}/close', function($request) use ($accountingPeriodController) {
    return $accountingPeriodController->closePeriod($request);
});
$router->addRoute('POST', '/api/v1/accounting/periods/{id}/reopen', function($request) use ($accountingPeriodController) {
    return $accountingPeriodController->reopenPeriod($request);
});

// Account Suggestion Routes
if (!class_exists('AccountSuggestionService')) {
    require_once __DIR__ . '/../modules/Accounting/Services/AccountSuggestionService.php';
}
$accountSuggestionService = new AccountSuggestionService();

$router->addRoute('GET', '/api/v1/accounting/suggest-accounts', function($request) use ($accountSuggestionService) {
    $authMiddleware = new AuthMiddleware();
    $user = $authMiddleware->authenticate();
    $transactionType = $_GET['transaction_type'] ?? null;
    $description = $_GET['description'] ?? null;
    $amount = $_GET['amount'] ?? null;
    $result = $accountSuggestionService->suggestAccounts($transactionType, $description, $amount);
    if ($result['success']) {
        Response::success($result['suggestions'], $result['message'] ?? 'Account suggestions retrieved successfully');
    } else {
        Response::error($result['message'] ?? 'Failed to get account suggestions');
    }
});

$router->addRoute('GET', '/api/v1/accounting/accounts/search', function($request) use ($accountSuggestionService) {
    $authMiddleware = new AuthMiddleware();
    $user = $authMiddleware->authenticate();
    $searchTerm = $_GET['search_term'] ?? null;
    $accountType = $_GET['account_type'] ?? null;
    $result = $accountSuggestionService->searchAccounts($user['tenant_id'], $searchTerm, $accountType);
    Response::success($result, 'Accounts retrieved successfully');
});

$router->addRoute('GET', '/api/v1/accounting/journal-templates', function($request) use ($accountSuggestionService) {
    $authMiddleware = new AuthMiddleware();
    $user = $authMiddleware->authenticate();
    $templates = $accountSuggestionService->getJournalTemplates();
    Response::success($templates, 'Journal templates retrieved successfully');
});

// Currency Service Routes
if (!class_exists('ExchangeRateService')) {
    require_once __DIR__ . '/../core/CurrencyService.php';
}
$currencyService = new ExchangeRateService();

$router->addRoute('GET', '/api/v1/accounting/currencies', function($request) use ($currencyService) {
    $authMiddleware = new AuthMiddleware();
    $user = $authMiddleware->authenticate();
    $currencies = $currencyService->getCurrencies();
    Response::success($currencies, 'Currencies retrieved successfully');
});

$router->addRoute('POST', '/api/v1/accounting/exchange-rates', function($request) use ($currencyService) {
    $authMiddleware = new AuthMiddleware();
    $user = $authMiddleware->authenticate();
    $data = $request['body'] ?? [];
    $result = $currencyService->setExchangeRate($user['tenant_id'], $data['from_currency'], $data['to_currency'], $data['rate'], $data['effective_date'], $user['user_id']);
    if ($result['success']) {
        Response::success([], $result['message']);
    } else {
        Response::error($result['message']);
    }
});

$router->addRoute('GET', '/api/v1/accounting/exchange-rates', function($request) use ($currencyService) {
    $authMiddleware = new AuthMiddleware();
    $user = $authMiddleware->authenticate();
    $fromCurrency = $_GET['from_currency'] ?? null;
    $toCurrency = $_GET['to_currency'] ?? null;
    $rates = $currencyService->getExchangeRates($user['tenant_id'], $fromCurrency, $toCurrency);
    Response::success($rates, 'Exchange rates retrieved successfully');
});

$router->addRoute('GET', '/api/v1/accounting/exchange-rates/latest', function($request) use ($currencyService) {
    $authMiddleware = new AuthMiddleware();
    $user = $authMiddleware->authenticate();
    $rates = $currencyService->getLatestExchangeRates($user['tenant_id']);
    Response::success($rates, 'Latest exchange rates retrieved successfully');
});

// Report Queue Routes
if (!class_exists('ReportQueueService')) {
    require_once __DIR__ . '/../core/ReportQueueService.php';
}
$reportQueueService = new ReportQueueService();

$router->addRoute('POST', '/api/v1/accounting/reports/enqueue', function($request) use ($reportQueueService) {
    $authMiddleware = new AuthMiddleware();
    $user = $authMiddleware->authenticate();
    $data = $request['body'] ?? [];
    $result = $reportQueueService->enqueueReport($user['tenant_id'], $user['branch_id'], $user['user_id'], $data['report_type'], $data['report_name'], $data['parameters'] ?? [], $data['priority'] ?? 0);
    if ($result['success']) {
        Response::success($result, $result['message']);
    } else {
        Response::error($result['message']);
    }
});

$router->addRoute('GET', '/api/v1/accounting/reports/jobs', function($request) use ($reportQueueService) {
    $authMiddleware = new AuthMiddleware();
    $user = $authMiddleware->authenticate();
    $jobs = $reportQueueService->getUserReportJobs($user['user_id']);
    Response::success($jobs, 'Report jobs retrieved successfully');
});

$router->addRoute('GET', '/api/v1/accounting/reports/jobs/{id}', function($request) use ($reportQueueService) {
    $authMiddleware = new AuthMiddleware();
    $user = $authMiddleware->authenticate();
    $jobId = $request['params']['id'];
    $job = $reportQueueService->getReportJob($jobId);
    Response::success($job, 'Report job retrieved successfully');
});

// Supply Chain Routes
$router->addRoute('POST', '/api/v1/supply-chain/requisitions', function($request) use ($supplyChainController) {
    return $supplyChainController->createRequisition($request);
});
$router->addRoute('GET', '/api/v1/supply-chain/requisitions', function($request) use ($supplyChainController) {
    return $supplyChainController->getAll($request);
});
$router->addRoute('POST', '/api/v1/supply-chain/requisitions/{id}/approve', function($request) use ($supplyChainController) {
    return $supplyChainController->approveRequisition($request);
});

// Maintenance Routes
$router->addRoute('POST', '/api/v1/maintenance/assets', function($request) use ($maintenanceController) {
    return $maintenanceController->createAsset($request);
});
$router->addRoute('GET', '/api/v1/maintenance/assets', function($request) use ($maintenanceController) {
    return $maintenanceController->getAssets($request);
});
$router->addRoute('POST', '/api/v1/maintenance/schedules', function($request) use ($maintenanceController) {
    return $maintenanceController->createSchedule($request);
});
$router->addRoute('GET', '/api/v1/maintenance/schedules', function($request) use ($maintenanceController) {
    return $maintenanceController->getSchedules($request);
});
$router->addRoute('POST', '/api/v1/maintenance/schedules/{id}/complete', function($request) use ($maintenanceController) {
    return $maintenanceController->completeMaintenance($request);
});

// Quality Routes
$router->addRoute('POST', '/api/v1/quality/checks', function($request) use ($qualityController) {
    return $qualityController->createCheck($request);
});
$router->addRoute('GET', '/api/v1/quality/checks', function($request) use ($qualityController) {
    return $qualityController->getChecks($request);
});
$router->addRoute('POST', '/api/v1/quality/incidents', function($request) use ($qualityController) {
    return $qualityController->createIncident($request);
});
$router->addRoute('GET', '/api/v1/quality/incidents', function($request) use ($qualityController) {
    return $qualityController->getIncidents($request);
});
$router->addRoute('POST', '/api/v1/quality/incidents/{id}/resolve', function($request) use ($qualityController) {
    return $qualityController->resolveIncident($request);
});

// Sustainability Routes
$router->addRoute('POST', '/api/v1/sustainability/waste', function($request) use ($sustainabilityController) {
    return $sustainabilityController->recordWaste($request);
});
$router->addRoute('GET', '/api/v1/sustainability/waste', function($request) use ($sustainabilityController) {
    return $sustainabilityController->getWasteTracking($request);
});
$router->addRoute('POST', '/api/v1/sustainability/metrics', function($request) use ($sustainabilityController) {
    return $sustainabilityController->recordMetrics($request);
});
$router->addRoute('GET', '/api/v1/sustainability/metrics', function($request) use ($sustainabilityController) {
    return $sustainabilityController->getSustainabilityMetrics($request);
});

// Integration Routes
$router->addRoute('POST', '/api/v1/integrations/{type}/settings', function($request) use ($integrationController) {
    return $integrationController->saveSettings($request);
});
$router->addRoute('GET', '/api/v1/integrations/{type}/settings', function($request) use ($integrationController) {
    return $integrationController->getSettings($request);
});
$router->addRoute('POST', '/api/v1/integrations/{type}/test', function($request) use ($integrationController) {
    return $integrationController->testConnection($request);
});
$router->addRoute('POST', '/api/v1/integrations/{type}/sync', function($request) use ($integrationController) {
    return $integrationController->syncOrder($request);
});
$router->addRoute('GET', '/api/v1/integrations/{type}/logs', function($request) use ($integrationController) {
    return $integrationController->getLogs($request);
});

// Advanced AI Routes
$router->addRoute('POST', '/api/v1/ai/menu-engineering', function($request) use ($advancedAIController) {
    return $advancedAIController->analyzeMenu($request);
});
$router->addRoute('GET', '/api/v1/ai/menu-engineering', function($request) use ($advancedAIController) {
    return $advancedAIController->getMenuAnalysis($request);
});
$router->addRoute('POST', '/api/v1/ai/staff-optimization', function($request) use ($advancedAIController) {
    return $advancedAIController->optimizeStaff($request);
});
$router->addRoute('POST', '/api/v1/ai/fraud-detection', function($request) use ($advancedAIController) {
    return $advancedAIController->detectFraud($request);
});
$router->addRoute('GET', '/api/v1/ai/fraud-alerts', function($request) use ($advancedAIController) {
    return $advancedAIController->getFraudAlerts($request);
});
$router->addRoute('POST', '/api/v1/ai/executive-insights', function($request) use ($advancedAIController) {
    return $advancedAIController->generateInsights($request);
});
$router->addRoute('GET', '/api/v1/ai/executive-insights', function($request) use ($advancedAIController) {
    return $advancedAIController->getExecutiveInsights($request);
});

// Enterprise Routes
$router->addRoute('POST', '/api/v1/enterprise/shift-schedules', function($request) use ($enterpriseController) {
    return $enterpriseController->createShiftSchedule($request);
});
$router->addRoute('GET', '/api/v1/enterprise/shift-schedules', function($request) use ($enterpriseController) {
    return $enterpriseController->getShiftSchedules($request);
});
$router->addRoute('POST', '/api/v1/enterprise/performance-evaluations', function($request) use ($enterpriseController) {
    return $enterpriseController->createPerformanceEvaluation($request);
});
$router->addRoute('GET', '/api/v1/enterprise/performance-evaluations', function($request) use ($enterpriseController) {
    return $enterpriseController->getPerformanceEvaluations($request);
});
$router->addRoute('POST', '/api/v1/enterprise/cash-flow', function($request) use ($enterpriseController) {
    return $enterpriseController->recordCashFlow($request);
});
$router->addRoute('GET', '/api/v1/enterprise/cash-flow', function($request) use ($enterpriseController) {
    return $enterpriseController->getCashFlow($request);
});
$router->addRoute('POST', '/api/v1/enterprise/budgets', function($request) use ($enterpriseController) {
    return $enterpriseController->createBudget($request);
});
$router->addRoute('GET', '/api/v1/enterprise/budgets', function($request) use ($enterpriseController) {
    return $enterpriseController->getBudgets($request);
});
$router->addRoute('POST', '/api/v1/enterprise/budgets/update-actuals', function($request) use ($enterpriseController) {
    return $enterpriseController->updateBudgetActuals($request);
});

// Offline Sync Routes
$router->addRoute('POST', '/api/v1/offline/queue', function($request) use ($offlineSyncController) {
    return $offlineSyncController->queueOperation($request);
});
$router->addRoute('POST', '/api/v1/offline/sync', function($request) use ($offlineSyncController) {
    return $offlineSyncController->syncPending($request);
});
$router->addRoute('POST', '/api/v1/offline/conflicts/{id}/resolve', function($request) use ($offlineSyncController) {
    return $offlineSyncController->resolveConflict($request);
});
$router->addRoute('GET', '/api/v1/offline/status', function($request) use ($offlineSyncController) {
    return $offlineSyncController->getSyncStatus($request);
});
$router->addRoute('GET', '/api/v1/offline/conflicts', function($request) use ($offlineSyncController) {
    return $offlineSyncController->getConflicts($request);
});

// Customer Credit Routes
$router->addRoute('POST', '/api/v1/crm/credits', function($request) use ($creditController) {
    return $creditController->createCredit($request);
});
$router->addRoute('POST', '/api/v1/crm/credits/{id}/pay', function($request) use ($creditController) {
    return $creditController->payCredit($request);
});
$router->addRoute('GET', '/api/v1/crm/customers/{customer_id}/credits', function($request) use ($creditController) {
    return $creditController->getCustomerCredits($request);
});
$router->addRoute('GET', '/api/v1/crm/credits/overdue', function($request) use ($creditController) {
    return $creditController->getOverdueCredits($request);
});

// Customer Pricing Routes
$router->addRoute('POST', '/api/v1/crm/customer-pricing', function($request) use ($customerPricingController) {
    return $customerPricingController->setCustomerPrice($request);
});
$router->addRoute('GET', '/api/v1/crm/customer-pricing', function($request) use ($customerPricingController) {
    return $customerPricingController->getCustomerPrice($request);
});
$router->addRoute('GET', '/api/v1/crm/customers/{customer_id}/pricing', function($request) use ($customerPricingController) {
    return $customerPricingController->getCustomerPricings($request);
});

// Bonus Routes
$router->addRoute('POST', '/api/v1/hr/bonuses', function($request) use ($bonusController) {
    return $bonusController->createBonus($request);
});
$router->addRoute('POST', '/api/v1/hr/bonuses/{id}/approve', function($request) use ($bonusController) {
    return $bonusController->approveBonus($request);
});
$router->addRoute('POST', '/api/v1/hr/bonuses/{id}/pay', function($request) use ($bonusController) {
    return $bonusController->payBonus($request);
});
$router->addRoute('GET', '/api/v1/hr/employees/{employee_id}/bonuses', function($request) use ($bonusController) {
    return $bonusController->getEmployeeBonuses($request);
});
$router->addRoute('GET', '/api/v1/hr/bonuses/pending', function($request) use ($bonusController) {
    return $bonusController->getPendingBonuses($request);
});

// Tip Routes
$router->addRoute('POST', '/api/v1/hr/tips', function($request) use ($tipController) {
    return $tipController->distributeTip($request);
});
$router->addRoute('GET', '/api/v1/hr/tips', function($request) use ($tipController) {
    return $tipController->getTipDistributions($request);
});
$router->addRoute('GET', '/api/v1/hr/employees/{employee_id}/tips', function($request) use ($tipController) {
    return $tipController->getEmployeeTips($request);
});

// Commission Routes
$router->addRoute('POST', '/api/v1/hr/commissions', function($request) use ($commissionController) {
    return $commissionController->createCommission($request);
});
$router->addRoute('POST', '/api/v1/hr/commissions/{id}/approve', function($request) use ($commissionController) {
    return $commissionController->approveCommission($request);
});
$router->addRoute('POST', '/api/v1/hr/commissions/{id}/pay', function($request) use ($commissionController) {
    return $commissionController->payCommission($request);
});
$router->addRoute('GET', '/api/v1/hr/employees/{employee_id}/commissions', function($request) use ($commissionController) {
    return $commissionController->getEmployeeCommissions($request);
});
$router->addRoute('GET', '/api/v1/hr/commissions/pending', function($request) use ($commissionController) {
    return $commissionController->getPendingCommissions($request);
});

// Report Export Routes
$router->addRoute('GET', '/api/v1/reports/export/{type}/{format}', withAuthAndPermission(
    function($request) use ($reportController) {
        return $reportController->exportReport($request);
    },
    'REPORT_VIEW',
    $permissionMiddleware,
    $authMiddleware
));

// Inventory Advanced Routes
$router->addRoute('POST', '/api/v1/inventory/repurpose', function($request) use ($inventoryAdvancedController) {
    return $inventoryAdvancedController->repurposeStock($request);
});
$router->addRoute('POST', '/api/v1/inventory/zero-cost-stock', function($request) use ($inventoryAdvancedController) {
    return $inventoryAdvancedController->zeroCostStockIn($request);
});
$router->addRoute('POST', '/api/v1/inventory/stock-transfer', function($request) use ($inventoryAdvancedController) {
    return $inventoryAdvancedController->createStockTransfer($request);
});
$router->addRoute('POST', '/api/v1/inventory/stock-transfer/{id}/receive', function($request) use ($inventoryAdvancedController) {
    return $inventoryAdvancedController->receiveStockTransfer($request);
});
$router->addRoute('GET', '/api/v1/inventory/stock-transfers', function($request) use ($inventoryAdvancedController) {
    return $inventoryAdvancedController->getStockTransfers($request);
});
$router->addRoute('GET', '/api/v1/inventory/repurposing-history', function($request) use ($inventoryAdvancedController) {
    return $inventoryAdvancedController->getRepurposingHistory($request);
});

// Kitchen Performance Routes
$router->addRoute('POST', '/api/v1/kitchen/chef-performance', function($request) use ($kitchenPerformanceController) {
    return $kitchenPerformanceController->recordChefPerformance($request);
});
$router->addRoute('GET', '/api/v1/kitchen/metrics', function($request) use ($kitchenPerformanceController) {
    return $kitchenPerformanceController->getKitchenMetrics($request);
});
$router->addRoute('GET', '/api/v1/kitchen/chef-performance/{employee_id}', function($request) use ($kitchenPerformanceController) {
    return $kitchenPerformanceController->getChefPerformance($request);
});
$router->addRoute('GET', '/api/v1/kitchen/bottleneck-analysis', function($request) use ($kitchenPerformanceController) {
    return $kitchenPerformanceController->getBottleneckAnalysis($request);
});

// Customer Advanced Routes
$router->addRoute('POST', '/api/v1/crm/customers/{customer_id}/favorites', function($request) use ($customerAdvancedController) {
    return $customerAdvancedController->addFavoriteProduct($request);
});
$router->addRoute('GET', '/api/v1/crm/customers/{customer_id}/favorites', function($request) use ($customerAdvancedController) {
    return $customerAdvancedController->getCustomerFavorites($request);
});
$router->addRoute('GET', '/api/v1/crm/customers/{customer_id}/habit-analysis', function($request) use ($customerAdvancedController) {
    return $customerAdvancedController->getCustomerHabitAnalysis($request);
});
$router->addRoute('POST', '/api/v1/crm/birthday-promotions', function($request) use ($customerAdvancedController) {
    return $customerAdvancedController->createBirthdayPromotion($request);
});
$router->addRoute('GET', '/api/v1/crm/birthday-promotions', function($request) use ($customerAdvancedController) {
    return $customerAdvancedController->getBirthdayPromotions($request);
});
$router->addRoute('POST', '/api/v1/crm/birthday-promotions/{id}/use', function($request) use ($customerAdvancedController) {
    return $customerAdvancedController->useBirthdayPromotion($request);
});

// Cost Center Routes
$router->addRoute('POST', '/api/v1/accounting/cost-centers', function($request) use ($costCenterController) {
    return $costCenterController->createCostCenter($request);
});
$router->addRoute('GET', '/api/v1/accounting/cost-centers', function($request) use ($costCenterController) {
    return $costCenterController->getCostCenters($request);
});
$router->addRoute('GET', '/api/v1/accounting/cost-centers/{id}/report', function($request) use ($costCenterController) {
    return $costCenterController->getCostCenterReport($request);
});
$router->addRoute('PUT', '/api/v1/accounting/cost-centers/{id}', function($request) use ($costCenterController) {
    return $costCenterController->updateCostCenter($request);
});

// WhatsApp Routes
$router->addRoute('POST', '/api/v1/whatsapp/settings', function($request) use ($whatsappController) {
    return $whatsappController->saveSettings($request);
});
$router->addRoute('GET', '/api/v1/whatsapp/settings', function($request) use ($whatsappController) {
    return $whatsappController->getSettings($request);
});
$router->addRoute('POST', '/api/v1/whatsapp/send', function($request) use ($whatsappController) {
    return $whatsappController->sendMessage($request);
});
$router->addRoute('POST', '/api/v1/whatsapp/reports/{type}/send', function($request) use ($whatsappController) {
    return $whatsappController->sendReport($request);
});
$router->addRoute('POST', '/api/v1/whatsapp/report-schedules', function($request) use ($whatsappController) {
    return $whatsappController->createReportSchedule($request);
});
$router->addRoute('GET', '/api/v1/whatsapp/report-schedules', function($request) use ($whatsappController) {
    return $whatsappController->getReportSchedules($request);
});
$router->addRoute('GET', '/api/v1/whatsapp/message-logs', function($request) use ($whatsappController) {
    return $whatsappController->getMessageLogs($request);
});

// Tax Calculation Routes
$router->addRoute('POST', '/api/v1/accounting/tax-rates', function($request) use ($taxCalculationController) {
    return $taxCalculationController->saveTaxRate($request);
});
$router->addRoute('GET', '/api/v1/accounting/tax-rates', function($request) use ($taxCalculationController) {
    return $taxCalculationController->getTaxRate($request);
});
$router->addRoute('POST', '/api/v1/accounting/orders/{id}/tax', function($request) use ($taxCalculationController) {
    return $taxCalculationController->calculateOrderTax($request);
});
$router->addRoute('GET', '/api/v1/accounting/tax/monthly', function($request) use ($taxCalculationController) {
    return $taxCalculationController->calculateMonthlyTax($request);
});
$router->addRoute('GET', '/api/v1/accounting/tax/report', function($request) use ($taxCalculationController) {
    return $taxCalculationController->generateTaxReport($request);
});

// Supply Chain Routes
$router->addRoute('POST', '/api/v1/supply-chain/purchase-plans', function($request) use ($purchasePlanningController) {
    return $purchasePlanningController->generatePurchasePlan($request);
});
$router->addRoute('POST', '/api/v1/supply-chain/purchase-plans/{id}/approve', function($request) use ($purchasePlanningController) {
    return $purchasePlanningController->approvePurchasePlan($request);
});
$router->addRoute('GET', '/api/v1/supply-chain/purchase-plans', function($request) use ($purchasePlanningController) {
    return $purchasePlanningController->getPurchasePlans($request);
});
$router->addRoute('POST', '/api/v1/supply-chain/quality-checks', function($request) use ($qualityControlController) {
    return $qualityControlController->createQualityCheck($request);
});
$router->addRoute('PUT', '/api/v1/supply-chain/quality-checks/{id}', function($request) use ($qualityControlController) {
    return $qualityControlController->updateQualityCheckResult($request);
});
$router->addRoute('GET', '/api/v1/supply-chain/quality-checks', function($request) use ($qualityControlController) {
    return $qualityControlController->getQualityChecks($request);
});
$router->addRoute('GET', '/api/v1/supply-chain/quality-report', function($request) use ($qualityControlController) {
    return $qualityControlController->getQualityReport($request);
});

// Supplier Performance Routes
$router->addRoute('POST', '/api/v1/supply-chain/supplier-performance', function($request) use ($supplierPerformanceController) {
    return $supplierPerformanceController->evaluateSupplier($request);
});
$router->addRoute('GET', '/api/v1/supply-chain/suppliers/{id}/performance', function($request) use ($supplierPerformanceController) {
    return $supplierPerformanceController->getSupplierPerformance($request);
});
$router->addRoute('GET', '/api/v1/supply-chain/supplier-ranking', function($request) use ($supplierPerformanceController) {
    return $supplierPerformanceController->getSupplierRanking($request);
});

// Currency Routes
$router->addRoute('POST', '/api/v1/settings/currencies', function($request) use ($currencyController) {
    return $currencyController->addCurrency($request);
});
$router->addRoute('PUT', '/api/v1/settings/currencies/{id}/exchange-rate', function($request) use ($currencyController) {
    return $currencyController->updateExchangeRate($request);
});
$router->addRoute('GET', '/api/v1/settings/currencies', function($request) use ($currencyController) {
    return $currencyController->getCurrencies($request);
});
$router->addRoute('GET', '/api/v1/settings/currencies/convert', function($request) use ($currencyController) {
    return $currencyController->convertCurrency($request);
});

// AI Smart Procurement Routes
$router->addRoute('POST', '/api/v1/ai/procurement/recommendations', function($request) use ($smartProcurementController) {
    return $smartProcurementController->generateRecommendation($request);
});

// AI Kitchen Intelligence Routes
$router->addRoute('POST', '/api/v1/ai/kitchen/analyze', function($request) use ($kitchenIntelligenceController) {
    return $kitchenIntelligenceController->analyzePerformance($request);
});

// AI Customer Intelligence Routes
$router->addRoute('POST', '/api/v1/ai/customer/analyze', function($request) use ($customerIntelligenceController) {
    return $customerIntelligenceController->analyzeBehavior($request);
});

// AI Dynamic Pricing Routes
$router->addRoute('POST', '/api/v1/ai/pricing/generate', function($request) use ($dynamicPricingController) {
    return $dynamicPricingController->generatePricing($request);
});

// AI Waste Reduction Routes
$router->addRoute('POST', '/api/v1/ai/waste/record', function($request) use ($wasteReductionController) {
    return $wasteReductionController->recordWaste($request);
});
$router->addRoute('GET', '/api/v1/ai/waste/report', function($request) use ($wasteReductionController) {
    return $wasteReductionController->getWasteReport($request);
});

// Predictive Maintenance Routes
$router->addRoute('POST', '/api/v1/maintenance/predict', function($request) use ($predictiveMaintenanceController) {
    return $predictiveMaintenanceController->predictNeeds($request);
});

// Work Order Routes
$router->addRoute('POST', '/api/v1/maintenance/work-orders', function($request) use ($workOrderController) {
    return $workOrderController->createWorkOrder($request);
});
$router->addRoute('PUT', '/api/v1/maintenance/work-orders/{id}', function($request) use ($workOrderController) {
    return $workOrderController->updateWorkOrder($request);
});
$router->addRoute('GET', '/api/v1/maintenance/work-orders', function($request) use ($workOrderController) {
    return $workOrderController->getWorkOrders($request);
});

// Equipment History Routes
$router->addRoute('POST', '/api/v1/maintenance/equipment-history', function($request) use ($equipmentHistoryController) {
    return $equipmentHistoryController->addHistory($request);
});
$router->addRoute('GET', '/api/v1/maintenance/assets/{id}/history', function($request) use ($equipmentHistoryController) {
    return $equipmentHistoryController->getEquipmentHistory($request);
});

// Offline Status Routes
$router->addRoute('GET', '/api/v1/offline/status', function($request) use ($offlineStatusController) {
    return $offlineStatusController->getStatus($request);
});

// Public offline status check (no authentication required)
$router->addRoute('GET', '/api/v1/public/offline/status', function($request) use ($offlineStatusController) {
    return $offlineStatusController->getPublicStatus($request);
});

// Kiosk Routes
$router->addRoute('GET', '/api/v1/kiosk/menu', function($request) use ($kioskController) {
    return $kioskController->getMenu($request);
});
$router->addRoute('POST', '/api/v1/kiosk/orders', function($request) use ($kioskController) {
    return $kioskController->createOrder($request);
});

// Mobile Routes
$router->addRoute('GET', '/api/v1/mobile/menu', function($request) use ($mobileOrderController) {
    return $mobileOrderController->getMenu($request);
});
$router->addRoute('GET', '/api/v1/mobile/quick-order/{id}', function($request) use ($mobileOrderController) {
    return $mobileOrderController->getQuickOrder($request);
});

// Consumer Routes (Public - No Auth Required)
$router->addRoute('GET', '/api/v1/consumer/restaurants/featured', function($request) use ($consumerController) {
    return $consumerController->getFeaturedRestaurants($request);
});
$router->addRoute('GET', '/api/v1/consumer/restaurants/nearby', function($request) use ($consumerController) {
    return $consumerController->getNearbyRestaurants($request);
});
$router->addRoute('GET', '/api/v1/consumer/restaurants', function($request) use ($consumerController) {
    return $consumerController->getRestaurants($request);
});
$router->addRoute('GET', '/api/v1/consumer/restaurants/{id}', function($request) use ($consumerController) {
    return $consumerController->getRestaurantDetails($request);
});
$router->addRoute('GET', '/api/v1/consumer/cuisines', function($request) use ($consumerController) {
    return $consumerController->getCuisines($request);
});
$router->addRoute('GET', '/api/v1/consumer/menu/{restaurant_id}', function($request) use ($consumerController) {
    return $consumerController->getRestaurantMenu($request);
});
$router->addRoute('GET', '/api/v1/consumer/faq', function($request) use ($consumerController) {
    return $consumerController->getFaq($request);
});

// Consumer Auth Routes
$router->addRoute('POST', '/api/v1/consumer/auth/login', function($request) use ($consumerController) {
    return $consumerController->login($request);
});
$router->addRoute('POST', '/api/v1/consumer/auth/send-otp', function($request) use ($consumerController) {
    return $consumerController->sendOtp($request);
});
$router->addRoute('POST', '/api/v1/consumer/auth/verify-otp', function($request) use ($consumerController) {
    return $consumerController->verifyOtp($request);
});

// Consumer Order Routes (Testing - No Auth Required)
$router->addRoute('POST', '/api/v1/consumer/orders', function($request) use ($consumerController) {
    return $consumerController->placeOrder($request);
});
$router->addRoute('GET', '/api/v1/consumer/orders', function($request) use ($consumerController) {
    return $consumerController->getOrders($request);
});

// Consumer Reservation Routes (Testing - No Auth Required)
$router->addRoute('POST', '/api/v1/consumer/reservations', function($request) use ($consumerController) {
    return $consumerController->makeReservation($request);
});
$router->addRoute('GET', '/api/v1/consumer/reservations', function($request) use ($consumerController) {
    return $consumerController->getReservations($request);
});

// Consumer Loyalty Routes (Testing - No Auth Required)
$router->addRoute('GET', '/api/v1/consumer/loyalty', function($request) use ($consumerController) {
    return $consumerController->getLoyaltyPoints($request);
});
$router->addRoute('POST', '/api/v1/consumer/loyalty/redeem', function($request) use ($consumerController) {
    return $consumerController->redeemReward($request);
});

// Consumer Review Routes (Testing - No Auth Required)
$router->addRoute('POST', '/api/v1/consumer/reviews', function($request) use ($consumerController) {
    return $consumerController->submitReview($request);
});

// Consumer Favorites Routes (Testing - No Auth Required)
$router->addRoute('GET', '/api/v1/consumer/favorites', function($request) use ($consumerController) {
    return $consumerController->getFavorites($request);
});

// WhatsApp Ordering Routes
$router->addRoute('POST', '/api/v1/whatsapp/orders', function($request) use ($whatsAppOrderingController) {
    return $whatsAppOrderingController->processOrder($request);
});

// Quality Compliance Routes
$router->addRoute('POST', '/api/v1/quality/compliance-checks', function($request) use ($qualityComplianceController) {
    return $qualityComplianceController->createComplianceCheck($request);
});
$router->addRoute('GET', '/api/v1/quality/compliance-report', function($request) use ($qualityComplianceController) {
    return $qualityComplianceController->getComplianceReport($request);
});
$router->addRoute('POST', '/api/v1/quality/food-safety-protocols', function($request) use ($qualityComplianceController) {
    return $qualityComplianceController->addFoodSafetyProtocol($request);
});
$router->addRoute('GET', '/api/v1/quality/food-safety-protocols', function($request) use ($qualityComplianceController) {
    return $qualityComplianceController->getFoodSafetyProtocols($request);
});

// Loyalty Routes - Points
$router->addRoute('GET', '/api/v1/loyalty/points', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->getPoints($request);
    },
    'LOYALTY_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/loyalty/points/award', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->awardPoints($request);
    },
    'LOYALTY_MANAGE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/loyalty/points/redeem', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->redeemPoints($request);
    },
    'LOYALTY_MANAGE',
    $permissionMiddleware,
    $authMiddleware
));

// Loyalty Routes - Rewards
$router->addRoute('GET', '/api/v1/loyalty/rewards', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->getRewards($request);
    },
    'LOYALTY_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/loyalty/rewards/{id}', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->getReward($request);
    },
    'LOYALTY_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/loyalty/rewards', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->createReward($request);
    },
    'LOYALTY_MANAGE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('PUT', '/api/v1/loyalty/rewards/{id}', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->updateReward($request);
    },
    'LOYALTY_MANAGE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('DELETE', '/api/v1/loyalty/rewards/{id}', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->deleteReward($request);
    },
    'LOYALTY_MANAGE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/loyalty/rewards/{id}/redeem', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->redeemReward($request);
    },
    'LOYALTY_MANAGE',
    $permissionMiddleware,
    $authMiddleware
));

// Loyalty Routes - Customer Loyalty
$router->addRoute('GET', '/api/v1/loyalty/customers', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->getCustomerLoyalty($request);
    },
    'LOYALTY_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/loyalty/customers/{id}', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->getCustomerLoyaltyByCustomer($request);
    },
    'LOYALTY_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('POST', '/api/v1/loyalty/customers/enroll', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->enrollCustomer($request);
    },
    'LOYALTY_MANAGE',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/loyalty/customers/top', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->getTopCustomers($request);
    },
    'LOYALTY_VIEW',
    $permissionMiddleware,
    $authMiddleware
));
$router->addRoute('GET', '/api/v1/loyalty/customers/tier/{tier}', withAuthAndPermission(
    function($request) {
        $loyaltyController = new LoyaltyController();
        return $loyaltyController->getCustomersByTier($request);
    },
    'LOYALTY_VIEW',
    $permissionMiddleware,
    $authMiddleware
));

// Feature Toggle Module
if (!class_exists('FeatureToggleController')) {
    require_once __DIR__ . '/../core/FeatureToggleController.php';
}

// Feature Toggle Routes
$router->addRoute('GET', '/api/v1/features/modules', withAuth(
    function($request) {
        $featureToggleController = new FeatureToggleController();
        return $featureToggleController->getAllModules($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/features/user', withAuth(
    function($request) {
        $featureToggleController = new FeatureToggleController();
        return $featureToggleController->getUserFeatures($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/features/user/{user_id}', withAuth(
    function($request) {
        $featureToggleController = new FeatureToggleController();
        return $featureToggleController->getUserFeaturesById($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/features/role/{role_id}', withAuth(
    function($request) {
        $featureToggleController = new FeatureToggleController();
        return $featureToggleController->getRoleFeatures($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/features/user/enable', withAuth(
    function($request) {
        $featureToggleController = new FeatureToggleController();
        return $featureToggleController->enableFeatureForUser($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/features/user/disable', withAuth(
    function($request) {
        $featureToggleController = new FeatureToggleController();
        return $featureToggleController->disableFeatureForUser($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/features/role/enable', withAuth(
    function($request) {
        $featureToggleController = new FeatureToggleController();
        return $featureToggleController->enableFeatureForRole($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/features/role/disable', withAuth(
    function($request) {
        $featureToggleController = new FeatureToggleController();
        return $featureToggleController->disableFeatureForRole($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/features/check/{module_code}', withAuth(
    function($request) {
        $featureToggleController = new FeatureToggleController();
        return $featureToggleController->checkFeature($request);
    },
    $authMiddleware
));

// Operational Management Module
if (!class_exists('AttendanceController')) {
    require_once __DIR__ . '/../modules/HR/Controllers/AttendanceController.php';
}
if (!class_exists('HolidayController')) {
    require_once __DIR__ . '/../modules/HR/Controllers/HolidayController.php';
}
if (!class_exists('BusinessHoursController')) {
    require_once __DIR__ . '/../modules/HR/Controllers/BusinessHoursController.php';
}
if (!class_exists('EmergencyClosureController')) {
    require_once __DIR__ . '/../modules/HR/Controllers/EmergencyClosureController.php';
}

// Attendance Routes
$router->addRoute('POST', '/api/v1/attendance/check-in', withAuth(
    function($request) {
        $attendanceController = new AttendanceController();
        return $attendanceController->checkIn($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/attendance/check-out', withAuth(
    function($request) {
        $attendanceController = new AttendanceController();
        return $attendanceController->checkOut($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/attendance', withAuth(
    function($request) {
        $attendanceController = new AttendanceController();
        return $attendanceController->getAttendance($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/attendance/break/start', withAuth(
    function($request) {
        $attendanceController = new AttendanceController();
        return $attendanceController->startBreak($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/attendance/break/end', withAuth(
    function($request) {
        $attendanceController = new AttendanceController();
        return $attendanceController->endBreak($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/attendance/summary', withAuth(
    function($request) {
        $attendanceController = new AttendanceController();
        return $attendanceController->getSummary($request);
    },
    $authMiddleware
));

// Holiday Routes
$router->addRoute('POST', '/api/v1/holidays', withAuth(
    function($request) {
        $holidayController = new HolidayController();
        return $holidayController->create($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/holidays', withAuth(
    function($request) {
        $holidayController = new HolidayController();
        return $holidayController->getHolidays($request);
    },
    $authMiddleware
));

$router->addRoute('PUT', '/api/v1/holidays/{holiday_id}', withAuth(
    function($request) {
        $holidayController = new HolidayController();
        return $holidayController->update($request);
    },
    $authMiddleware
));

$router->addRoute('DELETE', '/api/v1/holidays/{holiday_id}', withAuth(
    function($request) {
        $holidayController = new HolidayController();
        return $holidayController->delete($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/holidays/check', withAuth(
    function($request) {
        $holidayController = new HolidayController();
        return $holidayController->checkHoliday($request);
    },
    $authMiddleware
));

// Business Hours Routes
$router->addRoute('POST', '/api/v1/business-hours', withAuth(
    function($request) {
        $businessHoursController = new BusinessHoursController();
        return $businessHoursController->setHours($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/business-hours', withAuth(
    function($request) {
        $businessHoursController = new BusinessHoursController();
        return $businessHoursController->getHours($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/business-hours/check', withAuth(
    function($request) {
        $businessHoursController = new BusinessHoursController();
        return $businessHoursController->checkOpen($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/business-hours/special', withAuth(
    function($request) {
        $businessHoursController = new BusinessHoursController();
        return $businessHoursController->createSpecialSchedule($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/business-hours/special', withAuth(
    function($request) {
        $businessHoursController = new BusinessHoursController();
        return $businessHoursController->getSpecialSchedules($request);
    },
    $authMiddleware
));

$router->addRoute('DELETE', '/api/v1/business-hours/special/{schedule_id}', withAuth(
    function($request) {
        $businessHoursController = new BusinessHoursController();
        return $businessHoursController->deleteSpecialSchedule($request);
    },
    $authMiddleware
));

// Emergency Closure Routes
$router->addRoute('POST', '/api/v1/emergency-closures', withAuth(
    function($request) {
        $emergencyClosureController = new EmergencyClosureController();
        return $emergencyClosureController->create($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/emergency-closures/active', withAuth(
    function($request) {
        $emergencyClosureController = new EmergencyClosureController();
        return $emergencyClosureController->getActive($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/emergency-closures', withAuth(
    function($request) {
        $emergencyClosureController = new EmergencyClosureController();
        return $emergencyClosureController->getAll($request);
    },
    $authMiddleware
));

$router->addRoute('PUT', '/api/v1/emergency-closures/{closure_id}', withAuth(
    function($request) {
        $emergencyClosureController = new EmergencyClosureController();
        return $emergencyClosureController->update($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/emergency-closures/{closure_id}/close', withAuth(
    function($request) {
        $emergencyClosureController = new EmergencyClosureController();
        return $emergencyClosureController->close($request);
    },
    $authMiddleware
));

$router->addRoute('PUT', '/api/v1/emergency-closures/{closure_id}/notification', withAuth(
    function($request) {
        $emergencyClosureController = new EmergencyClosureController();
        return $emergencyClosureController->updateNotification($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/emergency-closures/check', withAuth(
    function($request) {
        $emergencyClosureController = new EmergencyClosureController();
        return $emergencyClosureController->checkStatus($request);
    },
    $authMiddleware
));

// Edge Case Handling Module
if (!class_exists('RoleFallbackController')) {
    require_once __DIR__ . '/../core/RoleFallbackController.php';
}
if (!class_exists('MenuController')) {
    require_once __DIR__ . '/../core/MenuController.php';
}

// Role Fallback Routes
$router->addRoute('POST', '/api/v1/tenant/single-member', withAuth(
    function($request) {
        $roleFallbackController = new RoleFallbackController();
        return $roleFallbackController->setSingleMember($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/tenant/single-member', withAuth(
    function($request) {
        $roleFallbackController = new RoleFallbackController();
        return $roleFallbackController->checkSingleMember($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/roles/fallback', withAuth(
    function($request) {
        $roleFallbackController = new RoleFallbackController();
        return $roleFallbackController->setFallback($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/roles/fallback', withAuth(
    function($request) {
        $roleFallbackController = new RoleFallbackController();
        return $roleFallbackController->getFallbacks($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/roles/available', withAuth(
    function($request) {
        $roleFallbackController = new RoleFallbackController();
        return $roleFallbackController->getAvailableRoles($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/roles/exists', withAuth(
    function($request) {
        $roleFallbackController = new RoleFallbackController();
        return $roleFallbackController->checkRoleExists($request);
    },
    $authMiddleware
));

// Menu/Navigation Routes
$router->addRoute('GET', '/api/v1/menu/user', withAuth(
    function($request) {
        $menuController = new MenuController();
        return $menuController->getUserMenu($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/menu/role', withAuth(
    function($request) {
        $menuController = new MenuController();
        return $menuController->setRoleMenu($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/menu/role', withAuth(
    function($request) {
        $menuController = new MenuController();
        return $menuController->getRoleMenu($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/menu/role/copy', withAuth(
    function($request) {
        $menuController = new MenuController();
        return $menuController->copyRoleMenu($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/menu/access', withAuth(
    function($request) {
        $menuController = new MenuController();
        return $menuController->checkAccess($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/menu/role/modules', withAuth(
    function($request) {
        $menuController = new MenuController();
        return $menuController->getRoleModules($request);
    },
    $authMiddleware
));

// Custom Role & Module Creation Module
if (!class_exists('CustomRoleController')) {
    require_once __DIR__ . '/../core/CustomRoleController.php';
}
if (!class_exists('CustomModuleController')) {
    require_once __DIR__ . '/../core/CustomModuleController.php';
}

// Custom Role Routes
$router->addRoute('POST', '/api/v1/roles/from-template', withAuth(
    function($request) {
        $customRoleController = new CustomRoleController();
        return $customRoleController->createFromTemplate($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/roles/custom', withAuth(
    function($request) {
        $customRoleController = new CustomRoleController();
        return $customRoleController->createCustom($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/roles/templates', withAuth(
    function($request) {
        $customRoleController = new CustomRoleController();
        return $customRoleController->getTemplates($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/roles/templates/details', withAuth(
    function($request) {
        $customRoleController = new CustomRoleController();
        return $customRoleController->getTemplateDetails($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/roles/clone', withAuth(
    function($request) {
        $customRoleController = new CustomRoleController();
        return $customRoleController->cloneRole($request);
    },
    $authMiddleware
));

// Custom Module Routes
$router->addRoute('POST', '/api/v1/modules/custom', withAuth(
    function($request) {
        $customModuleController = new CustomModuleController();
        return $customModuleController->createModule($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/modules/custom', withAuth(
    function($request) {
        $customModuleController = new CustomModuleController();
        return $customModuleController->getCustomModules($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/modules/all', withAuth(
    function($request) {
        $customModuleController = new CustomModuleController();
        return $customModuleController->getAllModules($request);
    },
    $authMiddleware
));

$router->addRoute('PUT', '/api/v1/modules/custom/{custom_module_id}', withAuth(
    function($request) {
        $customModuleController = new CustomModuleController();
        return $customModuleController->updateModule($request);
    },
    $authMiddleware
));

$router->addRoute('DELETE', '/api/v1/modules/custom/{custom_module_id}', withAuth(
    function($request) {
        $customModuleController = new CustomModuleController();
        return $customModuleController->deleteModule($request);
    },
    $authMiddleware
));

// Custom Permission Routes
$router->addRoute('POST', '/api/v1/permissions/custom', withAuth(
    function($request) {
        $customModuleController = new CustomModuleController();
        return $customModuleController->createPermission($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/permissions/custom', withAuth(
    function($request) {
        $customModuleController = new CustomModuleController();
        return $customModuleController->getCustomPermissions($request);
    },
    $authMiddleware
));

$router->addRoute('GET', '/api/v1/permissions/all', withAuth(
    function($request) {
        $customModuleController = new CustomModuleController();
        return $customModuleController->getAllPermissions($request);
    },
    $authMiddleware
));

$router->addRoute('POST', '/api/v1/modules/assign', withAuth(
    function($request) {
        $customModuleController = new CustomModuleController();
        return $customModuleController->assignToRole($request);
    },
    $authMiddleware
));

// Dispatch the request
$router->dispatch();
