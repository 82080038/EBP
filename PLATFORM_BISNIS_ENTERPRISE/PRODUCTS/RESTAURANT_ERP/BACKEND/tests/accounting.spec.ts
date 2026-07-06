import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost:8000/api/v1';

let authToken: string;
let tenantId: number = 1;
let branchId: number = 1;
let userId: number = 1;

test.describe('Accounting Module Tests', () => {

  test.beforeAll(async () => {
    // Login to get auth token
    const response = await fetch(`${BASE_URL}/auth/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        username: 'admin',
        password: 'admin123'
      })
    });

    const data = await response.json();
    authToken = data.data.access_token;
    tenantId = data.data.user.tenant_id;
    branchId = data.data.user.branch_id;
    userId = data.data.user.user_id;
    expect(authToken).toBeDefined();
  });

  test.describe('General Ledger', () => {
    
    test('should create journal entry successfully', async () => {
      const response = await fetch(`${BASE_URL}/accounting/journal-entries`, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${authToken}`
        },
        body: JSON.stringify({
          journal_date: '2026-07-07',
          description: 'Test journal entry',
          lines: [
            {
              account_id: 1,
              debit_amount: 1000,
              credit_amount: 0,
              description: 'Debit entry'
            },
            {
              account_id: 2,
              debit_amount: 0,
              credit_amount: 1000,
              description: 'Credit entry'
            }
          ]
        })
      });

      const data = await response.json();
      console.log('Create Journal Entry Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(true);
      expect(data.data.journal_id).toBeDefined();
    });

    test('should fail to create journal entry with unbalanced debit/credit', async () => {
      const response = await fetch(`${BASE_URL}/accounting/journal-entries`, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${authToken}`
        },
        body: JSON.stringify({
          journal_date: '2026-07-07',
          description: 'Test unbalanced entry',
          lines: [
            {
              account_id: 1,
              debit_amount: 1000,
              credit_amount: 0,
              description: 'Debit entry'
            },
            {
              account_id: 2,
              debit_amount: 0,
              credit_amount: 500,
              description: 'Credit entry'
            }
          ]
        })
      });

      const data = await response.json();
      console.log('Unbalanced Entry Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(false);
      expect(data.message).toContain('Debit must equal credit');
    });

    test('should get trial balance', async () => {
      const response = await fetch(`${BASE_URL}/accounting/trial-balance?as_of_date=2026-07-07`, {
        method: 'GET',
        headers: { 
          'Authorization': `Bearer ${authToken}`
        }
      });

      const data = await response.json();
      console.log('Trial Balance Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(true);
      expect(data.data).toBeDefined();
    });

    test('should get balance sheet', async () => {
      const response = await fetch(`${BASE_URL}/accounting/balance-sheet?as_of_date=2026-07-07`, {
        method: 'GET',
        headers: { 
          'Authorization': `Bearer ${authToken}`
        }
      });

      const data = await response.json();
      console.log('Balance Sheet Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(true);
      expect(data.data).toBeDefined();
    });

    test('should get profit & loss statement', async () => {
      const response = await fetch(`${BASE_URL}/accounting/profit-loss?period_start=2026-07-01&period_end=2026-07-07`, {
        method: 'GET',
        headers: { 
          'Authorization': `Bearer ${authToken}`
        }
      });

      const data = await response.json();
      console.log('Profit Loss Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(true);
      expect(data.data).toBeDefined();
    });
  });

  test.describe('Accounts Receivable', () => {
    
    test('should create invoice successfully', async () => {
      const response = await fetch(`${BASE_URL}/accounting/accounts-receivable`, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${authToken}`
        },
        body: JSON.stringify({
          invoice_date: '2026-07-07',
          customer_id: 1,
          amount: 5000,
          description: 'Test invoice'
        })
      });

      const data = await response.json();
      console.log('Create Invoice Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(true);
      expect(data.data.ar_id).toBeDefined();
    });

    test('should get accounts receivable list', async () => {
      const response = await fetch(`${BASE_URL}/accounting/accounts-receivable`, {
        method: 'GET',
        headers: { 
          'Authorization': `Bearer ${authToken}`
        }
      });

      const data = await response.json();
      console.log('AR List Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(true);
      expect(data.data).toBeDefined();
    });

    test('should add payment to invoice', async () => {
      // First get an invoice
      const listResponse = await fetch(`${BASE_URL}/accounting/accounts-receivable`, {
        method: 'GET',
        headers: { 
          'Authorization': `Bearer ${authToken}`
        }
      });
      const listData = await listResponse.json();
      
      if (listData.data && listData.data.length > 0) {
        const arId = listData.data[0].ar_id;
        
        const response = await fetch(`${BASE_URL}/accounting/accounts-receivable/payments`, {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${authToken}`
          },
          body: JSON.stringify({
            ar_id: arId,
            payment_date: '2026-07-07',
            amount: 2000,
            payment_method: 'CASH',
            description: 'Test payment'
          })
        });

        const data = await response.json();
        console.log('Add Payment Response:', JSON.stringify(data, null, 2));
        expect(response.status).toBe(200);
        expect(data.success).toBe(true);
      }
    });
  });

  test.describe('Accounts Payable', () => {
    
    test('should create bill successfully', async () => {
      const response = await fetch(`${BASE_URL}/accounting/accounts-payable`, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${authToken}`
        },
        body: JSON.stringify({
          bill_date: '2026-07-07',
          supplier_id: 1,
          amount: 3000,
          description: 'Test bill'
        })
      });

      const data = await response.json();
      console.log('Create Bill Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(true);
      expect(data.data.ap_id).toBeDefined();
    });

    test('should get accounts payable list', async () => {
      const response = await fetch(`${BASE_URL}/accounting/accounts-payable`, {
        method: 'GET',
        headers: { 
          'Authorization': `Bearer ${authToken}`
        }
      });

      const data = await response.json();
      console.log('AP List Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(true);
      expect(data.data).toBeDefined();
    });
  });

  test.describe('Bank Reconciliation', () => {
    
    test('should create bank reconciliation', async () => {
      const response = await fetch(`${BASE_URL}/accounting/bank-reconciliations`, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${authToken}`
        },
        body: JSON.stringify({
          bank_account_id: 1,
          reconciliation_date: '2026-07-07',
          statement_balance: 10000,
          book_balance: 9500,
          description: 'Test reconciliation'
        })
      });

      const data = await response.json();
      console.log('Create Bank Reconciliation Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(true);
      expect(data.data.reconciliation_id).toBeDefined();
    });

    test('should get bank reconciliations list', async () => {
      const response = await fetch(`${BASE_URL}/accounting/bank-reconciliations`, {
        method: 'GET',
        headers: { 
          'Authorization': `Bearer ${authToken}`
        }
      });

      const data = await response.json();
      console.log('Bank Reconciliations Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(true);
      expect(data.data).toBeDefined();
    });
  });

  test.describe('Fixed Assets', () => {
    
    test('should create fixed asset', async () => {
      const response = await fetch(`${BASE_URL}/accounting/fixed-assets`, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${authToken}`
        },
        body: JSON.stringify({
          asset_code: 'FA-001',
          asset_name: 'Test Equipment',
          asset_category: 'EQUIPMENT',
          purchase_date: '2026-07-07',
          purchase_cost: 50000,
          useful_life: 5,
          depreciation_method: 'STRAIGHT_LINE',
          location: 'Main Office'
        })
      });

      const data = await response.json();
      console.log('Create Fixed Asset Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(true);
      expect(data.data.asset_id).toBeDefined();
    });

    test('should get fixed assets list', async () => {
      const response = await fetch(`${BASE_URL}/accounting/fixed-assets`, {
        method: 'GET',
        headers: { 
          'Authorization': `Bearer ${authToken}`
        }
      });

      const data = await response.json();
      console.log('Fixed Assets Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(true);
      expect(data.data).toBeDefined();
    });
  });

  test.describe('Budget Management', () => {
    
    test('should create budget', async () => {
      const response = await fetch(`${BASE_URL}/accounting/budgets`, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${authToken}`
        },
        body: JSON.stringify({
          fiscal_year: 2026,
          budget_name: 'Test Budget 2026',
          start_date: '2026-01-01',
          end_date: '2026-12-31',
          total_budget: 1000000,
          description: 'Test budget'
        })
      });

      const data = await response.json();
      console.log('Create Budget Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(true);
      expect(data.data.budget_id).toBeDefined();
    });

    test('should get budgets list', async () => {
      const response = await fetch(`${BASE_URL}/accounting/budgets`, {
        method: 'GET',
        headers: { 
          'Authorization': `Bearer ${authToken}`
        }
      });

      const data = await response.json();
      console.log('Budgets Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(true);
      expect(data.data).toBeDefined();
    });
  });

  test.describe('Accounting Periods', () => {
    
    test('should create accounting period', async () => {
      const response = await fetch(`${BASE_URL}/accounting/periods`, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${authToken}`
        },
        body: JSON.stringify({
          fiscal_year: 2026,
          period_number: 7,
          period_name: 'July 2026',
          start_date: '2026-07-01',
          end_date: '2026-07-31',
          description: 'July 2026 period'
        })
      });

      const data = await response.json();
      console.log('Create Period Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(true);
      expect(data.data.period_id).toBeDefined();
    });

    test('should get accounting periods list', async () => {
      const response = await fetch(`${BASE_URL}/accounting/periods`, {
        method: 'GET',
        headers: { 
          'Authorization': `Bearer ${authToken}`
        }
      });

      const data = await response.json();
      console.log('Periods Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(true);
      expect(data.data).toBeDefined();
    });

    test('should get current period', async () => {
      const response = await fetch(`${BASE_URL}/accounting/periods/current`, {
        method: 'GET',
        headers: { 
          'Authorization': `Bearer ${authToken}`
        }
      });

      const data = await response.json();
      console.log('Current Period Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(true);
    });
  });

  test.describe('Account Suggestions', () => {
    
    test('should get account suggestions for sales', async () => {
      const response = await fetch(`${BASE_URL}/accounting/suggest-accounts?transaction_type=SALES`, {
        method: 'GET',
        headers: { 
          'Authorization': `Bearer ${authToken}`
        }
      });

      const data = await response.json();
      console.log('Account Suggestions Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(true);
      expect(data.data).toBeDefined();
    });

    test('should search accounts', async () => {
      const response = await fetch(`${BASE_URL}/accounting/accounts/search?search_term=cash`, {
        method: 'GET',
        headers: { 
          'Authorization': `Bearer ${authToken}`
        }
      });

      const data = await response.json();
      console.log('Search Accounts Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(true);
      expect(data.data).toBeDefined();
    });
  });

  test.describe('Currency Service', () => {
    
    test('should get available currencies', async () => {
      const response = await fetch(`${BASE_URL}/accounting/currencies`, {
        method: 'GET',
        headers: { 
          'Authorization': `Bearer ${authToken}`
        }
      });

      const data = await response.json();
      console.log('Currencies Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(true);
      expect(data.data).toBeDefined();
    });

    test('should set exchange rate', async () => {
      const response = await fetch(`${BASE_URL}/accounting/exchange-rates`, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${authToken}`
        },
        body: JSON.stringify({
          from_currency: 'USD',
          to_currency: 'IDR',
          rate: 15000,
          effective_date: '2026-07-07'
        })
      });

      const data = await response.json();
      console.log('Set Exchange Rate Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(true);
    });

    test('should get exchange rates', async () => {
      const response = await fetch(`${BASE_URL}/accounting/exchange-rates`, {
        method: 'GET',
        headers: { 
          'Authorization': `Bearer ${authToken}`
        }
      });

      const data = await response.json();
      console.log('Exchange Rates Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(true);
      expect(data.data).toBeDefined();
    });
  });

  test.describe('Report Queue', () => {
    
    test('should enqueue report generation', async () => {
      const response = await fetch(`${BASE_URL}/accounting/reports/enqueue`, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${authToken}`
        },
        body: JSON.stringify({
          report_type: 'TRIAL_BALANCE',
          report_name: 'Trial Balance Report',
          parameters: {
            as_of_date: '2026-07-07'
          }
        })
      });

      const data = await response.json();
      console.log('Enqueue Report Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(true);
      expect(data.data.report_job_id).toBeDefined();
    });

    test('should get user report jobs', async () => {
      const response = await fetch(`${BASE_URL}/accounting/reports/jobs`, {
        method: 'GET',
        headers: { 
          'Authorization': `Bearer ${authToken}`
        }
      });

      const data = await response.json();
      console.log('Report Jobs Response:', JSON.stringify(data, null, 2));
      expect(response.status).toBe(200);
      expect(data.success).toBe(true);
      expect(data.data).toBeDefined();
    });
  });
});
