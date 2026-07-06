<?php

if (!class_exists('QualityComplianceRepository')) {
    require_once __DIR__ . '/../Repositories/QualityComplianceRepository.php';
}


class QualityComplianceService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new QualityComplianceRepository();
                
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function createComplianceCheck($data, $tenantId, $branchId, $userId)
    {
        try {
            if (empty($data['check_type']) || empty($data['check_date'])) {
                return [
                    'success' => false,
                    'message' => 'Check type and check date are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $branchId;
            $data['checked_by'] = $userId;
            
            $checkId = $this->repository->createComplianceCheck($data);

            return [
                'success' => true,
                'message' => 'Compliance check created successfully',
                'check_id' => $checkId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create compliance check: ' . $e->getMessage()
            ];
        }
    }

    public function getComplianceReport($tenantId, $branchId, $dateFrom, $dateTo)
    {
        try {
            $report = $this->repository->getComplianceReport($tenantId, $branchId, $dateFrom, $dateTo);
            
            return [
                'success' => true,
                'message' => 'Compliance report retrieved successfully',
                'data' => $report
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get report: ' . $e->getMessage()
            ];
        }
    }

    public function addFoodSafetyProtocol($data, $tenantId, $branchId)
    {
        try {
            if (empty($data['protocol_name']) || empty($data['protocol_type'])) {
                return [
                    'success' => false,
                    'message' => 'Protocol name and type are required'
                ];
            }

            $data['tenant_id'] = $tenantId;
            $data['branch_id'] = $branchId;
            
            $protocolId = $this->repository->createFoodSafetyProtocol($data);

            return [
                'success' => true,
                'message' => 'Food safety protocol added successfully',
                'protocol_id' => $protocolId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to add protocol: ' . $e->getMessage()
            ];
        }
    }

    public function getFoodSafetyProtocols($tenantId, $branchId)
    {
        try {
            $protocols = $this->repository->getFoodSafetyProtocols($tenantId, $branchId);
            
            return [
                'success' => true,
                'message' => 'Food safety protocols retrieved successfully',
                'data' => $protocols
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get protocols: ' . $e->getMessage()
            ];
        }
    }
}
