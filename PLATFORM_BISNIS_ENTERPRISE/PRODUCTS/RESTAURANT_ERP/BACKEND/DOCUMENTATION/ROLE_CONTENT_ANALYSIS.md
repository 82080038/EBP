# EBP Restaurant ERP - Role-Based Content Analysis

**Date**: 2026-07-05
**Purpose**: Deep analysis of page content per role for all 28 tabs

---

## Overview

This document defines what content should be visible/editable for each role in every tab. This is separate from tab visibility (menu access) — even when a tab is visible, not all actions within it should be available to all roles.

---

## Permission Matrix

### Action Definitions
- **V**: View (read-only)
- **C**: Create
- **E**: Edit/Update
- **D**: Delete
- **A**: Approve/Complete
- **X**: Execute special action
- **-**: Not available

---

## Tab 1: Overview

| Content | Platform Owner | Tenant Owner | Administrator | Manager | Waiter | Kitchen | Cashier | Inventory | Host |
|---------|---------------|--------------|---------------|---------|--------|---------|---------|-----------|------|
| Total Orders | V | V | V | V | V | V | V | V | V |
| Total Revenue | V | V | V | V | - | - | V | - | - |
| Active Tables | V | V | V | V | V | - | V | - | V |
| Pending Orders | V | V | V | V | V | V | V | - | V |
| Recent Activity | V | V | V | V | V (limited) | V (kitchen) | V (cashier) | - | V |
| Quick Actions | - | C/E | C/E | C/E | C (orders) | X (status) | X (payment) | - | C (reservation) |

**Implementation**: Show/hide quick action buttons based on role. Limit recent activity data scope.

---

## Tab 2: Menu

| Content | Platform Owner | Tenant Owner | Administrator | Manager | Waiter | Kitchen | Cashier | Inventory | Host |
|---------|---------------|--------------|---------------|---------|--------|---------|---------|-----------|------|
| Categories List | - | V | V | V | V | V | V | V | V |
| Products List | - | V | V | V | V | V | V | V | V |
| Create Category | - | C/E/D | C/E/D | - | - | - | - | - | - |
| Create Product | - | C/E/D | C/E/D | E (price) | - | - | - | - | - |
| Edit Prices | - | C/E | C/E | E | - | - | - | - | - |
| Delete Product | - | D | D | - | - | - | - | - | - |

**Implementation**: Hide create/edit/delete buttons for non-admin roles. Show read-only view for operational staff.

---

## Tab 3: Tables

| Content | Platform Owner | Tenant Owner | Administrator | Manager | Waiter | Kitchen | Cashier | Inventory | Host |
|---------|---------------|--------------|---------------|---------|--------|---------|---------|-----------|------|
| Table List | - | V | V | V | V | - | V | - | V |
| Create Table | - | C/E/D | C/E/D | E | - | - | - | - | - |
| Update Status | - | E | E | E | E | - | E | - | E |
| Assign Order | - | X | X | X | X | - | X | - | X |

**Implementation**: Hide create/delete buttons for non-admin. Allow status update only for Waiter, Manager, Host.

---

## Tab 4: Orders

| Content | Platform Owner | Tenant Owner | Administrator | Manager | Waiter | Kitchen | Cashier | Inventory | Host |
|---------|---------------|--------------|---------------|---------|--------|---------|---------|-----------|------|
| Order List | - | V | V | V | V | V | V | - | - |
| Create Order | - | C | C | C | C | - | - | - | - |
| Edit Order | - | E | E | E (before served) | E (before served) | - | - | - | - |
| Delete Order | - | D | D | - | - | - | - | - | - |
| Update Kitchen Status | - | X | X | X | - | X | - | - | - |
| Process Payment | - | X | X | X | - | - | X | - | - |
| Apply Discount | - | X | X | X | - | - | X (with override) | - | - |

**Implementation**: Hide create order for Kitchen/Cashier. Hide payment for non-cashier. Hide status update for non-kitchen.

---

## Tab 5: Inventory

| Content | Platform Owner | Tenant Owner | Administrator | Manager | Waiter | Kitchen | Cashier | Inventory | Host |
|---------|---------------|--------------|---------------|---------|--------|---------|---------|-----------|------|
| Inventory List | - | V | V | V | - | V | - | V | - |
| Low Stock Alert | - | V | V | V | - | V | - | V | - |
| Create Item | - | C | C | - | - | - | - | C | - |
| Edit Stock | - | E | E | E | - | - | - | E | - |
| Delete Item | - | D | D | - | - | - | - | D | - |
| Adjust Stock | - | X | X | X | - | - | - | X | - |

