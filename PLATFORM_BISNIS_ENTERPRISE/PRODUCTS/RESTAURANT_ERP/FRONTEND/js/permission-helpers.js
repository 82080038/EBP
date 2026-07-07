/**
 * EBP Restaurant ERP - Permission Helpers
 * 
 * Helper functions for checking granular permissions at the action level
 * Based on the research document: RESEARCH_ROLE_BASED_NAVIGATION_F&B_INDUSTRY.md
 * 
 * @version 2.0 - Now loads permissions dynamically from backend
 * @date 2026-07-06
 */

/**
 * Action permissions mapping by role
 * This defines which roles can perform which actions on which modules
 * NOTE: This is now a fallback if backend permissions fail to load
 */
const ACTION_PERMISSIONS = {
    // Menu permissions
    'menu.create': ['Administrator', 'Tenant Owner'],
    'menu.edit': ['Administrator', 'Tenant Owner', 'Restaurant Manager'],
    'menu.delete': ['Administrator', 'Tenant Owner'],
    'menu.view': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Waiter', 'Kitchen Staff', 'Cashier', 'Inventory Manager', 'Host/Hostess', 'Bartender', 'Barista', 'Sommelier'],
    'menu.edit_price': ['Administrator', 'Tenant Owner', 'Restaurant Manager'],
    'menu.manage_modifiers': ['Administrator', 'Tenant Owner'],
    'menu.view_recipe': ['Administrator', 'Tenant Owner', 'Kitchen Staff', 'Inventory Manager'],

    // Order permissions
    'order.create': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Waiter', 'Bartender', 'Barista'],
    'order.edit': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Waiter'],
    'order.delete': ['Administrator', 'Tenant Owner'],
    'order.view': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Waiter', 'Kitchen Staff', 'Cashier', 'Inventory Manager', 'Host/Hostess', 'Bartender', 'Barista', 'Sommelier'],
    'order.payment': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Cashier'],
    'order.discount': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Cashier'],
    'order.split_bill': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Cashier'],
    'order.merge': ['Administrator', 'Tenant Owner', 'Restaurant Manager'],
    'order.void': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Cashier'],
    'order.refund': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Cashier'],
    'order.kitchen_status': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Kitchen Staff'],
    'order.tab_open': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Cashier', 'Bartender'],
    'order.tab_close': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Cashier', 'Bartender'],

    // Table permissions
    'table.create': ['Administrator', 'Tenant Owner'],
    'table.edit': ['Administrator', 'Tenant Owner'],
    'table.delete': ['Administrator', 'Tenant Owner'],
    'table.view': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Waiter', 'Cashier', 'Host/Hostess', 'Bartender'],
    'table.update_status': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Waiter', 'Cashier', 'Host/Hostess', 'Bartender'],
    'table.assign_order': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Waiter', 'Cashier', 'Host/Hostess', 'Bartender'],
    'table.merge': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Host/Hostess'],
    'table.split': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Host/Hostess'],

    // Inventory permissions
    'inventory.create': ['Administrator', 'Tenant Owner', 'Inventory Manager'],
    'inventory.edit': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Inventory Manager'],
    'inventory.delete': ['Administrator', 'Tenant Owner', 'Inventory Manager'],
    'inventory.view': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Kitchen Staff', 'Inventory Manager', 'Bartender', 'Barista', 'Sommelier'],
    'inventory.adjust': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Inventory Manager'],
    'inventory.stock_opname': ['Administrator', 'Tenant Owner', 'Inventory Manager'],
    'inventory.create_po': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Inventory Manager'],
    'inventory.receive_po': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Inventory Manager'],
    'inventory.view_low_stock': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Kitchen Staff', 'Inventory Manager', 'Bartender', 'Barista'],
    'inventory.view_expiring': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Kitchen Staff', 'Inventory Manager', 'Bartender', 'Barista'],

    // Kitchen permissions
    'kitchen.view': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Kitchen Staff'],
    'kitchen.update_status': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Kitchen Staff'],
    'kitchen.fire_course': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Kitchen Staff'],
    'kitchen.cancel_item': ['Administrator', 'Tenant Owner', 'Restaurant Manager'],

    // Reservation permissions
    'reservation.create': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Waiter', 'Host/Hostess'],
    'reservation.edit': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Waiter', 'Host/Hostess'],
    'reservation.delete': ['Administrator', 'Tenant Owner'],
    'reservation.view': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Waiter', 'Host/Hostess'],
    'reservation.confirm': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Waiter', 'Host/Hostess'],
    'reservation.waitlist': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Host/Hostess'],
    'reservation.view_guest_notes': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Host/Hostess', 'Sommelier'],

    // Accounting permissions
    'accounting.view_revenue': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Cashier'],
    'accounting.view_expenses': ['Administrator', 'Tenant Owner', 'Restaurant Manager'],
    'accounting.view_profit': ['Administrator', 'Tenant Owner', 'Restaurant Manager'],
    'accounting.view_transactions': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Cashier'],
    'accounting.create_journal': ['Administrator', 'Tenant Owner'],
    'accounting.view_tax': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Cashier'],
    'accounting.manage_payables': ['Administrator', 'Tenant Owner'],
    'accounting.manage_receivables': ['Administrator', 'Tenant Owner'],

    // CRM permissions
    'crm.view_customers': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Waiter', 'Host/Hostess', 'Sommelier'],
    'crm.view_customer_detail': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Waiter', 'Host/Hostess', 'Sommelier'],
    'crm.add_customer': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Waiter', 'Cashier', 'Host/Hostess', 'Sommelier'],
    'crm.edit_customer': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Sommelier'],
    'crm.manage_loyalty': ['Administrator', 'Tenant Owner'],
    'crm.view_history': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Sommelier'],
    'crm.view_preferences': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Sommelier'],
    'crm.marketing': ['Administrator', 'Tenant Owner', 'Restaurant Manager'],

    // Report permissions
    'report.sales': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Cashier'],
    'report.inventory': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Inventory Manager'],
    'report.staff': ['Administrator', 'Tenant Owner', 'Restaurant Manager'],
    'report.financial': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Cashier'],
    'report.custom': ['Administrator', 'Tenant Owner', 'Restaurant Manager'],
    'report.export': ['Administrator', 'Tenant Owner', 'Restaurant Manager'],
    'report.schedule': ['Administrator', 'Tenant Owner'],

    // HR permissions
    'hr.view_employees': ['Administrator', 'Tenant Owner', 'Restaurant Manager'],
    'hr.add_employee': ['Administrator', 'Tenant Owner'],
    'hr.edit_employee': ['Administrator', 'Tenant Owner', 'Restaurant Manager'],
    'hr.delete_employee': ['Administrator', 'Tenant Owner'],
    'hr.view_payroll': ['Administrator', 'Tenant Owner', 'Restaurant Manager'],
    'hr.manage_payroll': ['Administrator', 'Tenant Owner', 'Restaurant Manager'],
    'hr.view_schedule': ['Administrator', 'Tenant Owner', 'Restaurant Manager'],
    'hr.create_schedule': ['Administrator', 'Tenant Owner', 'Restaurant Manager'],
    'hr.performance': ['Administrator', 'Tenant Owner', 'Restaurant Manager'],
    'hr.view_own_profile': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Waiter', 'Kitchen Staff', 'Cashier', 'Inventory Manager', 'Host/Hostess', 'Bartender', 'Barista', 'Sommelier'],
    'hr.view_own_schedule': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Waiter', 'Kitchen Staff', 'Cashier', 'Inventory Manager', 'Host/Hostess', 'Bartender', 'Barista', 'Sommelier'],

    // Delivery permissions
    'delivery.view': ['Administrator', 'Tenant Owner', 'Restaurant Manager'],
    'delivery.create': ['Administrator', 'Tenant Owner', 'Restaurant Manager'],
    'delivery.edit': ['Administrator', 'Tenant Owner', 'Restaurant Manager'],
    'delivery.assign_driver': ['Administrator', 'Tenant Owner', 'Restaurant Manager'],
    'delivery.update_status': ['Administrator', 'Tenant Owner', 'Restaurant Manager'],
    'delivery.track': ['Administrator', 'Tenant Owner', 'Restaurant Manager'],

    // Supply Chain permissions
    'supplychain.view': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Inventory Manager'],
    'supplychain.manage_suppliers': ['Administrator', 'Tenant Owner', 'Inventory Manager'],
    'supplychain.purchase_planning': ['Administrator', 'Tenant Owner', 'Inventory Manager'],
    'supplychain.quality_control': ['Administrator', 'Tenant Owner', 'Inventory Manager'],

    // Quality permissions
    'quality.view': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Inventory Manager'],
    'quality.manage': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Inventory Manager'],
    'quality.create_check': ['Administrator', 'Tenant Owner', 'Restaurant Manager', 'Inventory Manager'],

    // Loyalty permissions
    'loyalty.view': ['Administrator', 'Tenant Owner', 'Barista'],
    'loyalty.manage': ['Administrator', 'Tenant Owner'],
    'loyalty.redeem': ['Administrator', 'Tenant Owner', 'Barista'],

    // Settings permissions
    'settings.view': ['Administrator', 'Tenant Owner'],
    'settings.manage': ['Administrator', 'Tenant Owner'],
    'settings.tax_config': ['Administrator', 'Tenant Owner'],
    'settings.payment_config': ['Administrator', 'Tenant Owner'],

    // User permissions
    'user.view': ['Administrator', 'Tenant Owner', 'Restaurant Manager'],
    'user.create': ['Administrator', 'Tenant Owner'],
    'user.edit': ['Administrator', 'Tenant Owner'],
    'user.delete': ['Administrator', 'Tenant Owner'],
    'user.assign_role': ['Administrator', 'Tenant Owner']
};

