<?php

if (!class_exists('AccountsPayableRepository')) {
    require_once __DIR__ . '/../Repositories/AccountsPayableRepository.php';
}

if (!class_exists('Audit')) {
    require_once __DIR__ . '/../../../core/Audit.php';
}

class AccountsPayableService
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
        $this->repository = new AccountsPayableRepository($this->db);
    }

    public function createBill($data, $tenantId, $branchId, $userId)
    {
        try {
            if (empty($data['bill_date']) || empty($data['amount'])) {
                return [
                    'success' => false,
                    'message' => 'Bill date and amount are required'
                ];
            }

            $billNumber = 'BILL-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            $billData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'supplier_id' => $data['supplier_id'] ?? null,
                'bill_number' => $billNumber,
                'bill_date' => $data['bill_date'],
                'due_date' => $data['due_date'] ?? date('Y-m-d', strtotime('+30 days')),
                'amount' => $data['amount'],
                'paid_amount' => 0,
                'balance_amount' => $data['amount'],
                'status' => 'PENDING',
                'description' => $data['description'] ?? null
            ];

            $apId = $this->repository->createBill($billData);

            // Log audit trail
            // // $this->audit->log();

            return [
                'success' => true,
                'message' => 'Bill created successfully',
                'ap_id' => $apId,
                'bill_number' => $billNumber
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create bill: ' . $e->getMessage()
            ];
        }
    }

    public function getBills($tenantId, $branchId, $status = null, $supplierId = null)
    {
        try {
            $bills = $this->repository->getBills($tenantId, $branchId, $status, $supplierId);
            
            return [
                'success' => true,
                'message' => 'Bills retrieved successfully',
                'data' => $bills
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get bills: ' . $e->getMessage()
            ];
        }
    }

    public function getBill($tenantId, $branchId, $apId)
    {
        try {
            $bill = $this->repository->getBill($tenantId, $branchId, $apId);
            
            if (!$bill) {
                return [
                    'success' => false,
                    'message' => 'Bill not found'
                ];
            }

            // Get payments
            $payments = $this->repository->getBillPayments($apId);
            $bill['payments'] = $payments;

            return [
                'success' => true,
                'message' => 'Bill retrieved successfully',
                'data' => $bill
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get bill: ' . $e->getMessage()
            ];
        }
    }

    public function addPayment($data, $tenantId, $branchId, $userId)
    {
        try {
            if (empty($data['payment_date']) || empty($data['amount'])) {
                return [
                    'success' => false,
                    'message' => 'Payment date and amount are required'
                ];
            }

            // Get bill
            $bill = $this->repository->getBill($tenantId, $branchId, $data['ap_id']);
            
            if (!$bill) {
                return [
                    'success' => false,
                    'message' => 'Bill not found'
                ];
            }

            // Create payment
            $paymentData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'ap_id' => $data['ap_id'],
                'payment_date' => $data['payment_date'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null
            ];

            $paymentId = $this->repository->addPayment($paymentData);

            // Update bill
            $newPaidAmount = $bill['paid_amount'] + $data['amount'];
            $newBalance = $bill['balance_amount'] - $data['amount'];
            $newStatus = $newBalance <= 0 ? 'PAID' : ($newPaidAmount > 0 ? 'PARTIAL' : 'PENDING');

            $this->repository->updateBill($data['ap_id'], [
                'paid_amount' => $newPaidAmount,
                'balance_amount' => $newBalance,
                'status' => $newStatus
            ]);

            return [
                'success' => true,
                'message' => 'Payment added successfully',
                'data' => [
                    'payment_id' => $paymentId,
                    'new_balance' => $newBalance,
                    'new_status' => $newStatus
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to add payment: ' . $e->getMessage()
            ];
        }
    }

    public function getAgingReport($tenantId, $branchId)
    {
        try {
            $agingReport = $this->repository->getAgingReport($tenantId, $branchId);
            
            return [
                'success' => true,
                'message' => 'Aging report retrieved successfully',
                'data' => $agingReport
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get aging report: ' . $e->getMessage()
            ];
        }
    }
}
