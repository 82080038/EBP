-- Accounting Tables for EBP Restaurant ERP
-- These tables complete the accounting module

-- General Ledger
CREATE TABLE IF NOT EXISTS general_ledger (
    ledger_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    journal_entry_id BIGINT UNSIGNED NOT NULL,
    journal_line_id BIGINT UNSIGNED NOT NULL,
    account_id BIGINT UNSIGNED NOT NULL,
    transaction_date DATE NOT NULL,
    reference_type VARCHAR(50),
    reference_id BIGINT UNSIGNED,
    description TEXT,
    debit_amount DECIMAL(15,2) DEFAULT 0.00,
    credit_amount DECIMAL(15,2) DEFAULT 0.00,
    balance DECIMAL(15,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    FOREIGN KEY (account_id) REFERENCES chart_of_accounts(account_id),
    FOREIGN KEY (journal_entry_id) REFERENCES journal_entries(journal_entry_id),
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_account (account_id),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_reference (reference_type, reference_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Accounts Receivable
CREATE TABLE IF NOT EXISTS accounts_receivable (
    ar_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED,
    invoice_number VARCHAR(50) NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    paid_amount DECIMAL(15,2) DEFAULT 0.00,
    balance_amount DECIMAL(15,2) NOT NULL,
    status ENUM('DRAFT', 'PENDING', 'PARTIAL', 'PAID', 'OVERDUE', 'CANCELLED') DEFAULT 'PENDING',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_customer (customer_id),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date),
    UNIQUE KEY uk_invoice_number (tenant_id, invoice_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Accounts Receivable Payments
CREATE TABLE IF NOT EXISTS ar_payments (
    ar_payment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    ar_id BIGINT UNSIGNED NOT NULL,
    payment_date DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    payment_method VARCHAR(50),
    reference_number VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    FOREIGN KEY (ar_id) REFERENCES accounts_receivable(ar_id),
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_ar (ar_id),
    INDEX idx_payment_date (payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Accounts Payable
CREATE TABLE IF NOT EXISTS accounts_payable (
    ap_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    supplier_id BIGINT UNSIGNED,
    bill_number VARCHAR(50) NOT NULL,
    bill_date DATE NOT NULL,
    due_date DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    paid_amount DECIMAL(15,2) DEFAULT 0.00,
    balance_amount DECIMAL(15,2) NOT NULL,
    status ENUM('DRAFT', 'PENDING', 'PARTIAL', 'PAID', 'OVERDUE', 'CANCELLED') DEFAULT 'PENDING',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_supplier (supplier_id),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date),
    UNIQUE KEY uk_bill_number (tenant_id, bill_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Accounts Payable Payments
CREATE TABLE IF NOT EXISTS ap_payments (
    ap_payment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    ap_id BIGINT UNSIGNED NOT NULL,
    payment_date DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    payment_method VARCHAR(50),
    reference_number VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    FOREIGN KEY (ap_id) REFERENCES accounts_payable(ap_id),
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_ap (ap_id),
    INDEX idx_payment_date (payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bank Accounts
CREATE TABLE IF NOT EXISTS bank_accounts (
    bank_account_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    account_name VARCHAR(100) NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    bank_name VARCHAR(100) NOT NULL,
    account_type ENUM('CHECKING', 'SAVINGS', 'CREDIT_CARD', 'CASH') DEFAULT 'CHECKING',
    currency VARCHAR(3) DEFAULT 'IDR',
    balance DECIMAL(15,2) DEFAULT 0.00,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_is_active (is_active),
    UNIQUE KEY uk_account_number (tenant_id, account_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bank Reconciliations
CREATE TABLE IF NOT EXISTS bank_reconciliations (
    reconciliation_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    bank_account_id BIGINT UNSIGNED NOT NULL,
    reconciliation_date DATE NOT NULL,
    statement_balance DECIMAL(15,2) NOT NULL,
    book_balance DECIMAL(15,2) NOT NULL,
    difference DECIMAL(15,2) NOT NULL,
    status ENUM('DRAFT', 'RECONCILED', 'UNRECONCILED') DEFAULT 'DRAFT',
    reconciled_by BIGINT UNSIGNED,
    reconciled_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(bank_account_id),
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_bank_account (bank_account_id),
    INDEX idx_reconciliation_date (reconciliation_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bank Reconciliation Items
CREATE TABLE IF NOT EXISTS bank_reconciliation_items (
    reconciliation_item_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reconciliation_id BIGINT UNSIGNED NOT NULL,
    item_type ENUM('DEPOSIT', 'WITHDRAWAL', 'ADJUSTMENT') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    description TEXT,
    reference_type VARCHAR(50),
    reference_id BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reconciliation_id) REFERENCES bank_reconciliations(reconciliation_id),
    INDEX idx_reconciliation (reconciliation_id),
    INDEX idx_item_type (item_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Fixed Assets
CREATE TABLE IF NOT EXISTS fixed_assets (
    asset_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    asset_code VARCHAR(50) NOT NULL,
    asset_name VARCHAR(100) NOT NULL,
    asset_category VARCHAR(50),
    purchase_date DATE NOT NULL,
    purchase_cost DECIMAL(15,2) NOT NULL,
    salvage_value DECIMAL(15,2) DEFAULT 0.00,
    useful_life INT NOT NULL,
    depreciation_method ENUM('STRAIGHT_LINE', 'DECLINING_BALANCE', 'UNITS_OF_PRODUCTION') DEFAULT 'STRAIGHT_LINE',
    current_value DECIMAL(15,2),
    accumulated_depreciation DECIMAL(15,2) DEFAULT 0.00,
    location VARCHAR(100),
    status ENUM('ACTIVE', 'DISPOSED', 'SOLD', 'WRITTEN_OFF') DEFAULT 'ACTIVE',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_asset_code (asset_code),
    INDEX idx_status (status),
    INDEX idx_category (asset_category),
    UNIQUE KEY uk_asset_code (tenant_id, asset_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Fixed Asset Depreciation
CREATE TABLE IF NOT EXISTS asset_depreciation (
    depreciation_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_id BIGINT UNSIGNED NOT NULL,
    fiscal_year INT NOT NULL,
    fiscal_month INT NOT NULL,
    depreciation_amount DECIMAL(15,2) NOT NULL,
    accumulated_depreciation DECIMAL(15,2) NOT NULL,
    book_value DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES fixed_assets(asset_id),
    INDEX idx_asset (asset_id),
    INDEX idx_fiscal_year (fiscal_year),
    UNIQUE KEY uk_asset_period (asset_id, fiscal_year, fiscal_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Budgets
CREATE TABLE IF NOT EXISTS budgets (
    budget_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    budget_name VARCHAR(100) NOT NULL,
    fiscal_year INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_budget DECIMAL(15,2) NOT NULL,
    status ENUM('DRAFT', 'APPROVED', 'ACTIVE', 'CLOSED') DEFAULT 'DRAFT',
    approved_by BIGINT UNSIGNED,
    approved_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_fiscal_year (fiscal_year),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Budget Items
CREATE TABLE IF NOT EXISTS budget_items (
    budget_item_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    budget_id BIGINT UNSIGNED NOT NULL,
    account_id BIGINT UNSIGNED NOT NULL,
    budgeted_amount DECIMAL(15,2) NOT NULL,
    actual_amount DECIMAL(15,2) DEFAULT 0.00,
    variance DECIMAL(15,2) DEFAULT 0.00,
    period_type ENUM('MONTHLY', 'QUARTERLY', 'YEARLY') DEFAULT 'MONTHLY',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (budget_id) REFERENCES budgets(budget_id),
    FOREIGN KEY (account_id) REFERENCES chart_of_accounts(account_id),
    INDEX idx_budget (budget_id),
    INDEX idx_account (account_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Accounting Periods
CREATE TABLE IF NOT EXISTS accounting_periods (
    period_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    fiscal_year INT NOT NULL,
    period_number INT NOT NULL,
    period_name VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('OPEN', 'CLOSED', 'LOCKED') DEFAULT 'OPEN',
    closed_by BIGINT UNSIGNED,
    closed_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_fiscal_year (fiscal_year),
    INDEX idx_status (status),
    UNIQUE KEY uk_period (tenant_id, branch_id, fiscal_year, period_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cash Flow Items
CREATE TABLE IF NOT EXISTS cash_flow_items (
    cash_flow_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    transaction_date DATE NOT NULL,
    cash_flow_type ENUM('OPERATING', 'INVESTING', 'FINANCING') NOT NULL,
    sub_type VARCHAR(50),
    amount DECIMAL(15,2) NOT NULL,
    description TEXT,
    reference_type VARCHAR(50),
    reference_id BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id),
    INDEX idx_tenant_branch (tenant_id, branch_id),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_cash_flow_type (cash_flow_type),
    INDEX idx_reference (reference_type, reference_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
