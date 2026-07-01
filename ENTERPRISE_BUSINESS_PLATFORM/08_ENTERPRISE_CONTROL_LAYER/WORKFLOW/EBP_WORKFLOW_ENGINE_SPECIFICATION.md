# Enterprise Business Platform (EBP)

# Workflow Engine Specification


**Document ID:** EBP-WORKFLOW-ENGINE-001

**Version:** 1.0

**Category:** Enterprise Engine Specification

**Status:** Official Specification



---

# 1. Introduction


Dokumen ini mendefinisikan Workflow Engine untuk Enterprise Business Platform (EBP).

Workflow Engine memungkinkan:


* Process automation
* Approval management
* Task routing
* Process monitoring
* Business process orchestration

Tujuan:


```

BUSINESS PROCESS

+

WORKFLOW ENGINE

=

AUTOMATED APPROVAL

```



---

# 2. Problem Statement


Masalah yang dihadapi:


Semua bisnis memiliki approval process.


### Restaurant - Purchase Approval


```
Staff Gudang

↓

Supervisor

↓

Owner

↓

Purchase Order

```


### Hotel - Maintenance Approval


```

Maintenance Staff

↓

Manager

↓

Approval

```


### Pertanyaan


Apakah kita hardcode approval logic?


### Jawaban


Tidak.


Solusi:


```

Workflow Engine

```



---

# 3. Workflow Engine Philosophy


EBP Workflow Engine menggunakan prinsip:


```

PROCESS DEFINITION

+

PROCESS INSTANCE

=

RUNNING WORKFLOW

```


Artinya:


* Process didefinisikan sekali
* Instance berjalan per transaksi
* Process dapat diubah tanpa coding
* Process dapat diubah per tenant



---

# 4. Workflow Components


## 1. Workflow Definition


Template workflow.


Contoh:


```
Purchase Approval Workflow

Steps:

1. Create Request
2. Supervisor Approval
3. Owner Approval
4. Final Approval

```


## 2. Workflow Instance


Instance yang berjalan.


Contoh:


```
Purchase Request #1001

Status: Waiting Supervisor Approval

Current Step: Supervisor Approval

```


## 3. Workflow Step


Setiap step dalam workflow.


Contoh:


```
Step: Supervisor Approval

Action: Approve/Reject

Assignee: Supervisor

```


## 4. Workflow Transition


Perpindahan antar step.


Contoh:


```
Supervisor Approval (APPROVE)

↓

Owner Approval

```


## 5. Workflow Action


Action yang dapat dilakukan.


Contoh:


```
APPROVE

REJECT

RETURN

CANCEL

```



---

# 5. Database Schema


## workflow_definitions


```sql
CREATE TABLE workflow_definitions (
    definition_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    workflow_name VARCHAR(100) NOT NULL,
    workflow_code VARCHAR(50) NOT NULL,
    workflow_category VARCHAR(50) NOT NULL,
    workflow_description TEXT,
    definition_json JSON NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    version INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by BIGINT,
    updated_by BIGINT,
    
    UNIQUE KEY uk_tenant_code (tenant_id, workflow_code),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_category (workflow_category),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```


## workflow_instances


```sql
CREATE TABLE workflow_instances (
    instance_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    definition_id BIGINT NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id BIGINT NOT NULL,
    current_step VARCHAR(100) NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'rejected', 'cancelled') NOT NULL,
    started_by BIGINT,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    context_data JSON,
    
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_definition_id (definition_id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_status (status),
    INDEX idx_current_step (current_step)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```


## workflow_steps


```sql
CREATE TABLE workflow_steps (
    step_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    definition_id BIGINT NOT NULL,
    step_name VARCHAR(100) NOT NULL,
    step_order INT NOT NULL,
    step_type ENUM('start', 'approval', 'task', 'condition', 'end') NOT NULL,
    assignee_type ENUM('user', 'role', 'dynamic') NOT NULL,
    assignee_value VARCHAR(100),
    action_required ENUM('approve', 'reject', 'return', 'cancel', 'complete') NOT NULL,
    timeout_hours INT,
    auto_action_on_timeout ENUM('approve', 'reject', 'escalate', 'return') NULL,
    
    INDEX idx_definition_id (definition_id),
    INDEX idx_step_order (step_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```


## workflow_transitions


```sql
CREATE TABLE workflow_transitions (
    transition_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    definition_id BIGINT NOT NULL,
    from_step VARCHAR(100) NOT NULL,
    to_step VARCHAR(100) NOT NULL,
    condition_expression TEXT,
    
    INDEX idx_definition_id (definition_id),
    INDEX idx_from_step (from_step)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```


## workflow_actions


