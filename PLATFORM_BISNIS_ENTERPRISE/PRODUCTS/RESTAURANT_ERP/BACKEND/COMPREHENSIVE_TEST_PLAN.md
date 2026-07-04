# Comprehensive F&B Application Test Plan

**Document ID:** TEST-PLAN-FB-001

**Version:** 1.0

**Test Date:** 2026-07-02

**Scope:** All F&B features and user roles

---

# 1. Test Environment

## 1.1 Test Users

| Username | Password | Role | Email |
|----------|----------|------|-------|
| admin | password | Administrator | admin@restaurant.com |
| manager | password | Restaurant Manager | manager@restaurant.com |
| waiter | password | Waiter | waiter@restaurant.com |
| kitchen | password | Kitchen Staff | kitchen@restaurant.com |
| cashier | password | Cashier | cashier@restaurant.com |
| inventory | password | Inventory Manager | inventory@restaurant.com |
| host | password | Host/Hostess | host@restaurant.com |

## 1.2 Test Data

- **Categories:** 3 categories (Main Course, Beverages, Appetizers)
- **Tables:** Multiple tables available
- **Products:** Sample products in database
- **Inventory:** Sample inventory items

---

# 2. Test Scope

## 2.1 F&B Features

1. **Authentication & Authorization**
   - Login/logout
   - Role-based access control
   - Permission verification

2. **Menu Management**
   - View categories
   - View products
   - Create/update/delete categories (Manager/Admin)
   - Create/update/delete products (Manager/Admin)

3. **Order Management**
   - Create orders
   - Update order status
   - View orders
   - Delete orders (Manager/Admin)

4. **Table Management**
   - View tables
   - Update table status
   - Assign tables to orders
   - Manage tables (Manager/Admin)

5. **Kitchen Operations**
   - View kitchen orders
   - Update kitchen order status
   - Kitchen display system

6. **Inventory Management**
   - View inventory
   - Stock adjustments
   - Inventory reports

7. **Reservation Management**
   - Create reservations
   - Update reservations
   - View reservations
   - Manage reservations (Manager/Admin)

8. **Payment Processing**
   - Process payments
   - View payment history
   - Payment methods

9. **Reporting**
   - Sales reports
   - Inventory reports
   - Performance reports

---

# 3. Role-Based Test Matrix

## 3.1 Administrator (admin)

| Feature | View | Create | Update | Delete | Expected Result |
|---------|------|--------|--------|--------|----------------|
| Authentication | ✅ | - | - | - | Should login successfully |
| Menu | ✅ | ✅ | ✅ | ✅ | Full access |
| Orders | ✅ | ✅ | ✅ | ✅ | Full access |
| Tables | ✅ | ✅ | ✅ | ✅ | Full access |
| Kitchen | ✅ | ✅ | ✅ | ✅ | Full access |
| Inventory | ✅ | ✅ | ✅ | ✅ | Full access |
| Reservations | ✅ | ✅ | ✅ | ✅ | Full access |
| Payments | ✅ | ✅ | ✅ | ✅ | Full access |
| Reports | ✅ | - | - | - | View access |
| Users | ✅ | ✅ | ✅ | ✅ | Full access |
| Settings | ✅ | ✅ | ✅ | ✅ | Full access |

## 3.2 Restaurant Manager (manager)

| Feature | View | Create | Update | Delete | Expected Result |
|---------|------|--------|--------|--------|----------------|
| Authentication | ✅ | - | - | - | Should login successfully |
| Menu | ✅ | ✅ | ✅ | ✅ | Full access |
| Orders | ✅ | ✅ | ✅ | ✅ | Full access |
| Tables | ✅ | ✅ | ✅ | ✅ | Full access |
| Kitchen | ✅ | ✅ | ✅ | ✅ | Full access |
| Inventory | ✅ | ✅ | ✅ | ✅ | Full access |
| Reservations | ✅ | ✅ | ✅ | ✅ | Full access |
| Payments | ✅ | ✅ | ✅ | ✅ | Full access |
| Reports | ✅ | - | - | - | View access |
| Users | ❌ | ❌ | ❌ | ❌ | No access |
| Settings | ✅ | ✅ | ✅ | ✅ | Full access |

