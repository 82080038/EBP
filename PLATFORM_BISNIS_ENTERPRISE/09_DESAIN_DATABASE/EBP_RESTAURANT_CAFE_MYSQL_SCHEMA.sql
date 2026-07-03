/*
========================================================

ENTERPRISE BUSINESS PLATFORM (EBP)

RESTAURANT & CAFE ERP

MYSQL DATABASE SCHEMA

PART 1:
CORE ENTERPRISE FOUNDATION


Version:
1.0


Database:
MySQL 8.x


Architecture:

Multi Tenant
Audit Ready
Soft Delete
Enterprise Ready


========================================================
*/


CREATE DATABASE IF NOT EXISTS ebp_restaurant_erp
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;


USE ebp_restaurant_erp;



/*
========================================================
COMMON STANDARD

Semua tabel transaksi menggunakan:

tenant_id
created_by
created_at
updated_by
updated_at
deleted_at
status

========================================================
*/



/*
========================================================
TABLE:
tenants

Root Enterprise Tenant

========================================================
*/


CREATE TABLE tenants (

    tenant_id BIGINT AUTO_INCREMENT PRIMARY KEY,

    tenant_code VARCHAR(50)
        NOT NULL UNIQUE,


    tenant_name VARCHAR(150)
        NOT NULL,


    business_type VARCHAR(50)
        DEFAULT 'RESTAURANT',


    status ENUM(
        'ACTIVE',
        'INACTIVE'
    )
    DEFAULT 'ACTIVE',


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,


    updated_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,


    deleted_at TIMESTAMP NULL



) ENGINE=InnoDB;



CREATE INDEX idx_tenant_status
ON tenants(status);





/*
========================================================
TABLE:
companies


Satu tenant dapat memiliki beberapa perusahaan

========================================================
*/


CREATE TABLE companies (

    company_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    company_code VARCHAR(50)
        NOT NULL,


    company_name VARCHAR(150)
        NOT NULL,


    tax_number VARCHAR(100),


    address TEXT,


    phone VARCHAR(50),


    email VARCHAR(100),



    status ENUM(
        'ACTIVE',
        'INACTIVE'
    )
    DEFAULT 'ACTIVE',



    created_by BIGINT,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,


    updated_by BIGINT,


    updated_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,


    deleted_at TIMESTAMP NULL,



    CONSTRAINT fk_company_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id)



) ENGINE=InnoDB;



CREATE INDEX idx_company_tenant
ON companies(tenant_id);





/*
========================================================
TABLE:
branches


Cabang / Outlet

========================================================
*/


CREATE TABLE branches (


    branch_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    company_id BIGINT NOT NULL,


    branch_code VARCHAR(50)
        NOT NULL,


    branch_name VARCHAR(150)
        NOT NULL,


    address TEXT,


    phone VARCHAR(50),


    opening_time TIME,


    closing_time TIME,



    status ENUM(
        'ACTIVE',
        'INACTIVE'
    )
    DEFAULT 'ACTIVE',



    created_by BIGINT,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,


    updated_by BIGINT,


    updated_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,


    deleted_at TIMESTAMP NULL,



    CONSTRAINT fk_branch_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id),



    CONSTRAINT fk_branch_company

    FOREIGN KEY (company_id)

    REFERENCES companies(company_id)



) ENGINE=InnoDB;



CREATE INDEX idx_branch_tenant
ON branches(tenant_id);


CREATE INDEX idx_branch_company
ON branches(company_id);





/*
========================================================
USER MANAGEMENT

========================================================
*/


CREATE TABLE users (


    user_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    username VARCHAR(100)
        NOT NULL UNIQUE,


    password_hash VARCHAR(255)
        NOT NULL,


    full_name VARCHAR(150),


    email VARCHAR(150),


    phone VARCHAR(50),



    status ENUM(
        'ACTIVE',
        'LOCKED',
        'INACTIVE'
    )
    DEFAULT 'ACTIVE',



    last_login TIMESTAMP NULL,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,


    updated_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,


    deleted_at TIMESTAMP NULL,



    CONSTRAINT fk_user_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id)



) ENGINE=InnoDB;




CREATE INDEX idx_user_tenant
ON users(tenant_id);





/*
========================================================
ROLE MANAGEMENT

========================================================
*/


CREATE TABLE roles (


    role_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT,


    role_code VARCHAR(50)
        NOT NULL,


    role_name VARCHAR(100)
        NOT NULL,


    description TEXT,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,


    updated_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP



) ENGINE=InnoDB;





/*
========================================================
PERMISSION

========================================================
*/


CREATE TABLE permissions (


    permission_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    permission_code VARCHAR(100)
        UNIQUE,


    permission_name VARCHAR(150),


    description TEXT


) ENGINE=InnoDB;





/*
========================================================
USER ROLE

Many To Many

========================================================
*/


CREATE TABLE user_roles (


    user_id BIGINT NOT NULL,


    role_id BIGINT NOT NULL,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    PRIMARY KEY(
        user_id,
        role_id
    ),



    CONSTRAINT fk_user_role_user

    FOREIGN KEY(user_id)

    REFERENCES users(user_id),



    CONSTRAINT fk_user_role_role

    FOREIGN KEY(role_id)

    REFERENCES roles(role_id)



) ENGINE=InnoDB;





