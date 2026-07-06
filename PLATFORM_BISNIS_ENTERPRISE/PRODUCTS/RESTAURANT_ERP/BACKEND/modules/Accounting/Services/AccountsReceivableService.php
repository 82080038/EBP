<?php

if (!class_exists('AccountsReceivableRepository')) {
    require_once __DIR__ . '/../Repositories/AccountsReceivableRepository.php';
}

if (!class_exists('Audit')) {
    require_once __DIR__ . '/../../../core/Audit.php';
}

class AccountsReceivableService
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
        $this->repository = new AccountsReceivableRepository($this->db);
    }

    public function createInvoice($data, $tenantId, $branchId, $userId)
    {
        try {
            if (empty($data['invoice_date']) || empty($data['amount'])) {
                return [
                    'success' => false,
                    'message' => 'Invoice date and amount are required'
                ];
            }

            $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            $invoiceData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'customer_id' => $data['customer_id'] ?? null,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? date('Y-m-d', strtotime('+30 days')),
                'amount' => $data['amount'],
                'paid_amount' => 0,
                'balance_amount' => $data['amount'],
                'status' => 'PENDING',
                'description' => $data['description'] ?? null
            ];

            $arId = $this->repository->createInvoice($invoiceData);

            // Log audit trail
            // // $this->audit->log();

            return [
                'success' => true,
                'message' => 'Invoice created successfully',
                'ar_id' => $arId,
                'invoice_number' => $invoiceNumber
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create invoice: ' . $e->getMessage()
            ];
        }
    }

    public function getInvoices($tenantId, $branchId, $status = null, $customerId = null)
    {
        try {
            $invoices = $this->repository->getInvoices($tenantId, $branchId, $status, $customerId);
            
            return [
                'success' => true,
                'message' => 'Invoices retrieved successfully',
                'data' => $invoices
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get invoices: ' . $e->getMessage()
            ];
        }
    }

    public function getInvoice($tenantId, $branchId, $arId)
    {
        try {
            $invoice = $this->repository->getInvoice($tenantId, $branchId, $arId);
            
            if (!$invoice) {
                return [
                    'success' => false,
                    'message' => 'Invoice not found'
                ];
            }

            // Get payments
            $payments = $this->repository->getInvoicePayments($arId);
            $invoice['payments'] = $payments;

            return [
                'success' => true,
                'message' => 'Invoice retrieved successfully',
                'data' => $invoice
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get invoice: ' . $e->getMessage()
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

            // Get invoice
            $invoice = $this->repository->getInvoice($tenantId, $branchId, $data['ar_id']);
            
            if (!$invoice) {
                return [
                    'success' => false,
                    'message' => 'Invoice not found'
                ];
            }

            // Create payment
            $paymentData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'ar_id' => $data['ar_id'],
                'payment_date' => $data['payment_date'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? $data['description'] ?? null
            ];

            $paymentId = $this->repository->addPayment($paymentData);

            // Update invoice
            $newPaidAmount = $invoice['paid_amount'] + $data['amount'];
            $newBalance = $invoice['balance_amount'] - $data['amount'];
            $newStatus = $newBalance <= 0 ? 'PAID' : ($newPaidAmount > 0 ? 'PARTIAL' : 'PENDING');

            $this->repository->updateInvoice($data['ar_id'], [
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
