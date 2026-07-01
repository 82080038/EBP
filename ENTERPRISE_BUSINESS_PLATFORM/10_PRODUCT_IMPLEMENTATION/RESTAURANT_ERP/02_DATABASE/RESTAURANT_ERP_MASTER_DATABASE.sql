-- RESTAURANT ERP
-- Master Database Schema
-- Document ID: EBP-PRODUCT-RESTAURANT-DB-001
-- Version: 1.0

-- ============================================
-- EBP STANDARD COLUMNS (All tables must have)
-- ============================================
-- id BIGINT AUTO_INCREMENT PRIMARY KEY
-- tenant_id BIGINT NOT NULL
-- created_at DATETIME NOT NULL
-- created_by BIGINT
-- updated_at DATETIME
-- updated_by BIGINT
-- deleted_at DATETIME NULL
-- version INT DEFAULT 1
-- status VARCHAR(20) DEFAULT 'ACTIVE'

-- ============================================
-- PHASE 1: FOUNDATION
-- ============================================

-- Users Table
CREATE TABLE users (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    avatar VARCHAR(255),
    status VARCHAR(20) DEFAULT 'ACTIVE',
    last_login_at DATETIME,
    last_login_ip VARCHAR(45),
    mfa_enabled BOOLEAN DEFAULT FALSE,
    mfa_secret VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by BIGINT,
    deleted_at DATETIME NULL,
    version INT DEFAULT 1,
    INDEX idx_tenant (tenant_id),
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Roles Table
CREATE TABLE roles (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    is_system BOOLEAN DEFAULT FALSE,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by BIGINT,
    deleted_at DATETIME NULL,
    version INT DEFAULT 1,
    UNIQUE KEY uk_tenant_name (tenant_id, name),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permissions Table
CREATE TABLE permissions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) UNIQUE NOT NULL,
    description TEXT,
    module VARCHAR(50),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_module (module)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role Permissions Table
CREATE TABLE role_permissions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT NOT NULL,
    permission_id BIGINT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_role_permission (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Roles Table
CREATE TABLE user_roles (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    role_id BIGINT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_user_role (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tenants Table
CREATE TABLE tenants (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    domain VARCHAR(150),
    logo VARCHAR(255),
    status VARCHAR(20) DEFAULT 'ACTIVE',
    subscription_plan VARCHAR(50),
    subscription_expiry DATETIME,
    max_users INT DEFAULT 10,
    max_branches INT DEFAULT 5,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by BIGINT,
    deleted_at DATETIME NULL,
    version INT DEFAULT 1,
    INDEX idx_code (code),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Configurations Table
CREATE TABLE configurations (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NULL,
    config_group VARCHAR(100),
    config_key VARCHAR(150) NOT NULL,
    config_value JSON,
    config_type VARCHAR(20) DEFAULT 'STRING',
    description TEXT,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by BIGINT,
    deleted_at DATETIME NULL,
    version INT DEFAULT 1,
    UNIQUE KEY uk_tenant_key (tenant_id, config_key),
    INDEX idx_tenant (tenant_id),
    INDEX idx_group (config_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit Logs Table
CREATE TABLE audit_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    user_id BIGINT,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(100),
    entity_id BIGINT,
    old_value JSON,
    new_value JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_user (user_id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PHASE 2: MASTER DATA
-- ============================================

-- Restaurants Table
CREATE TABLE restaurants (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(50) NOT NULL,
    address TEXT,
    city VARCHAR(100),
    province VARCHAR(100),
    country VARCHAR(100) DEFAULT 'Indonesia',
    postal_code VARCHAR(20),
    phone VARCHAR(20),
    email VARCHAR(150),
    logo VARCHAR(255),
    tax_number VARCHAR(50),
    operating_hours JSON,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by BIGINT,
    deleted_at DATETIME NULL,
    version INT DEFAULT 1,
    UNIQUE KEY uk_tenant_code (tenant_id, code),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Branches Table
CREATE TABLE branches (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    restaurant_id BIGINT NOT NULL,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(50) NOT NULL,
    address TEXT,
    city VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(150),
    manager_id BIGINT,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    operating_hours JSON,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by BIGINT,
    deleted_at DATETIME NULL,
    version INT DEFAULT 1,
    UNIQUE KEY uk_tenant_code (tenant_id, code),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    INDEX idx_tenant (tenant_id),
    INDEX idx_restaurant (restaurant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tables Table
CREATE TABLE tables (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    branch_id BIGINT NOT NULL,
    name VARCHAR(50) NOT NULL,
    code VARCHAR(50) NOT NULL,
    capacity INT DEFAULT 4,
    position_x INT,
    position_y INT,
    width INT,
    height INT,
    shape VARCHAR(20) DEFAULT 'RECTANGLE',
    status VARCHAR(20) DEFAULT 'AVAILABLE',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by BIGINT,
    deleted_at DATETIME NULL,
    version INT DEFAULT 1,
    UNIQUE KEY uk_branch_code (branch_id, code),
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    INDEX idx_tenant (tenant_id),
    INDEX idx_branch (branch_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Menu Categories Table
CREATE TABLE menu_categories (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    restaurant_id BIGINT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) NOT NULL,
    parent_id BIGINT,
    description TEXT,
    image VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_visible BOOLEAN DEFAULT TRUE,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by BIGINT,
    deleted_at DATETIME NULL,
    version INT DEFAULT 1,
    UNIQUE KEY uk_tenant_code (tenant_id, code),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_id) REFERENCES menu_categories(id) ON DELETE SET NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Menu Items Table
CREATE TABLE menu_items (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    restaurant_id BIGINT,
    category_id BIGINT,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(50) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    price DECIMAL(15, 2) NOT NULL,
    cost DECIMAL(15, 2) DEFAULT 0,
    unit VARCHAR(20) DEFAULT 'portion',
    preparation_time INT DEFAULT 0,
    is_available BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by BIGINT,
    deleted_at DATETIME NULL,
    version INT DEFAULT 1,
    UNIQUE KEY uk_tenant_code (tenant_id, code),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES menu_categories(id) ON DELETE SET NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_category (category_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Menu Modifiers Table
CREATE TABLE menu_modifiers (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    menu_item_id BIGINT NOT NULL,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(20) DEFAULT 'OPTION',
    options JSON,
    is_required BOOLEAN DEFAULT FALSE,
    max_selection INT DEFAULT 1,
    sort_order INT DEFAULT 0,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by BIGINT,
    deleted_at DATETIME NULL,
    version INT DEFAULT 1,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    INDEX idx_tenant (tenant_id),
    INDEX idx_menu_item (menu_item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customers Table
CREATE TABLE customers (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150),
    phone VARCHAR(20),
    address TEXT,
    date_of_birth DATE,
    loyalty_points INT DEFAULT 0,
    loyalty_tier VARCHAR(20) DEFAULT 'BRONZE',
    preferences JSON,
    notes TEXT,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by BIGINT,
    deleted_at DATETIME NULL,
    version INT DEFAULT 1,
    INDEX idx_tenant (tenant_id),
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_loyalty (loyalty_tier)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Suppliers Table
CREATE TABLE suppliers (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(50) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(150),
    address TEXT,
    city VARCHAR(100),
    tax_number VARCHAR(50),
    payment_term INT DEFAULT 30,
    credit_limit DECIMAL(15, 2) DEFAULT 0,
    rating DECIMAL(3, 2) DEFAULT 0,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by BIGINT,
    deleted_at DATETIME NULL,
    version INT DEFAULT 1,
    UNIQUE KEY uk_tenant_code (tenant_id, code),
    INDEX idx_tenant (tenant_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Employees Table
CREATE TABLE employees (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    branch_id BIGINT,
    user_id BIGINT,
    employee_number VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(50),
    department VARCHAR(50),
    phone VARCHAR(20),
    email VARCHAR(150),
    hire_date DATE,
    salary DECIMAL(15, 2),
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by BIGINT,
    deleted_at DATETIME NULL,
    version INT DEFAULT 1,
    UNIQUE KEY uk_tenant_number (tenant_id, employee_number),
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_branch (branch_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PHASE 3: POS OPERATION
-- ============================================

-- Orders Table
CREATE TABLE orders (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    branch_id BIGINT NOT NULL,
    order_number VARCHAR(50) NOT NULL,
    table_id BIGINT,
    customer_id BIGINT,
    cashier_id BIGINT,
    waiter_id BIGINT,
    order_type VARCHAR(20) NOT NULL,
    status VARCHAR(20) DEFAULT 'PENDING',
    subtotal DECIMAL(15, 2) NOT NULL,
    tax DECIMAL(15, 2) DEFAULT 0,
    service_charge DECIMAL(15, 2) DEFAULT 0,
    discount DECIMAL(15, 2) DEFAULT 0,
    total DECIMAL(15, 2) NOT NULL,
    paid_amount DECIMAL(15, 2) DEFAULT 0,
    change_amount DECIMAL(15, 2) DEFAULT 0,
    notes TEXT,
    order_time DATETIME NOT NULL,
    completed_time DATETIME,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by BIGINT,
    deleted_at DATETIME NULL,
    version INT DEFAULT 1,
    UNIQUE KEY uk_branch_number (branch_id, order_number),
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (cashier_id) REFERENCES employees(id) ON DELETE SET NULL,
    FOREIGN KEY (waiter_id) REFERENCES employees(id) ON DELETE SET NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_branch (branch_id),
    INDEX idx_order_number (order_number),
    INDEX idx_status (status),
    INDEX idx_order_time (order_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order Items Table
CREATE TABLE order_items (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    order_id BIGINT NOT NULL,
    menu_item_id BIGINT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(15, 2) NOT NULL,
    subtotal DECIMAL(15, 2) NOT NULL,
    discount DECIMAL(15, 2) DEFAULT 0,
    modifiers JSON,
    notes TEXT,
    status VARCHAR(20) DEFAULT 'PENDING',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE RESTRICT,
    INDEX idx_tenant (tenant_id),
    INDEX idx_order (order_id),
    INDEX idx_menu_item (menu_item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments Table
CREATE TABLE payments (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    order_id BIGINT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    reference_number VARCHAR(100),
    card_last_4 VARCHAR(4),
    payment_time DATETIME NOT NULL,
    status VARCHAR(20) DEFAULT 'COMPLETED',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE RESTRICT,
    INDEX idx_tenant (tenant_id),
    INDEX idx_order (order_id),
    INDEX idx_payment_time (payment_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reservations Table
CREATE TABLE reservations (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    branch_id BIGINT NOT NULL,
    customer_id BIGINT,
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    guest_count INT NOT NULL,
    table_id BIGINT,
    deposit_amount DECIMAL(15, 2) DEFAULT 0,
    deposit_status VARCHAR(20) DEFAULT 'PENDING',
    status VARCHAR(20) DEFAULT 'CONFIRMED',
    notes TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by BIGINT,
    deleted_at DATETIME NULL,
    version INT DEFAULT 1,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE SET NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_branch (branch_id),
    INDEX idx_date (reservation_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PHASE 4: INVENTORY
-- ============================================

-- Inventory Items Table
CREATE TABLE inventory_items (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    branch_id BIGINT,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(50) NOT NULL,
    category_id BIGINT,
    unit VARCHAR(20) NOT NULL,
    description TEXT,
    supplier_id BIGINT,
    min_stock DECIMAL(15, 2) DEFAULT 0,
    max_stock DECIMAL(15, 2) DEFAULT 0,
    reorder_point DECIMAL(15, 2) DEFAULT 0,
    cost_price DECIMAL(15, 2) DEFAULT 0,
    selling_price DECIMAL(15, 2) DEFAULT 0,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by BIGINT,
    deleted_at DATETIME NULL,
    version INT DEFAULT 1,
    UNIQUE KEY uk_tenant_code (tenant_id, code),
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_branch (branch_id),
    INDEX idx_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Movements Table
CREATE TABLE stock_movements (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    branch_id BIGINT NOT NULL,
    inventory_item_id BIGINT NOT NULL,
    movement_type VARCHAR(20) NOT NULL,
    quantity DECIMAL(15, 2) NOT NULL,
    unit_cost DECIMAL(15, 2) DEFAULT 0,
    total_cost DECIMAL(15, 2) DEFAULT 0,
    reference_type VARCHAR(50),
    reference_id BIGINT,
    notes TEXT,
    movement_date DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE RESTRICT,
    INDEX idx_tenant (tenant_id),
    INDEX idx_branch (branch_id),
    INDEX idx_item (inventory_item_id),
    INDEX idx_type (movement_type),
    INDEX idx_date (movement_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Purchase Orders Table
CREATE TABLE purchase_orders (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    branch_id BIGINT NOT NULL,
    supplier_id BIGINT NOT NULL,
    po_number VARCHAR(50) NOT NULL,
    order_date DATE NOT NULL,
    expected_date DATE,
    subtotal DECIMAL(15, 2) NOT NULL,
    tax DECIMAL(15, 2) DEFAULT 0,
    discount DECIMAL(15, 2) DEFAULT 0,
    total DECIMAL(15, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'DRAFT',
    notes TEXT,
    approved_by BIGINT,
    approved_at DATETIME,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by BIGINT,
    deleted_at DATETIME NULL,
    version INT DEFAULT 1,
    UNIQUE KEY uk_branch_number (branch_id, po_number),
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE RESTRICT,
    INDEX idx_tenant (tenant_id),
    INDEX idx_branch (branch_id),
    INDEX idx_supplier (supplier_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Purchase Order Items Table
CREATE TABLE purchase_order_items (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    purchase_order_id BIGINT NOT NULL,
    inventory_item_id BIGINT NOT NULL,
    quantity DECIMAL(15, 2) NOT NULL,
    unit_price DECIMAL(15, 2) NOT NULL,
    subtotal DECIMAL(15, 2) NOT NULL,
    received_quantity DECIMAL(15, 2) DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE RESTRICT,
    INDEX idx_tenant (tenant_id),
    INDEX idx_po (purchase_order_id),
    INDEX idx_item (inventory_item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Goods Receipts Table
CREATE TABLE goods_receipts (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    branch_id BIGINT NOT NULL,
    purchase_order_id BIGINT,
    gr_number VARCHAR(50) NOT NULL,
    receipt_date DATE NOT NULL,
    notes TEXT,
    status VARCHAR(20) DEFAULT 'COMPLETED',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE SET NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_branch (branch_id),
    INDEX idx_po (purchase_order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Goods Receipt Items Table
CREATE TABLE goods_receipt_items (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    goods_receipt_id BIGINT NOT NULL,
    inventory_item_id BIGINT NOT NULL,
    quantity DECIMAL(15, 2) NOT NULL,
    unit_cost DECIMAL(15, 2) NOT NULL,
    batch_number VARCHAR(50),
    expiry_date DATE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (goods_receipt_id) REFERENCES goods_receipts(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE RESTRICT,
    INDEX idx_tenant (tenant_id),
    INDEX idx_gr (goods_receipt_id),
    INDEX idx_item (inventory_item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Opnames Table
CREATE TABLE stock_opnames (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    branch_id BIGINT NOT NULL,
    opname_number VARCHAR(50) NOT NULL,
    opname_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'DRAFT',
    notes TEXT,
    approved_by BIGINT,
    approved_at DATETIME,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by BIGINT,
    deleted_at DATETIME NULL,
    version INT DEFAULT 1,
    UNIQUE KEY uk_branch_number (branch_id, opname_number),
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
    INDEX idx_tenant (tenant_id),
    INDEX idx_branch (branch_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Opname Items Table
CREATE TABLE stock_opname_items (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    stock_opname_id BIGINT NOT NULL,
    inventory_item_id BIGINT NOT NULL,
    system_quantity DECIMAL(15, 2) NOT NULL,
    actual_quantity DECIMAL(15, 2) NOT NULL,
    variance DECIMAL(15, 2) NOT NULL,
    variance_reason TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (stock_opname_id) REFERENCES stock_opnames(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE RESTRICT,
    INDEX idx_tenant (tenant_id),
    INDEX idx_opname (stock_opname_id),
    INDEX idx_item (inventory_item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Recipes Table
CREATE TABLE recipes (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    menu_item_id BIGINT NOT NULL,
    name VARCHAR(150) NOT NULL,
    yield_quantity DECIMAL(15, 2) NOT NULL,
    yield_unit VARCHAR(20) NOT NULL,
    preparation_time INT DEFAULT 0,
    instructions TEXT,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by BIGINT,
    deleted_at DATETIME NULL,
    version INT DEFAULT 1,
    UNIQUE KEY uk_menu (menu_item_id),
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Recipe Ingredients Table
CREATE TABLE recipe_ingredients (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    recipe_id BIGINT NOT NULL,
    inventory_item_id BIGINT NOT NULL,
    quantity DECIMAL(15, 2) NOT NULL,
    unit VARCHAR(20) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE RESTRICT,
    INDEX idx_tenant (tenant_id),
    INDEX idx_recipe (recipe_id),
    INDEX idx_item (inventory_item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PHASE 5: FINANCE
-- ============================================

-- Cashier Shifts Table
CREATE TABLE cashier_shifts (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    branch_id BIGINT NOT NULL,
    cashier_id BIGINT NOT NULL,
    shift_number VARCHAR(50) NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME,
    opening_balance DECIMAL(15, 2) DEFAULT 0,
    total_sales DECIMAL(15, 2) DEFAULT 0,
    total_cash DECIMAL(15, 2) DEFAULT 0,
    total_card DECIMAL(15, 2) DEFAULT 0,
    total_ewallet DECIMAL(15, 2) DEFAULT 0,
    closing_balance DECIMAL(15, 2) DEFAULT 0,
    variance DECIMAL(15, 2) DEFAULT 0,
    status VARCHAR(20) DEFAULT 'OPEN',
    notes TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
    FOREIGN KEY (cashier_id) REFERENCES employees(id) ON DELETE RESTRICT,
    INDEX idx_tenant (tenant_id),
    INDEX idx_branch (branch_id),
    INDEX idx_cashier (cashier_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Expenses Table
CREATE TABLE expenses (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    branch_id BIGINT NOT NULL,
    category_id BIGINT,
    expense_number VARCHAR(50) NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    description TEXT,
    expense_date DATE NOT NULL,
    payment_method VARCHAR(50),
    reference_number VARCHAR(100),
    status VARCHAR(20) DEFAULT 'PENDING',
    approved_by BIGINT,
    approved_at DATETIME,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by BIGINT,
    deleted_at DATETIME NULL,
    version INT DEFAULT 1,
    UNIQUE KEY uk_branch_number (branch_id, expense_number),
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
    INDEX idx_tenant (tenant_id),
    INDEX idx_branch (branch_id),
    INDEX idx_category (category_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Accounting Journals Table
CREATE TABLE accounting_journals (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    journal_number VARCHAR(50) NOT NULL,
    journal_date DATE NOT NULL,
    reference_type VARCHAR(50),
    reference_id BIGINT,
    description TEXT,
    total_debit DECIMAL(15, 2) NOT NULL,
    total_credit DECIMAL(15, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'POSTED',
    posted_by BIGINT,
    posted_at DATETIME,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT,
    INDEX idx_tenant (tenant_id),
    INDEX idx_journal_number (journal_number),
    INDEX idx_date (journal_date),
    INDEX idx_reference (reference_type, reference_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Journal Lines Table
CREATE TABLE journal_lines (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    journal_id BIGINT NOT NULL,
    account_code VARCHAR(50) NOT NULL,
    account_name VARCHAR(150) NOT NULL,
    debit DECIMAL(15, 2) DEFAULT 0,
    credit DECIMAL(15, 2) DEFAULT 0,
    description TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (journal_id) REFERENCES accounting_journals(id) ON DELETE CASCADE,
    INDEX idx_tenant (tenant_id),
    INDEX idx_journal (journal_id),
    INDEX idx_account (account_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INDEXES FOR PERFORMANCE
-- ============================================

-- Composite indexes for common queries
CREATE INDEX idx_orders_branch_status ON orders(branch_id, status);
CREATE INDEX idx_orders_date_status ON orders(order_time, status);
CREATE INDEX idx_stock_movements_item_date ON stock_movements(inventory_item_id, movement_date);
CREATE INDEX idx_audit_logs_tenant_created ON audit_logs(tenant_id, created_at);

-- ============================================
-- END OF SCHEMA
-- ============================================