/*
========================================================
ROLE PERMISSION

Many To Many

========================================================
*/


CREATE TABLE role_permissions (


    role_id BIGINT NOT NULL,


    permission_id BIGINT NOT NULL,



    PRIMARY KEY(
        role_id,
        permission_id
    ),



    CONSTRAINT fk_role_permission_role

    FOREIGN KEY(role_id)

    REFERENCES roles(role_id),



    CONSTRAINT fk_role_permission_permission

    FOREIGN KEY(permission_id)

    REFERENCES permissions(permission_id)



) ENGINE=InnoDB;





/*
========================================================
END OF PART 1

CORE FOUNDATION

========================================================
*/



/*
========================================================
PART 2:
MASTER DATA SCHEMA

Berisi:

- Customer Management
- Supplier Management
- Menu Management
- Recipe Management
- Inventory Management

========================================================
*/



/*
========================================================
CUSTOMER MANAGEMENT

========================================================
*/


CREATE TABLE customers (

    customer_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    customer_code VARCHAR(50)
        NOT NULL,


    name VARCHAR(150)
        NOT NULL,


    phone VARCHAR(50),


    email VARCHAR(150),


    address TEXT,



    membership_level ENUM(
        'REGULAR',
        'SILVER',
        'GOLD',
        'PLATINUM'
    )
    DEFAULT 'REGULAR',



    status ENUM(
        'ACTIVE',
        'INACTIVE'
    )
    DEFAULT 'ACTIVE',



    created_by BIGINT,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,


    updated_by BIGINT,


    updated_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,


    deleted_at TIMESTAMP NULL,



    CONSTRAINT fk_customer_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id)



) ENGINE=InnoDB;



CREATE INDEX idx_customer_tenant
ON customers(tenant_id);





/*
========================================================
CUSTOMER MEMBERSHIP

========================================================
*/


CREATE TABLE customer_memberships (


    membership_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    customer_id BIGINT NOT NULL,


    membership_type VARCHAR(50),


    start_date DATE,


    end_date DATE,



    status ENUM(
        'ACTIVE',
        'EXPIRED'
    )
    DEFAULT 'ACTIVE',



    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_membership_customer

    FOREIGN KEY (customer_id)

    REFERENCES customers(customer_id)



) ENGINE=InnoDB;





/*
========================================================
SUPPLIER MANAGEMENT

========================================================
*/


CREATE TABLE suppliers (


    supplier_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    supplier_code VARCHAR(50)
        NOT NULL,


    supplier_name VARCHAR(150)
        NOT NULL,


    contact_person VARCHAR(100),


    phone VARCHAR(50),


    email VARCHAR(150),


    address TEXT,



    payment_term VARCHAR(50),


    tax_number VARCHAR(100),



    status ENUM(
        'ACTIVE',
        'INACTIVE'
    )
    DEFAULT 'ACTIVE',



    created_by BIGINT,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,


    updated_by BIGINT,


    updated_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,


    deleted_at TIMESTAMP NULL,



    CONSTRAINT fk_supplier_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id)



) ENGINE=InnoDB;



CREATE INDEX idx_supplier_tenant
ON suppliers(tenant_id);





/*
========================================================
MENU CATEGORIES

========================================================
*/


CREATE TABLE menu_categories (


    category_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    category_code VARCHAR(50)
        NOT NULL,


    category_name VARCHAR(100)
        NOT NULL,


    description TEXT,



    display_order INT DEFAULT 0,



    status ENUM(
        'ACTIVE',
        'INACTIVE'
    )
    DEFAULT 'ACTIVE',



    created_by BIGINT,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,


    updated_by BIGINT,


    updated_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,



    CONSTRAINT fk_category_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id)



) ENGINE=InnoDB;





/*
========================================================
MENUS

========================================================
*/


CREATE TABLE menus (


    menu_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    category_id BIGINT,


    menu_code VARCHAR(50)
        NOT NULL,


    menu_name VARCHAR(150)
        NOT NULL,


    description TEXT,



    selling_price DECIMAL(18,2)
        NOT NULL,



    cost_price DECIMAL(18,2),


    image_url VARCHAR(255),



    status ENUM(
        'ACTIVE',
        'INACTIVE',
        'OUT_OF_STOCK'
    )
    DEFAULT 'ACTIVE',



    created_by BIGINT,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,


    updated_by BIGINT,


    updated_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,


    deleted_at TIMESTAMP NULL,



    CONSTRAINT fk_menu_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id),



    CONSTRAINT fk_menu_category

    FOREIGN KEY (category_id)

    REFERENCES menu_categories(category_id)



) ENGINE=InnoDB;



CREATE INDEX idx_menu_tenant
ON menus(tenant_id);





/*
========================================================
MENU PRICES

Untuk menyimpan histori harga

========================================================
*/


CREATE TABLE menu_prices (


    price_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    menu_id BIGINT NOT NULL,


    price DECIMAL(18,2)
        NOT NULL,


    effective_date DATE,


    expired_date DATE,



    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_price_menu

    FOREIGN KEY (menu_id)

    REFERENCES menus(menu_id)



) ENGINE=InnoDB;





/*
========================================================
RECIPES

Resep menu

========================================================
*/


