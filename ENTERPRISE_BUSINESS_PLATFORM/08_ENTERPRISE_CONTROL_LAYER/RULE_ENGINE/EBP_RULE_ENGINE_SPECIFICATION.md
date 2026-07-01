# Enterprise Business Platform (EBP)

# Rule Engine Specification


**Document ID:** EBP-RULE-ENGINE-001

**Version:** 1.0

**Category:** Enterprise Engine Specification

**Status:** Official Specification



---

# 1. Introduction


Dokumen ini mendefinisikan Rule Engine untuk Enterprise Business Platform (EBP).

Rule Engine memungkinkan:


* Business rules tanpa hardcoding
* Dynamic pricing
* Automated approval
* Custom validation
* Flexible automation

Tujuan:


```

BUSINESS LOGIC

+

RULE ENGINE

=

FLEXIBLE AUTOMATION

```



---

# 2. Problem Statement


Masalah yang dihadapi:


Setiap bisnis memiliki aturan berbeda.


### Restaurant A


```
IF customer.member = GOLD

AND purchase > 1000000

THEN discount 10%

```


### Restaurant B


```
IF purchase > 500000

THEN discount 5%

```


### Pertanyaan


Apakah kita hardcode di PHP?


### Jawaban


Tidak.


Solusi:


```

Rule Engine

```



---

# 3. Rule Engine Philosophy


EBP Rule Engine menggunakan prinsip:


```

LOGIC SEPARATION

```

Artinya:


* Business logic dipisah dari kode
* Rules dapat diubah tanpa coding
* Rules dapat diubah per tenant
* Rules dapat diubah secara real-time



---

# 4. Rule Types


## 1. Pricing Rules


Mengatur harga dan diskon.


Contoh:


```
IF

customer.member = GOLD

AND purchase > 1000000

THEN

discount 10%

```


## 2. Validation Rules


Mengatur validasi data.


Contoh:


```
IF

order.total > 10000000

AND customer.credit_limit < order.total

THEN

REJECT

```


## 3. Approval Rules


Mengatur approval.


Contoh:


```
IF

purchase.amount > 1000000

THEN

REQUIRE APPROVAL FROM manager

```


## 4. Automation Rules


Mengatur otomatisasi.


Contoh:


```
IF

order.status = PAID

THEN

CREATE journal entry

DEDUCT inventory

SEND notification

```


## 5. Routing Rules


Mengatur routing.


Contoh:


```
IF

order.type = DELIVERY

THEN

ROUTE TO delivery_team

```



---

# 5. Database Schema


## business_rules


```sql
CREATE TABLE business_rules (
    rule_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    rule_name VARCHAR(100) NOT NULL,
    rule_category ENUM('pricing', 'validation', 'approval', 'automation', 'routing') NOT NULL,
    rule_description TEXT,
    condition_expression TEXT NOT NULL,
    action_expression TEXT NOT NULL,
    priority INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    effective_from TIMESTAMP NULL,
    effective_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by BIGINT,
    updated_by BIGINT,
    
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_category (rule_category),
    INDEX idx_active (is_active),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```


## rule_execution_log


```sql
CREATE TABLE rule_execution_log (
    log_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    rule_id BIGINT NOT NULL,
    tenant_id BIGINT NOT NULL,
    context_data JSON,
    execution_result ENUM('matched', 'not_matched', 'error') NOT NULL,
    execution_time_ms INT,
    error_message TEXT,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_rule_id (rule_id),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_executed_at (executed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```



---

# 6. Rule Definition Language


## Syntax


Format:


```

IF

<condition>

THEN

<action>

```


## Condition Operators


```

AND

OR

NOT

=

!=

>

<

>=

<=

IN

NOT IN

LIKE

NOT LIKE

IS NULL

IS NOT NULL

```


## Action Types


```

SET

CALCULATE

REJECT

APPROVE

ROUTE

NOTIFY

EXECUTE

```



---

# 7. Rule Examples


## Pricing Rule


```
IF

customer.member_type = 'GOLD'

AND order.total > 1000000

THEN

SET discount = 10

```


## Validation Rule


```
IF

order.total > customer.credit_limit

THEN

REJECT 'Order exceeds credit limit'

```


