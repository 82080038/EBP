<?php

if (!class_exists('AccountingRepository')) {
    require_once __DIR__ . '/../Repositories/AccountingRepository.php';
}
if (!class_exists('database')) {
    require_once __DIR__ . '/../../../config/database.php';
}

class AccountingService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new AccountingRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createJournalEntry($data, $tenantId, $branchId, $userId)
    {
        try {
            if (empty($data['journal_date']) || empty($data['lines']) || count($data['lines']) < 2) {
                return [
                    'success' => false,
                    'message' => 'Journal date and at least 2 lines are required'
                ];
            }

            // Validate debit equals credit
            $totalDebit = array_sum(array_column($data['lines'], 'debit_amount'));
            $totalCredit = array_sum(array_column($data['lines'], 'credit_amount'));
            
            if (abs($totalDebit - $totalCredit) > 0.01) {
                return [
                    'success' => false,
                    'message' => 'Debit must equal credit'
                ];
            }

            $journalNumber = 'JE-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            $journalData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'journal_number' => $journalNumber,
                'journal_date' => $data['journal_date'],
                'description' => $data['description'] ?? null,
                'status' => 'POSTED',
                'posted_by' => $userId,
                'posted_at' => date('Y-m-d H:i:s')
            ];

            $journalId = $this->repository->createJournal($journalData);

            foreach ($data['lines'] as $line) {
                $this->repository->createJournalLine([
                    'journal_id' => $journalId,
                    'account_id' => $line['account_id'],
                    'debit_amount' => $line['debit_amount'] ?? 0,
                    'credit_amount' => $line['credit_amount'] ?? 0,
                    'description' => $line['description'] ?? null
                ]);
            }

            return [
                'success' => true,
                'message' => 'Journal entry created successfully',
                'journal_id' => $journalId,
                'journal_number' => $journalNumber
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create journal entry: ' . $e->getMessage()
            ];
        }
    }

    public function getTrialBalance($tenantId, $branchId, $asOfDate)
    {
        try {
            $trialBalance = $this->repository->getTrialBalance($tenantId, $branchId, $asOfDate);
            
            return [
                'success' => true,
                'message' => 'Trial balance retrieved successfully',
                'data' => $trialBalance
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get trial balance: ' . $e->getMessage()
            ];
        }
    }

    public function getBalanceSheet($tenantId, $branchId, $asOfDate)
    {
        try {
            $balanceSheet = $this->repository->getBalanceSheet($tenantId, $branchId, $asOfDate);
            
            return [
                'success' => true,
                'message' => 'Balance sheet retrieved successfully',
                'data' => $balanceSheet
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get balance sheet: ' . $e->getMessage()
            ];
        }
    }

    public function getProfitLoss($tenantId, $branchId, $periodStart, $periodEnd)
    {
        try {
            $profitLoss = $this->repository->getProfitLoss($tenantId, $branchId, $periodStart, $periodEnd);
            
            return [
                'success' => true,
                'message' => 'Profit & loss retrieved successfully',
                'data' => $profitLoss
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get profit & loss: ' . $e->getMessage()
            ];
        }
    }
}
