# Enterprise Business Platform (EBP)

# Subscription and Billing Architecture


**Document ID:** EBP-SUBSCRIPTION-BILLING-001

**Version:** 1.0

**Category:** Commercial Platform Architecture

**Status:** Official Architecture Specification



---

# 1. Introduction


Dokumen ini mendefinisikan arsitektur Subscription dan Billing untuk Enterprise Business Platform (EBP).

EBP sebagai SaaS platform membutuhkan sistem:


* Subscription management
* Billing automation
* Payment processing
* Invoice generation
- Usage tracking
- Revenue recognition

Tujuan:


```

PLATFORM

+

SUBSCRIPTION

+

BILLING

=

RECURRING REVENUE

```



---

# 2. Subscription Model


## Plan Types


### Basic Plan


```
Price: Rp 500.000/bulan
Features:
- POS
- Inventory
- Basic Reporting
Limits:
- 5 users
- 5 GB storage
- Unlimited transactions
```


### Professional Plan


```
Price: Rp 2.000.000/bulan
Features:
- All Basic Features
- Accounting
- Multi Branch
- Advanced Reporting
- API Access
Limits:
- 20 users
- 50 GB storage
- Unlimited transactions
```


### Enterprise Plan


```
Price: Custom
Features:
- All Professional Features
- AI Forecast
- Custom Development
- Dedicated Support
- SLA 99.9%
Limits:
- Unlimited users
- Unlimited storage
- Unlimited transactions
```



---

# 3. Database Schema


## subscription_plans


```sql
CREATE TABLE subscription_plans (
    plan_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    plan_name VARCHAR(50) NOT NULL,
    plan_code VARCHAR(20) NOT NULL UNIQUE,
    plan_type ENUM('basic', 'professional', 'enterprise') NOT NULL,
    price_monthly DECIMAL(10,2) NOT NULL,
    price_yearly DECIMAL(10,2) NOT NULL,
    yearly_discount_percent DECIMAL(5,2) DEFAULT 0,
    max_users INT,
    max_storage_gb INT,
    max_transactions_per_month INT,
    features JSON NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    trial_days INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_plan_type (plan_type),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```


## subscriptions


```sql
CREATE TABLE subscriptions (
    subscription_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    plan_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    subscription_status ENUM('trial', 'active', 'suspended', 'cancelled', 'expired') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    billing_cycle ENUM('monthly', 'yearly') NOT NULL,
    auto_renew BOOLEAN DEFAULT TRUE,
    current_users INT DEFAULT 0,
    current_storage_gb DECIMAL(10,2) DEFAULT 0,
    current_transactions_monthly INT DEFAULT 0,
    trial_end_date DATE NULL,
    cancelled_at TIMESTAMP NULL,
    cancel_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_tenant_product (tenant_id, product_id),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_status (subscription_status),
    INDEX idx_end_date (end_date),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```


## invoices


```sql
CREATE TABLE invoices (
    invoice_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    subscription_id BIGINT NOT NULL,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    invoice_date DATE NOT NULL,
    due_date DATE NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    status ENUM('draft', 'sent', 'paid', 'overdue', 'cancelled', 'refunded') NOT NULL,
    payment_method VARCHAR(50),
    payment_reference VARCHAR(100),
    paid_at TIMESTAMP NULL,
    reminder_sent_at TIMESTAMP NULL,
    overdue_sent_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_subscription_id (subscription_id),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date),
    INDEX idx_invoice_date (invoice_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```


## invoice_items


```sql
CREATE TABLE invoice_items (
    item_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    invoice_id BIGINT NOT NULL,
    item_type ENUM('subscription', 'usage_overage', 'addon', 'discount') NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    item_description TEXT,
    quantity INT DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    
    INDEX idx_invoice_id (invoice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```


## payments


```sql
CREATE TABLE payments (
    payment_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    invoice_id BIGINT NOT NULL,
    tenant_id BIGINT NOT NULL,
    payment_method ENUM('credit_card', 'bank_transfer', 'e_wallet', 'manual') NOT NULL,
    payment_gateway VARCHAR(50),
    payment_reference VARCHAR(100),
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    status ENUM('pending', 'processing', 'completed', 'failed', 'refunded') NOT NULL,
    failure_reason TEXT,
    processed_at TIMESTAMP NULL,
    refunded_at TIMESTAMP NULL,
    refund_amount DECIMAL(10,2),
    refund_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_invoice_id (invoice_id),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```


