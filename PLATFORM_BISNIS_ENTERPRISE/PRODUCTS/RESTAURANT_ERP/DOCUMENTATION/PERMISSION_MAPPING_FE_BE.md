# Permission Mapping: Frontend - Backend

## Overview

This document describes the permission synchronization system between the Frontend (FE) and Backend (BE) of the EBP Restaurant ERP application.

## Architecture

### Backend (BE)
- **Storage**: Database table `permissions` with `permission_name` column
- **Format**: UPPERCASE with underscores (e.g., `MENU_VIEW`, `MENU_CREATE`, `MENU_EDIT`)
- **Enforcement**: Server-side via `PermissionMiddleware` in routes
- **Dynamic**: Can be modified in database without code changes

### Frontend (FE)
- **Storage**: JavaScript object `ACTION_PERMISSIONS` (fallback) + dynamic loading from BE
- **Format**: Lowercase with dots (e.g., `menu.view`, `menu.create`, `menu.edit`)
- **Enforcement**: Client-side via `permission-helpers.js` for UX
- **Dynamic**: Loads from BE API endpoint on demand

## Permission Format Conversion

The backend automatically converts permission names from underscore format to dot format:

| Backend Format | Frontend Format | Example |
|----------------|-----------------|---------|
| `MENU_VIEW` | `menu.view` | View menu items |
| `MENU_CREATE` | `menu.create` | Create menu items |
| `MENU_EDIT` | `menu.edit` | Edit menu items |
| `MENU_DELETE` | `menu.delete` | Delete menu items |
| `ORDER_VIEW` | `order.view` | View orders |
| `INVENTORY_MANAGE` | `inventory.manage` | Manage inventory |

## API Endpoint

### Get User Permissions

**Endpoint**: `GET /api/v1/users/{id}/permissions`

**Authentication**: Required (JWT token)

**Permission Required**: `USER_VIEW`

**Response Format**:
```json
{
  "success": true,
  "message": "Permissions retrieved successfully",
  "data": {
    "roles": [
      {
        "role_code": "ADMIN",
        "role_name": "Administrator"
      }
    ],
    "permissions": [
      {
        "be_format": "MENU_VIEW",
        "fe_format": "menu.view",
        "description": "View menu categories and products"
      },
      {
        "be_format": "MENU_CREATE",
        "fe_format": "menu.create",
        "description": "Create new menu categories and products"
      }
    ]
  }
}
```

## Frontend Implementation

### Permission Loading

```javascript
// Load permissions from backend
async function loadPermissionsFromBackend(userId) {
    const apiClient = window.apiClient;
    const response = await apiClient.getUserPermissions(userId);
    
    if (response.success && response.data) {
        DYNAMIC_PERMISSIONS = response.data;
        return DYNAMIC_PERMISSIONS;
    }
    
    return null;
}
```

### Permission Checking

```javascript
// Check if user has permission (uses dynamic permissions first, falls back to static)
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
```

## Permission Categories

### Menu Permissions
- `menu.create` - Create menu items
- `menu.edit` - Edit menu items
- `menu.delete` - Delete menu items
- `menu.view` - View menu items
- `menu.edit_price` - Edit menu prices
- `menu.manage_modifiers` - Manage menu modifiers
- `menu.view_recipe` - View recipes

### Order Permissions
- `order.create` - Create orders
- `order.edit` - Edit orders
- `order.delete` - Delete orders
- `order.view` - View orders
- `order.payment` - Process payments
- `order.discount` - Apply discounts
- `order.split_bill` - Split bills
- `order.merge` - Merge orders
- `order.void` - Void orders
- `order.refund` - Process refunds
- `order.kitchen_status` - Update kitchen status
- `order.tab_open` - Open tabs
- `order.tab_close` - Close tabs

### Table Permissions
- `table.create` - Create tables
- `table.edit` - Edit tables
- `table.delete` - Delete tables
- `table.view` - View tables
- `table.update_status` - Update table status
- `table.assign_order` - Assign orders to tables
- `table.merge` - Merge tables
- `table.split` - Split tables

### Inventory Permissions
- `inventory.create` - Create inventory items
- `inventory.edit` - Edit inventory items
- `inventory.delete` - Delete inventory items
- `inventory.view` - View inventory
- `inventory.adjust` - Adjust stock
- `inventory.stock_opname` - Perform stock opname
- `inventory.create_po` - Create purchase orders
- `inventory.receive_po` - Receive purchase orders
- `inventory.view_low_stock` - View low stock alerts
- `inventory.view_expiring` - View expiring items

