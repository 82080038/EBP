<?php

if (!class_exists('BankReconciliationRepository')) {
    require_once __DIR__ . '/../Repositories/BankReconciliationRepository.php';
}

class BankReconciliationService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new BankReconciliationRepository();
                
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function createReconciliation($data, $tenantId, $branchId, $userId)
    {
        try {
            if (empty($data['bank_account_id']) || empty($data['reconciliation_date']) || empty($data['statement_balance'])) {
                return [
                    'success' => false,
                    'message' => 'Bank account, reconciliation date, and statement balance are required'
                ];
            }

            // Get current book balance
            $bookBalance = $this->repository->getBookBalance($tenantId, $branchId, $data['bank_account_id']);
            
            $difference = $data['statement_balance'] - $bookBalance;

            $reconciliationData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'bank_account_id' => $data['bank_account_id'],
                'reconciliation_date' => $data['reconciliation_date'],
                'statement_balance' => $data['statement_balance'],
                'book_balance' => $bookBalance,
                'difference' => $difference,
                'status' => 'DRAFT',
                'notes' => $data['notes'] ?? null
            ];

            $reconciliationId = $this->repository->createReconciliation($reconciliationData);

            return [
                'success' => true,
                'message' => 'Bank reconciliation created successfully',
                'reconciliation_id' => $reconciliationId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create bank reconciliation: ' . $e->getMessage()
            ];
        }
    }

    public function getReconciliations($tenantId, $branchId, $bankAccountId = null, $status = null)
    {
        try {
            $reconciliations = $this->repository->getReconciliations($tenantId, $branchId, $bankAccountId, $status);
            
            return [
                'success' => true,
                'message' => 'Bank reconciliations retrieved successfully',
                'data' => $reconciliations
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get bank reconciliations: ' . $e->getMessage()
            ];
        }
    }

    public function getReconciliation($tenantId, $branchId, $reconciliationId)
    {
        try {
            $reconciliation = $this->repository->getReconciliation($tenantId, $branchId, $reconciliationId);
            
            if (!$reconciliation) {
                return [
                    'success' => false,
                    'message' => 'Bank reconciliation not found'
                ];
            }

            // Get reconciliation items
            $items = $this->repository->getReconciliationItems($reconciliationId);
            $reconciliation['items'] = $items;

            return [
                'success' => true,
                'message' => 'Bank reconciliation retrieved successfully',
                'data' => $reconciliation
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get bank reconciliation: ' . $e->getMessage()
            ];
        }
    }

    public function addItem($data, $tenantId, $branchId)
    {
        try {
            if (empty($data['reconciliation_id']) || empty($data['item_type']) || empty($data['amount'])) {
                return [
                    'success' => false,
                    'message' => 'Reconciliation ID, item type, and amount are required'
                ];
            }

            $itemData = [
                'reconciliation_id' => $data['reconciliation_id'],
                'item_type' => $data['item_type'],
                'amount' => $data['amount'],
                'description' => $data['description'] ?? null,
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null
            ];

            $itemId = $this->repository->addItem($itemData);

            // Update reconciliation difference
            $this->updateReconciliationDifference($data['reconciliation_id']);

            return [
                'success' => true,
                'message' => 'Reconciliation item added successfully',
                'item_id' => $itemId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to add reconciliation item: ' . $e->getMessage()
            ];
        }
    }

    public function reconcile($tenantId, $branchId, $reconciliationId, $userId)
    {
        try {
            $reconciliation = $this->repository->getReconciliation($tenantId, $branchId, $reconciliationId);
            
            if (!$reconciliation) {
                return [
                    'success' => false,
                    'message' => 'Bank reconciliation not found'
                ];
            }

            // Check if difference is zero
            if (abs($reconciliation['difference']) > 0.01) {
                return [
                    'success' => false,
                    'message' => 'Cannot reconcile: difference is not zero'
                ];
            }

            $this->repository->updateReconciliation($reconciliationId, [
                'status' => 'RECONCILED',
                'reconciled_by' => $userId,
                'reconciled_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => 'Bank reconciliation completed successfully',
                'data' => [
                    'reconciliation_id' => $reconciliationId,
                    'status' => 'RECONCILED'
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to reconcile: ' . $e->getMessage()
            ];
        }
    }

    public function getBankAccounts($tenantId, $branchId)
    {
        try {
            $bankAccounts = $this->repository->getBankAccounts($tenantId, $branchId);
            
            return [
                'success' => true,
                'message' => 'Bank accounts retrieved successfully',
                'data' => $bankAccounts
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get bank accounts: ' . $e->getMessage()
            ];
        }
    }

    public function createBankAccount($data, $tenantId, $branchId)
    {
        try {
            if (empty($data['account_name']) || empty($data['account_number']) || empty($data['bank_name'])) {
                return [
                    'success' => false,
                    'message' => 'Account name, account number, and bank name are required'
                ];
            }

            $bankAccountData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'account_name' => $data['account_name'],
                'account_number' => $data['account_number'],
                'bank_name' => $data['bank_name'],
                'account_type' => $data['account_type'] ?? 'CHECKING',
                'currency' => $data['currency'] ?? 'IDR',
                'balance' => $data['balance'] ?? 0.00,
                'is_active' => 1
            ];

            $bankAccountId = $this->repository->createBankAccount($bankAccountData);

            return [
                'success' => true,
                'message' => 'Bank account created successfully',
                'bank_account_id' => $bankAccountId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create bank account: ' . $e->getMessage()
            ];
        }
    }

    private function updateReconciliationDifference($reconciliationId)
    {
        $reconciliation = $this->repository->getReconciliationById($reconciliationId);
        $items = $this->repository->getReconciliationItems($reconciliationId);
        
        $totalItems = 0;
        foreach ($items as $item) {
            if ($item['item_type'] === 'DEPOSIT') {
                $totalItems += $item['amount'];
            } else {
                $totalItems -= $item['amount'];
            }
        }

        $newDifference = $reconciliation['statement_balance'] - $reconciliation['book_balance'] + $totalItems;

        $this->repository->updateReconciliation($reconciliationId, [
            'difference' => $newDifference
        ]);
    }
}
