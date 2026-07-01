<?php

require_once __DIR__ . '/../Repositories/EnterpriseRepository.php';
require_once __DIR__ . '/../../../config/database.php';

class EnterpriseService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new EnterpriseRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createShiftSchedule($data, $tenantId, $branchId)
    {
        try {
            if (empty($data['shift_date']) || empty($data['shift_type']) || empty($data['start_time']) || empty($data['end_time'])) {
                return [
                    'success' => false,
                    'message' => 'Shift date, type, start time, and end time are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $branchId;
            $scheduleId = $this->repository->createShiftSchedule($data);

            return [
                'success' => true,
                'message' => 'Shift schedule created successfully',
                'schedule_id' => $scheduleId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create schedule: ' . $e->getMessage()
            ];
        }
    }

    public function createPerformanceEvaluation($data, $tenantId, $branchId, $evaluatorId)
    {
        try {
            if (empty($data['employee_id']) || empty($data['evaluation_period_start']) || empty($data['evaluation_period_end'])) {
                return [
                    'success' => false,
                    'message' => 'Employee ID and evaluation period are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $branchId;
            $data['evaluator_id'] = $evaluatorId;
            $evaluationId = $this->repository->createPerformanceEvaluation($data);

            return [
                'success' => true,
                'message' => 'Performance evaluation created successfully',
                'evaluation_id' => $evaluationId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create evaluation: ' . $e->getMessage()
            ];
        }
    }

    public function recordCashFlow($data, $tenantId, $branchId)
    {
        try {
            if (empty($data['flow_date']) || empty($data['flow_type']) || empty($data['amount'])) {
                return [
                    'success' => false,
                    'message' => 'Flow date, type, and amount are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $branchId;
            $cashFlowId = $this->repository->createCashFlow($data);

            return [
                'success' => true,
                'message' => 'Cash flow recorded successfully',
                'cash_flow_id' => $cashFlowId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to record cash flow: ' . $e->getMessage()
            ];
        }
    }

    public function createBudget($data, $tenantId, $branchId)
    {
        try {
            if (empty($data['budget_name']) || empty($data['budget_type']) || empty($data['period_start']) || empty($data['period_end']) || empty($data['budgeted_amount'])) {
                return [
                    'success' => false,
                    'message' => 'Budget name, type, period, and amount are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $branchId;
            $budgetId = $this->repository->createBudget($data);

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

    public function updateBudgetActuals($tenantId, $branchId, $periodStart, $periodEnd)
    {
        try {
            $budgets = $this->repository->getBudgets($tenantId, $branchId, $periodStart, $periodEnd);
            
            foreach ($budgets as $budget) {
                $actualAmount = $this->calculateActualAmount($tenantId, $branchId, $budget['budget_type'], $periodStart, $periodEnd);
                $variance = $actualAmount - $budget['budgeted_amount'];
                $variancePercent = $budget['budgeted_amount'] > 0 ? ($variance / $budget['budgeted_amount']) * 100 : 0;
                
                $this->repository->updateBudget($budget['budget_id'], [
                    'actual_amount' => $actualAmount,
                    'variance' => $variance,
                    'variance_percentage' => $variancePercent
                ]);
            }

            return [
                'success' => true,
                'message' => 'Budget actuals updated successfully',
                'updated_budgets' => count($budgets)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update budget actuals: ' . $e->getMessage()
            ];
        }
    }

    private function calculateActualAmount($tenantId, $branchId, $budgetType, $periodStart, $periodEnd)
    {
        if ($budgetType === 'REVENUE') {
            $sql = "SELECT SUM(total_amount) as total FROM orders WHERE tenant_id = ? AND created_at BETWEEN ? AND ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId, $periodStart . ' 00:00:00', $periodEnd . ' 23:59:59']);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } elseif ($budgetType === 'EXPENSE') {
            $sql = "SELECT SUM(total_cost) as total FROM goods_receipt WHERE tenant_id = ? AND receipt_date BETWEEN ? AND ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tenantId, $periodStart, $periodEnd]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        }
        return 0;
    }

    public function getShiftSchedules($tenantId, $branchId, $date = null)
    {
        try {
            $schedules = $this->repository->getShiftSchedules($tenantId, $branchId, $date);
            
            return [
                'success' => true,
                'message' => 'Shift schedules retrieved successfully',
                'data' => $schedules
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get schedules: ' . $e->getMessage()
            ];
        }
    }

    public function getPerformanceEvaluations($tenantId, $branchId, $employeeId = null)
    {
        try {
            $evaluations = $this->repository->getPerformanceEvaluations($tenantId, $branchId, $employeeId);
            
            return [
                'success' => true,
                'message' => 'Performance evaluations retrieved successfully',
                'data' => $evaluations
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get evaluations: ' . $e->getMessage()
            ];
        }
    }

    public function getCashFlow($tenantId, $branchId, $startDate = null, $endDate = null)
    {
        try {
            $cashFlow = $this->repository->getCashFlow($tenantId, $branchId, $startDate, $endDate);
            
            return [
                'success' => true,
                'message' => 'Cash flow retrieved successfully',
                'data' => $cashFlow
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get cash flow: ' . $e->getMessage()
            ];
        }
    }

    public function getBudgets($tenantId, $branchId, $periodStart = null, $periodEnd = null)
    {
        try {
            $budgets = $this->repository->getBudgets($tenantId, $branchId, $periodStart, $periodEnd);
            
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
}
