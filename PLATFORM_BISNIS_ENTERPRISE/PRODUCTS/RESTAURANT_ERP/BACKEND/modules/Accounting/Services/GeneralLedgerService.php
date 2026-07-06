<?php

if (!class_exists('GeneralLedgerRepository')) {
    require_once __DIR__ . '/../Repositories/GeneralLedgerRepository.php';
}

class GeneralLedgerService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new GeneralLedgerRepository();
                
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getLedger($tenantId, $branchId, $startDate, $endDate, $accountId = null)
    {
        try {
            $ledger = $this->repository->getLedger($tenantId, $branchId, $startDate, $endDate, $accountId);
            
            return [
                'success' => true,
                'message' => 'General ledger retrieved successfully',
                'data' => $ledger
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get general ledger: ' . $e->getMessage()
            ];
        }
    }

    public function getAccountBalance($tenantId, $branchId, $accountId, $asOfDate)
    {
        try {
            $balance = $this->repository->getAccountBalance($tenantId, $branchId, $accountId, $asOfDate);
            
            return [
                'success' => true,
                'message' => 'Account balance retrieved successfully',
                'data' => $balance
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get account balance: ' . $e->getMessage()
            ];
        }
    }

    public function getCashFlowStatement($tenantId, $branchId, $startDate, $endDate)
    {
        try {
            $cashFlow = $this->repository->getCashFlowStatement($tenantId, $branchId, $startDate, $endDate);
            
            return [
                'success' => true,
                'message' => 'Cash flow statement retrieved successfully',
                'data' => $cashFlow
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get cash flow statement: ' . $e->getMessage()
            ];
        }
    }

    public function postToGeneralLedger($journalEntryId, $tenantId, $branchId, $userId)
    {
        try {
            // Get journal entry details
            $journalEntry = $this->repository->getJournalEntry($journalEntryId);
            
            if (!$journalEntry) {
                return [
                    'success' => false,
                    'message' => 'Journal entry not found'
                ];
            }

            // Get journal lines
            $journalLines = $this->repository->getJournalLines($journalEntryId);
            
            // Post each line to general ledger
            foreach ($journalLines as $line) {
                $this->repository->createGeneralLedgerEntry([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'journal_entry_id' => $journalEntryId,
                    'journal_line_id' => $line['journal_line_id'],
                    'account_id' => $line['account_id'],
                    'transaction_date' => $journalEntry['journal_date'],
                    'reference_type' => $journalEntry['reference_type'],
                    'reference_id' => $journalEntry['reference_id'],
                    'description' => $line['description'] ?? $journalEntry['description'],
                    'debit_amount' => $line['debit'],
                    'credit_amount' => $line['credit']
                ]);
            }

            return [
                'success' => true,
                'message' => 'Journal entry posted to general ledger successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to post to general ledger: ' . $e->getMessage()
            ];
        }
    }
}