## 3.3 Waiter (waiter)

| Feature | View | Create | Update | Delete | Expected Result |
|---------|------|--------|--------|--------|----------------|
| Authentication | ✅ | - | - | - | Should login successfully |
| Menu | ✅ | ❌ | ❌ | ❌ | View only |
| Orders | ✅ | ✅ | ✅ | ❌ | Create and update only |
| Tables | ✅ | ❌ | ✅ | ❌ | View and assign only |
| Kitchen | ✅ | ❌ | ❌ | ❌ | View only |
| Inventory | ❌ | ❌ | ❌ | ❌ | No access |
| Reservations | ✅ | ✅ | ✅ | ❌ | Create and update only |
| Payments | ❌ | ❌ | ❌ | ❌ | No access |
| Reports | ❌ | ❌ | ❌ | ❌ | No access |
| Users | ❌ | ❌ | ❌ | ❌ | No access |
| Settings | ❌ | ❌ | ❌ | ❌ | No access |

## 3.4 Kitchen Staff (kitchen)

| Feature | View | Create | Update | Delete | Expected Result |
|---------|------|--------|--------|--------|----------------|
| Authentication | ✅ | - | - | - | Should login successfully |
| Menu | ✅ | ❌ | ❌ | ❌ | View only |
| Orders | ✅ | ❌ | ❌ | ❌ | View only |
| Tables | ❌ | ❌ | ❌ | ❌ | No access |
| Kitchen | ✅ | ❌ | ✅ | ❌ | View and update status only |
| Inventory | ❌ | ❌ | ❌ | ❌ | No access |
| Reservations | ❌ | ❌ | ❌ | ❌ | No access |
| Payments | ❌ | ❌ | ❌ | ❌ | No access |
| Reports | ❌ | ❌ | ❌ | ❌ | No access |
| Users | ❌ | ❌ | ❌ | ❌ | No access |
| Settings | ❌ | ❌ | ❌ | ❌ | No access |

## 3.5 Cashier (cashier)

| Feature | View | Create | Update | Delete | Expected Result |
|---------|------|--------|--------|--------|----------------|
| Authentication | ✅ | - | - | - | Should login successfully |
| Menu | ✅ | ❌ | ❌ | ❌ | View only |
| Orders | ✅ | ✅ | ✅ | ❌ | Create and update only |
| Tables | ❌ | ❌ | ❌ | ❌ | No access |
| Kitchen | ❌ | ❌ | ❌ | ❌ | No access |
| Inventory | ❌ | ❌ | ❌ | ❌ | No access |
| Reservations | ❌ | ❌ | ❌ | ❌ | No access |
| Payments | ✅ | ✅ | ✅ | ❌ | Create and process only |
| Reports | ❌ | ❌ | ❌ | ❌ | No access |
| Users | ❌ | ❌ | ❌ | ❌ | No access |
| Settings | ❌ | ❌ | ❌ | ❌ | No access |

## 3.6 Inventory Manager (inventory)

| Feature | View | Create | Update | Delete | Expected Result |
|---------|------|--------|--------|--------|----------------|
| Authentication | ✅ | - | - | - | Should login successfully |
| Menu | ✅ | ❌ | ❌ | ❌ | View only |
| Orders | ❌ | ❌ | ❌ | ❌ | No access |
| Tables | ❌ | ❌ | ❌ | ❌ | No access |
| Kitchen | ❌ | ❌ | ❌ | ❌ | No access |
| Inventory | ✅ | ✅ | ✅ | ❌ | Full access except delete |
| Reservations | ❌ | ❌ | ❌ | ❌ | No access |
| Payments | ❌ | ❌ | ❌ | ❌ | No access |
| Reports | ❌ | ❌ | ❌ | ❌ | No access |
| Users | ❌ | ❌ | ❌ | ❌ | No access |
| Settings | ❌ | ❌ | ❌ | ❌ | No access |