**Implementation**: Hide create/edit/delete buttons for Kitchen (read-only). Full access for Inventory Manager and Admin.

---

## Tab 6: Kitchen

| Content | Platform Owner | Tenant Owner | Administrator | Manager | Waiter | Kitchen | Cashier | Inventory | Host |
|---------|---------------|--------------|---------------|---------|--------|---------|---------|-----------|------|
| Kitchen Orders List | - | V | V | V | - | V | - | - | - |
| Update Status (Pending→In Progress) | - | X | X | X | - | X | - | - | - |
| Update Status (Ready→Served) | - | X | X | X | X | - | - | - | - |
| Mark Complete | - | X | X | X | - | X | - | - | - |
| Cancel Item | - | X | X | X | - | - | - | - | - |

**Implementation**: Only Kitchen Staff, Manager, Admin can update kitchen status. Waiter can mark ready→served.

---

## Tab 7: Users

| Content | Platform Owner | Tenant Owner | Administrator | Manager | Waiter | Kitchen | Cashier | Inventory | Host |
|---------|---------------|--------------|---------------|---------|--------|---------|---------|-----------|------|
| User List | V (all tenants) | V (tenant only) | V (tenant only) | V | - | - | - | - | - |
| Create User | C | C | C | - | - | - | - | - | - |
| Edit User | E | E | E | - | - | - | - | - | - |
| Delete User | D | D | D | - | - | - | - | - | - |
| Assign Role | X | X | X | - | - | - | - | - | - |

**Implementation**: Platform Owner sees all users. Tenant users only see tenant users. Manager can view but not create/edit.

---

## Tab 8: Settings

| Content | Platform Owner | Tenant Owner | Administrator | Manager | Waiter | Kitchen | Cashier | Inventory | Host |
|---------|---------------|--------------|---------------|---------|--------|---------|---------|-----------|------|
| Platform Settings | C/E | - | - | - | - | - | - | - | - |
| Tenant Settings | - | C/E | C/E | - | - | - | - | - | - |
| Tax Configuration | - | C/E | C/E | - | - | - | - | - | - |
| Receipt Settings | - | C/E | C/E | - | - | - | - | - | - |
| Payment Methods | - | C/E | C/E | - | - | - | - | - | - |
| Currency | C/E | V | V | - | - | - | - | - | - |

**Implementation**: Platform Owner sees platform settings. Tenant Owner/Admin see tenant settings. Operational staff see read-only or no settings.

---

## Tab 9: Accounting

| Content | Platform Owner | Tenant Owner | Administrator | Manager | Waiter | Kitchen | Cashier | Inventory | Host |
|---------|---------------|--------------|---------------|---------|--------|---------|---------|-----------|------|
| Revenue Summary | V | V | V | V | - | - | V | - | - |
| Expenses | V | V | V | V | - | - | - | - | - |
| Net Profit | V | V | V | V | - | - | - | - | - |
| Transactions | V | V | V | V | - | - | V | - | - |
| Create Journal Entry | C/E | C/E | C/E | - | - | - | - | - | - |
| Tax Report | V | V | V | V | - | - | V | - | - |

**Implementation**: Cashier can view transactions but not create journal entries. Manager can view all but not edit.

---

## Tab 10: Reservation

| Content | Platform Owner | Tenant Owner | Administrator | Manager | Waiter | Kitchen | Cashier | Inventory | Host |
|---------|---------------|--------------|---------------|---------|--------|---------|---------|-----------|------|
| Reservation List | - | V | V | V | V | - | - | - | V |
| Create Reservation | - | C | C | C | C | - | - | - | C |
| Edit Reservation | - | E | E | E | E | - | - | - | E |
| Cancel Reservation | - | E | E | E | E | - | - | - | E |
| Confirm/Seat | - | X | X | X | X | - | - | - | X |

**Implementation**: Host and Waiter can manage reservations. Kitchen/Cashier cannot.

---

## Tab 11: CRM

