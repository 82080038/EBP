<?php

if (!class_exists('BudgetRepository')) {
    require_once __DIR__ . '/../Repositories/BudgetRepository.php';
}

class BudgetService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new BudgetRepository();
                
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function createBudget($data, $tenantId, $branchId, $userId)
    {
        try {
            if (empty($data['budget_name']) || empty($data['fiscal_year']) || empty($data['start_date']) || empty($data['end_date']) || empty($data['total_budget'])) {
                return [
                    'success' => false,
                    'message' => 'Budget name, fiscal year, start date, end date, and total budget are required'
                ];
            }

            $budgetData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'budget_name' => $data['budget_name'],
                'fiscal_year' => $data['fiscal_year'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'total_budget' => $data['total_budget'],
                'status' => 'DRAFT',
                'notes' => $data['notes'] ?? null
            ];

            $budgetId = $this->repository->createBudget($budgetData);

            return [
                'success' => true,
                'message' => 'Budget created successfully',
                'budget_id' => $budgetId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create budget: ' . $e->getMessage()
            ];
        }
    }

    public function getBudgets($tenantId, $branchId, $fiscalYear = null, $status = null)
    {
        try {
            $budgets = $this->repository->getBudgets($tenantId, $branchId, $fiscalYear, $status);
            
            return [
                'success' => true,
                'message' => 'Budgets retrieved successfully',
                'data' => $budgets
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get budgets: ' . $e->getMessage()
            ];
        }
    }

    public function getBudget($tenantId, $branchId, $budgetId)
    {
        try {
            $budget = $this->repository->getBudget($tenantId, $branchId, $budgetId);
            
            if (!$budget) {
                return [
                    'success' => false,
                    'message' => 'Budget not found'
                ];
            }

            // Get budget items
            $items = $this->repository->getBudgetItems($budgetId);
            $budget['items'] = $items;

            return [
                'success' => true,
                'message' => 'Budget retrieved successfully',
                'data' => $budget
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get budget: ' . $e->getMessage()
            ];
        }
    }

    public function addBudgetItem($data, $tenantId, $branchId)
    {
        try {
            if (empty($data['budget_id']) || empty($data['account_id']) || empty($data['budgeted_amount'])) {
                return [
                    'success' => false,
                    'message' => 'Budget ID, account ID, and budgeted amount are required'
                ];
            }

            $budget = $this->repository->getBudget($tenantId, $branchId, $data['budget_id']);
            
            if (!$budget) {
                return [
                    'success' => false,
                    'message' => 'Budget not found'
                ];
            }

            if ($budget['status'] !== 'DRAFT') {
                return [
                    'success' => false,
                    'message' => 'Cannot add items to approved budget'
                ];
            }

            $itemData = [
                'budget_id' => $data['budget_id'],
                'account_id' => $data['account_id'],
                'budgeted_amount' => $data['budgeted_amount'],
                'actual_amount' => 0,
                'variance' => 0,
                'period_type' => $data['period_type'] ?? 'MONTHLY'
            ];

            $budgetItemId = $this->repository->addBudgetItem($itemData);

            return [
                'success' => true,
                'message' => 'Budget item added successfully',
                'budget_item_id' => $budgetItemId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to add budget item: ' . $e->getMessage()
            ];
        }
    }

    public function approveBudget($tenantId, $branchId, $budgetId, $userId)
    {
        try {
            $budget = $this->repository->getBudget($tenantId, $branchId, $budgetId);
            
            if (!$budget) {
                return [
                    'success' => false,
                    'message' => 'Budget not found'
                ];
            }

            if ($budget['status'] !== 'DRAFT') {
                return [
                    'success' => false,
                    'message' => 'Budget is not in draft status'
                ];
            }

            $this->repository->updateBudget($budgetId, [
                'status' => 'APPROVED',
                'approved_by' => $userId,
                'approved_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => 'Budget approved successfully',
                'data' => [
                    'budget_id' => $budgetId,
                    'status' => 'APPROVED'
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to approve budget: ' . $e->getMessage()
            ];
        }
    }

    public function getBudgetVariance($tenantId, $branchId, $budgetId)
    {
        try {
            $budget = $this->repository->getBudget($tenantId, $branchId, $budgetId);
            
            if (!$budget) {
                return [
                    'success' => false,
                    'message' => 'Budget not found'
                ];
            }

            $items = $this->repository->getBudgetItems($budgetId);
            
            // Calculate actual amounts from general ledger
            $highVarianceItems = [];
            foreach ($items as &$item) {
                $actualAmount = $this->repository->getActualAmount($tenantId, $branchId, $item['account_id'], $budget['start_date'], $budget['end_date']);
                $item['actual_amount'] = $actualAmount;
                $item['variance'] = $item['budgeted_amount'] - $actualAmount;
                $item['variance_percentage'] = $item['budgeted_amount'] > 0 ? ($item['variance'] / $item['budgeted_amount']) * 100 : 0;
                
                // Check for high variance (>20%)
                if (abs($item['variance_percentage']) > 20) {
                    $highVarianceItems[] = [
                        'account_name' => $item['account_name'],
                        'variance_percentage' => $item['variance_percentage'],
                        'budgeted_amount' => $item['budgeted_amount'],
                        'actual_amount' => $actualAmount,
                        'variance' => $item['variance']
                    ];
                }
            }

            // Log high variance alerts
            if (!empty($highVarianceItems)) {
                $this->logVarianceAlerts($tenantId, $budgetId, $highVarianceItems);
            }

            return [
                'success' => true,
                'message' => 'Budget variance retrieved successfully',
                'data' => [
                    'budget' => $budget,
                    'items' => $items,
                    'high_variance_alerts' => $highVarianceItems
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get budget variance: ' . $e->getMessage()
            ];
        }
    }

    private function logVarianceAlerts($tenantId, $budgetId, $highVarianceItems)
    {
        // Log to audit trail
        if (!class_exists('Audit')) {
            require_once __DIR__ . '/../../../core/Audit.php';
        }
        $audit = new Audit();
        
        $audit->log(
            $tenantId,
            0, // System user
            'ACCOUNTING',
            'BUDGET_VARIANCE_ALERT',
            $budgetId,
            'budgets',
            null,
            [
                'budget_id' => $budgetId,
                'high_variance_count' => count($highVarianceItems),
                'high_variance_items' => $highVarianceItems
            ]
        );
    }
}
