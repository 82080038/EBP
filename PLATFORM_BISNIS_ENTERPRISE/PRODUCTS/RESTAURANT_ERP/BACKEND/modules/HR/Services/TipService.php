<?php

if (!class_exists('TipRepository')) {
    require_once __DIR__ . '/../Repositories/TipRepository.php';
}


class TipService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new TipRepository();
                
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function distributeTip($data, $tenantId, $branchId)
    {
        try {
            if (empty($data['order_id']) || empty($data['total_tip_amount']) || empty($data['recipients'])) {
                return [
                    'success' => false,
                    'message' => 'Order ID, total tip amount, and recipients are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $branchId;
            $data['distribution_date'] = date('Y-m-d');
            
            $tipId = $this->repository->createTipDistribution($data);
            
            foreach ($data['recipients'] as $recipient) {
                $this->repository->addTipRecipient([
                    'tip_id' => $tipId,
                    'employee_id' => $recipient['employee_id'],
                    'tip_amount' => $recipient['tip_amount'],
                    'percentage' => $recipient['percentage'] ?? null
                ]);
            }

            return [
                'success' => true,
                'message' => 'Tip distributed successfully',
                'tip_id' => $tipId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to distribute tip: ' . $e->getMessage()
            ];
        }
    }

    public function getTipDistributions($tenantId, $branchId, $date = null)
    {
        try {
            $distributions = $this->repository->getTipDistributions($tenantId, $branchId, $date);
            
            return [
                'success' => true,
                'message' => 'Tip distributions retrieved successfully',
                'data' => $distributions
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get tip distributions: ' . $e->getMessage()
            ];
        }
    }

    public function getEmployeeTips($tenantId, $branchId, $employeeId, $startDate = null, $endDate = null)
    {
        try {
            $tips = $this->repository->getEmployeeTips($tenantId, $branchId, $employeeId, $startDate, $endDate);
            
            return [
                'success' => true,
                'message' => 'Employee tips retrieved successfully',
                'data' => $tips
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get employee tips: ' . $e->getMessage()
            ];
        }
    }
}
