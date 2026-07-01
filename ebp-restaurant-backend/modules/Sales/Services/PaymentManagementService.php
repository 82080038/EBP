<?php

require_once __DIR__ . '/../Repositories/PaymentManagementRepository.php';
require_once __DIR__ . '/../../../config/database.php';

class PaymentManagementService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new PaymentManagementRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createCreditNote($data, $tenantId)
    {
        try {
            if (empty($data['credit_note_number']) || empty($data['total_amount'])) {
                return [
                    'success' => false,
                    'message' => 'Credit note number and total amount are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $data['remaining_amount'] = $data['total_amount'];
            $creditNoteId = $this->repository->createCreditNote($data);

            // Create installments if provided
            if (!empty($data['installments'])) {
                foreach ($data['installments'] as $installment) {
                    $installment['credit_note_id'] = $creditNoteId;
                    $this->repository->createInstallment($installment);
                }
            }

            return [
                'success' => true,
                'message' => 'Credit note created successfully',
                'credit_note_id' => $creditNoteId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create credit note: ' . $e->getMessage()
            ];
        }
    }

    public function createVoucher($data, $tenantId)
    {
        try {
            if (empty($data['voucher_code']) || empty($data['voucher_name']) || empty($data['voucher_type'])) {
                return [
                    'success' => false,
                    'message' => 'Voucher code, name, and type are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $voucherId = $this->repository->createVoucher($data);

            return [
                'success' => true,
                'message' => 'Voucher created successfully',
                'voucher_id' => $voucherId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create voucher: ' . $e->getMessage()
            ];
        }
    }

    public function applyVoucher($voucherCode, $orderAmount, $tenantId)
    {
        try {
            $voucher = $this->repository->getVoucherByCode($voucherCode, $tenantId);
            
            if (!$voucher) {
                return [
                    'success' => false,
                    'message' => 'Voucher not found'
                ];
            }

            if ($voucher['status'] !== 'ACTIVE') {
                return [
                    'success' => false,
                    'message' => 'Voucher is not active'
                ];
            }

            $today = date('Y-m-d');
            if ($voucher['valid_from'] > $today || $voucher['valid_until'] < $today) {
                return [
                    'success' => false,
                    'message' => 'Voucher is expired or not yet valid'
                ];
            }

            if ($orderAmount < $voucher['min_purchase_amount']) {
                return [
                    'success' => false,
                    'message' => 'Minimum purchase amount not met'
                ];
            }

            if ($voucher['usage_limit'] && $voucher['usage_count'] >= $voucher['usage_limit']) {
                return [
                    'success' => false,
                    'message' => 'Voucher usage limit reached'
                ];
            }

            // Calculate discount
            $discount = 0;
            if ($voucher['voucher_type'] === 'PERCENTAGE') {
                $discount = $orderAmount * ($voucher['discount_value'] / 100);
                if ($voucher['max_discount'] && $discount > $voucher['max_discount']) {
                    $discount = $voucher['max_discount'];
                }
            } elseif ($voucher['voucher_type'] === 'FIXED_AMOUNT') {
                $discount = $voucher['discount_value'];
            }

            return [
                'success' => true,
                'message' => 'Voucher applied successfully',
                'data' => [
                    'voucher_id' => $voucher['voucher_id'],
                    'discount' => $discount,
                    'final_amount' => $orderAmount - $discount
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to apply voucher: ' . $e->getMessage()
            ];
        }
    }

    public function openCashDrawer($drawerId, $openingBalance, $userId, $tenantId)
    {
        try {
            $drawer = $this->repository->getCashDrawer($drawerId, $tenantId);
            
            if (!$drawer) {
                return [
                    'success' => false,
                    'message' => 'Cash drawer not found'
                ];
            }

            if ($drawer['status'] === 'OPEN') {
                return [
                    'success' => false,
                    'message' => 'Cash drawer is already open'
                ];
            }

            $this->repository->updateCashDrawer($drawerId, [
                'status' => 'OPEN',
                'opening_balance' => $openingBalance,
                'current_balance' => $openingBalance,
                'assigned_user_id' => $userId,
                'opened_at' => date('Y-m-d H:i:s')
            ]);

            // Record opening transaction
            $this->repository->createDrawerTransaction([
                'drawer_id' => $drawerId,
                'transaction_type' => 'OPENING',
                'amount' => $openingBalance,
                'notes' => 'Drawer opened'
            ]);

            return [
                'success' => true,
                'message' => 'Cash drawer opened successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to open cash drawer: ' . $e->getMessage()
            ];
        }
    }

    public function closeCashDrawer($drawerId, $expectedAmount, $actualAmount, $userId, $tenantId)
    {
        try {
            $drawer = $this->repository->getCashDrawer($drawerId, $tenantId);
            
            if (!$drawer) {
                return [
                    'success' => false,
                    'message' => 'Cash drawer not found'
                ];
            }

            if ($drawer['status'] !== 'OPEN') {
                return [
                    'success' => false,
                    'message' => 'Cash drawer is not open'
                ];
            }

            $discrepancy = $actualAmount - $expectedAmount;

            $this->repository->updateCashDrawer($drawerId, [
                'status' => 'CLOSED',
                'current_balance' => $actualAmount,
                'closed_at' => date('Y-m-d H:i:s')
            ]);

            // Record closing transaction
            $this->repository->createDrawerTransaction([
                'drawer_id' => $drawerId,
                'transaction_type' => 'CLOSING',
                'amount' => $actualAmount,
                'notes' => "Drawer closed. Discrepancy: " . ($discrepancy >= 0 ? '+' : '') . $discrepancy
            ]);

            return [
                'success' => true,
                'message' => 'Cash drawer closed successfully',
                'data' => [
                    'expected_amount' => $expectedAmount,
                    'actual_amount' => $actualAmount,
                    'discrepancy' => $discrepancy
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to close cash drawer: ' . $e->getMessage()
            ];
        }
    }

    public function applyRounding($amount, $tenantId)
    {
        try {
            $paymentMethod = $this->repository->getPaymentMethodByType('CASH', $tenantId);
            
            if (!$paymentMethod || $paymentMethod['rounding_rule'] === 'NONE') {
                return $amount;
            }

            $roundingAmount = $paymentMethod['rounding_amount'] ?: 100;
            $remainder = $amount % $roundingAmount;

            switch ($paymentMethod['rounding_rule']) {
                case 'UP':
                    $roundedAmount = $amount + ($roundingAmount - $remainder);
                    break;
                case 'DOWN':
                    $roundedAmount = $amount - $remainder;
                    break;
                case 'NEAREST':
                    $roundedAmount = $remainder >= ($roundingAmount / 2) 
                        ? $amount + ($roundingAmount - $remainder) 
                        : $amount - $remainder;
                    break;
                default:
                    $roundedAmount = $amount;
            }

            return $roundedAmount;

        } catch (Exception $e) {
            return $amount;
        }
    }
}