/**
 * Dynamic permissions loaded from backend
 * This will be populated with permissions from the backend API
 */
let DYNAMIC_PERMISSIONS = {
    roles: [],
    permissions: []
};

/**
 * Load user permissions from backend
 * 
 * @param {number} userId - User ID
 * @returns {Promise<Object>} User permissions from backend
 */
async function loadPermissionsFromBackend(userId) {
    try {
        const apiClient = window.apiClient;
        if (!apiClient) {
            console.warn('API client not available, using fallback permissions');
            return null;
        }

        const response = await apiClient.getUserPermissions(userId);

        if (response.success && response.data) {
            DYNAMIC_PERMISSIONS = response.data;
            console.log('Permissions loaded from backend:', DYNAMIC_PERMISSIONS);
            return DYNAMIC_PERMISSIONS;
        }

        return null;
    } catch (error) {
        console.error('Failed to load permissions from backend:', error);
        return null;
    }
}

/**
 * Check if a role can perform a specific action
 * Uses dynamic permissions from backend if available, falls back to static mapping
 * 
 * @param {string} role - Role name
 * @param {string} action - Action permission code (e.g., 'menu.create')
 * @returns {boolean} True if role has permission
 */
function canPerformAction(role, action) {
    // First check dynamic permissions from backend
    if (DYNAMIC_PERMISSIONS.permissions && DYNAMIC_PERMISSIONS.permissions.length > 0) {
        const hasPermission = DYNAMIC_PERMISSIONS.permissions.some(p => p.fe_format === action);
        return hasPermission;
    }

    // Fallback to static mapping
    const allowedRoles = ACTION_PERMISSIONS[action];
    return allowedRoles && allowedRoles.includes(role);
}