```sql
CREATE TABLE workflow_actions (
    action_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    instance_id BIGINT NOT NULL,
    step_id BIGINT NOT NULL,
    action_type ENUM('approve', 'reject', 'return', 'cancel', 'complete') NOT NULL,
    action_by BIGINT NOT NULL,
    action_comment TEXT,
    action_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_instance_id (instance_id),
    INDEX idx_step_id (step_id),
    INDEX idx_action_by (action_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```



---

# 6. Workflow Definition Example


## Purchase Approval Workflow


```json
{
  "workflow_name": "Purchase Approval",
  "workflow_code": "PURCHASE_APPROVAL",
  "workflow_category": "purchasing",
  "steps": [
    {
      "step_name": "create_request",
      "step_order": 1,
      "step_type": "start",
      "assignee_type": "user",
      "assignee_value": "requester"
    },
    {
      "step_name": "supervisor_approval",
      "step_order": 2,
      "step_type": "approval",
      "assignee_type": "role",
      "assignee_value": "supervisor",
      "action_required": "approve",
      "timeout_hours": 24,
      "auto_action_on_timeout": "escalate"
    },
    {
      "step_name": "owner_approval",
      "step_order": 3,
      "step_type": "approval",
      "assignee_type": "role",
      "assignee_value": "owner",
      "action_required": "approve",
      "timeout_hours": 48,
      "auto_action_on_timeout": "escalate"
    },
    {
      "step_name": "final_approval",
      "step_order": 4,
      "step_type": "end",
      "assignee_type": "role",
      "assignee_value": "admin"
    }
  ],
  "transitions": [
    {
      "from_step": "create_request",
      "to_step": "supervisor_approval"
    },
    {
      "from_step": "supervisor_approval",
      "to_step": "owner_approval",
      "condition": "action == 'approve'"
    },
    {
      "from_step": "supervisor_approval",
      "to_step": "create_request",
      "condition": "action == 'return'"
    },
    {
      "from_step": "supervisor_approval",
      "to_step": "final_approval",
      "condition": "action == 'reject'"
    },
    {
      "from_step": "owner_approval",
      "to_step": "final_approval",
      "condition": "action == 'approve'"
    },
    {
      "from_step": "owner_approval",
      "to_step": "supervisor_approval",
      "condition": "action == 'return'"
    },
    {
      "from_step": "owner_approval",
      "to_step": "final_approval",
      "condition": "action == 'reject'"
    }
  ]
}
```



---

# 7. Workflow Engine API


## Start Workflow


```php
class WorkflowEngine
{
    public function startWorkflow($tenantId, $workflowCode, $entityType, $entityId, $context, $startedBy)
    {
        // Get workflow definition
        $definition = $this->getWorkflowDefinition($tenantId, $workflowCode);
        
        if (!$definition) {
            throw new Exception("Workflow definition not found: {$workflowCode}");
        }
        
        // Create workflow instance
        $instanceId = $this->createWorkflowInstance(
            $tenantId,
            $definition['definition_id'],
            $entityType,
            $entityId,
            $context,
            $startedBy
        );
        
        // Get first step
        $firstStep = $this->getFirstStep($definition['definition_id']);
        
        // Update instance current step
        $this->updateCurrentStep($instanceId, $firstStep['step_name']);
        
        // Notify assignee
        $this->notifyAssignee($instanceId, $firstStep);
        
        return $instanceId;
    }
}
```


## Process Action


```php
public function processAction($instanceId, $action, $comment, $actionBy)
{
    // Get current instance
    $instance = $this->getWorkflowInstance($instanceId);
    
    // Get current step
    $currentStep = $this->getStep($instance['definition_id'], $instance['current_step']);
    
    // Validate action
    if (!in_array($action, $this->getAllowedActions($currentStep))) {
        throw new Exception("Invalid action: {$action}");
    }
    
    // Record action
    $this->recordAction($instanceId, $currentStep['step_id'], $action, $comment, $actionBy);
    
    // Determine next step
    $nextStep = $this->getNextStep($instance['definition_id'], $instance['current_step'], $action);
    
    if ($nextStep) {
        // Move to next step
        $this->updateCurrentStep($instanceId, $nextStep['step_name']);
        
        // Notify assignee
        $this->notifyAssignee($instanceId, $nextStep);
        
        // Update status
        $this->updateStatus($instanceId, 'in_progress');
    } else {
        // Workflow completed
        $this->updateStatus($instanceId, 'completed');
        $this->markCompleted($instanceId);
    }
    
    return $nextStep;
}
```


## Get Pending Tasks


