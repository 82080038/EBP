/**
 * EBP Restaurant ERP - Role-Based Menu Access Configuration
 * 
 * This file defines which menu tabs are accessible to each role
 * based on the research document: RESEARCH_ROLE_BASED_NAVIGATION_F&B_INDUSTRY.md
 * 
 * @version 1.0
 * @date 2026-07-06
 */

const MENU_ACCESS = {
    PLATFORM_OWNER: {
        label: 'Platform Owner',
        tabs: [
            'overview', 'enterprise', 'tenant', 'users', 'settings',
            'reports', 'accounting', 'hr', 'crm', 'ai', 'integration',
            'quality', 'supplychain', 'sustainability', 'location',
            'maintenance', 'whatsapp'
        ]
    },
    TENANT_OWNER: {
        label: 'Tenant Owner',
        tabs: [
            'overview', 'menu', 'tables', 'orders', 'inventory', 'kitchen',
            'users', 'settings', 'accounting', 'reservation', 'crm', 'reports',
            'hr', 'delivery', 'ai', 'integration', 'quality', 'supplychain',
            'sustainability', 'location', 'maintenance', 'whatsapp', 'loyalty'
        ]
    },
    TENANT_MEMBER: {
        Administrator: {
            label: 'Administrator',
            tabs: [
                'overview', 'menu', 'tables', 'orders', 'inventory', 'kitchen',
                'users', 'settings', 'accounting', 'reservation', 'crm', 'reports',
                'hr', 'delivery', 'ai', 'integration', 'quality', 'supplychain',
                'sustainability', 'location', 'maintenance', 'whatsapp', 'loyalty'
            ]
        },
        'Restaurant Manager': {
            label: 'Restaurant Manager',
            tabs: [
                'overview', 'menu', 'tables', 'orders', 'inventory', 'kitchen',
                'reservation', 'reports', 'hr', 'crm', 'delivery', 'supplychain',
                'quality', 'accounting'
            ]
        },
        Waiter: {
            label: 'Waiter',
            tabs: [
                'overview', 'tables', 'orders', 'reservation', 'menu'
            ]
        },
        'Kitchen Staff': {
            label: 'Kitchen Staff',
            tabs: [
                'overview', 'kitchen', 'orders', 'inventory', 'menu'
            ]
        },
        Cashier: {
            label: 'Cashier',
            tabs: [
                'overview', 'orders', 'accounting', 'reports', 'tables', 'menu'
            ]
        },
        'Inventory Manager': {
            label: 'Inventory Manager',
            tabs: [
                'overview', 'inventory', 'supplychain', 'quality', 'orders', 'reports', 'menu'
            ]
        },
        'Host/Hostess': {
            label: 'Host/Hostess',
            tabs: [
                'overview', 'tables', 'reservation', 'orders', 'menu'
            ]
        },
        Bartender: {
            label: 'Bartender',
            tabs: [
                'overview', 'orders', 'inventory', 'menu', 'tables'
            ]
        },
        Barista: {
            label: 'Barista',
            tabs: [
                'overview', 'orders', 'inventory', 'menu', 'loyalty'
            ]
        },
        Sommelier: {
            label: 'Sommelier',
            tabs: [
                'overview', 'menu', 'inventory', 'crm', 'orders'
            ]
        }
    }
};

/**
 * Get menu tabs for a user based on their role and level
 * 
 * @param {Object} user - User object with role information
 * @param {string} user.role_name - Name of the user's role
 * @param {boolean} user.is_platform_owner - Whether user is platform owner
 * @param {boolean} user.is_tenant_owner - Whether user is tenant owner
 * @returns {Array} Array of accessible tab names
 */
function getMenuForUser(user) {
    if (user.is_platform_owner) {
        return MENU_ACCESS.PLATFORM_OWNER.tabs;
    }
    
    if (user.is_tenant_owner) {
        return MENU_ACCESS.TENANT_OWNER.tabs;
    }
    
    // Tenant member
    const roleConfig = MENU_ACCESS.TENANT_MEMBER[user.role_name];
    if (roleConfig) {
        return roleConfig.tabs;
    }
    
    // Default: minimal access
    return ['overview'];
}

/**
 * Check if a user has access to a specific tab
 * 
 * @param {Object} user - User object
 * @param {string} tabName - Name of the tab to check
 * @returns {boolean} True if user has access to the tab
 */
function hasTabAccess(user, tabName) {
    const accessibleTabs = getMenuForUser(user);
    return accessibleTabs.includes(tabName);
}

/**
 * Filter menu items based on user's role
 * 
 * @param {Array} menuItems - Array of menu item objects
 * @param {Object} user - User object
 * @returns {Array} Filtered array of menu items
 */
function filterMenuByRole(menuItems, user) {
    const accessibleTabs = getMenuForUser(user);
    return menuItems.filter(item => accessibleTabs.includes(item.id || item.tab));
}

/**
 * Get role label for display
 * 
 * @param {Object} user - User object
 * @returns {string} Role label
 */
function getRoleLabel(user) {
    if (user.is_platform_owner) {
        return MENU_ACCESS.PLATFORM_OWNER.label;
    }
    
    if (user.is_tenant_owner) {
        return MENU_ACCESS.TENANT_OWNER.label;
    }
    
    const roleConfig = MENU_ACCESS.TENANT_MEMBER[user.role_name];
    return roleConfig ? roleConfig.label : user.role_name || 'Unknown Role';
}

// Export for use in other modules (if using modules)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        MENU_ACCESS,
        getMenuForUser,
        hasTabAccess,
        filterMenuByRole,
        getRoleLabel
    };
}