### Kitchen Permissions
- `kitchen.view` - View kitchen orders
- `kitchen.update_status` - Update kitchen status
- `kitchen.fire_course` - Fire course
- `kitchen.cancel_item` - Cancel kitchen items

### Reservation Permissions
- `reservation.create` - Create reservations
- `reservation.edit` - Edit reservations
- `reservation.delete` - Delete reservations
- `reservation.view` - View reservations
- `reservation.confirm` - Confirm reservations
- `reservation.waitlist` - Manage waitlist
- `reservation.view_guest_notes` - View guest notes

### Accounting Permissions
- `accounting.view_revenue` - View revenue reports
- `accounting.view_expenses` - View expense reports
- `accounting.view_profit` - View profit reports
- `accounting.view_transactions` - View transactions
- `accounting.create_journal` - Create journal entries
- `accounting.view_tax` - View tax reports
- `accounting.manage_payables` - Manage payables
- `accounting.manage_receivables` - Manage receivables

### CRM Permissions
- `crm.view_customers` - View customer list
- `crm.view_customer_detail` - View customer details
- `crm.add_customer` - Add customers
- `crm.edit_customer` - Edit customers
- `crm.manage_loyalty` - Manage loyalty program
- `crm.view_history` - View purchase history
- `crm.view_preferences` - View customer preferences
- `crm.marketing` - Marketing campaigns

### Report Permissions
- `report.sales` - View sales reports
- `report.inventory` - View inventory reports
- `report.staff` - View staff reports
- `report.financial` - View financial reports
- `report.custom` - Custom reports
- `report.export` - Export reports
- `report.schedule` - Schedule reports

### HR Permissions
- `hr.view_employees` - View employees
- `hr.add_employee` - Add employees
- `hr.edit_employee` - Edit employees
- `hr.delete_employee` - Delete employees
- `hr.view_payroll` - View payroll
- `hr.manage_payroll` - Manage payroll
- `hr.view_schedule` - View schedules
- `hr.create_schedule` - Create schedules
- `hr.performance` - Performance reviews
- `hr.view_own_profile` - View own profile
- `hr.view_own_schedule` - View own schedule

### Delivery Permissions
- `delivery.view` - View deliveries
- `delivery.create` - Create deliveries
- `delivery.edit` - Edit deliveries
- `delivery.assign_driver` - Assign drivers
- `delivery.update_status` - Update status
- `delivery.track` - Track deliveries

### Supply Chain Permissions
- `supplychain.view` - View supply chain
- `supplychain.manage_suppliers` - Manage suppliers
- `supplychain.purchase_planning` - Purchase planning
- `supplychain.quality_control` - Quality control

### Quality Permissions
- `quality.view` - View quality information
- `quality.manage` - Manage quality
- `quality.create_check` - Create quality checks

### Loyalty Permissions
- `loyalty.view` - View loyalty program
- `loyalty.manage` - Manage loyalty program
- `loyalty.redeem` - Redeem points

### Settings Permissions
- `settings.view` - View settings
- `settings.manage` - Manage settings
- `settings.tax_config` - Tax configuration
- `settings.payment_config` - Payment configuration

### User Permissions
- `user.view` - View users
- `user.create` - Create users
- `user.edit` - Edit users
- `user.delete` - Delete users
- `user.assign_role` - Assign roles

## Security Considerations

1. **Backend is authoritative**: All permission checks on the backend are enforced regardless of frontend state
2. **Frontend is for UX only**: Frontend permission checks improve user experience but can be bypassed
3. **Fallback mechanism**: If backend permissions fail to load, frontend uses static mapping as fallback
4. **Token-based authentication**: All permission API calls require valid JWT token
5. **Role-based access**: Permissions are assigned to roles, users inherit permissions from their roles

## Usage Example

```javascript
// In your application initialization
const currentUser = getCurrentUser();
await loadPermissionsFromBackend(currentUser.id);

// Check permissions throughout the app
if (canPerformAction(currentUser.role_name, 'menu.create')) {
    // Show create menu button
}

if (canCreate(currentUser, 'inventory')) {
    // Show create inventory button
}
```

## Maintenance

### Adding New Permissions

1. **Backend**: Add permission to database via migration
2. **Frontend**: The system will automatically load new permissions from backend
3. **Fallback**: Optionally add to `ACTION_PERMISSIONS` for offline support

### Updating Permissions

1. **Backend**: Update permission in database
2. **Frontend**: Reload permissions by calling `loadPermissionsFromBackend()`
3. **No code changes required**: The system handles format conversion automatically

## Version History

- **v2.0** (2026-07-06): Implemented dynamic permission loading from backend with automatic format conversion
- **v1.0** (2026-07-06): Initial static permission mapping