| Content | Platform Owner | Tenant Owner | Administrator | Manager | Waiter | Kitchen | Cashier | Inventory | Host |
|---------|---------------|--------------|---------------|---------|--------|---------|---------|-----------|------|
| Customer List | - | V | V | V | V | - | - | - | V |
| Customer Detail | - | V | V | V | V | - | - | - | V |
| Add Customer | - | C | C | C | C | - | - | - | C |
| Edit Customer | - | E | E | E | - | - | - | - | - |
| Loyalty Points | - | E | E | E | - | - | - | - | - |
| Marketing Campaign | - | C/E | C/E | C/E | - | - | - | - | - |

**Implementation**: Waiter/Host can add customers during order/reservation. Manager can run campaigns. Admin full access.

---

## Tab 12: Reports

| Content | Platform Owner | Tenant Owner | Administrator | Manager | Waiter | Kitchen | Cashier | Inventory | Host |
|---------|---------------|--------------|---------------|---------|--------|---------|---------|-----------|------|
| Sales Report | V | V | V | V | - | - | V | - | - |
| Inventory Report | V | V | V | V | - | - | - | V | - |
| Staff Performance | V | V | V | V | - | - | - | - | - |
| Financial Report | V | V | V | V | - | - | V | - | - |
| Custom Report Builder | V | C | C | C (limited) | - | - | - | - | - |
| Export Data | V | X | X | X | - | - | - | - | - |

**Implementation**: Report visibility depends on role. Operational staff see limited reports.

---

## Tab 13: HR

| Content | Platform Owner | Tenant Owner | Administrator | Manager | Waiter | Kitchen | Cashier | Inventory | Host |
|---------|---------------|--------------|---------------|---------|--------|---------|---------|-----------|------|
| Employee List | V | V | V | V | V (own) | V (own) | V (own) | V (own) | V (own) |
| Add Employee | C | C | C | - | - | - | - | - | - |
| Edit Employee | E | E | E | E (limited) | - | - | - | - | - |
| Payroll | V | V | V | V | - | - | - | - | - |
| Shift Schedule | V | V | V | V | V (own) | V (own) | V (own) | V (own) | V (own) |
| Performance Review | C/E | C/E | C/E | C/E | - | - | - | - | - |

**Implementation**: Manager can do performance review. Staff can only view their own profile/schedule.

---

## Tab 14: Delivery

| Content | Platform Owner | Tenant Owner | Administrator | Manager | Waiter | Kitchen | Cashier | Inventory | Host |
|---------|---------------|--------------|---------------|---------|--------|---------|---------|-----------|------|
| Delivery List | - | V | V | V | - | - | - | - | - |
| Create Delivery | - | C | C | C | - | - | - | - | - |
| Assign Driver | - | E | E | E | - | - | - | - | - |
| Update Status | - | E | E | E | - | - | - | - | - |
| Track Delivery | - | V | V | V | - | - | - | - | - |

**Implementation**: Manager/Admin can manage deliveries. Waiter might create but currently limited to Manager.

---

## Tab 15-28: Advanced/Configuration Tabs

| Tab | Platform Owner | Tenant Owner | Administrator | Manager | Others |
|-----|---------------|--------------|---------------|---------|--------|
| **AI** | C/E/V | V | V | V | - |
| **Integration** | C/E | C/E | C/E | V | - |
| **Quality** | C/E | C/E | C/E | V | V (read) for Inventory |
| **SupplyChain** | C/E | C/E | C/E | V | C/E for Inventory Manager |
| **Sustainability** | V | V | V | V | - |
| **Location** | C/E | C/E | C/E | V | - |
| **Maintenance** | C/E | C/E | C/E | V | - |
| **Enterprise** | C/E | - | - | - | - |
| **Tenant** | C/E | - | - | - | - |
| **WhatsApp** | C/E | C/E | C/E | V | - |

---

## Implementation Strategy

1. **Create helper functions**: `canCreate(role)`, `canEdit(role)`, `canDelete(role)`, `canView(role)`
2. **Add data attributes**: Add `data-role-min="Administrator"` to buttons/forms
3. **Hide elements**: Use `hideElementByRole()` in each load function
4. **Scope data**: Modify API calls to include role/scope parameters
5. **Backend enforcement**: Future improvement using PermissionMiddleware

---

## Priority Order

1. **High Priority**: Orders, Inventory, Kitchen, Users, Settings
2. **Medium Priority**: Reservation, Tables, Menu, Accounting, CRM, HR
3. **Low Priority**: Reports, Delivery, AI, Integration, advanced tabs