CREATE TABLE recipes (


    recipe_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    menu_id BIGINT NOT NULL,


    version INT DEFAULT 1,


    effective_date DATE,



    status ENUM(
        'ACTIVE',
        'INACTIVE'
    )
    DEFAULT 'ACTIVE',



    created_by BIGINT,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_recipe_menu

    FOREIGN KEY (menu_id)

    REFERENCES menus(menu_id)



) ENGINE=InnoDB;





/*
========================================================
RECIPE DETAILS

Detail bahan resep

========================================================
*/


CREATE TABLE recipe_details (


    recipe_detail_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    recipe_id BIGINT NOT NULL,


    item_id BIGINT NOT NULL,


    quantity DECIMAL(18,4)
        NOT NULL,


    unit VARCHAR(50),


    cost DECIMAL(18,2),



    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_recipe_detail_recipe

    FOREIGN KEY (recipe_id)

    REFERENCES recipes(recipe_id),



    CONSTRAINT fk_recipe_detail_item

    FOREIGN KEY (item_id)

    REFERENCES inventory_items(item_id)



) ENGINE=InnoDB;





/*
========================================================
INVENTORY CATEGORIES

========================================================
*/


CREATE TABLE inventory_categories (


    inventory_category_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    category_code VARCHAR(50)
        NOT NULL,


    category_name VARCHAR(100)
        NOT NULL,


    description TEXT,



    created_by BIGINT,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_inventory_category_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id)



) ENGINE=InnoDB;





/*
========================================================
INVENTORY ITEMS

Bahan baku

========================================================
*/


CREATE TABLE inventory_items (


    item_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    category_id BIGINT,


    item_code VARCHAR(50)
        NOT NULL,


    item_name VARCHAR(150)
        NOT NULL,


    description TEXT,



    unit_id BIGINT,



    minimum_stock DECIMAL(18,4),


    maximum_stock DECIMAL(18,4),


    current_cost DECIMAL(18,2),


    average_cost DECIMAL(18,2),



    status ENUM(
        'ACTIVE',
        'INACTIVE'
    )
    DEFAULT 'ACTIVE',



    created_by BIGINT,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,


    updated_by BIGINT,


    updated_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,


    deleted_at TIMESTAMP NULL,



    CONSTRAINT fk_item_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id),



    CONSTRAINT fk_item_category

    FOREIGN KEY (category_id)

    REFERENCES inventory_categories(inventory_category_id)



) ENGINE=InnoDB;



CREATE INDEX idx_item_tenant
ON inventory_items(tenant_id);





/*
========================================================
UNITS

Satuan

========================================================
*/


CREATE TABLE units (


    unit_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    unit_code VARCHAR(20)
        NOT NULL UNIQUE,


    unit_name VARCHAR(50)
        NOT NULL


) ENGINE=InnoDB;





/*
========================================================
END OF PART 2

MASTER DATA

========================================================
*/



/*
========================================================
PART 3:
TRANSACTION DATA SCHEMA

Berisi:

- Order Management
- Payment Management
- Kitchen Management
- Table Management

========================================================
*/



/*
========================================================
RESTAURANT TABLES

Meja restoran

========================================================
*/


CREATE TABLE restaurant_tables (

    table_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    branch_id BIGINT NOT NULL,


    table_number VARCHAR(20)
        NOT NULL,


    capacity INT DEFAULT 4,


    area VARCHAR(50),


    status ENUM(
        'AVAILABLE',
        'OCCUPIED',
        'RESERVED',
        'CLEANING'
    )
    DEFAULT 'AVAILABLE',



    created_by BIGINT,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,


    updated_by BIGINT,


    updated_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,



    CONSTRAINT fk_table_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id),



    CONSTRAINT fk_table_branch

    FOREIGN KEY (branch_id)

    REFERENCES branches(branch_id)



) ENGINE=InnoDB;



CREATE INDEX idx_table_branch
ON restaurant_tables(branch_id);





/*
========================================================
ORDERS

Transaksi order

========================================================
*/


CREATE TABLE orders (


    order_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    branch_id BIGINT NOT NULL,


    customer_id BIGINT,


    table_id BIGINT,


    order_number VARCHAR(50)
        NOT NULL UNIQUE,


    order_type ENUM(
        'DINE_IN',
        'TAKEAWAY',
        'DELIVERY',
        'ONLINE'
    )
    DEFAULT 'DINE_IN',



    order_status ENUM(
        'NEW',
        'CONFIRMED',
        'COOKING',
        'READY',
        'SERVED',
        'PAID',
        'CANCELLED'
    )
    DEFAULT 'NEW',



    subtotal DECIMAL(18,2),


    discount DECIMAL(18,2),


    tax DECIMAL(18,2),


    service_charge DECIMAL(18,2),


    total_amount DECIMAL(18,2),


    notes TEXT,



    created_by BIGINT,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,


    updated_by BIGINT,


    updated_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,



    CONSTRAINT fk_order_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id),



    CONSTRAINT fk_order_branch

    FOREIGN KEY (branch_id)

    REFERENCES branches(branch_id),



    CONSTRAINT fk_order_customer

    FOREIGN KEY (customer_id)

    REFERENCES customers(customer_id),



    CONSTRAINT fk_order_table

    FOREIGN KEY (table_id)

    REFERENCES restaurant_tables(table_id)



) ENGINE=InnoDB;



