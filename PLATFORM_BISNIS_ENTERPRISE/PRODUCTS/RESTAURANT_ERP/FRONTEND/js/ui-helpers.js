/**
 * EBP Restaurant ERP - UI Helpers for Role-Based Access
 * 
 * Helper functions for hiding/showing UI elements based on user role and permissions
 * Based on the research document: RESEARCH_ROLE_BASED_NAVIGATION_F&B_INDUSTRY.md
 * 
 * @version 1.0
 * @date 2026-07-06
 */

/**
 * Hide UI element based on role minimum requirement
 * 
 * @param {string} elementId - ID of the element to hide
 * @param {Object} user - User object with role information
 */
function hideElementByRole(elementId, user) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    // Check data-role-min attribute
    const minRole = element.getAttribute('data-role-min');
    if (minRole && !hasMinimumRole(user.role_name, minRole)) {
        element.style.display = 'none';
        return;
    }
    
    // Check data-permission attribute
    const permission = element.getAttribute('data-permission');
    if (permission && !canPerform(user, permission)) {
        element.style.display = 'none';
        return;
    }
    
    // Check data-module and data-action attributes
    const module = element.getAttribute('data-module');
    const action = element.getAttribute('data-action');
    if (module && action) {
        const actionPermission = `${module}.${action}`;
        if (!canPerform(user, actionPermission)) {
            element.style.display = 'none';
            return;
        }
    }
}

/**
 * Hide all elements with role-based visibility attributes
 * 
 * @param {Object} user - User object with role information
 */
function hideElementsByRole(user) {
    // Hide all elements with data-role-min attribute
    document.querySelectorAll('[data-role-min]').forEach(el => {
        const minRole = el.getAttribute('data-role-min');
        if (!hasMinimumRole(user.role_name, minRole)) {
            el.style.display = 'none';
        }
    });
    
    // Hide all elements with data-permission attribute
    document.querySelectorAll('[data-permission]').forEach(el => {
        const permission = el.getAttribute('data-permission');
        if (!canPerform(user, permission)) {
            el.style.display = 'none';
        }
    });
    
    // Hide all elements with data-module and data-action attributes
    document.querySelectorAll('[data-module][data-action]').forEach(el => {
        const module = el.getAttribute('data-module');
        const action = el.getAttribute('data-action');
        const actionPermission = `${module}.${action}`;
        if (!canPerform(user, actionPermission)) {
            el.style.display = 'none';
        }
    });
}

/**
 * Check if user has minimum role level
 * 
 * @param {string} userRole - User's current role
 * @param {string} minRole - Minimum required role
 * @returns {boolean} True if user has minimum role
 */
function hasMinimumRole(userRole, minRole) {
    const roleHierarchy = [
        'Platform Owner',
        'Tenant Owner',
        'Administrator',
        'Restaurant Manager',
        'Waiter',
        'Kitchen Staff',
        'Cashier',
        'Inventory Manager',
        'Host/Hostess',
        'Bartender',
        'Barista',
        'Sommelier'
    ];
    
    const userIndex = roleHierarchy.indexOf(userRole);
    const minIndex = roleHierarchy.indexOf(minRole);
    
    return userIndex >= 0 && minIndex >= 0 && userIndex <= minIndex;
}

/**
 * Disable UI element based on permissions (instead of hiding)
 * 
 * @param {string} elementId - ID of the element to disable
 * @param {Object} user - User object with role information
 */
function disableElementByPermission(elementId, user) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const permission = element.getAttribute('data-permission');
    if (permission && !canPerform(user, permission)) {
        element.disabled = true;
        element.setAttribute('title', 'You do not have permission to perform this action');
    }
}

/**
 * Disable all elements with permission requirements
 * 
 * @param {Object} user - User object with role information
 */
function disableElementsByPermission(user) {
    document.querySelectorAll('[data-permission]').forEach(el => {
        const permission = el.getAttribute('data-permission');
        if (!canPerform(user, permission)) {
            el.disabled = true;
            el.setAttribute('title', 'You do not have permission to perform this action');
        }
    });
}

/**
 * Render menu navigation based on user role
 * 
 * @param {Array} menuItems - Array of menu item objects
 * @param {Object} user - User object
 * @param {string} containerId - ID of the menu container
 */
function renderMenuByRole(menuItems, user, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    const accessibleMenu = filterMenuByRole(menuItems, user);
    
    container.innerHTML = '';
    accessibleMenu.forEach(item => {
        const menuItem = document.createElement('a');
        menuItem.href = item.href || '#';
        menuItem.className = item.className || 'menu-item';
        menuItem.textContent = item.label;
        menuItem.dataset.tab = item.id || item.tab;
        container.appendChild(menuItem);
    });
}

/**
 * Initialize role-based UI on page load
 * 
 * @param {Object} user - User object with role information
 */
function initializeRoleBasedUI(user) {
    // Hide elements based on role
    hideElementsByRole(user);
    
    // Disable elements based on permissions
    disableElementsByPermission(user);
    
    // Update user role display
    const roleDisplay = document.getElementById('user-role-display');
    if (roleDisplay) {
        roleDisplay.textContent = getRoleLabel(user);
    }
}

/**
 * Update UI when user role changes
 * 
 * @param {Object} newUser - New user object
 */
function updateUIForRoleChange(newUser) {
    // Show all elements first
    document.querySelectorAll('[data-role-min], [data-permission], [data-module][data-action]').forEach(el => {
        el.style.display = '';
        el.disabled = false;
    });
    
    // Re-apply role-based hiding
    initializeRoleBasedUI(newUser);
}

// Export for use in other modules (if using modules)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        hideElementByRole,
        hideElementsByRole,
        hasMinimumRole,
        disableElementByPermission,
        disableElementsByPermission,
        renderMenuByRole,
        initializeRoleBasedUI,
        updateUIForRoleChange
    };
}
