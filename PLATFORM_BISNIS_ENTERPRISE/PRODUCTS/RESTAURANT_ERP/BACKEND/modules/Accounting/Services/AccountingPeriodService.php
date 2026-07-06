<?php

if (!class_exists('AccountingPeriodRepository')) {
    require_once __DIR__ . '/../Repositories/AccountingPeriodRepository.php';
}

class AccountingPeriodService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->repository = new AccountingPeriodRepository($this->db);
    }

    public function createPeriod($data, $tenantId, $branchId, $userId)
    {
        try {
            if (empty($data['fiscal_year']) || empty($data['period_number']) || empty($data['period_name']) || empty($data['start_date']) || empty($data['end_date'])) {
                return [
                    'success' => false,
                    'message' => 'Fiscal year, period number, period name, start date, and end date are required'
                ];
            }

            // Check if period already exists
            $existingPeriod = $this->repository->getPeriodByNumber($tenantId, $branchId, $data['fiscal_year'], $data['period_number']);
            if ($existingPeriod) {
                return [
                    'success' => false,
                    'message' => 'Period already exists for this fiscal year and period number'
                ];
            }

            $periodData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'fiscal_year' => $data['fiscal_year'],
                'period_number' => $data['period_number'],
                'period_name' => $data['period_name'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => 'OPEN',
                'notes' => $data['notes'] ?? null
            ];

            $periodId = $this->repository->createPeriod($periodData);

            return [
                'success' => true,
                'message' => 'Accounting period created successfully',
                'period_id' => $periodId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create accounting period: ' . $e->getMessage()
            ];
        }
    }

    public function getPeriods($tenantId, $branchId, $fiscalYear = null, $status = null)
    {
        try {
            $periods = $this->repository->getPeriods($tenantId, $branchId, $fiscalYear, $status);
            
            return [
                'success' => true,
                'message' => 'Accounting periods retrieved successfully',
                'data' => $periods
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get accounting periods: ' . $e->getMessage()
            ];
        }
    }

    public function getCurrentPeriod($tenantId, $branchId)
    {
        try {
            $currentPeriod = $this->repository->getCurrentPeriod($tenantId, $branchId);
            
            if (!$currentPeriod) {
                return [
                    'success' => false,
                    'message' => 'No current accounting period found'
                ];
            }

            return [
                'success' => true,
                'message' => 'Current accounting period retrieved successfully',
                'data' => $currentPeriod
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get current accounting period: ' . $e->getMessage()
            ];
        }
    }

    public function closePeriod($tenantId, $branchId, $periodId, $userId)
    {
        try {
            $period = $this->repository->getPeriod($tenantId, $branchId, $periodId);
            
            if (!$period) {
                return [
                    'success' => false,
                    'message' => 'Accounting period not found'
                ];
            }

            if ($period['status'] !== 'OPEN') {
                return [
                    'success' => false,
                    'message' => 'Period is not in OPEN status'
                ];
            }

            // Check if there are any open periods after this one
            $futurePeriods = $this->repository->getFuturePeriods($tenantId, $branchId, $period['fiscal_year'], $period['period_number']);
            if (!empty($futurePeriods)) {
                return [
                    'success' => false,
                    'message' => 'Cannot close period: there are open periods after this one'
                ];
            }

            // Check for unposted journal entries in this period
            $unpostedEntries = $this->repository->getUnpostedJournalEntries($tenantId, $branchId, $period['start_date'], $period['end_date']);
            if (!empty($unpostedEntries)) {
                return [
                    'success' => false,
                    'message' => 'Cannot close period: ' . count($unpostedEntries) . ' journal entries are not posted'
                ];
            }

            // Check trial balance is balanced
            $trialBalance = $this->repository->getTrialBalance($tenantId, $branchId, $period['end_date']);
            $totalDebit = array_sum(array_column($trialBalance, 'total_debit'));
            $totalCredit = array_sum(array_column($trialBalance, 'total_credit'));
            
            if (abs($totalDebit - $totalCredit) > 0.01) {
                return [
                    'success' => false,
                    'message' => 'Cannot close period: Trial balance is not balanced (Debit: ' . $totalDebit . ', Credit: ' . $totalCredit . ')'
                ];
            }

            $this->repository->updatePeriod($periodId, [
                'status' => 'CLOSED',
                'closed_by' => $userId,
                'closed_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => 'Accounting period closed successfully',
                'data' => [
                    'period_id' => $periodId,
                    'status' => 'CLOSED'
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to close accounting period: ' . $e->getMessage()
            ];
        }
    }

    public function reopenPeriod($tenantId, $branchId, $periodId, $userId)
    {
        try {
            $period = $this->repository->getPeriod($tenantId, $branchId, $periodId);
            
            if (!$period) {
                return [
                    'success' => false,
                    'message' => 'Accounting period not found'
                ];
            }

            if ($period['status'] !== 'CLOSED') {
                return [
                    'success' => false,
                    'message' => 'Period is not in CLOSED status'
                ];
            }

            $this->repository->updatePeriod($periodId, [
                'status' => 'OPEN',
                'closed_by' => null,
                'closed_at' => null
            ]);

            return [
                'success' => true,
                'message' => 'Accounting period reopened successfully',
                'data' => [
                    'period_id' => $periodId,
                    'status' => 'OPEN'
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to reopen accounting period: ' . $e->getMessage()
            ];
        }
    }
}