## 3.7 Host/Hostess (host)

| Feature | View | Create | Update | Delete | Expected Result |
|---------|------|--------|--------|--------|----------------|
| Authentication | ✅ | - | - | - | Should login successfully |
| Menu | ❌ | ❌ | ❌ | ❌ | No access |
| Orders | ❌ | ❌ | ❌ | ❌ | No access |
| Tables | ✅ | ❌ | ✅ | ❌ | View and update only |
| Kitchen | ❌ | ❌ | ❌ | ❌ | No access |
| Inventory | ❌ | ❌ | ❌ | ❌ | No access |
| Reservations | ✅ | ✅ | ✅ | ❌ | Create and update only |
| Payments | ❌ | ❌ | ❌ | ❌ | No access |
| Reports | ❌ | ❌ | ❌ | ❌ | No access |
| Users | ❌ | ❌ | ❌ | ❌ | No access |
| Settings | ❌ | ❌ | ❌ | ❌ | No access |

---

# 4. Test Execution Plan

## 4.1 Phase 1: Authentication Testing

**Test Case 1.1:** Login for each role
- **Steps:** Login with each test user
- **Expected:** Successful login with valid JWT token
- **Actual:** To be tested

**Test Case 1.2:** Invalid credentials
- **Steps:** Login with invalid username/password
- **Expected:** Login failure with error message
- **Actual:** To be tested

**Test Case 1.3:** Token validation
- **Steps:** Use valid token for API requests
- **Expected:** Successful API calls
- **Actual:** To be tested

**Test Case 1.4:** Token expiration
- **Steps:** Use expired token
- **Expected:** Authentication failure
- **Actual:** To be tested

## 4.2 Phase 2: Menu Management Testing

**Test Case 2.1:** View categories (All roles)
- **Steps:** GET /api/v1/menu/categories
- **Expected:** Admin, Manager, Waiter, Kitchen, Cashier, Inventory can view
- **Actual:** To be tested

**Test Case 2.2:** Create category (Admin, Manager)
- **Steps:** POST /api/v1/menu/categories
- **Expected:** Admin and Manager can create
- **Actual:** To be tested

**Test Case 2.3:** Create category (Other roles)
- **Steps:** POST /api/v1/menu/categories with Waiter, Kitchen, Cashier, Inventory, Host
- **Expected:** Permission denied
- **Actual:** To be tested

## 4.3 Phase 3: Order Management Testing

**Test Case 3.1:** View orders (Admin, Manager, Waiter, Kitchen, Cashier)
- **Steps:** GET /api/v1/orders
- **Expected:** Specified roles can view
- **Actual:** To be tested

**Test Case 3.2:** Create order (Admin, Manager, Waiter, Cashier)
- **Steps:** POST /api/v1/orders
- **Expected:** Specified roles can create
- **Actual:** To be tested

**Test Case 3.3:** Update order status (Admin, Manager, Waiter, Kitchen)
- **Steps:** PUT /api/v1/orders/{id}/status
- **Expected:** Specified roles can update
- **Actual:** To be tested

## 4.4 Phase 4: Table Management Testing

**Test Case 4.1:** View tables (Admin, Manager, Waiter, Host)
- **Steps:** GET /api/v1/tables
- **Expected:** Specified roles can view
- **Actual:** To be tested

**Test Case 4.2:** Update table status (Admin, Manager, Waiter, Host)
- **Steps:** PUT /api/v1/tables/{id}/status
- **Expected:** Specified roles can update
- **Actual:** To be tested

## 4.5 Phase 5: Kitchen Operations Testing

**Test Case 5.1:** View kitchen orders (Admin, Manager, Kitchen)
- **Steps:** GET /api/v1/kitchen/orders
- **Expected:** Specified roles can view
- **Actual:** To be tested

**Test Case 5.2:** Update kitchen order status (Admin, Manager, Kitchen)
- **Steps:** PUT /api/v1/kitchen/orders/{id}/status
- **Expected:** Specified roles can update
- **Actual:** To be tested