## usage_tracking


```sql
CREATE TABLE usage_tracking (
    usage_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    subscription_id BIGINT NOT NULL,
    metric_type ENUM('users', 'storage', 'transactions', 'api_calls') NOT NULL,
    metric_value DECIMAL(10,2) NOT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_subscription_id (subscription_id),
    INDEX idx_metric_type (metric_type),
    INDEX idx_recorded_at (recorded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```



---

# 4. Billing Cycle


## Monthly Billing


```

Day 1: Generate Invoice

Day 1-5: Send Invoice

Day 15: Due Date

Day 16: Overdue

Day 30: Suspension

```


## Yearly Billing


```

Anniversary Date: Generate Invoice

Anniversary Date + 15 days: Due Date

Anniversary Date + 30 days: Overdue

Anniversary Date + 60 days: Suspension

```



---

# 5. Billing Engine API


## Generate Invoice


```php
class BillingEngine
{
    public function generateInvoice($subscriptionId)
    {
        $subscription = $this->getSubscription($subscriptionId);
        $plan = $this->getPlan($subscription['plan_id']);
        
        // Calculate period
        $periodStart = $subscription['start_date'];
        $periodEnd = $this->calculatePeriodEnd($subscription);
        
        // Calculate base amount
        $baseAmount = $subscription['billing_cycle'] == 'yearly' 
            ? $plan['price_yearly'] 
            : $plan['price_monthly'];
        
        // Calculate usage overage
        $overageAmount = $this->calculateUsageOverage($subscription);
        
        // Calculate discount
        $discountAmount = $this->calculateDiscount($subscription);
        
        // Calculate tax
        $taxAmount = $this->calculateTax($baseAmount + $overageAmount - $discountAmount);
        
        // Calculate total
        $totalAmount = $baseAmount + $overageAmount - $discountAmount + $taxAmount;
        
        // Create invoice
        $invoiceId = $this->createInvoice([
            'tenant_id' => $subscription['tenant_id'],
            'subscription_id' => $subscriptionId,
            'invoice_number' => $this->generateInvoiceNumber(),
            'invoice_date' => date('Y-m-d'),
            'due_date' => $this->calculateDueDate(),
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'subtotal' => $baseAmount + $overageAmount,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount
        ]);
        
        // Add invoice items
        $this->addInvoiceItems($invoiceId, [
            [
                'item_type' => 'subscription',
                'item_name' => $plan['plan_name'] . ' - ' . $subscription['billing_cycle'],
                'quantity' => 1,
                'unit_price' => $baseAmount,
                'total_price' => $baseAmount
            ],
            [
                'item_type' => 'usage_overage',
                'item_name' => 'Usage Overage',
                'quantity' => 1,
                'unit_price' => $overageAmount,
                'total_price' => $overageAmount
            ],
            [
                'item_type' => 'discount',
                'item_name' => 'Discount',
                'quantity' => 1,
                'unit_price' => -$discountAmount,
                'total_price' => -$discountAmount
            ]
        ]);
        
        return $invoiceId;
    }
}
```


## Process Payment


```php
public function processPayment($invoiceId, $paymentMethod, $paymentData)
{
    $invoice = $this->getInvoice($invoiceId);
    
    if ($invoice['status'] == 'paid') {
        throw new Exception('Invoice already paid');
    }
    
    // Create payment record
    $paymentId = $this->createPayment([
        'invoice_id' => $invoiceId,
        'tenant_id' => $invoice['tenant_id'],
        'payment_method' => $paymentMethod,
        'amount' => $invoice['total_amount'],
        'status' => 'pending'
    ]);
    
    // Process payment based on method
    switch ($paymentMethod) {
        case 'credit_card':
            $result = $this->processCreditCard($paymentId, $paymentData);
            break;
        
        case 'bank_transfer':
            $result = $this->processBankTransfer($paymentId, $paymentData);
            break;
        
        case 'e_wallet':
            $result = $this->processEWallet($paymentId, $paymentData);
            break;
        
        case 'manual':
            $result = $this->processManual($paymentId, $paymentData);
            break;
    }
    
    if ($result['success']) {
        // Update payment status
        $this->updatePaymentStatus($paymentId, 'completed', $result['reference']);
        
        // Update invoice status
        $this->updateInvoiceStatus($invoiceId, 'paid');
        
        // Update subscription end date
        $this->extendSubscription($invoice['subscription_id']);
        
        // Send payment confirmation
        $this->sendPaymentConfirmation($invoiceId);
    } else {
        // Update payment status
        $this->updatePaymentStatus($paymentId, 'failed', null, $result['error']);
    }
    
    return $result;
}
```


