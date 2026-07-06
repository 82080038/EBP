<?php

if (!class_exists('QualityRepository')) {
    require_once __DIR__ . '/../Repositories/QualityRepository.php';
}


class QualityService
{
    private $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new QualityRepository();
                
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function createQualityCheck($data, $tenantId, $branchId, $userId)
    {
        try {
            if (empty($data['check_type']) || empty($data['check_date'])) {
                return [
                    'success' => false,
                    'message' => 'Check type and date are required'
                ];
            }

            $checkData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'check_type' => $data['check_type'],
                'check_date' => $data['check_date'],
                'checked_by' => $userId,
                'check_result' => $data['check_result'] ?? 'PASS',
                'temperature' => $data['temperature'] ?? null,
                'notes' => $data['notes'] ?? null
            ];

            $checkId = $this->repository->createCheck($checkData);

            return [
                'success' => true,
                'message' => 'Quality check created successfully',
                'check_id' => $checkId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create quality check: ' . $e->getMessage()
            ];
        }
    }

    public function createIncident($data, $tenantId, $branchId, $userId)
    {
        try {
            if (empty($data['incident_type']) || empty($data['incident_date'])) {
                return [
                    'success' => false,
                    'message' => 'Incident type and date are required'
                ];
            }

            $incidentData = [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'incident_type' => $data['incident_type'],
                'incident_date' => $data['incident_date'],
                'severity' => $data['severity'] ?? 'MEDIUM',
                'description' => $data['description'],
                'reported_by' => $userId,
                'status' => 'OPEN'
            ];

            $incidentId = $this->repository->createIncident($incidentData);

            return [
                'success' => true,
                'message' => 'Incident created successfully',
                'incident_id' => $incidentId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create incident: ' . $e->getMessage()
            ];
        }
    }

    public function resolveIncident($incidentId, $resolutionNotes, $userId, $tenantId)
    {
        try {
            $this->repository->updateIncident($incidentId, [
                'status' => 'RESOLVED',
                'resolved_at' => date('Y-m-d H:i:s'),
                'resolution_notes' => $resolutionNotes
            ]);

            return [
                'success' => true,
                'message' => 'Incident resolved successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to resolve incident: ' . $e->getMessage()
            ];
        }
    }

    public function getQualityChecks($tenantId, $branchId = null)
    {
        try {
            $checks = $this->repository->getChecksByTenant($tenantId, $branchId);
            
            return [
                'success' => true,
                'message' => 'Quality checks retrieved successfully',
                'data' => $checks
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get quality checks: ' . $e->getMessage()
            ];
        }
    }

    public function getIncidents($tenantId, $branchId = null)
    {
        try {
            $incidents = $this->repository->getIncidentsByTenant($tenantId, $branchId);
            
            return [
                'success' => true,
                'message' => 'Incidents retrieved successfully',
                'data' => $incidents
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get incidents: ' . $e->getMessage()
            ];
        }
    }
}