```php
public function getPendingTasks($userId, $tenantId)
{
    $userRoles = $this->getUserRoles($userId);
    
    $tasks = $this->db->query(
        "SELECT wi.*, ws.step_name, ws.action_required
         FROM workflow_instances wi
         INNER JOIN workflow_steps ws ON wi.current_step = ws.step_name
         WHERE wi.tenant_id = ?
         AND wi.status = 'in_progress'
         AND (ws.assignee_type = 'user' AND ws.assignee_value = ?
              OR ws.assignee_type = 'role' AND ws.assignee_value IN (?))
         ORDER BY wi.started_at DESC",
        [$tenantId, $userId, implode(',', $userRoles)]
    )->fetchAll();
    
    return $tasks;
}
```



---

# 8. Workflow Timeout Handling


## Check Timeout


```php
public function checkTimeouts()
{
    $timedOutSteps = $this->db->query(
        "SELECT wi.*, ws.*
         FROM workflow_instances wi
         INNER JOIN workflow_steps ws ON wi.current_step = ws.step_name
         WHERE wi.status = 'in_progress'
         AND ws.timeout_hours IS NOT NULL
         AND TIMESTAMPDIFF(HOUR, wi.started_at, NOW()) > ws.timeout_hours"
    )->fetchAll();
    
    foreach ($timedOutSteps as $timedOut) {
        $this->handleTimeout($timedOut);
    }
}
```


## Handle Timeout


```php
private function handleTimeout($timedOut)
{
    $autoAction = $timedOut['auto_action_on_timeout'];
    
    switch ($autoAction) {
        case 'approve':
            $this->processAction($timedOut['instance_id'], 'approve', 'Auto-approved due to timeout', 0);
            break;
        
        case 'reject':
            $this->processAction($timedOut['instance_id'], 'reject', 'Auto-rejected due to timeout', 0);
            break;
        
        case 'escalate':
            $this->escalateWorkflow($timedOut['instance_id']);
            break;
        
        case 'return':
            $this->processAction($timedOut['instance_id'], 'return', 'Returned due to timeout', 0);
            break;
    }
}
```



---

# 9. Workflow Escalation


## Escalate Workflow


```php
private function escalateWorkflow($instanceId)
{
    $instance = $this->getWorkflowInstance($instanceId);
    
    // Get escalation rules
    $escalationRule = $this->getEscalationRule($instance['definition_id'], $instance['current_step']);
    
    if ($escalationRule) {
        // Move to escalation step
        $this->updateCurrentStep($instanceId, $escalationRule['escalate_to_step']);
        
        // Notify escalation assignee
        $escalationStep = $this->getStep($instance['definition_id'], $escalationRule['escalate_to_step']);
        $this->notifyAssignee($instanceId, $escalationStep);
        
        // Log escalation
        $this->logEscalation($instanceId, $escalationRule);
    }
}
```



---

# 10. Workflow Monitoring


## Get Workflow Status


```php
public function getWorkflowStatus($instanceId)
{
    $instance = $this->getWorkflowInstance($instanceId);
    
    $actions = $this->db->query(
        "SELECT wa.*, u.username
         FROM workflow_actions wa
         LEFT JOIN users u ON wa.action_by = u.user_id
         WHERE wa.instance_id = ?
         ORDER BY wa.action_at ASC",
        [$instanceId]
    )->fetchAll();
    
    return [
        'instance' => $instance,
        'actions' => $actions,
        'current_step' => $this->getStep($instance['definition_id'], $instance['current_step']),
        'progress' => $this->calculateProgress($instance)
    ];
}
```


## Calculate Progress


```php
private function calculateProgress($instance)
{
    $totalSteps = $this->getTotalSteps($instance['definition_id']);
    $currentStepOrder = $this->getStepOrder($instance['definition_id'], $instance['current_step']);
    
    $progress = ($currentStepOrder / $totalSteps) * 100;
    
    return round($progress, 2);
}
```



---

# 11. Workflow Testing


## Unit Tests


```php
public function testStartWorkflow()
{
    $instanceId = $this->workflowEngine->startWorkflow(
        1,
        'PURCHASE_APPROVAL',
        'purchase',
        1001,
        ['amount' => 5000000],
        1
    );
    
    $instance = $this->workflowEngine->getWorkflowInstance($instanceId);
    
    $this->assertEquals('in_progress', $instance['status']);
    $this->assertEquals('create_request', $instance['current_step']);
}

public function testProcessApproval()
{
    $instanceId = $this->workflowEngine->startWorkflow(
        1,
        'PURCHASE_APPROVAL',
        'purchase',
        1001,
        ['amount' => 5000000],
        1
    );
    
    $nextStep = $this->workflowEngine->processAction(
        $instanceId,
        'approve',
        'Approved',
        2
    );
    
    $this->assertEquals('supervisor_approval', $nextStep['step_name']);
}
```