## Check Usage


```php
public function checkUsage($tenantId, $subscriptionId)
{
    $subscription = $this->getSubscription($subscriptionId);
    $plan = $this->getPlan($subscription['plan_id']);
    
    // Get current usage
    $currentUsers = $this->getCurrentUserCount($tenantId);
    $currentStorage = $this->getCurrentStorageUsage($tenantId);
    $currentTransactions = $this->getCurrentTransactionCount($tenantId);
    
    // Check limits
    $usage = [
        'users' => [
            'current' => $currentUsers,
            'limit' => $plan['max_users'],
            'overage' => max(0, $currentUsers - $plan['max_users']),
            'percentage' => $plan['max_users'] > 0 ? ($currentUsers / $plan['max_users']) * 100 : 0
        ],
        'storage' => [
            'current' => $currentStorage,
            'limit' => $plan['max_storage_gb'],
            'overage' => max(0, $currentStorage - $plan['max_storage_gb']),
            'percentage' => $plan['max_storage_gb'] > 0 ? ($currentStorage / $plan['max_storage_gb']) * 100 : 0
        ],
        'transactions' => [
            'current' => $currentTransactions,
            'limit' => $plan['max_transactions_per_month'],
            'overage' => max(0, $currentTransactions - $plan['max_transactions_per_month']),
            'percentage' => $plan['max_transactions_per_month'] > 0 ? ($currentTransactions / $plan['max_transactions_per_month']) * 100 : 0
        ]
    ];
    
    return $usage;
}
```



---

# 6. Payment Gateway Integration


## Supported Gateways


### Credit Card


* Midtrans
* Xendit
* Stripe
* PayPal


### Bank Transfer


* Virtual Account
* Manual bank transfer


### E-Wallet


* GoPay
* OVO
* Dana
* LinkAja


## Integration Example (Midtrans)


```php
class MidtransGateway
{
    public function createPayment($invoice, $paymentData)
    {
        $params = [
            'transaction_details' => [
                'order_id' => $invoice['invoice_number'],
                'gross_amount' => $invoice['total_amount']
            ],
            'customer_details' => [
                'first_name' => $paymentData['first_name'],
                'last_name' => $paymentData['last_name'],
                'email' => $paymentData['email'],
                'phone' => $paymentData['phone']
            ],
            'item_details' => $this->mapInvoiceItems($invoice),
            'enabled_payments' => ['credit_card', 'bank_transfer', 'e_wallet']
        ];
        
        $response = $this->midtrans->createTransaction($params);
        
        return [
            'success' => true,
            'reference' => $response['token'],
            'redirect_url' => $response['redirect_url']
        ];
    }
    
    public function verifyPayment($notification)
    {
        $orderId = $notification['order_id'];
        $transactionStatus = $notification['transaction_status'];
        
        if ($transactionStatus == 'settlement') {
            $this->markInvoiceAsPaid($orderId);
        }
        
        return true;
    }
}
```



---

# 7. Usage Tracking


## Track User Usage


```php
public function trackUserUsage($tenantId)
{
    $userCount = $this->db->query(
        "SELECT COUNT(*) as count FROM users WHERE tenant_id = ? AND status = 'ACTIVE'",
        [$tenantId]
    )->fetch()['count'];
    
    $this->db->query(
        "INSERT INTO usage_tracking 
         (tenant_id, subscription_id, metric_type, metric_value, period_start, period_end)
         VALUES (?, ?, 'users', ?, ?, ?)",
        [
            $tenantId,
            $this->getActiveSubscriptionId($tenantId),
            $userCount,
            date('Y-m-01'),
            date('Y-m-t')
        ]
    );
}
```


## Track Storage Usage


```php
public function trackStorageUsage($tenantId)
{
    $storageUsage = $this->calculateStorageUsage($tenantId); // in GB
    
    $this->db->query(
        "INSERT INTO usage_tracking 
         (tenant_id, subscription_id, metric_type, metric_value, period_start, period_end)
         VALUES (?, ?, 'storage', ?, ?, ?)",
        [
            $tenantId,
            $this->getActiveSubscriptionId($tenantId),
            $storageUsage,
            date('Y-m-01'),
            date('Y-m-t')
        ]
    );
}
```


