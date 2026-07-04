<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../bootstrap.php';

// Auth Module
if (!class_exists('AuthController')) {
    require_once __DIR__ . '/../modules/Auth/Controllers/AuthController.php';
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

// Initialize dependencies
$db = new Database();
$router = new Router();

// Initialize controllers
$authController = new AuthController();
$orderController = new OrderController();
$paymentManagementController = new PaymentManagementController();
$menuController = new MenuController();
$productVariantController = new ProductVariantController();
$productModifierController = new ProductModifierController();
$comboController = new ComboController();
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
$aiController = new AIController();
$deliveryController = new DeliveryController();
$employeeController = new EmployeeController();
$accountingController = new AccountingController();
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
$whatsAppOrderingController = new WhatsAppOrderingController();
$qualityComplianceController = new QualityComplianceController();
$userController = new UserController();
$settingController = new SettingController();
$reportController = new ReportController();
$tenantController = new TenantController();

// Auth Routes
$router->addRoute('POST', '/api/v1/auth/login', function($request) use ($authController) {
    return $authController->login($request);
});

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

// Sales Routes
$router->addRoute('POST', '/api/v1/sales/orders', function($request) use ($orderController) {
    return $orderController->create($request);
});

// Orders Routes (alias for frontend compatibility)
$router->addRoute('GET', '/api/v1/orders', function($request) use ($orderController) {
    return $orderController->getAll($request);
});
$router->addRoute('GET', '/api/v1/orders/{id}', function($request) use ($orderController) {
    return $orderController->get($request);
});
$router->addRoute('POST', '/api/v1/orders', function($request) use ($orderController) {
    return $orderController->create($request);
});
$router->addRoute('PUT', '/api/v1/orders/{id}', function($request) use ($orderController) {
    return $orderController->update($request);
});
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

// Menu Routes - Categories
$router->addRoute('GET', '/api/v1/menu/categories', function($request) use ($menuController) {
    return $menuController->getCategories($request);
});
$router->addRoute('GET', '/api/v1/menu/categories/{id}', function($request) use ($menuController) {
    return $menuController->getCategory($request);
});
$router->addRoute('POST', '/api/v1/menu/categories', function($request) use ($menuController) {
    return $menuController->createCategory($request);
});
$router->addRoute('PUT', '/api/v1/menu/categories/{id}', function($request) use ($menuController) {
    return $menuController->updateCategory($request);
});
$router->addRoute('DELETE', '/api/v1/menu/categories/{id}', function($request) use ($menuController) {
    return $menuController->deleteCategory($request);
});

// Menu Routes - Products
$router->addRoute('GET', '/api/v1/menu/products', function($request) use ($menuController) {
    return $menuController->getProducts($request);
});
$router->addRoute('GET', '/api/v1/menu/products/{id}', function($request) use ($menuController) {
    return $menuController->getProduct($request);
});
$router->addRoute('POST', '/api/v1/menu/products', function($request) use ($menuController) {
    return $menuController->createProduct($request);
});
$router->addRoute('PUT', '/api/v1/menu/products/{id}', function($request) use ($menuController) {
    return $menuController->updateProduct($request);
});
$router->addRoute('DELETE', '/api/v1/menu/products/{id}', function($request) use ($menuController) {
    return $menuController->deleteProduct($request);
});

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
$router->addRoute('GET', '/api/v1/menu/recipes', function($request) use ($menuController) {
    return $menuController->getRecipes($request);
});
$router->addRoute('GET', '/api/v1/menu/recipes/{id}', function($request) use ($menuController) {
    return $menuController->getRecipe($request);
});
$router->addRoute('POST', '/api/v1/menu/recipes', function($request) use ($menuController) {
    return $menuController->createRecipe($request);
});
$router->addRoute('PUT', '/api/v1/menu/recipes/{id}', function($request) use ($menuController) {
    return $menuController->updateRecipe($request);
});
$router->addRoute('DELETE', '/api/v1/menu/recipes/{id}', function($request) use ($menuController) {
    return $menuController->deleteRecipe($request);
});

// Table Routes
$router->addRoute('GET', '/api/v1/tables', function($request) use ($tableController) {
    return $tableController->getTables($request);
});
$router->addRoute('GET', '/api/v1/tables/available', function($request) use ($tableController) {
    return $tableController->getAvailableTables($request);
});
$router->addRoute('GET', '/api/v1/tables/{id}', function($request) use ($tableController) {
    return $tableController->getTable($request);
});
$router->addRoute('POST', '/api/v1/tables', function($request) use ($tableController) {
    return $tableController->createTable($request);
});
$router->addRoute('PUT', '/api/v1/tables/{id}', function($request) use ($tableController) {
    return $tableController->updateTable($request);
});
$router->addRoute('PATCH', '/api/v1/tables/{id}/status', function($request) use ($tableController) {
    return $tableController->updateTableStatus($request);
});
$router->addRoute('DELETE', '/api/v1/tables/{id}', function($request) use ($tableController) {
    return $tableController->deleteTable($request);
});

// Reservation Routes
$router->addRoute('GET', '/api/v1/reservations', function($request) use ($reservationController) {
    return $reservationController->getReservations($request);
});
$router->addRoute('GET', '/api/v1/reservations/date/{date}', function($request) use ($reservationController) {
    return $reservationController->getReservationsByDate($request);
});
$router->addRoute('GET', '/api/v1/reservations/{id}', function($request) use ($reservationController) {
    return $reservationController->getReservation($request);
});
$router->addRoute('GET', '/api/v1/reservations/check-availability', function($request) use ($reservationController) {
    return $reservationController->checkAvailability($request);
});
$router->addRoute('POST', '/api/v1/reservations', function($request) use ($reservationController) {
    return $reservationController->createReservation($request);
});
$router->addRoute('PUT', '/api/v1/reservations/{id}', function($request) use ($reservationController) {
    return $reservationController->updateReservation($request);
});
$router->addRoute('PATCH', '/api/v1/reservations/{id}/status', function($request) use ($reservationController) {
    return $reservationController->updateReservationStatus($request);
});
$router->addRoute('DELETE', '/api/v1/reservations/{id}', function($request) use ($reservationController) {
    return $reservationController->deleteReservation($request);
});

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
$router->addRoute('GET', '/api/v1/inventory', function($request) use ($inventoryController) {
    return $inventoryController->getInventory($request);
});
$router->addRoute('GET', '/api/v1/inventory/low-stock', function($request) use ($inventoryController) {
    return $inventoryController->getLowStock($request);
});
$router->addRoute('GET', '/api/v1/inventory/{id}', function($request) use ($inventoryController) {
    return $inventoryController->getInventoryItem($request);
});
$router->addRoute('POST', '/api/v1/inventory', function($request) use ($inventoryController) {
    return $inventoryController->createInventory($request);
});
$router->addRoute('PUT', '/api/v1/inventory/{id}', function($request) use ($inventoryController) {
    return $inventoryController->updateInventory($request);
});
$router->addRoute('POST', '/api/v1/inventory/adjust', function($request) use ($inventoryController) {
    return $inventoryController->adjustStock($request);
});
$router->addRoute('DELETE', '/api/v1/inventory/{id}', function($request) use ($inventoryController) {
    return $inventoryController->deleteInventory($request);
});
$router->addRoute('GET', '/api/v1/inventory/transactions', function($request) use ($inventoryController) {
    return $inventoryController->getTransactions($request);
});

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
$router->addRoute('GET', '/api/v1/kitchen/orders', function($request) use ($kitchenController) {
    return $kitchenController->getKitchenOrders($request);
});
$router->addRoute('GET', '/api/v1/kitchen/orders/pending', function($request) use ($kitchenController) {
    return $kitchenController->getPendingOrders($request);
});
$router->addRoute('GET', '/api/v1/kitchen/orders/in-progress', function($request) use ($kitchenController) {
    return $kitchenController->getInProgressOrders($request);
});
$router->addRoute('GET', '/api/v1/kitchen/orders/ready', function($request) use ($kitchenController) {
    return $kitchenController->getReadyOrders($request);
});
$router->addRoute('GET', '/api/v1/kitchen/orders/{id}', function($request) use ($kitchenController) {
    return $kitchenController->getKitchenOrder($request);
});
$router->addRoute('POST', '/api/v1/kitchen/orders', function($request) use ($kitchenController) {
    return $kitchenController->createKitchenOrder($request);
});
$router->addRoute('PATCH', '/api/v1/kitchen/orders/{id}/status', function($request) use ($kitchenController) {
    return $kitchenController->updateKitchenOrderStatus($request);
});
$router->addRoute('PATCH', '/api/v1/kitchen/orders/{id}/priority', function($request) use ($kitchenController) {
    return $kitchenController->updateKitchenOrderPriority($request);
});
$router->addRoute('PATCH', '/api/v1/kitchen/items/{id}/status', function($request) use ($kitchenController) {
    return $kitchenController->updateKitchenItemStatus($request);
});

// User Routes
$router->addRoute('GET', '/api/v1/users', function($request) use ($userController) {
    return $userController->getUsers($request);
});
$router->addRoute('GET', '/api/v1/users/{id}', function($request) use ($userController) {
    return $userController->getUser($request);
});
$router->addRoute('POST', '/api/v1/users', function($request) use ($userController) {
    return $userController->createUser($request);
});
$router->addRoute('PUT', '/api/v1/users/{id}', function($request) use ($userController) {
    return $userController->updateUser($request);
});
$router->addRoute('POST', '/api/v1/users/{id}/change-password', function($request) use ($userController) {
    return $userController->changePassword($request);
});
$router->addRoute('DELETE', '/api/v1/users/{id}', function($request) use ($userController) {
    return $userController->deleteUser($request);
});

// Settings Routes
$router->addRoute('GET', '/api/v1/settings', function($request) use ($settingController) {
    return $settingController->getSettings($request);
});
$router->addRoute('GET', '/api/v1/settings/{key}', function($request) use ($settingController) {
    return $settingController->getSetting($request);
});
$router->addRoute('GET', '/api/v1/settings/group/{prefix}', function($request) use ($settingController) {
    return $settingController->getSettingGroup($request);
});
$router->addRoute('POST', '/api/v1/settings', function($request) use ($settingController) {
    return $settingController->createSetting($request);
});
$router->addRoute('PUT', '/api/v1/settings/{id}', function($request) use ($settingController) {
    return $settingController->updateSetting($request);
});
$router->addRoute('POST', '/api/v1/settings/upsert', function($request) use ($settingController) {
    return $settingController->upsertSetting($request);
});
$router->addRoute('DELETE', '/api/v1/settings/{id}', function($request) use ($settingController) {
    return $settingController->deleteSetting($request);
});
$router->addRoute('POST', '/api/v1/settings/initialize', function($request) use ($settingController) {
    return $settingController->initializeSettings($request);
});

// Report Routes
$router->addRoute('GET', '/api/v1/reports/sales', function($request) use ($reportController) {
    return $reportController->getSalesReport($request);
});
$router->addRoute('GET', '/api/v1/reports/top-products', function($request) use ($reportController) {
    return $reportController->getTopSellingProducts($request);
});
$router->addRoute('GET', '/api/v1/reports/inventory', function($request) use ($reportController) {
    return $reportController->getInventoryReport($request);
});
$router->addRoute('GET', '/api/v1/reports/stock-movement', function($request) use ($reportController) {
    return $reportController->getStockMovementReport($request);
});
$router->addRoute('GET', '/api/v1/reports/kitchen-performance', function($request) use ($reportController) {
    return $reportController->getKitchenPerformanceReport($request);
});
$router->addRoute('GET', '/api/v1/reports/reservations', function($request) use ($reportController) {
    return $reportController->getReservationReport($request);
});
$router->addRoute('GET', '/api/v1/reports/financial', function($request) use ($reportController) {
    return $reportController->getFinancialReport($request);
});
$router->addRoute('GET', '/api/v1/reports/dashboard', function($request) use ($reportController) {
    return $reportController->getDashboardSummary($request);
});
$router->addRoute('GET', '/api/v1/reports/profit-loss', function($request) use ($reportController) {
    return $reportController->getProfitLossReport($request);
});
$router->addRoute('GET', '/api/v1/reports/cost-analysis', function($request) use ($reportController) {
    return $reportController->getCostAnalysisReport($request);
});
$router->addRoute('GET', '/api/v1/reports/food-cost', function($request) use ($reportController) {
    return $reportController->getFoodCostPercentage($request);
});
$router->addRoute('GET', '/api/v1/reports/sales-by-hour', function($request) use ($reportController) {
    return $reportController->getSalesByHour($request);
});
$router->addRoute('GET', '/api/v1/reports/payment-breakdown', function($request) use ($reportController) {
    return $reportController->getPaymentMethodBreakdown($request);
});
$router->addRoute('GET', '/api/v1/reports/inventory-usage', function($request) use ($reportController) {
    return $reportController->getInventoryUsageReport($request);
});
$router->addRoute('GET', '/api/v1/reports/labor-cost', function($request) use ($reportController) {
    return $reportController->getLaborCostAnalysis($request);
});
$router->addRoute('GET', '/api/v1/reports/tax', function($request) use ($reportController) {
    return $reportController->getTaxReport($request);
});

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
$router->addRoute('GET', '/api/v1/reports/export/{type}/{format}', function($request) use ($reportController) {
    return $reportController->exportReport($request);
});

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

// Dispatch the request
$router->dispatch();