## Approval Rule


```
IF

purchase.amount > 1000000

THEN

REQUIRE APPROVAL FROM role = 'manager'

```


## Automation Rule


```
IF

order.status = 'PAID'

THEN

EXECUTE create_journal_entry(order)

EXECUTE deduct_inventory(order)

EXECUTE send_notification(order.customer_id, 'order_paid')

```



---

# 8. Rule Engine API


## Evaluate Rule


```php
class RuleEngine
{
    public function evaluate($tenantId, $category, $context)
    {
        $rules = $this->getActiveRules($tenantId, $category);
        
        $results = [];
        
        foreach ($rules as $rule) {
            $result = $this->evaluateRule($rule, $context);
            
            $results[] = [
                'rule_id' => $rule['rule_id'],
                'rule_name' => $rule['rule_name'],
                'matched' => $result['matched'],
                'action' => $result['action']
            ];
            
            $this->logExecution($rule['rule_id'], $tenantId, $context, $result);
        }
        
        return $results;
    }
    
    private function evaluateRule($rule, $context)
    {
        $condition = $rule['condition_expression'];
        $action = $rule['action_expression'];
        
        $matched = $this->evaluateCondition($condition, $context);
        
        if ($matched) {
            $actionResult = $this->executeAction($action, $context);
            
            return [
                'matched' => true,
                'action' => $actionResult
            ];
        }
        
        return [
            'matched' => false,
            'action' => null
        ];
    }
}
```


## Evaluate Condition


```php
private function evaluateCondition($condition, $context)
{
    // Parse condition
    $tokens = $this->parseCondition($condition);
    
    // Evaluate each token
    $result = true;
    
    foreach ($tokens as $token) {
        if ($token['type'] == 'comparison') {
            $left = $this->getValue($token['left'], $context);
            $right = $this->getValue($token['right'], $context);
            
            $tokenResult = $this->compare($left, $token['operator'], $right);
            
            if ($token['logical'] == 'AND') {
                $result = $result && $tokenResult;
            } elseif ($token['logical'] == 'OR') {
                $result = $result || $tokenResult;
            }
        }
    }
    
    return $result;
}
```


## Execute Action


```php
private function executeAction($action, $context)
{
    $actionType = $this->parseActionType($action);
    
    switch ($actionType) {
        case 'SET':
            return $this->executeSetAction($action, $context);
        
        case 'CALCULATE':
            return $this->executeCalculateAction($action, $context);
        
        case 'REJECT':
            return $this->executeRejectAction($action, $context);
        
        case 'APPROVE':
            return $this->executeApproveAction($action, $context);
        
        case 'ROUTE':
            return $this->executeRouteAction($action, $context);
        
        case 'NOTIFY':
            return $this->executeNotifyAction($action, $context);
        
        case 'EXECUTE':
            return $this->executeExecuteAction($action, $context);
        
        default:
            throw new Exception("Unknown action type: {$actionType}");
    }
}
```



---

# 9. Rule Priority


Rules dievaluasi berdasarkan priority.


```

Priority 1 (Highest)

↓

Priority 2

↓

Priority 3

↓

...

↓

Priority N (Lowest)

```


## Stop on First Match


Default behavior:


```

Stop setelah rule pertama yang match

```


## Continue All Matches


Optional behavior:


```

Evaluasi semua rules

```



---

# 10. Rule Context


Context adalah data yang dievaluasi.


## Example Context


```php
$context = [
    'customer' => [
        'id' => 1,
        'member_type' => 'GOLD',
        'credit_limit' => 5000000
    ],
    'order' => [
        'id' => 100,
        'total' => 1500000,
        'status' => 'PENDING'
    ],
    'purchase' => [
        'amount' => 2000000
    ]
];
```



---

# 11. Rule Caching


Rules di-cache untuk:


* Mengurangi database query
* Meningkatkan performance
* Mengurangi latency


## Cache Implementation