## Track Transaction Usage


```php
public function trackTransactionUsage($tenantId)
{
    $transactionCount = $this->db->query(
        "SELECT COUNT(*) as count FROM orders 
         WHERE tenant_id = ? 
         AND created_at >= ? 
         AND created_at <= ?",
        [
            $tenantId,
            date('Y-m-01'),
            date('Y-m-t')
        ]
    )->fetch()['count'];
    
    $this->db->query(
        "INSERT INTO usage_tracking 
         (tenant_id, subscription_id, metric_type, metric_value, period_start, period_end)
         VALUES (?, ?, 'transactions', ?, ?, ?)",
        [
            $tenantId,
            $this->getActiveSubscriptionId($tenantId),
            $transactionCount,
            date('Y-m-01'),
            date('Y-m-t')
        ]
    );
}
```



---

# 8. Overage Calculation


## Calculate Overage Amount


```php
private function calculateUsageOverage($subscription)
{
    $plan = $this->getPlan($subscription['plan_id']);
    $usage = $this->checkUsage($subscription['tenant_id'], $subscription['subscription_id']);
    
    $overageAmount = 0;
    
    // User overage
    if ($usage['users']['overage'] > 0) {
        $overageAmount += $usage['users']['overage'] * 50000; // Rp 50.000 per user
    }
    
    // Storage overage
    if ($usage['storage']['overage'] > 0) {
        $overageAmount += $usage['storage']['overage'] * 10000; // Rp 10.000 per GB
    }
    
    // Transaction overage
    if ($usage['transactions']['overage'] > 0) {
        $overageAmount += $usage['transactions']['overage'] * 100; // Rp 100 per transaction
    }
    
    return $overageAmount;
}
```



---

# 9. Invoice Generation Schedule


## Cron Job


```bash
# Daily at 00:00
0 0 * * * php /path/to/ebp/bin/billing:generate-invoices

# Daily at 09:00
0 9 * * * php /path/to/ebp/bin/billing:send-reminders

# Daily at 16:00
0 16 * * * php /path/to/ebp/bin/billing:check-overdue

# Daily at 00:00
0 0 * * * php /path/to/ebp/bin/billing:track-usage
```



---

# 10. Reminder System


## Reminder Schedule


### Before Due Date


```

3 days before: First reminder

1 day before: Second reminder

```


### After Due Date


```

1 day after: Overdue notice

7 days after: Final notice

14 days after: Suspension notice

```


## Send Reminder


```php
public function sendReminder($invoiceId, $reminderType)
{
    $invoice = $this->getInvoice($invoiceId);
    $tenant = $this->getTenant($invoice['tenant_id']);
    
    $template = $this->getReminderTemplate($reminderType);
    
    $email = [
        'to' => $tenant['billing_email'],
        'subject' => $template['subject'],
        'body' => $this->renderTemplate($template, [
            'invoice_number' => $invoice['invoice_number'],
            'due_date' => $invoice['due_date'],
            'amount' => $invoice['total_amount'],
            'payment_link' => $this->generatePaymentLink($invoiceId)
        ])
    ];
    
    $this->emailService->send($email);
    
    // Update reminder sent timestamp
    $this->updateReminderSent($invoiceId, $reminderType);
}
```



---

# 11. Subscription Management


## Upgrade Plan


```php
public function upgradePlan($tenantId, $newPlanId)
{
    $currentSubscription = $this->getActiveSubscription($tenantId);
    $newPlan = $this->getPlan($newPlanId);
    
    // Calculate prorated amount
    $proratedAmount = $this->calculateProratedAmount($currentSubscription, $newPlan);
    
    // Create invoice for upgrade
    $invoiceId = $this->createInvoice([
        'tenant_id' => $tenantId,
        'subscription_id' => $currentSubscription['subscription_id'],
        'invoice_number' => $this->generateInvoiceNumber(),
        'invoice_date' => date('Y-m-d'),
        'due_date' => date('Y-m-d', strtotime('+7 days')),
        'subtotal' => $proratedAmount,
        'tax_amount' => $this->calculateTax($proratedAmount),
        'total_amount' => $proratedAmount + $this->calculateTax($proratedAmount)
    ]);
    
    // Update subscription plan
    $this->updateSubscriptionPlan($currentSubscription['subscription_id'], $newPlanId);
    
    return $invoiceId;
}
```


## Downgrade Plan