## 4.6 Phase 6: Inventory Management Testing

**Test Case 6.1:** View inventory (Admin, Manager, Inventory)
- **Steps:** GET /api/v1/inventory
- **Expected:** Specified roles can view
- **Actual:** To be tested

**Test Case 6.2:** Stock adjustment (Admin, Manager, Inventory)
- **Steps:** POST /api/v1/inventory/adjustments
- **Expected:** Specified roles can adjust
- **Actual:** To be tested

## 4.7 Phase 7: Reservation Management Testing

**Test Case 7.1:** View reservations (Admin, Manager, Waiter, Host)
- **Steps:** GET /api/v1/reservations
- **Expected:** Specified roles can view
- **Actual:** To be tested

**Test Case 7.2:** Create reservation (Admin, Manager, Waiter, Host)
- **Steps:** POST /api/v1/reservations
- **Expected:** Specified roles can create
- **Actual:** To be tested

## 4.8 Phase 8: Payment Processing Testing

**Test Case 8.1:** Process payment (Admin, Manager, Cashier)
- **Steps:** POST /api/v1/payments
- **Expected:** Specified roles can process
- **Actual:** To be tested

---

# 5. Test Execution Results

## 5.1 Authentication Results

| Test Case | Status | Notes |
|-----------|--------|-------|
| 1.1 Login for each role | ⏳ Pending | |
| 1.2 Invalid credentials | ⏳ Pending | |
| 1.3 Token validation | ⏳ Pending | |
| 1.4 Token expiration | ⏳ Pending | |

## 5.2 Menu Management Results

| Test Case | Status | Notes |
|-----------|--------|-------|
| 2.1 View categories | ⏳ Pending | |
| 2.2 Create category (Admin, Manager) | ⏳ Pending | |
| 2.3 Create category (Other roles) | ⏳ Pending | |

## 5.3 Order Management Results

| Test Case | Status | Notes |
|-----------|--------|-------|
| 3.1 View orders | ⏳ Pending | |
| 3.2 Create order | ⏳ Pending | |
| 3.3 Update order status | ⏳ Pending | |

## 5.4 Table Management Results

| Test Case | Status | Notes |
|-----------|--------|-------|
| 4.1 View tables | ⏳ Pending | |
| 4.2 Update table status | ⏳ Pending | |

## 5.5 Kitchen Operations Results

| Test Case | Status | Notes |
|-----------|--------|-------|
| 5.1 View kitchen orders | ⏳ Pending | |
| 5.2 Update kitchen order status | ⏳ Pending | |

## 5.6 Inventory Management Results

| Test Case | Status | Notes |
|-----------|--------|-------|
| 6.1 View inventory | ⏳ Pending | |
| 6.2 Stock adjustment | ⏳ Pending | |

## 5.7 Reservation Management Results

| Test Case | Status | Notes |
|-----------|--------|-------|
| 7.1 View reservations | ⏳ Pending | |
| 7.2 Create reservation | ⏳ Pending | |

## 5.8 Payment Processing Results

| Test Case | Status | Notes |
|-----------|--------|-------|
| 8.1 Process payment | ⏳ Pending | |

---

# 6. Issue Tracking

## 6.1 Critical Issues

| Issue ID | Description | Severity | Status |
|----------|-------------|----------|--------|
| - | - | - | - |

## 6.2 Medium Issues

| Issue ID | Description | Severity | Status |
|----------|-------------|----------|--------|
| - | - | - | - |

## 6.3 Low Issues

| Issue ID | Description | Severity | Status |
|----------|-------------|----------|--------|
| - | - | - | - |

---

# 7. Recommendations

## 7.1 Immediate Actions

- [ ] Execute all test cases
- [ ] Document results
- [ ] Fix critical issues
- [ ] Re-test after fixes

## 7.2 Long-term Improvements

- [ ] Add automated regression tests
- [ ] Implement role-based UI
- [ ] Add audit logging
- [ ] Enhance permission system

---

**End of Document**

**Document ID:** TEST-PLAN-FB-001

**Version:** 1.0