CREATE INDEX idx_order_branch
ON orders(branch_id);





/*
========================================================
ORDER DETAILS

Detail item order

========================================================
*/


CREATE TABLE order_details (


    order_detail_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    order_id BIGINT NOT NULL,


    menu_id BIGINT NOT NULL,


    qty INT NOT NULL,


    unit_price DECIMAL(18,2)
        NOT NULL,


    discount DECIMAL(18,2),


    subtotal DECIMAL(18,2),


    notes TEXT,



    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_order_detail_order

    FOREIGN KEY (order_id)

    REFERENCES orders(order_id),



    CONSTRAINT fk_order_detail_menu

    FOREIGN KEY (menu_id)

    REFERENCES menus(menu_id)



) ENGINE=InnoDB;





/*
========================================================
PAYMENTS

Pembayaran

========================================================
*/


CREATE TABLE payments (


    payment_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    order_id BIGINT NOT NULL,


    payment_method ENUM(
        'CASH',
        'DEBIT',
        'CREDIT_CARD',
        'QR_PAYMENT',
        'TRANSFER',
        'E_WALLET'
    ),


    amount DECIMAL(18,2)
        NOT NULL,


    change_amount DECIMAL(18,2),


    payment_status ENUM(
        'PENDING',
        'COMPLETED',
        'REFUNDED',
        'FAILED'
    )
    DEFAULT 'PENDING',



    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    created_by BIGINT,



    CONSTRAINT fk_payment_order

    FOREIGN KEY (order_id)

    REFERENCES orders(order_id)



) ENGINE=InnoDB;





/*
========================================================
INVOICES

Invoice / Nota

========================================================
*/


CREATE TABLE invoices (


    invoice_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    invoice_number VARCHAR(50)
        NOT NULL UNIQUE,


    order_id BIGINT NOT NULL,


    subtotal DECIMAL(18,2),


    discount DECIMAL(18,2),


    tax DECIMAL(18,2),


    service_charge DECIMAL(18,2),


    grand_total DECIMAL(18,2),


    paid_amount DECIMAL(18,2),


    remaining_amount DECIMAL(18,2),



    invoice_status ENUM(
        'DRAFT',
        'ISSUED',
        'PAID',
        'CANCELLED'
    )
    DEFAULT 'DRAFT',



    issued_at TIMESTAMP NULL,



    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_invoice_order

    FOREIGN KEY (order_id)

    REFERENCES orders(order_id)



) ENGINE=InnoDB;





/*
========================================================
KITCHEN ORDERS

Order dapur

========================================================
*/


CREATE TABLE kitchen_orders (


    kitchen_order_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    order_id BIGINT NOT NULL,


    status ENUM(
        'PENDING',
        'COOKING',
        'READY',
        'SERVED'
    )
    DEFAULT 'PENDING',



    priority ENUM(
        'NORMAL',
        'HIGH',
        'URGENT'
    )
    DEFAULT 'NORMAL',



    start_time TIMESTAMP NULL,


    finish_time TIMESTAMP NULL,



    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_kitchen_order_order

    FOREIGN KEY (order_id)

    REFERENCES orders(order_id)



) ENGINE=InnoDB;





/*
========================================================
KITCHEN ORDER DETAILS

Detail order dapur

========================================================
*/


CREATE TABLE kitchen_order_details (


    kitchen_order_detail_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    kitchen_order_id BIGINT NOT NULL,


    menu_id BIGINT NOT NULL,


    qty INT NOT NULL,



    status ENUM(
        'PENDING',
        'COOKING',
        'READY'
    )
    DEFAULT 'PENDING',



    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_kitchen_detail_kitchen

    FOREIGN KEY (kitchen_order_id)

    REFERENCES kitchen_orders(kitchen_order_id),



    CONSTRAINT fk_kitchen_detail_menu

    FOREIGN KEY (menu_id)

    REFERENCES menus(menu_id)



) ENGINE=InnoDB;





/*
========================================================
END OF PART 3

TRANSACTION DATA

========================================================
*/



/*
========================================================
PART 4:
INVENTORY DATA SCHEMA

Berisi:

- Stock Balance
- Stock Transaction
- Stock Opname
- Stock Transfer

========================================================
*/



/*
========================================================
STOCK BALANCES

Saldo stok per cabang

========================================================
*/


CREATE TABLE stock_balances (


    stock_balance_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    branch_id BIGINT NOT NULL,


    item_id BIGINT NOT NULL,


    quantity DECIMAL(18,4)
        DEFAULT 0,


    average_cost DECIMAL(18,2),


    last_transaction_date TIMESTAMP NULL,



    updated_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,



    CONSTRAINT fk_stock_balance_branch

    FOREIGN KEY (branch_id)

    REFERENCES branches(branch_id),



    CONSTRAINT fk_stock_balance_item

    FOREIGN KEY (item_id)

    REFERENCES inventory_items(item_id)



) ENGINE=InnoDB;



CREATE UNIQUE INDEX idx_stock_balance_branch_item
ON stock_balances(branch_id, item_id);





/*
========================================================
STOCK TRANSACTIONS

Pergerakan stok

========================================================
*/


