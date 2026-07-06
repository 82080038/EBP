<?php

if (!class_exists('IntegrationRepository')) {
    require_once __DIR__ . '/../Repositories/IntegrationRepository.php';
}


class IntegrationService
{
    public $repository;
    private $db;

    public function __construct()
    {
        $this->repository = new IntegrationRepository();
                
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function saveIntegrationSettings($tenantId, $branchId, $integrationType, $settings)
    {
        try {
            foreach ($settings as $key => $value) {
                $isEncrypted = in_array($key, ['api_key', 'api_secret', 'client_secret', 'access_token']);
                
                $this->repository->upsertSetting([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'integration_type' => $integrationType,
                    'setting_key' => $key,
                    'setting_value' => $isEncrypted ? $this->encrypt($value) : $value,
                    'is_encrypted' => $isEncrypted ? 1 : 0
                ]);
            }

            return [
                'success' => true,
                'message' => 'Integration settings saved successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to save settings: ' . $e->getMessage()
            ];
        }
    }

    public function getIntegrationSettings($tenantId, $branchId, $integrationType)
    {
        try {
            $settings = $this->repository->getSettings($tenantId, $branchId, $integrationType);
            
            $decryptedSettings = [];
            foreach ($settings as $setting) {
                $value = $setting['is_encrypted'] ? $this->decrypt($setting['setting_value']) : $setting['setting_value'];
                $decryptedSettings[$setting['setting_key']] = $value;
            }

            return [
                'success' => true,
                'message' => 'Settings retrieved successfully',
                'data' => $decryptedSettings
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get settings: ' . $e->getMessage()
            ];
        }
    }

    public function testConnection($tenantId, $branchId, $integrationType)
    {
        try {
            $settings = $this->getIntegrationSettings($tenantId, $branchId, $integrationType);
            
            if (!$settings['success']) {
                return [
                    'success' => false,
                    'message' => 'Failed to get settings'
                ];
            }

            // Simulate connection test based on integration type
            $connectionResult = $this->simulateConnectionTest($integrationType, $settings['data']);

            // Log the test
            $this->repository->logIntegration([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'integration_type' => $integrationType,
                'action' => 'CONNECTION_TEST',
                'request_payload' => json_encode($settings['data']),
                'response_payload' => json_encode($connectionResult),
                'status' => $connectionResult['success'] ? 'SUCCESS' : 'ERROR',
                'error_message' => $connectionResult['success'] ? null : $connectionResult['message']
            ]);

            return $connectionResult;

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ];
        }
    }

    public function syncOrder($tenantId, $branchId, $integrationType, $externalOrderId)
    {
        try {
            $settings = $this->getIntegrationSettings($tenantId, $branchId, $integrationType);
            
            if (!$settings['success']) {
                return [
                    'success' => false,
                    'message' => 'Failed to get settings'
                ];
            }

            // Simulate order sync
            $syncResult = $this->simulateOrderSync($integrationType, $externalOrderId, $settings['data']);

            $this->repository->logIntegration([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'integration_type' => $integrationType,
                'action' => 'SYNC_ORDER',
                'request_payload' => json_encode(['external_order_id' => $externalOrderId]),
                'response_payload' => json_encode($syncResult),
                'status' => $syncResult['success'] ? 'SUCCESS' : 'ERROR',
                'error_message' => $syncResult['success'] ? null : $syncResult['message'],
                'external_id' => $externalOrderId
            ]);

            return $syncResult;

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Order sync failed: ' . $e->getMessage()
            ];
        }
    }

    private function encrypt($data)
    {
        // Simple encryption - in production use proper encryption
        return base64_encode($data);
    }

    private function decrypt($data)
    {
        return base64_decode($data);
    }

    private function simulateConnectionTest($integrationType, $settings)
    {
        // Simulate connection test - in production, make actual API calls
        if (empty($settings['api_key']) && empty($settings['client_id'])) {
            return [
                'success' => false,
                'message' => 'Missing API credentials'
            ];
        }

        return [
            'success' => true,
            'message' => 'Connection successful',
            'data' => [
                'integration' => $integrationType,
                'status' => 'connected',
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];
    }

    private function simulateOrderSync($integrationType, $externalOrderId, $settings)
    {
        // Simulate order sync - in production, make actual API calls
        return [
            'success' => true,
            'message' => 'Order synced successfully',
            'data' => [
                'external_order_id' => $externalOrderId,
                'internal_order_id' => 'ORD-' . rand(10000, 99999),
                'status' => 'synced'
            ]
        ];
    }
}