```php
class RuleCache
{
    private $cache;
    private $ttl = 3600; // 1 hour
    
    public function getRules($tenantId, $category)
    {
        $key = "rules:{$tenantId}:{$category}";
        
        $rules = $this->cache->get($key);
        
        if ($rules === null) {
            $rules = $this->loadRulesFromDatabase($tenantId, $category);
            $this->cache->set($key, $rules, $this->ttl);
        }
        
        return $rules;
    }
    
    public function invalidate($tenantId)
    {
        $pattern = "rules:{$tenantId}:*";
        $this->cache->deleteByPattern($pattern);
    }
}
```



---

# 12. Rule Testing


## Unit Tests


```php
public function testPricingRule()
{
    $context = [
        'customer' => [
            'member_type' => 'GOLD'
        ],
        'order' => [
            'total' => 1500000
        ]
    ];
    
    $result = $this->ruleEngine->evaluate(1, 'pricing', $context);
    
    $this->assertTrue($result[0]['matched']);
    $this->assertEquals(10, $result[0]['action']['discount']);
}

public function testValidationRule()
{
    $context = [
        'order' => [
            'total' => 6000000
        ],
        'customer' => [
            'credit_limit' => 5000000
        ]
    ];
    
    $result = $this->ruleEngine->evaluate(1, 'validation', $context);
    
    $this->assertTrue($result[0]['matched']);
    $this->assertEquals('REJECT', $result[0]['action']['type']);
}
```



---

# 13. Rule Performance


## Optimization


* Caching (Redis)
* Lazy loading
* Batch evaluation
* Parallel execution


## Metrics


Monitor:


* Rule evaluation time
* Cache hit rate
* Database query count
* Rule match rate



---

# 14. Rule Security


## Access Control


Hanya user dengan permission:


```

RULE_MANAGE

```


boleleh mengubah rules.


## Rule Validation


Rules divalidasi sebelum disimpan:


* Syntax check
* Security check
* Performance check


## Sandbox


Rules dievaluasi di sandbox:


* Isolate execution
* Prevent infinite loops
* Limit execution time



---

# 15. Rule Versioning


Rules dapat di-versioning:


```

v1.0: IF customer.member = GOLD THEN discount 5%

v2.0: IF customer.member = GOLD AND purchase > 1000000 THEN discount 10%

```


## Rollback


Rules dapat di-rollback ke versi sebelumnya.



---

# 16. Rule Import/Export


## Export


```php
public function exportRules($tenantId)
{
    $rules = $this->db->query(
        "SELECT * FROM business_rules WHERE tenant_id = ?",
        [$tenantId]
    )->fetchAll();
    
    return [
        'tenant_id' => $tenantId,
        'exported_at' => date('Y-m-d H:i:s'),
        'rules' => $rules
    ];
}
```


## Import


```php
public function importRules($tenantId, $rules)
{
    foreach ($rules['rules'] as $rule) {
        $this->db->query(
            "INSERT INTO business_rules 
             (tenant_id, rule_name, rule_category, condition_expression, action_expression, priority)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $tenantId,
                $rule['rule_name'],
                $rule['rule_category'],
                $rule['condition_expression'],
                $rule['action_expression'],
                $rule['priority']
            ]
        );
    }
}
```



---

# 17. Best Practices


## Rule Naming


Format:


```

[category]_[entity]_[action]

```


Example:


```
pricing_customer_discount

validation_order_credit_limit

approval_purchase_amount

automation_order_paid

```


## Rule Documentation


Dokumentasikan setiap rule:


```php
/**
 * Pricing rule for GOLD member discount
 * 
 * Condition: customer.member_type = GOLD AND order.total > 1000000
 * Action: SET discount = 10
 * 
 * @category pricing
 * @priority 1
 */
```


## Rule Testing


Selalu test rules:


* Unit test
* Integration test
* Performance test



---

# 18. Conclusion


EBP Rule Engine memungkinkan:


```

BUSINESS LOGIC

+

RULE ENGINE

=

FLEXIBLE AUTOMATION

```


Manfaat:


* Business logic tanpa hardcoding
* Rules dapat diubah tanpa coding
* Rules dapat diubah per tenant
* Rules dapat diubah secara real-time
* Professional enterprise platform


EBP Rule Engine adalah kunci untuk platform yang truly flexible dan truly enterprise.



---

# END OF DOCUMENT


Document ID:

EBP-RULE-ENGINE-001


Version:

1.0