CREATE TABLE stock_transactions (


    stock_transaction_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    branch_id BIGINT NOT NULL,


    item_id BIGINT NOT NULL,


    transaction_type ENUM(
        'PURCHASE',
        'SALE_USAGE',
        'TRANSFER_IN',
        'TRANSFER_OUT',
        'ADJUSTMENT',
        'WASTE',
        'RETURN',
        'PRODUCTION'
    ),


    quantity DECIMAL(18,4)
        NOT NULL,


    unit_cost DECIMAL(18,2),


    total_cost DECIMAL(18,2),



    reference_type VARCHAR(50),


    reference_id BIGINT,



    notes TEXT,



    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    created_by BIGINT,



    CONSTRAINT fk_stock_transaction_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id),



    CONSTRAINT fk_stock_transaction_branch

    FOREIGN KEY (branch_id)

    REFERENCES branches(branch_id),



    CONSTRAINT fk_stock_transaction_item

    FOREIGN KEY (item_id)

    REFERENCES inventory_items(item_id)



) ENGINE=InnoDB;



CREATE INDEX idx_stock_transaction_branch
ON stock_transactions(branch_id);





/*
========================================================
STOCK OPNAMES

Stock opname

========================================================
*/


CREATE TABLE stock_opnames (


    opname_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    branch_id BIGINT NOT NULL,


    opname_number VARCHAR(50)
        NOT NULL UNIQUE,


    opname_date DATE NOT NULL,



    status ENUM(
        'DRAFT',
        'IN_PROGRESS',
        'COMPLETED',
        'CANCELLED'
    )
    DEFAULT 'DRAFT',



    notes TEXT,



    created_by BIGINT,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_opname_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id),



    CONSTRAINT fk_opname_branch

    FOREIGN KEY (branch_id)

    REFERENCES branches(branch_id)



) ENGINE=InnoDB;





/*
========================================================
STOCK OPNAME DETAILS

Detail stock opname

========================================================
*/


CREATE TABLE stock_opname_details (


    opname_detail_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    opname_id BIGINT NOT NULL,


    item_id BIGINT NOT NULL,


    system_quantity DECIMAL(18,4),


    actual_quantity DECIMAL(18,4),


    difference DECIMAL(18,4),


    adjustment_cost DECIMAL(18,2),



    notes TEXT,



    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_opname_detail_opname

    FOREIGN KEY (opname_id)

    REFERENCES stock_opnames(opname_id),



    CONSTRAINT fk_opname_detail_item

    FOREIGN KEY (item_id)

    REFERENCES inventory_items(item_id)



) ENGINE=InnoDB;





/*
========================================================
STOCK TRANSFERS

Transfer stok antar cabang

========================================================
*/


CREATE TABLE stock_transfers (


    transfer_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    from_branch_id BIGINT NOT NULL,


    to_branch_id BIGINT NOT NULL,


    transfer_number VARCHAR(50)
        NOT NULL UNIQUE,


    transfer_date DATE,



    status ENUM(
        'DRAFT',
        'PENDING',
        'IN_TRANSIT',
        'RECEIVED',
        'CANCELLED'
    )
    DEFAULT 'DRAFT',



    notes TEXT,



    created_by BIGINT,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_transfer_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id),



    CONSTRAINT fk_transfer_from_branch

    FOREIGN KEY (from_branch_id)

    REFERENCES branches(branch_id),



    CONSTRAINT fk_transfer_to_branch

    FOREIGN KEY (to_branch_id)

    REFERENCES branches(branch_id)



) ENGINE=InnoDB;





/*
========================================================
STOCK TRANSFER DETAILS

Detail transfer stok

========================================================
*/


CREATE TABLE stock_transfer_details (


    transfer_detail_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    transfer_id BIGINT NOT NULL,


    item_id BIGINT NOT NULL,


    quantity DECIMAL(18,4)
        NOT NULL,



    unit_cost DECIMAL(18,2),



    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_transfer_detail_transfer

    FOREIGN KEY (transfer_id)

    REFERENCES stock_transfers(transfer_id),



    CONSTRAINT fk_transfer_detail_item

    FOREIGN KEY (item_id)

    REFERENCES inventory_items(item_id)



) ENGINE=InnoDB;





/*
========================================================
END OF PART 4

INVENTORY DATA

========================================================
*/



/*
========================================================
PART 5:
PURCHASING DATA SCHEMA

Berisi:

- Purchase Requests
- Purchase Orders
- Purchase Order Details
- Goods Receipts

========================================================
*/



/*
========================================================
PURCHASE REQUESTS

Permintaan pembelian

========================================================
*/


CREATE TABLE purchase_requests (


    request_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    branch_id BIGINT NOT NULL,


    request_number VARCHAR(50)
        NOT NULL UNIQUE,


    request_date DATE NOT NULL,



    status ENUM(
        'DRAFT',
        'PENDING_APPROVAL',
        'APPROVED',
        'REJECTED',
        'CANCELLED'
    )
    DEFAULT 'DRAFT',



    notes TEXT,



    created_by BIGINT,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_purchase_request_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id),



    CONSTRAINT fk_purchase_request_branch

    FOREIGN KEY (branch_id)

    REFERENCES branches(branch_id)



) ENGINE=InnoDB;





