<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

if (!class_exists('TenantService')) {
    require_once __DIR__ . '/../Services/TenantService.php';
}

class TenantController {
    private $tenantService;

    public function __construct() {
        $this->tenantService = new TenantService();
    }

    public function register($request) {
        try {
            $data = $request['body'];
            
            // Validate required fields
            $required = ['tenantName', 'tenantCode', 'companyName', 'branchName', 'restaurantType', 'tableCount', 'adminUsername', 'adminEmail', 'adminPassword', 'adminFullName'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return Response::error("Field '$field' is required", 400);
                }
            }

            // Prepare tenant data
            $tenantData = [
                'tenant_code' => $data['tenantCode'],
                'tenant_name' => $data['tenantName'],
                'business_type' => $data['restaurantType'],
                'status' => 'ACTIVE'
            ];

            // Prepare company data
            $companyData = [
                'company_code' => $data['tenantCode'],
                'company_name' => $data['companyName'],
                'address' => $data['address'] ?? '',
                'phone' => $data['phone'] ?? '',
                'logo_url' => $data['logoUrl'] ?? null,
                'tax_id' => $data['taxId'] ?? null,
                'currency_code' => $data['currencyCode'] ?? 'IDR',
                'time_zone' => $data['timeZone'] ?? 'Asia/Jakarta',
                'status' => 'ACTIVE'
            ];

            // Prepare branch data
            $branchData = [
                'branch_code' => 'MAIN',
                'branch_name' => $data['branchName'],
                'address' => $data['address'] ?? '',
                'phone' => $data['phone'] ?? '',
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'delivery_radius_km' => $data['deliveryRadiusKm'] ?? 5,
                'image_url' => $data['branchImageUrl'] ?? null,
                'operating_hours' => $data['operatingHours'] ?? null,
                'tax_rate' => $data['taxRate'] ?? 0,
                'service_charge' => $data['serviceCharge'] ?? 0,
                'tip_config' => $data['tipConfig'] ?? 'OPTIONAL',
                'delivery_fee' => $data['deliveryFee'] ?? 0,
                'minimum_order_amount' => $data['minimumOrderAmount'] ?? 0,
                'free_delivery_threshold' => $data['freeDeliveryThreshold'] ?? 0,
                'is_main' => true,
                'status' => 'ACTIVE'
            ];

            // Prepare admin user data
            $userData = [
                'username' => $data['adminUsername'],
                'email' => $data['adminEmail'],
                'password' => $data['adminPassword'],
                'full_name' => $data['adminFullName'],
                'status' => 'ACTIVE'
            ];

            // Additional roles
            $additionalRoles = $data['additionalRoles'] ?? [];

            // Table configuration
            $tableConfig = [
                'table_count' => $data['tableCount'],
                'has_reservations' => ($data['hasReservations'] ?? 'yes') === 'yes',
                'has_kitchen' => ($data['hasKitchen'] ?? 'yes') === 'yes'
            ];

            // Register tenant
            $result = $this->tenantService->registerTenant($tenantData, $companyData, $branchData, $userData, $additionalRoles, $tableConfig);

            if ($result['success']) {
                return Response::success($result['message'], 'Tenant registered successfully');
            } else {
                return Response::error($result['message'], 400);
            }

        } catch (Exception $e) {
            return Response::error('Registration failed: ' . $e->getMessage(), 500);
        }
    }

    public function getTenants($request) {
        try {
            $result = $this->tenantService->getAllTenants();
            return Response::success($result['data'], $result['message']);
        } catch (Exception $e) {
            return Response::error('Failed to get tenants: ' . $e->getMessage(), 500);
        }
    }

    public function getTenant($request) {
        try {
            $tenantId = $request['params']['id'] ?? null;
            if (!$tenantId) {
                return Response::error('Tenant ID is required', 400);
            }

            $result = $this->tenantService->getTenantById($tenantId);
            return Response::success($result['data'], $result['message']);
        } catch (Exception $e) {
            return Response::error('Failed to get tenant: ' . $e->getMessage(), 500);
        }
    }

    public function configure($request) {
        try {
            $data = $request['body'];
            $userId = $request['user_id'] ?? null;

            if (!$userId) {
                return Response::error('User not authenticated', 401);
            }

            $result = $this->tenantService->configureTenant($userId, $data);

            if ($result['success']) {
                return Response::success($result['message'], 'Configuration saved successfully');
            } else {
                return Response::error($result['message'], 400);
            }
        } catch (Exception $e) {
            return Response::error('Configuration failed: ' . $e->getMessage(), 500);
        }
    }
}
