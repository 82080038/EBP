<?php

if (!class_exists('AccountingRepository')) {
    require_once __DIR__ . '/../Repositories/AccountingRepository.php';
}

if (!class_exists('Audit')) {
    require_once __DIR__ . '/../../../core/Audit.php';
}


class AccountingService
{
    private $repository;
    private $db;
    private $audit;

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
        $this->repository = new AccountingRepository($this->db);
        // $this->audit = new Audit();
    }

    public function createJournalEntry($data, $tenantId, $branchId, $userId)
    {
        // Start transaction
        // $this->db->beginTransaction();
        
        try {
            if (empty($data['journal_date']) || empty($data['lines']) || count($data['lines']) < 2) {
                // $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Journal date and at least 2 lines are required'
                ];
            }

            // Validate journal date is not in future
            // if (strtotime($data['journal_date']) > strtotime(date('Y-m-d'))) {
            //     $this->db->rollBack();
            //     return [
            //         'success' => false,
            //         'message' => 'Journal date cannot be in the future'
            //     ];
            // }

            // Validate debit equals credit
            $totalDebit = array_sum(array_column($data['lines'], 'debit_amount'));
            $totalCredit = array_sum(array_column($data['lines'], 'credit_amount'));
            
            if (abs($totalDebit - $totalCredit) > 0.01) {
                // $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Debit must equal credit (Debit: ' . $totalDebit . ', Credit: ' . $totalCredit . ')'
                ];
            }

            // Validate all accounts exist
            foreach ($data['lines'] as $line) {
                if (!$this->repository->accountExists($line['account_id'], $tenantId)) {
                    // $this->db->rollBack();
                    return [
                        'success' => false,
                        'message' => 'Account ID ' . $line['account_id'] . ' does not exist'
                    ];
                }
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

            $journalId = $this->repository->createJournal($journalData, $this->db);

            foreach ($data['lines'] as $line) {
                $this->repository->createJournalLine([
                    'journal_entry_id' => $journalId,
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit_amount'] ?? 0,
                    'credit' => $line['credit_amount'] ?? 0,
                    'description' => $line['description'] ?? null
                ], $this->db);
            }

            // Commit transaction
            // $this->db->commit();

            // Log audit trail
            // // $this->audit->log();

            return [
                'success' => true,
                'message' => 'Journal entry created successfully',
                'journal_id' => $journalId,
                'journal_number' => $journalNumber
            ];

        } catch (Exception $e) {
            // if ($this->db->inTransaction()) {
            //     $this->db->rollBack();
            // }
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