/*
========================================================
PURCHASE REQUEST DETAILS

Detail permintaan pembelian

========================================================
*/


CREATE TABLE purchase_request_details (


    request_detail_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    request_id BIGINT NOT NULL,


    item_id BIGINT NOT NULL,


    requested_quantity DECIMAL(18,4)
        NOT NULL,



    estimated_cost DECIMAL(18,2),



    notes TEXT,



    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_request_detail_request

    FOREIGN KEY (request_id)

    REFERENCES purchase_requests(request_id),



    CONSTRAINT fk_request_detail_item

    FOREIGN KEY (item_id)

    REFERENCES inventory_items(item_id)



) ENGINE=InnoDB;





/*
========================================================
PURCHASE ORDERS

Purchase Order

========================================================
*/


CREATE TABLE purchase_orders (


    purchase_order_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    branch_id BIGINT NOT NULL,


    supplier_id BIGINT NOT NULL,


    po_number VARCHAR(50)
        NOT NULL UNIQUE,


    order_date DATE NOT NULL,


    expected_delivery_date DATE,



    status ENUM(
        'DRAFT',
        'SENT',
        'PARTIAL_RECEIVED',
        'RECEIVED',
        'CANCELLED'
    )
    DEFAULT 'DRAFT',



    subtotal DECIMAL(18,2),


    tax DECIMAL(18,2),


    discount DECIMAL(18,2),


    total_amount DECIMAL(18,2),



    notes TEXT,



    created_by BIGINT,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_po_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id),



    CONSTRAINT fk_po_branch

    FOREIGN KEY (branch_id)

    REFERENCES branches(branch_id),



    CONSTRAINT fk_po_supplier

    FOREIGN KEY (supplier_id)

    REFERENCES suppliers(supplier_id)



) ENGINE=InnoDB;



CREATE INDEX idx_po_branch
ON purchase_orders(branch_id);





/*
========================================================
PURCHASE ORDER DETAILS

Detail Purchase Order

========================================================
*/


CREATE TABLE purchase_order_details (


    po_detail_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    purchase_order_id BIGINT NOT NULL,


    item_id BIGINT NOT NULL,


    quantity DECIMAL(18,4)
        NOT NULL,


    unit_price DECIMAL(18,2)
        NOT NULL,


    discount DECIMAL(18,2),


    subtotal DECIMAL(18,2),



    received_quantity DECIMAL(18,4)
        DEFAULT 0,



    notes TEXT,



    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_po_detail_po

    FOREIGN KEY (purchase_order_id)

    REFERENCES purchase_orders(purchase_order_id),



    CONSTRAINT fk_po_detail_item

    FOREIGN KEY (item_id)

    REFERENCES inventory_items(item_id)



) ENGINE=InnoDB;





/*
========================================================
GOODS RECEIPTS

Penerimaan barang

========================================================
*/


CREATE TABLE goods_receipts (


    receipt_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    branch_id BIGINT NOT NULL,


    purchase_order_id BIGINT,


    supplier_id BIGINT NOT NULL,


    receipt_number VARCHAR(50)
        NOT NULL UNIQUE,


    received_date DATE NOT NULL,



    status ENUM(
        'DRAFT',
        'COMPLETED',
        'CANCELLED'
    )
    DEFAULT 'DRAFT',



    notes TEXT,



    created_by BIGINT,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_receipt_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id),



    CONSTRAINT fk_receipt_branch

    FOREIGN KEY (branch_id)

    REFERENCES branches(branch_id),



    CONSTRAINT fk_receipt_po

    FOREIGN KEY (purchase_order_id)

    REFERENCES purchase_orders(purchase_order_id),



    CONSTRAINT fk_receipt_supplier

    FOREIGN KEY (supplier_id)

    REFERENCES suppliers(supplier_id)



) ENGINE=InnoDB;





/*
========================================================
GOODS RECEIPT DETAILS

Detail penerimaan barang

========================================================
*/


CREATE TABLE goods_receipt_details (


    receipt_detail_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    receipt_id BIGINT NOT NULL,


    item_id BIGINT NOT NULL,


    received_quantity DECIMAL(18,4)
        NOT NULL,


    unit_cost DECIMAL(18,2),


    total_cost DECIMAL(18,2),



    notes TEXT,



    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_receipt_detail_receipt

    FOREIGN KEY (receipt_id)

    REFERENCES goods_receipts(receipt_id),



    CONSTRAINT fk_receipt_detail_item

    FOREIGN KEY (item_id)

    REFERENCES inventory_items(item_id)



) ENGINE=InnoDB;





/*
========================================================
END OF PART 5

PURCHASING DATA

========================================================
*/



/*
========================================================
PART 6:
ACCOUNTING DATA SCHEMA

Berisi:

- Chart of Accounts
- Journal Entries
- Journal Details
- Expenses

========================================================
*/



/*
========================================================
ACCOUNTS

Chart of Accounts

========================================================
*/


CREATE TABLE accounts (


    account_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    account_code VARCHAR(50)
        NOT NULL,


    account_name VARCHAR(150)
        NOT NULL,


    account_type ENUM(
        'ASSET',
        'LIABILITY',
        'EQUITY',
        'REVENUE',
        'EXPENSE'
    ),


    parent_account_id BIGINT,



    status ENUM(
        'ACTIVE',
        'INACTIVE'
    )
    DEFAULT 'ACTIVE',



    created_by BIGINT,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_account_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id)



) ENGINE=InnoDB;





