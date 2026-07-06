<?php

if (!class_exists('BonusRepository')) {
    require_once __DIR__ . '/../Repositories/BonusRepository.php';
}


class BonusService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new BonusRepository();
                
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function createBonus($data, $tenantId, $branchId, $userId)
    {
        try {
            if (empty($data['employee_id']) || empty($data['bonus_amount']) || empty($data['bonus_type'])) {
                return [
                    'success' => false,
                    'message' => 'Employee ID, bonus amount, and bonus type are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $branchId;
            $bonusId = $this->repository->createBonus($data);

            return [
                'success' => true,
                'message' => 'Bonus created successfully',
                'bonus_id' => $bonusId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create bonus: ' . $e->getMessage()
            ];
        }
    }

    public function approveBonus($bonusId, $tenantId, $userId)
    {
        try {
            $this->repository->updateBonusStatus($bonusId, 'APPROVED', $userId);
            
            return [
                'success' => true,
                'message' => 'Bonus approved successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to approve bonus: ' . $e->getMessage()
            ];
        }
    }

    public function payBonus($bonusId, $tenantId)
    {
        try {
            $this->repository->updateBonusStatus($bonusId, 'PAID', null);
            
            return [
                'success' => true,
                'message' => 'Bonus paid successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to pay bonus: ' . $e->getMessage()
            ];
        }
    }

    public function getEmployeeBonuses($tenantId, $branchId, $employeeId)
    {
        try {
            $bonuses = $this->repository->getEmployeeBonuses($tenantId, $branchId, $employeeId);
            
            return [
                'success' => true,
                'message' => 'Bonuses retrieved successfully',
                'data' => $bonuses
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get bonuses: ' . $e->getMessage()
            ];
        }
    }

    public function getPendingBonuses($tenantId, $branchId)
    {
        try {
            $bonuses = $this->repository->getPendingBonuses($tenantId, $branchId);
            
            return [
                'success' => true,
                'message' => 'Pending bonuses retrieved successfully',
                'data' => $bonuses
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get pending bonuses: ' . $e->getMessage()
            ];
        }
    }
}
