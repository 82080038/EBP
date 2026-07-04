-- Migration for Missing Accounting and Report Tables

-- Accounts (Chart of Accounts)
CREATE TABLE IF NOT EXISTS accounts (
    account_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    account_code VARCHAR(20) NOT NULL,
    account_name VARCHAR(200) NOT NULL,
    account_type ENUM('ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE') NOT NULL,
    parent_account_id BIGINT UNSIGNED,
    balance DECIMAL(15,2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (account_id),
    UNIQUE KEY idx_accounts_tenant_code (tenant_id, account_code),
    KEY idx_accounts_tenant_id (tenant_id),
    KEY idx_accounts_type (account_type),
    KEY idx_accounts_parent (parent_account_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (parent_account_id) REFERENCES accounts(account_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transactions (General Ledger)
CREATE TABLE IF NOT EXISTS transactions (
    transaction_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    transaction_number VARCHAR(50) NOT NULL,
    transaction_date DATE NOT NULL,
    transaction_type ENUM('SALE', 'PURCHASE', 'PAYMENT', 'RECEIPT', 'ADJUSTMENT', 'TRANSFER') NOT NULL,
    reference_type VARCHAR(50),
    reference_id BIGINT UNSIGNED,
    description TEXT,
    total_amount DECIMAL(15,2) NOT NULL,
    status ENUM('DRAFT', 'POSTED', 'VOID') DEFAULT 'DRAFT',
    created_by BIGINT UNSIGNED,
    posted_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (transaction_id),
    UNIQUE KEY idx_transactions_tenant_number (tenant_id, transaction_number),
    KEY idx_transactions_tenant_id (tenant_id),
    KEY idx_transactions_branch_id (branch_id),
    KEY idx_transactions_date (transaction_date),
    KEY idx_transactions_type (transaction_type),
    KEY idx_transactions_status (status),
    KEY idx_transactions_reference (reference_type, reference_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transaction Lines (Journal Entries)
CREATE TABLE IF NOT EXISTS transaction_lines (
    line_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    transaction_id BIGINT UNSIGNED NOT NULL,
    account_id BIGINT UNSIGNED NOT NULL,
    debit DECIMAL(15,2) DEFAULT 0.00,
    credit DECIMAL(15,2) DEFAULT 0.00,
    description VARCHAR(255),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (line_id),
    KEY idx_transaction_lines_transaction_id (transaction_id),
    KEY idx_transaction_lines_account_id (account_id),
    FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(account_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tax Reports
CREATE TABLE IF NOT EXISTS tax_reports (
    report_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    report_type ENUM('PB1', 'PPN', 'PPh') NOT NULL,
    report_period VARCHAR(7) NOT NULL, -- YYYY-MM
    tax_id VARCHAR(50),
    total_sales DECIMAL(15,2) DEFAULT 0.00,
    total_tax DECIMAL(15,2) DEFAULT 0.00,
    total_deductible DECIMAL(15,2) DEFAULT 0.00,
    status ENUM('DRAFT', 'SUBMITTED', 'APPROVED', 'REJECTED') DEFAULT 'DRAFT',
    submitted_at TIMESTAMP NULL,
    approved_at TIMESTAMP NULL,
    generated_by BIGINT UNSIGNED,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (report_id),
    UNIQUE KEY idx_tax_reports_tenant_type_period (tenant_id, report_type, report_period),
    KEY idx_tax_reports_tenant_id (tenant_id),
    KEY idx_tax_reports_branch_id (branch_id),
    KEY idx_tax_reports_period (report_period),
    KEY idx_tax_reports_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (generated_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reports (Report Definitions)
CREATE TABLE IF NOT EXISTS reports (
    report_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    report_name VARCHAR(200) NOT NULL,
    report_type ENUM('SALES', 'INVENTORY', 'FINANCIAL', 'HR', 'OPERATIONAL', 'CUSTOM') NOT NULL,
    report_category VARCHAR(100),
    description TEXT,
    query_template TEXT,
    parameters JSON,
    is_scheduled BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (report_id),
    KEY idx_reports_tenant_id (tenant_id),
    KEY idx_reports_type (report_type),
    KEY idx_reports_category (report_category),
    KEY idx_reports_active (is_active),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Report Schedules
CREATE TABLE IF NOT EXISTS report_schedules (
    schedule_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED,
    report_id BIGINT UNSIGNED NOT NULL,
    schedule_name VARCHAR(200) NOT NULL,
    frequency ENUM('DAILY', 'WEEKLY', 'MONTHLY', 'QUARTERLY', 'YEARLY') NOT NULL,
    schedule_config JSON,
    recipients JSON,
    next_run_at TIMESTAMP NOT NULL,
    last_run_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (schedule_id),
    KEY idx_report_schedules_tenant_id (tenant_id),
    KEY idx_report_schedules_branch_id (branch_id),
    KEY idx_report_schedules_report_id (report_id),
    KEY idx_report_schedules_next_run (next_run_at),
    KEY idx_report_schedules_active (is_active),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (report_id) REFERENCES reports(report_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exchange Rates (Missing from Phase 13)
CREATE TABLE IF NOT EXISTS exchange_rates (
    rate_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL,
    from_currency VARCHAR(3) NOT NULL,
    to_currency VARCHAR(3) NOT NULL,
    rate DECIMAL(15,6) NOT NULL,
    effective_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (rate_id),
    UNIQUE KEY idx_exchange_rates_tenant_from_to_date (tenant_id, from_currency, to_currency, effective_date),
    KEY idx_exchange_rates_tenant_id (tenant_id),
    KEY idx_exchange_rates_currencies (from_currency, to_currency),
    KEY idx_exchange_rates_date (effective_date),
    KEY idx_exchange_rates_active (is_active),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default accounts for tenant 1
INSERT INTO accounts (tenant_id, account_code, account_name, account_type, balance) VALUES
(1, '1000', 'Cash', 'ASSET', 0.00),
(1, '1100', 'Bank Account', 'ASSET', 0.00),
(1, '1200', 'Accounts Receivable', 'ASSET', 0.00),
(1, '1300', 'Inventory', 'ASSET', 0.00),
(1, '1400', 'Fixed Assets', 'ASSET', 0.00),
(1, '2000', 'Accounts Payable', 'LIABILITY', 0.00),
(1, '2100', 'Sales Tax Payable', 'LIABILITY', 0.00),
(1, '3000', 'Owner Equity', 'EQUITY', 0.00),
(1, '4000', 'Sales Revenue', 'REVENUE', 0.00),
(1, '5000', 'Cost of Goods Sold', 'EXPENSE', 0.00),
(1, '6000', 'Operating Expenses', 'EXPENSE', 0.00),
(1, '7000', 'Payroll Expenses', 'EXPENSE', 0.00)
ON DUPLICATE KEY UPDATE account_name = VALUES(account_name);

-- Insert default exchange rates for tenant 1
INSERT INTO exchange_rates (tenant_id, from_currency, to_currency, rate, effective_date) VALUES
(1, 'USD', 'IDR', 15000.000000, CURDATE()),
(1, 'EUR', 'IDR', 16500.000000, CURDATE()),
(1, 'SGD', 'IDR', 11000.000000, CURDATE())
ON DUPLICATE KEY UPDATE rate = VALUES(rate), effective_date = VALUES(effective_date);