/*
========================================================
JOURNAL ENTRIES

Header jurnal

========================================================
*/


CREATE TABLE journal_entries (


    journal_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    branch_id BIGINT NOT NULL,


    journal_number VARCHAR(50)
        NOT NULL UNIQUE,


    transaction_date DATE NOT NULL,



    reference_type VARCHAR(50),


    reference_id BIGINT,


    description TEXT,



    status ENUM(
        'DRAFT',
        'POSTED',
        'CANCELLED'
    )
    DEFAULT 'DRAFT',



    created_by BIGINT,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_journal_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id),



    CONSTRAINT fk_journal_branch

    FOREIGN KEY (branch_id)

    REFERENCES branches(branch_id)



) ENGINE=InnoDB;



CREATE INDEX idx_journal_branch
ON journal_entries(branch_id);





/*
========================================================
JOURNAL DETAILS

Detail jurnal (Double Entry)

========================================================
*/


CREATE TABLE journal_details (


    journal_detail_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    journal_id BIGINT NOT NULL,


    account_id BIGINT NOT NULL,


    debit DECIMAL(18,2)
        DEFAULT 0,


    credit DECIMAL(18,2)
        DEFAULT 0,



    description TEXT,



    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_journal_detail_journal

    FOREIGN KEY (journal_id)

    REFERENCES journal_entries(journal_id),



    CONSTRAINT fk_journal_detail_account

    FOREIGN KEY (account_id)

    REFERENCES accounts(account_id)



) ENGINE=InnoDB;





/*
========================================================
EXPENSES

Pengeluaran operasional

========================================================
*/


CREATE TABLE expenses (


    expense_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    branch_id BIGINT NOT NULL,


    expense_number VARCHAR(50)
        NOT NULL UNIQUE,


    expense_date DATE NOT NULL,



    category VARCHAR(100),


    amount DECIMAL(18,2)
        NOT NULL,



    description TEXT,



    approval_status ENUM(
        'PENDING',
        'APPROVED',
        'REJECTED'
    )
    DEFAULT 'PENDING',



    approved_by BIGINT,


    approved_at TIMESTAMP NULL,



    created_by BIGINT,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_expense_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id),



    CONSTRAINT fk_expense_branch

    FOREIGN KEY (branch_id)

    REFERENCES branches(branch_id)



) ENGINE=InnoDB;





/*
========================================================
END OF PART 6

ACCOUNTING DATA

========================================================
*/



/*
========================================================
PART 7:
AUDIT & SECURITY DATA SCHEMA

Berisi:

- Audit Logs
- Approval Logs
- Security Events
- Notifications

========================================================
*/



/*
========================================================
AUDIT LOGS

Log aktivitas sistem

========================================================
*/


CREATE TABLE audit_logs (


    audit_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    user_id BIGINT,


    module VARCHAR(50),


    action VARCHAR(50),


    record_id BIGINT,


    table_name VARCHAR(100),


    old_value TEXT,


    new_value TEXT,


    ip_address VARCHAR(50),


    user_agent TEXT,



    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_audit_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id),



    CONSTRAINT fk_audit_user

    FOREIGN KEY (user_id)

    REFERENCES users(user_id)



) ENGINE=InnoDB;



CREATE INDEX idx_audit_tenant
ON audit_logs(tenant_id);





/*
========================================================
APPROVAL LOGS

Log persetujuan

========================================================
*/


CREATE TABLE approval_logs (


    approval_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    transaction_type VARCHAR(50),


    transaction_id BIGINT,


    approver_id BIGINT,



    status ENUM(
        'PENDING',
        'APPROVED',
        'REJECTED'
    )
    DEFAULT 'PENDING',



    notes TEXT,



    approval_date TIMESTAMP NULL,



    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_approval_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id),



    CONSTRAINT fk_approval_approver

    FOREIGN KEY (approver_id)

    REFERENCES users(user_id)



) ENGINE=InnoDB;





/*
========================================================
SECURITY EVENTS

Event keamanan

========================================================
*/


CREATE TABLE security_events (


    event_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    user_id BIGINT,


    event_type ENUM(
        'LOGIN_SUCCESS',
        'LOGIN_FAILED',
        'PASSWORD_CHANGE',
        'PERMISSION_DENIED',
        'SUSPICIOUS_ACTIVITY'
    ),


    ip_address VARCHAR(50),


    user_agent TEXT,


    description TEXT,



    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_security_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id),



    CONSTRAINT fk_security_user

    FOREIGN KEY (user_id)

    REFERENCES users(user_id)



) ENGINE=InnoDB;





/*
========================================================
NOTIFICATIONS

Notifikasi sistem

========================================================
*/


CREATE TABLE notifications (


    notification_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    user_id BIGINT NOT NULL,


    type VARCHAR(50),


    title VARCHAR(200),


    message TEXT,



    status ENUM(
        'UNREAD',
        'READ',
        'ARCHIVED'
    )
    DEFAULT 'UNREAD',



    reference_type VARCHAR(50),


    reference_id BIGINT,



    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    read_at TIMESTAMP NULL,



    CONSTRAINT fk_notification_user

    FOREIGN KEY (user_id)

    REFERENCES users(user_id)



) ENGINE=InnoDB;





