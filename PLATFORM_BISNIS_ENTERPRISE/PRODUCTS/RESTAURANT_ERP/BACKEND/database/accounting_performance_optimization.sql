-- Accounting Performance Optimization
-- Database indexes and constraints for performance and data integrity

-- Performance Indexes for General Ledger
CREATE INDEX IF NOT EXISTS idx_gl_tenant_branch_date ON general_ledger(tenant_id, branch_id, transaction_date);
CREATE INDEX IF NOT EXISTS idx_gl_account_date ON general_ledger(account_id, transaction_date);
CREATE INDEX IF NOT EXISTS idx_gl_reference ON general_ledger(reference_type, reference_id);
CREATE INDEX IF NOT EXISTS idx_gl_tenant_account ON general_ledger(tenant_id, account_id);

-- Performance Indexes for Journal Entries
CREATE INDEX IF NOT EXISTS idx_je_tenant_date ON journal_entries(tenant_id, journal_date);
CREATE INDEX IF NOT EXISTS idx_je_tenant_branch_date ON journal_entries(tenant_id, branch_id, journal_date);
CREATE INDEX IF NOT EXISTS idx_je_reference ON journal_entries(reference_type, reference_id);
CREATE INDEX IF NOT EXISTS idx_je_status ON journal_entries(status);

-- Performance Indexes for Journal Lines
CREATE INDEX IF NOT EXISTS idx_jl_journal_entry ON journal_lines(journal_entry_id);
CREATE INDEX IF NOT EXISTS idx_jl_account ON journal_lines(account_id);

-- Performance Indexes for Accounts Receivable
CREATE INDEX IF NOT EXISTS idx_ar_tenant_branch ON accounts_receivable(tenant_id, branch_id);
CREATE INDEX IF NOT EXISTS idx_ar_customer ON accounts_receivable(customer_id);
CREATE INDEX IF NOT EXISTS idx_ar_status ON accounts_receivable(status);
CREATE INDEX IF NOT EXISTS idx_ar_due_date ON accounts_receivable(due_date);
CREATE INDEX IF NOT EXISTS idx_ar_invoice_number ON accounts_receivable(tenant_id, invoice_number);

-- Performance Indexes for AR Payments
CREATE INDEX IF NOT EXISTS idx_arp_ar ON ar_payments(ar_id);
CREATE INDEX IF NOT EXISTS idx_arp_date ON ar_payments(payment_date);
CREATE INDEX IF NOT EXISTS idx_arp_tenant_branch ON ar_payments(tenant_id, branch_id);

-- Performance Indexes for Accounts Payable
CREATE INDEX IF NOT EXISTS idx_ap_tenant_branch ON accounts_payable(tenant_id, branch_id);
CREATE INDEX IF NOT EXISTS idx_ap_supplier ON accounts_payable(supplier_id);
CREATE INDEX IF NOT EXISTS idx_ap_status ON accounts_payable(status);
CREATE INDEX IF NOT EXISTS idx_ap_due_date ON accounts_payable(due_date);
CREATE INDEX IF NOT EXISTS idx_ap_bill_number ON accounts_payable(tenant_id, bill_number);

-- Performance Indexes for AP Payments
CREATE INDEX IF NOT EXISTS idx_app_ap ON ap_payments(ap_id);
CREATE INDEX IF NOT EXISTS idx_app_date ON ap_payments(payment_date);
CREATE INDEX IF NOT EXISTS idx_app_tenant_branch ON ap_payments(tenant_id, branch_id);

-- Performance Indexes for Chart of Accounts
CREATE INDEX IF NOT EXISTS idx_coa_tenant_type ON chart_of_accounts(tenant_id, account_type);
CREATE INDEX IF NOT EXISTS idx_coa_tenant_code ON chart_of_accounts(tenant_id, account_code);
CREATE INDEX IF NOT EXISTS idx_coa_active ON chart_of_accounts(is_active);