```php
public function downgradePlan($tenantId, $newPlanId)
{
    $currentSubscription = $this->getActiveSubscription($tenantId);
    
    // Downgrade takes effect at next billing cycle
    $this->schedulePlanChange($currentSubscription['subscription_id'], $newPlanId);
    
    return [
        'message' => 'Plan will be downgraded at next billing cycle',
        'effective_date' => $currentSubscription['end_date']
    ];
}
```


## Cancel Subscription


```php
public function cancelSubscription($subscriptionId, $reason)
{
    $subscription = $this->getSubscription($subscriptionId);
    
    // Update subscription status
    $this->updateSubscriptionStatus($subscriptionId, 'cancelled', $reason);
    
    // Access remains until end of current period
    $this->updateSubscriptionEndDate($subscriptionId, $subscription['end_date']);
    
    // Send cancellation confirmation
    $this->sendCancellationConfirmation($subscriptionId);
    
    return [
        'message' => 'Subscription cancelled',
        'access_until' => $subscription['end_date']
    ];
}
```



---

# 12. Revenue Recognition


## Recognize Revenue


```php
public function recognizeRevenue($invoiceId)
{
    $invoice = $this->getInvoice($invoiceId);
    
    if ($invoice['status'] != 'paid') {
        return;
    }
    
    // Calculate daily revenue
    $daysInPeriod = $this->calculateDaysInPeriod($invoice['period_start'], $invoice['period_end']);
    $dailyRevenue = $invoice['total_amount'] / $daysInPeriod;
    
    // Create revenue recognition schedule
    for ($date = $invoice['period_start']; $date <= $invoice['period_end']; $date = date('Y-m-d', strtotime($date . ' +1 day'))) {
        $this->createRevenueRecognition([
            'invoice_id' => $invoiceId,
            'date' => $date,
            'amount' => $dailyRevenue
        ]);
    }
}
```



---

# 13. Reporting


## Revenue Report


```php
public function getRevenueReport($startDate, $endDate)
{
    $revenue = $this->db->query(
        "SELECT 
            DATE(paid_at) as date,
            SUM(total_amount) as revenue,
            COUNT(*) as invoice_count
         FROM invoices
         WHERE status = 'paid'
         AND paid_at BETWEEN ? AND ?
         GROUP BY DATE(paid_at)
         ORDER BY date ASC",
        [$startDate, $endDate]
    )->fetchAll();
    
    return $revenue;
}
```


## Subscription Report


```php
public function getSubscriptionReport()
{
    $subscriptions = $this->db->query(
        "SELECT 
            plan_name,
            COUNT(*) as total_subscriptions,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'trial' THEN 1 ELSE 0 END) as trial,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
         FROM subscriptions s
         INNER JOIN subscription_plans p ON s.plan_id = p.plan_id
         GROUP BY plan_name"
    )->fetchAll();
    
    return $subscriptions;
}
```


## MRR (Monthly Recurring Revenue)


```php
public function calculateMRR()
{
    $mrr = $this->db->query(
        "SELECT 
            SUM(CASE 
                WHEN billing_cycle = 'monthly' THEN price_monthly
                WHEN billing_cycle = 'yearly' THEN price_yearly / 12
            END) as mrr
         FROM subscriptions s
         INNER JOIN subscription_plans p ON s.plan_id = p.plan_id
         WHERE s.status = 'active'"
    )->fetch()['mrr'];
    
    return $mrr;
}
```



---

# 14. Best Practices


## Invoice Numbering


Format:


```

INV-{YYYY}{MM}-{TENANT_ID}-{SEQUENCE}

```


Example:


```

INV-202607-001-0001

```


## Tax Calculation


Always calculate tax after discount:


```

Base Amount

+ Overage

- Discount

= Taxable Amount

× Tax Rate

= Tax Amount

```


## Data Retention


Keep financial data for:


* Invoices: 7 years
* Payments: 7 years
- Usage tracking: 2 years



---

# 15. Conclusion


EBP Subscription and Billing Architecture memungkinkan:


```

PLATFORM

+

SUBSCRIPTION

+

BILLING

=

RECURRING REVENUE

```


Manfaat:


* Automated billing
* Multiple payment methods
* Usage tracking
* Revenue recognition
* Professional SaaS platform
* Sustainable business model


EBP Subscription and Billing Architecture adalah kunci untuk menjadi software company yang profitable dan scalable.



---

# END OF DOCUMENT


Document ID:

EBP-SUBSCRIPTION-BILLING-001


Version:

1.0
