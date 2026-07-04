<?php

if (!class_exists('CreditRepository')) {
    require_once __DIR__ . '/../Repositories/CreditRepository.php';
}


class CreditService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new CreditRepository();
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createCredit($data, $tenantId, $branchId)
    {
        try {
            if (empty($data['customer_id']) || empty($data['credit_amount']) || empty($data['credit_type'])) {
                return [
                    'success' => false,
                    'message' => 'Customer ID, credit amount, and credit type are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $branchId;
            $creditId = $this->repository->createCredit($data);

            return [
                'success' => true,
                'message' => 'Credit created successfully',
                'credit_id' => $creditId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create credit: ' . $e->getMessage()
            ];
        }
    }

    public function payCredit($creditId, $amount, $tenantId)
    {
        try {
            $credit = $this->repository->getCredit($creditId, $tenantId);
            
            if (!$credit) {
                return [
                    'success' => false,
                    'message' => 'Credit not found'
                ];
            }

            if ($credit['status'] !== 'ACTIVE') {
                return [
                    'success' => false,
                    'message' => 'Credit is not active'
                ];
            }

            if ($amount > $credit['credit_amount']) {
                return [
                    'success' => false,
                    'message' => 'Payment amount exceeds credit amount'
                ];
            }

            $this->repository->updateCreditStatus($creditId, 'PAID');

            return [
                'success' => true,
                'message' => 'Credit paid successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to pay credit: ' . $e->getMessage()
            ];
        }
    }

    public function getCustomerCredits($tenantId, $branchId, $customerId)
    {
        try {
            $credits = $this->repository->getCustomerCredits($tenantId, $branchId, $customerId);
            
            return [
                'success' => true,
                'message' => 'Credits retrieved successfully',
                'data' => $credits
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get credits: ' . $e->getMessage()
            ];
        }
    }

    public function getOverdueCredits($tenantId, $branchId)
    {
        try {
            $credits = $this->repository->getOverdueCredits($tenantId, $branchId);
            
            return [
                'success' => true,
                'message' => 'Overdue credits retrieved successfully',
                'data' => $credits
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get overdue credits: ' . $e->getMessage()
            ];
        }
    }
}