---

# 12. Workflow Performance


## Optimization


* Caching workflow definitions
* Lazy loading workflow steps
* Batch processing timeouts
* Parallel task assignment


## Metrics


Monitor:


* Workflow execution time
* Average approval time
* Timeout rate
* Escalation rate



---

# 13. Workflow Security


## Access Control


Hanya user dengan permission:


```

WORKFLOW_MANAGE

```


boleleh mengubah workflow definitions.


## Action Validation


Hanya assignee yang boleh melakukan action:

```php
if (!$this->isAssignee($userId, $step)) {
    throw new Exception("User is not assigned to this step");
}
```



---

# 14. Workflow Versioning


Workflow definitions dapat di-versioning:


```

v1.0: 3-step approval

v2.0: 4-step approval with escalation

```


## Migration


Running instances menggunakan versi saat dimulai.

New instances menggunakan versi terbaru.



---

# 15. Workflow Import/Export


## Export


```php
public function exportWorkflow($definitionId)
{
    $definition = $this->db->query(
        "SELECT * FROM workflow_definitions WHERE definition_id = ?",
        [$definitionId]
    )->fetch();
    
    $steps = $this->db->query(
        "SELECT * FROM workflow_steps WHERE definition_id = ? ORDER BY step_order",
        [$definitionId]
    )->fetchAll();
    
    $transitions = $this->db->query(
        "SELECT * FROM workflow_transitions WHERE definition_id = ?",
        [$definitionId]
    )->fetchAll();
    
    return [
        'definition' => $definition,
        'steps' => $steps,
        'transitions' => $transitions
    ];
}
```


## Import


```php
public function importWorkflow($tenantId, $workflowData)
{
    // Insert definition
    $definitionId = $this->db->query(
        "INSERT INTO workflow_definitions 
         (tenant_id, workflow_name, workflow_code, workflow_category, definition_json)
         VALUES (?, ?, ?, ?, ?)",
        [
            $tenantId,
            $workflowData['definition']['workflow_name'],
            $workflowData['definition']['workflow_code'],
            $workflowData['definition']['workflow_category'],
            json_encode($workflowData)
        ]
    )->lastInsertId();
    
    // Insert steps
    foreach ($workflowData['steps'] as $step) {
        $this->db->query(
            "INSERT INTO workflow_steps 
             (definition_id, step_name, step_order, step_type, assignee_type, assignee_value, action_required)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $definitionId,
                $step['step_name'],
                $step['step_order'],
                $step['step_type'],
                $step['assignee_type'],
                $step['assignee_value'],
                $step['action_required']
            ]
        );
    }
    
    // Insert transitions
    foreach ($workflowData['transitions'] as $transition) {
        $this->db->query(
            "INSERT INTO workflow_transitions 
             (definition_id, from_step, to_step, condition_expression)
             VALUES (?, ?, ?, ?)",
            [
                $definitionId,
                $transition['from_step'],
                $transition['to_step'],
                $transition['condition_expression']
            ]
        );
    }
    
    return $definitionId;
}
```



---

# 16. Best Practices


## Workflow Naming


Format:


```

[CATEGORY]_[ENTITY]_[ACTION]

```


Example:


```
PURCHASE_APPROVAL

LEAVE_REQUEST

EXPENSE_CLAIM

MAINTENANCE_REQUEST

```


## Step Naming


Format:


```

[ROLE]_[ACTION]

```


Example:


```
supervisor_approval

manager_review

owner_approval

admin_final

```


## Workflow Documentation


Dokumentasikan setiap workflow:


```php
/**
 * Purchase Approval Workflow
 * 
 * Steps:
 * 1. Create Request (Requester)
 * 2. Supervisor Approval (Supervisor, 24h timeout)
 * 3. Owner Approval (Owner, 48h timeout)
 * 4. Final Approval (Admin)
 * 
 * @category purchasing
 * @version 1.0
 */
```



---

# 17. Conclusion


EBP Workflow Engine memungkinkan:


```

BUSINESS PROCESS

+

WORKFLOW ENGINE

=

AUTOMATED APPROVAL

```


Manfaat:


* Process automation tanpa hardcoding
* Approval process yang terstruktur
* Process monitoring dan tracking
* Process dapat diubah tanpa coding
* Professional enterprise platform


EBP Workflow Engine adalah kunci untuk platform yang memiliki approval process yang robust dan flexible.



---

# END OF DOCUMENT


Document ID:

EBP-WORKFLOW-ENGINE-001


Version:

1.0
