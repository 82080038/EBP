-- Sample Chart of Accounts for Testing
-- This creates a basic chart of accounts structure for the accounting module

INSERT INTO chart_of_accounts (account_code, account_name, account_type, parent_id, is_active, tenant_id, created_at) VALUES
-- Assets
('1000', 'Cash', 'ASSET', NULL, 1, 1, NOW()),
('1100', 'Accounts Receivable', 'ASSET', NULL, 1, 1, NOW()),
('1200', 'Inventory', 'ASSET', NULL, 1, 1, NOW()),
('1300', 'Prepaid Expenses', 'ASSET', NULL, 1, 1, NOW()),
('1500', 'Fixed Assets', 'ASSET', NULL, 1, 1, NOW()),
('1600', 'Accumulated Depreciation', 'ASSET', NULL, 1, 1, NOW()),

-- Liabilities
('2000', 'Accounts Payable', 'LIABILITY', NULL, 1, 1, NOW()),
('2100', 'Salaries Payable', 'LIABILITY', NULL, 1, 1, NOW()),
('2200', 'Taxes Payable', 'LIABILITY', NULL, 1, 1, NOW()),
('2300', 'Loans Payable', 'LIABILITY', NULL, 1, 1, NOW()),

-- Equity
('3000', 'Owner Equity', 'EQUITY', NULL, 1, 1, NOW()),
('3100', 'Retained Earnings', 'EQUITY', NULL, 1, 1, NOW()),

-- Revenue
('4000', 'Sales Revenue', 'REVENUE', NULL, 1, 1, NOW()),
('4100', 'Service Revenue', 'REVENUE', NULL, 1, 1, NOW()),
('4200', 'Other Revenue', 'REVENUE', NULL, 1, 1, NOW()),

-- Expenses
('6000', 'Cost of Goods Sold', 'EXPENSE', NULL, 1, 1, NOW()),
('6100', 'Salary Expense', 'EXPENSE', NULL, 1, 1, NOW()),
('6200', 'Rent Expense', 'EXPENSE', NULL, 1, 1, NOW()),
('6300', 'Utility Expense', 'EXPENSE', NULL, 1, 1, NOW()),
('6400', 'Marketing Expense', 'EXPENSE', NULL, 1, 1, NOW()),
('6500', 'Other Expenses', 'EXPENSE', NULL, 1, 1, NOW());
