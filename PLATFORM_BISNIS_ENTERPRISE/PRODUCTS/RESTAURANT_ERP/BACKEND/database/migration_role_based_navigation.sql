/*
========================================================

EBP RESTAURANT BACKEND - MIGRATION: ROLE-BASED NAVIGATION

This migration adds granular permissions for role-based navigation
based on research document: RESEARCH_ROLE_BASED_NAVIGATION_F&B_INDUSTRY.md

Date: 2026-07-06
Version: 1.0

========================================================
*/

USE ebp_restaurant_db;

/*
========================================================
GRANULAR PERMISSIONS - ACTION LEVEL
========================================================
*/

-- Menu permissions
INSERT INTO permissions (permission_code, permission_name, description) VALUES
('MENU_CREATE', 'Create Menu Items', 'Create new menu categories and products'),
('MENU_EDIT', 'Edit Menu Items', 'Edit existing menu categories and products'),
('MENU_DELETE', 'Delete Menu Items', 'Delete menu categories and products'),
('MENU_VIEW', 'View Menu Items', 'View menu categories and products'),
('MENU_EDIT_PRICE', 'Edit Menu Prices', 'Edit menu item prices'),
('MENU_MANAGE_MODIFIERS', 'Manage Menu Modifiers', 'Manage product modifiers and options'),
('MENU_VIEW_RECIPE', 'View Recipes', 'View recipe details')
ON DUPLICATE KEY UPDATE permission_name=VALUES(permission_name), description=VALUES(description);

-- Order permissions
INSERT INTO permissions (permission_code, permission_name, description) VALUES
('ORDER_CREATE', 'Create Orders', 'Create new orders'),
('ORDER_EDIT', 'Edit Orders', 'Edit existing orders'),
('ORDER_DELETE', 'Delete Orders', 'Delete orders'),
('ORDER_VIEW', 'View Orders', 'View orders'),
('ORDER_PAYMENT', 'Process Payments', 'Process order payments'),
('ORDER_DISCOUNT', 'Apply Discounts', 'Apply discounts to orders'),
('ORDER_SPLIT_BILL', 'Split Bills', 'Split order bills'),
('ORDER_MERGE', 'Merge Orders', 'Merge multiple orders'),
('ORDER_VOID', 'Void Orders', 'Void orders'),
('ORDER_REFUND', 'Refund Orders', 'Process refunds'),
('ORDER_KITCHEN_STATUS', 'Update Kitchen Status', 'Update order kitchen status'),
('ORDER_TAB_OPEN', 'Open Tabs', 'Open customer tabs'),
('ORDER_TAB_CLOSE', 'Close Tabs', 'Close customer tabs')
ON DUPLICATE KEY UPDATE permission_name=VALUES(permission_name), description=VALUES(description);

-- Table permissions
INSERT INTO permissions (permission_code, permission_name, description) VALUES
('TABLE_CREATE', 'Create Tables', 'Create new tables'),
('TABLE_EDIT', 'Edit Tables', 'Edit table details'),
('TABLE_DELETE', 'Delete Tables', 'Delete tables'),
('TABLE_VIEW', 'View Tables', 'View tables'),
('TABLE_UPDATE_STATUS', 'Update Table Status', 'Update table status'),
('TABLE_ASSIGN_ORDER', 'Assign Order to Table', 'Assign orders to tables'),
('TABLE_MERGE', 'Merge Tables', 'Merge tables'),
('TABLE_SPLIT', 'Split Tables', 'Split tables')
ON DUPLICATE KEY UPDATE permission_name=VALUES(permission_name), description=VALUES(description);

-- Inventory permissions
INSERT INTO permissions (permission_code, permission_name, description) VALUES
('INVENTORY_CREATE', 'Create Inventory Items', 'Create new inventory items'),
('INVENTORY_EDIT', 'Edit Inventory Items', 'Edit inventory items'),
('INVENTORY_DELETE', 'Delete Inventory Items', 'Delete inventory items'),
('INVENTORY_VIEW', 'View Inventory', 'View inventory'),
('INVENTORY_ADJUST', 'Adjust Stock', 'Adjust inventory stock levels'),
('INVENTORY_STOCK_OPNAME', 'Stock Opname', 'Perform stock opname'),
('INVENTORY_CREATE_PO', 'Create Purchase Orders', 'Create purchase orders'),
('INVENTORY_RECEIVE_PO', 'Receive Purchase Orders', 'Receive purchase orders'),
('INVENTORY_VIEW_LOW_STOCK', 'View Low Stock', 'View low stock alerts'),
('INVENTORY_VIEW_EXPIRING', 'View Expiring', 'View expiring items')
ON DUPLICATE KEY UPDATE permission_name=VALUES(permission_name), description=VALUES(description);