/**
 * Check if user can create items in a module
 * 
 * @param {Object} user - User object
 * @param {string} module - Module name (e.g., 'menu', 'order')
 * @returns {boolean} True if user can create
 */
function canCreate(user, module) {
    if (user.is_platform_owner || user.is_tenant_owner) return true;
    return canPerformAction(user.role_name, `${module}.create`);
}

/**
 * Check if user can edit items in a module
 * 
 * @param {Object} user - User object
 * @param {string} module - Module name (e.g., 'menu', 'order')
 * @returns {boolean} True if user can edit
 */
function canEdit(user, module) {
    if (user.is_platform_owner || user.is_tenant_owner) return true;
    return canPerformAction(user.role_name, `${module}.edit`);
}

/**
 * Check if user can delete items in a module
 * 
 * @param {Object} user - User object
 * @param {string} module - Module name (e.g., 'menu', 'order')
 * @returns {boolean} True if user can delete
 */
function canDelete(user, module) {
    if (user.is_platform_owner || user.is_tenant_owner) return true;
    return canPerformAction(user.role_name, `${module}.delete`);
}

/**
 * Check if user can view items in a module
 * 
 * @param {Object} user - User object
 * @param {string} module - Module name (e.g., 'menu', 'order')
 * @returns {boolean} True if user can view
 */
function canView(user, module) {
    if (user.is_platform_owner || user.is_tenant_owner) return true;
    return canPerformAction(user.role_name, `${module}.view`);
}

/**
 * Check if user can perform a specific action
 * 
 * @param {Object} user - User object
 * @param {string} action - Action permission code
 * @returns {boolean} True if user can perform action
 */
function canPerform(user, action) {
    if (user.is_platform_owner || user.is_tenant_owner) return true;
    return canPerformAction(user.role_name, action);
}

/**
 * Get all permissions for a role
 * 
 * @param {string} role - Role name
 * @returns {Array} Array of permission codes
 */
function getPermissionsForRole(role) {
    const permissions = [];
    for (const [action, roles] of Object.entries(ACTION_PERMISSIONS)) {
        if (roles.includes(role)) {
            permissions.push(action);
        }
    }
    return permissions;
}

/**
 * Get all permissions for a user
 * 
 * @param {Object} user - User object
 * @returns {Array} Array of permission codes
 */
function getPermissionsForUser(user) {
    if (user.is_platform_owner) {
        return Object.keys(ACTION_PERMISSIONS);
    }
    if (user.is_tenant_owner) {
        return Object.keys(ACTION_PERMISSIONS);
    }
    return getPermissionsForRole(user.role_name);
}

// Export for use in other modules (if using modules)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        ACTION_PERMISSIONS,
        DYNAMIC_PERMISSIONS,
        loadPermissionsFromBackend,
        canPerformAction,
        canCreate,
        canEdit,
        canDelete,
        canView,
        canPerform,
        getPermissionsForRole,
        getPermissionsForUser
    };
}