-- Performance Indexes for Bank Reconciliations
CREATE INDEX IF NOT EXISTS idx_br_tenant_branch ON bank_reconciliations(tenant_id, branch_id);
CREATE INDEX IF NOT EXISTS idx_br_bank_account ON bank_reconciliations(bank_account_id);
CREATE INDEX IF NOT EXISTS idx_br_status ON bank_reconciliations(status);
CREATE INDEX IF NOT EXISTS idx_br_date ON bank_reconciliations(reconciliation_date);

-- Performance Indexes for Fixed Assets
CREATE INDEX IF NOT EXISTS idx_fa_tenant_branch ON fixed_assets(tenant_id, branch_id);
CREATE INDEX IF NOT EXISTS idx_fa_status ON fixed_assets(status);
CREATE INDEX IF NOT EXISTS idx_fa_category ON fixed_assets(asset_category);
CREATE INDEX IF NOT EXISTS idx_fa_code ON fixed_assets(tenant_id, asset_code);

-- Performance Indexes for Budgets
CREATE INDEX IF NOT EXISTS idx_budget_tenant_branch ON budgets(tenant_id, branch_id);
CREATE INDEX IF NOT EXISTS idx_budget_fiscal_year ON budgets(fiscal_year);
CREATE INDEX IF NOT EXISTS idx_budget_status ON budgets(status);

-- Performance Indexes for Budget Items
CREATE INDEX IF NOT EXISTS idx_bi_budget ON budget_items(budget_id);
CREATE INDEX IF NOT EXISTS idx_bi_account ON budget_items(account_id);

-- Performance Indexes for Accounting Periods
CREATE INDEX IF NOT EXISTS idx_period_tenant_branch ON accounting_periods(tenant_id, branch_id);
CREATE INDEX IF NOT EXISTS idx_period_fiscal_year ON accounting_periods(fiscal_year);
CREATE INDEX IF NOT EXISTS idx_period_status ON accounting_periods(status);
CREATE INDEX IF NOT EXISTS idx_period_dates ON accounting_periods(start_date, end_date);

-- Performance Indexes for Cash Flow Items
CREATE INDEX IF NOT EXISTS idx_cf_tenant_branch ON cash_flow_items(tenant_id, branch_id);
CREATE INDEX IF NOT EXISTS idx_cf_date ON cash_flow_items(transaction_date);
CREATE INDEX IF NOT EXISTS idx_cf_type ON cash_flow_items(cash_flow_type);
CREATE INDEX IF NOT EXISTS idx_cf_reference ON cash_flow_items(reference_type, reference_id);

-- Data Integrity Constraints
-- Note: Some constraints may already exist. This script adds only missing constraints.

-- Accounts Receivable: Ensure balance_amount is not negative
ALTER TABLE accounts_receivable 
ADD CONSTRAINT chk_ar_balance_not_negative 
CHECK (balance_amount >= 0);

-- Accounts Payable: Ensure balance_amount is not negative
ALTER TABLE accounts_payable 
ADD CONSTRAINT chk_ap_balance_not_negative 
CHECK (balance_amount >= 0);

-- Fixed Assets: Ensure useful_life is positive
ALTER TABLE fixed_assets 
ADD CONSTRAINT chk_fa_useful_life_positive 
CHECK (useful_life > 0);

-- Fixed Assets: Ensure purchase_cost is positive
ALTER TABLE fixed_assets 
ADD CONSTRAINT chk_fa_purchase_cost_positive 
CHECK (purchase_cost > 0);

-- Budgets: Ensure total_budget is positive
ALTER TABLE budgets 
ADD CONSTRAINT chk_budget_total_positive 
CHECK (total_budget > 0);

-- Budget Items: Ensure budgeted_amount is not negative
ALTER TABLE budget_items 
ADD CONSTRAINT chk_bi_budgeted_not_negative 
CHECK (budgeted_amount >= 0);

-- Accounting Periods: Ensure end_date >= start_date
ALTER TABLE accounting_periods 
ADD CONSTRAINT chk_period_date_order 
CHECK (end_date >= start_date);

-- Bank Reconciliations: Ensure statement_balance and book_balance are not negative
ALTER TABLE bank_reconciliations 
ADD CONSTRAINT chk_br_balances_not_negative 
CHECK (statement_balance >= 0 AND book_balance >= 0);