-- Kitchen permissions
INSERT INTO permissions (permission_code, permission_name, description) VALUES
('KITCHEN_VIEW', 'View Kitchen Orders', 'View kitchen display system'),
('KITCHEN_UPDATE_STATUS', 'Update Kitchen Status', 'Update kitchen order status'),
('KITCHEN_FIRE_COURSE', 'Fire Course', 'Fire course to kitchen'),
('KITCHEN_CANCEL_ITEM', 'Cancel Kitchen Item', 'Cancel kitchen items')
ON DUPLICATE KEY UPDATE permission_name=VALUES(permission_name), description=VALUES(description);

-- Reservation permissions
INSERT INTO permissions (permission_code, permission_name, description) VALUES
('RESERVATION_CREATE', 'Create Reservations', 'Create new reservations'),
('RESERVATION_EDIT', 'Edit Reservations', 'Edit existing reservations'),
('RESERVATION_DELETE', 'Delete Reservations', 'Delete reservations'),
('RESERVATION_VIEW', 'View Reservations', 'View reservations'),
('RESERVATION_CONFIRM', 'Confirm Reservations', 'Confirm and seat reservations'),
('RESERVATION_WAITLIST', 'Manage Waitlist', 'Manage reservation waitlist'),
('RESERVATION_VIEW_GUEST_NOTES', 'View Guest Notes', 'View guest notes and preferences')
ON DUPLICATE KEY UPDATE permission_name=VALUES(permission_name), description=VALUES(description);

-- Accounting permissions
INSERT INTO permissions (permission_code, permission_name, description) VALUES
('ACCOUNTING_VIEW_REVENUE', 'View Revenue', 'View revenue reports'),
('ACCOUNTING_VIEW_EXPENSES', 'View Expenses', 'View expense reports'),
('ACCOUNTING_VIEW_PROFIT', 'View Profit', 'View profit reports'),
('ACCOUNTING_VIEW_TRANSACTIONS', 'View Transactions', 'View financial transactions'),
('ACCOUNTING_CREATE_JOURNAL', 'Create Journal Entries', 'Create accounting journal entries'),
('ACCOUNTING_VIEW_TAX', 'View Tax Reports', 'View tax reports'),
('ACCOUNTING_MANAGE_PAYABLES', 'Manage Payables', 'Manage accounts payable'),
('ACCOUNTING_MANAGE_RECEIVABLES', 'Manage Receivables', 'Manage accounts receivable')
ON DUPLICATE KEY UPDATE permission_name=VALUES(permission_name), description=VALUES(description);

-- CRM permissions
INSERT INTO permissions (permission_code, permission_name, description) VALUES
('CRM_VIEW_CUSTOMERS', 'View Customers', 'View customer list'),
('CRM_VIEW_CUSTOMER_DETAIL', 'View Customer Details', 'View customer details'),
('CRM_ADD_CUSTOMER', 'Add Customers', 'Add new customers'),
('CRM_EDIT_CUSTOMER', 'Edit Customers', 'Edit customer information'),
('CRM_MANAGE_LOYALTY', 'Manage Loyalty Points', 'Manage customer loyalty points'),
('CRM_VIEW_HISTORY', 'View Purchase History', 'View customer purchase history'),
('CRM_VIEW_PREFERENCES', 'View Customer Preferences', 'View customer preferences'),
('CRM_MARKETING', 'Marketing Campaigns', 'Create and manage marketing campaigns')
ON DUPLICATE KEY UPDATE permission_name=VALUES(permission_name), description=VALUES(description);

-- Report permissions
INSERT INTO permissions (permission_code, permission_name, description) VALUES
('REPORT_SALES', 'View Sales Reports', 'View sales reports'),
('REPORT_INVENTORY', 'View Inventory Reports', 'View inventory reports'),
('REPORT_STAFF', 'View Staff Performance', 'View staff performance reports'),
('REPORT_FINANCIAL', 'View Financial Reports', 'View financial reports'),
('REPORT_CUSTOM', 'Custom Reports', 'Create custom reports'),
('REPORT_EXPORT', 'Export Data', 'Export report data'),
('REPORT_SCHEDULE', 'Schedule Reports', 'Schedule automated reports')
ON DUPLICATE KEY UPDATE permission_name=VALUES(permission_name), description=VALUES(description);