/*
========================================================
END OF PART 7

AUDIT & SECURITY DATA

========================================================
*/



/*
========================================================
PART 8:
AI ANALYTICS DATA SCHEMA

Berisi:

- Sales Analytics
- Menu Performance
- Forecast Data
- Fraud Detection

========================================================
*/



/*
========================================================
AI SALES DAILY

Aggregasi penjualan harian untuk AI

========================================================
*/


CREATE TABLE ai_sales_daily (


    id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    branch_id BIGINT NOT NULL,


    date DATE NOT NULL,



    total_sales DECIMAL(18,2),


    transaction_count INT,


    customer_count INT,


    average_transaction DECIMAL(18,2),


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_ai_sales_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id),



    CONSTRAINT fk_ai_sales_branch

    FOREIGN KEY (branch_id)

    REFERENCES branches(branch_id)



) ENGINE=InnoDB;



CREATE UNIQUE INDEX idx_ai_sales_date_branch
ON ai_sales_daily(date, branch_id);





/*
========================================================
AI MENU ANALYSIS

Analisis performa menu

========================================================
*/


CREATE TABLE ai_menu_analysis (


    id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    branch_id BIGINT NOT NULL,


    menu_id BIGINT NOT NULL,


    period_start DATE,


    period_end DATE,



    sales_qty INT,


    revenue DECIMAL(18,2),


    cost DECIMAL(18,2),


    profit DECIMAL(18,2),


    profit_margin DECIMAL(5,2),


    ranking INT,



    classification ENUM(
        'BEST_SELLER',
        'GOOD_SELLER',
        'AVERAGE',
        'SLOW_MOVING',
        'LOSS_MAKER'
    ),



    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_ai_menu_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id),



    CONSTRAINT fk_ai_menu_branch

    FOREIGN KEY (branch_id)

    REFERENCES branches(branch_id),



    CONSTRAINT fk_ai_menu_menu

    FOREIGN KEY (menu_id)

    REFERENCES menus(menu_id)



) ENGINE=InnoDB;





/*
========================================================
AI FORECAST SALES

Prediksi penjualan

========================================================
*/


CREATE TABLE ai_forecast_sales (


    forecast_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    branch_id BIGINT NOT NULL,


    forecast_date DATE NOT NULL,



    predicted_sales DECIMAL(18,2),


    predicted_transactions INT,


    confidence_level DECIMAL(5,2),


    model_version VARCHAR(50),



    actual_sales DECIMAL(18,2),


    actual_transactions INT,



    accuracy DECIMAL(5,2),



    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_ai_forecast_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id),



    CONSTRAINT fk_ai_forecast_branch

    FOREIGN KEY (branch_id)

    REFERENCES branches(branch_id)



) ENGINE=InnoDB;





/*
========================================================
AI FRAUD DETECTION

Deteksi kecurangan

========================================================
*/


CREATE TABLE ai_fraud_detection (


    fraud_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    user_id BIGINT,


    transaction_id BIGINT,


    transaction_type VARCHAR(50),


    risk_score DECIMAL(5,2),


    risk_level ENUM(
        'LOW',
        'MEDIUM',
        'HIGH',
        'CRITICAL'
    ),



    reason TEXT,



    status ENUM(
        'DETECTED',
        'INVESTIGATING',
        'CONFIRMED',
        'FALSE_POSITIVE'
    )
    DEFAULT 'DETECTED',



    investigated_by BIGINT,


    investigation_notes TEXT,



    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_ai_fraud_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id),



    CONSTRAINT fk_ai_fraud_user

    FOREIGN KEY (user_id)

    REFERENCES users(user_id)



) ENGINE=InnoDB;





/*
========================================================
AI STOCK PREDICTION

Prediksi stok

========================================================
*/


CREATE TABLE ai_stock_prediction (


    prediction_id BIGINT AUTO_INCREMENT PRIMARY KEY,


    tenant_id BIGINT NOT NULL,


    branch_id BIGINT NOT NULL,


    item_id BIGINT NOT NULL,


    prediction_date DATE NOT NULL,



    predicted_quantity DECIMAL(18,4),


    confidence_level DECIMAL(5,2),


    recommended_action ENUM(
        'BUY',
        'HOLD',
        'REDUCE'
    ),



    actual_quantity DECIMAL(18,4),


    accuracy DECIMAL(5,2),



    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT fk_ai_stock_tenant

    FOREIGN KEY (tenant_id)

    REFERENCES tenants(tenant_id),



    CONSTRAINT fk_ai_stock_branch

    FOREIGN KEY (branch_id)

    REFERENCES branches(branch_id),



    CONSTRAINT fk_ai_stock_item

    FOREIGN KEY (item_id)

    REFERENCES inventory_items(item_id)



) ENGINE=InnoDB;





/*
========================================================
END OF PART 8

AI ANALYTICS DATA

========================================================
*/



/*
========================================================
END OF COMPLETE SCHEMA

EBP RESTAURANT & CAFE ERP

MYSQL DATABASE SCHEMA COMPLETE

========================================================
*/