-- HR permissions
INSERT INTO permissions (permission_code, permission_name, description) VALUES
('HR_VIEW_EMPLOYEES', 'View Employees', 'View employee list'),
('HR_ADD_EMPLOYEE', 'Add Employees', 'Add new employees'),
('HR_EDIT_EMPLOYEE', 'Edit Employees', 'Edit employee information'),
('HR_DELETE_EMPLOYEE', 'Delete Employees', 'Delete employees'),
('HR_VIEW_PAYROLL', 'View Payroll', 'View payroll information'),
('HR_MANAGE_PAYROLL', 'Manage Payroll', 'Manage payroll processing'),
('HR_VIEW_SCHEDULE', 'View Schedule', 'View employee schedules'),
('HR_CREATE_SCHEDULE', 'Create Schedule', 'Create employee schedules'),
('HR_PERFORMANCE', 'Performance Review', 'Conduct performance reviews'),
('HR_VIEW_OWN_PROFILE', 'View Own Profile', 'View own employee profile'),
('HR_VIEW_OWN_SCHEDULE', 'View Own Schedule', 'View own schedule')
ON DUPLICATE KEY UPDATE permission_name=VALUES(permission_name), description=VALUES(description);

-- Delivery permissions
INSERT INTO permissions (permission_code, permission_name, description) VALUES
('DELIVERY_VIEW', 'View Deliveries', 'View delivery list'),
('DELIVERY_CREATE', 'Create Delivery', 'Create new delivery'),
('DELIVERY_EDIT', 'Edit Delivery', 'Edit delivery details'),
('DELIVERY_ASSIGN_DRIVER', 'Assign Driver', 'Assign driver to delivery'),
('DELIVERY_UPDATE_STATUS', 'Update Status', 'Update delivery status'),
('DELIVERY_TRACK', 'Track Delivery', 'Track delivery status')
ON DUPLICATE KEY UPDATE permission_name=VALUES(permission_name), description=VALUES(description);

-- Supply Chain permissions
INSERT INTO permissions (permission_code, permission_name, description) VALUES
('SUPPLYCHAIN_VIEW', 'View Supply Chain', 'View supply chain information'),
('SUPPLYCHAIN_MANAGE_SUPPLIERS', 'Manage Suppliers', 'Manage supplier information'),
('SUPPLYCHAIN_PURCHASE_PLANNING', 'Purchase Planning', 'Plan purchases'),
('SUPPLYCHAIN_QUALITY_CONTROL', 'Quality Control', 'Manage quality control')
ON DUPLICATE KEY UPDATE permission_name=VALUES(permission_name), description=VALUES(description);

-- Quality permissions
INSERT INTO permissions (permission_code, permission_name, description) VALUES
('QUALITY_VIEW', 'View Quality', 'View quality information'),
('QUALITY_MANAGE', 'Manage Quality', 'Manage quality control'),
('QUALITY_CREATE_CHECK', 'Create Quality Check', 'Create quality check records')
ON DUPLICATE KEY UPDATE permission_name=VALUES(permission_name), description=VALUES(description);

-- Loyalty permissions
INSERT INTO permissions (permission_code, permission_name, description) VALUES
('LOYALTY_VIEW', 'View Loyalty', 'View loyalty program'),
('LOYALTY_MANAGE', 'Manage Loyalty', 'Manage loyalty program'),
('LOYALTY_REDEEM', 'Redeem Points', 'Redeem loyalty points')
ON DUPLICATE KEY UPDATE permission_name=VALUES(permission_name), description=VALUES(description);

-- Settings permissions (keep existing)
INSERT INTO permissions (permission_code, permission_name, description) VALUES
('SETTINGS_VIEW', 'View Settings', 'View system settings'),
('SETTINGS_MANAGE', 'Manage Settings', 'Manage system settings'),
('SETTINGS_TAX_CONFIG', 'Configure Tax', 'Configure tax settings'),
('SETTINGS_PAYMENT_CONFIG', 'Configure Payment', 'Configure payment methods')
ON DUPLICATE KEY UPDATE permission_name=VALUES(permission_name), description=VALUES(description);

-- User permissions (keep existing)
INSERT INTO permissions (permission_code, permission_name, description) VALUES
('USER_VIEW', 'View Users', 'View user list'),
('USER_CREATE', 'Create Users', 'Create new users'),
('USER_EDIT', 'Edit Users', 'Edit user information'),
('USER_DELETE', 'Delete Users', 'Delete users'),
('USER_ASSIGN_ROLE', 'Assign Roles', 'Assign roles to users')
ON DUPLICATE KEY UPDATE permission_name=VALUES(permission_name), description=VALUES(description);

/*
========================================================
MIGRATION COMPLETE
========================================================
*/
