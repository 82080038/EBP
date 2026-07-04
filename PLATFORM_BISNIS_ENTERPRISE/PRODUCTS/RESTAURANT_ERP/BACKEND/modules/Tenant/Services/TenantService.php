<?php

// Load EBP Core and Backend Components
require_once __DIR__ . '/../../../bootstrap.php';

class TenantService {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function registerTenant($tenantData, $companyData, $branchData, $userData, $additionalRoles, $tableConfig) {
        $pdo = $this->db->connect();
        
        try {
            $pdo->beginTransaction();
            
            // Insert tenant
            $stmt = $pdo->prepare("INSERT INTO tenants (tenant_code, tenant_name, business_type, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$tenantData['tenant_code'], $tenantData['tenant_name'], $tenantData['business_type'], $tenantData['status']]);
            $tenantId = $pdo->lastInsertId();
            
            // Insert company
            $stmt = $pdo->prepare("INSERT INTO companies (company_code, company_name, address, phone, logo_url, tax_id, currency_code, time_zone, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $companyData['company_code'],
                $companyData['company_name'],
                $companyData['address'],
                $companyData['phone'],
                $companyData['logo_url'] ?? null,
                $companyData['tax_id'] ?? null,
                $companyData['currency_code'] ?? 'IDR',
                $companyData['time_zone'] ?? 'Asia/Jakarta',
                $companyData['status']
            ]);
            $companyId = $pdo->lastInsertId();

            // Insert branch
            $stmt = $pdo->prepare("INSERT INTO branches (tenant_id, company_id, branch_code, branch_name, address, phone, image_url, operating_hours, tax_rate, service_charge, tip_config, delivery_fee, minimum_order_amount, free_delivery_threshold, latitude, longitude, delivery_radius_km, is_main, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $tenantId,
                $companyId,
                $branchData['branch_code'],
                $branchData['branch_name'],
                $branchData['address'],
                $branchData['phone'],
                $branchData['image_url'] ?? null,
                $branchData['operating_hours'] ?? null,
                $branchData['tax_rate'] ?? 0,
                $branchData['service_charge'] ?? 0,
                $branchData['tip_config'] ?? 'OPTIONAL',
                $branchData['delivery_fee'] ?? 0,
                $branchData['minimum_order_amount'] ?? 0,
                $branchData['free_delivery_threshold'] ?? 0,
                $branchData['latitude'],
                $branchData['longitude'],
                $branchData['delivery_radius_km'],
                $branchData['is_main'],
                $branchData['status']
            ]);
            $branchId = $pdo->lastInsertId();
            
            // Insert user
            $hashedPassword = password_hash($userData['password'], PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, tenant_id, branch_id, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userData['username'], $userData['email'], $hashedPassword, $userData['full_name'], $tenantId, $branchId, $userData['status']]);
            $userId = $pdo->lastInsertId();
            
            // Assign admin role
            $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, (SELECT role_id FROM roles WHERE role_name = 'admin'))");
            $stmt->execute([$userId]);
            
            // Assign additional roles
            foreach ($additionalRoles as $role) {
                $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, (SELECT role_id FROM roles WHERE role_name = ?))");
                $stmt->execute([$userId, $role]);
            }
            
            // Create tables based on configuration
            for ($i = 1; $i <= $tableConfig['table_count']; $i++) {
                $stmt = $pdo->prepare("INSERT INTO tables (table_number, branch_id, status) VALUES (?, ?, 'AVAILABLE')");
                $stmt->execute([$i, $branchId]);
            }
            
            $pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Tenant registered successfully'
            ];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ];
        }
    }

    public function getAllTenants() {
        $pdo = $this->db->connect();
        
        try {
            $stmt = $pdo->query("SELECT * FROM tenants WHERE status = 'ACTIVE'");
            $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $tenants,
                'message' => 'Tenants retrieved successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get tenants: ' . $e->getMessage()
            ];
        }
    }

    public function getTenantById($tenantId) {
        $pdo = $this->db->connect();
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM tenants WHERE tenant_id = ?");
            $stmt->execute([$tenantId]);
            $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$tenant) {
                return [
                    'success' => false,
                    'message' => 'Tenant not found'
                ];
            }
            
            return [
                'success' => true,
                'data' => $tenant,
                'message' => 'Tenant retrieved successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get tenant: ' . $e->getMessage()
            ];
        }
    }

    public function configureTenant($userId, $data) {
        $pdo = $this->db->connect();
        
        try {
            // Get tenant_id from user
            $stmt = $pdo->prepare("SELECT tenant_id FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }
            
            $tenantId = $user['tenant_id'];
            
            $pdo->beginTransaction();
            
            // Save payment methods
            if (isset($data['paymentMethods'])) {
                $stmt = $pdo->prepare("INSERT INTO settings (tenant_id, setting_key, setting_value, setting_type) VALUES (?, ?, ?, 'JSON') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                $stmt->execute([$tenantId, 'payment_methods', json_encode($data['paymentMethods'])]);
            }
            
            // Save split payment
            if (isset($data['splitPayment'])) {
                $stmt = $pdo->prepare("INSERT INTO settings (tenant_id, setting_key, setting_value, setting_type) VALUES (?, ?, ?, 'BOOLEAN') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                $stmt->execute([$tenantId, 'split_payment', $data['splitPayment'] === 'yes' ? 'true' : 'false']);
            }
            
            // Save max discount percent
            if (isset($data['maxDiscountPercent'])) {
                $stmt = $pdo->prepare("INSERT INTO settings (tenant_id, setting_key, setting_value, setting_type) VALUES (?, ?, ?, 'NUMBER') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                $stmt->execute([$tenantId, 'max_discount_percent', $data['maxDiscountPercent']]);
            }
            
            // Save manager override password (hashed)
            if (isset($data['managerOverridePassword']) && !empty($data['managerOverridePassword'])) {
                $hashedPassword = password_hash($data['managerOverridePassword'], PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO settings (tenant_id, setting_key, setting_value, setting_type) VALUES (?, ?, ?, 'STRING') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                $stmt->execute([$tenantId, 'manager_override_password', $hashedPassword]);
            }
            
            // Save allow void
            if (isset($data['allowVoid'])) {
                $stmt = $pdo->prepare("INSERT INTO settings (tenant_id, setting_key, setting_value, setting_type) VALUES (?, ?, ?, 'BOOLEAN') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                $stmt->execute([$tenantId, 'allow_void', $data['allowVoid'] === 'yes' ? 'true' : 'false']);
            }
            
            // Save allow refund
            if (isset($data['allowRefund'])) {
                $stmt = $pdo->prepare("INSERT INTO settings (tenant_id, setting_key, setting_value, setting_type) VALUES (?, ?, ?, 'BOOLEAN') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                $stmt->execute([$tenantId, 'allow_refund', $data['allowRefund'] === 'yes' ? 'true' : 'false']);
            }
            
            // Save receipt header
            if (isset($data['receiptHeader'])) {
                $stmt = $pdo->prepare("INSERT INTO settings (tenant_id, setting_key, setting_value, setting_type) VALUES (?, ?, ?, 'STRING') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                $stmt->execute([$tenantId, 'receipt_header', $data['receiptHeader']]);
            }
            
            // Save receipt footer
            if (isset($data['receiptFooter'])) {
                $stmt = $pdo->prepare("INSERT INTO settings (tenant_id, setting_key, setting_value, setting_type) VALUES (?, ?, ?, 'STRING') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                $stmt->execute([$tenantId, 'receipt_footer', $data['receiptFooter']]);
            }
            
            // Save show customer info
            if (isset($data['showCustomerInfo'])) {
                $stmt = $pdo->prepare("INSERT INTO settings (tenant_id, setting_key, setting_value, setting_type) VALUES (?, ?, ?, 'BOOLEAN') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                $stmt->execute([$tenantId, 'show_customer_info', $data['showCustomerInfo'] === 'yes' ? 'true' : 'false']);
            }
            
            // Save receipt copies
            if (isset($data['receiptCopies'])) {
                $stmt = $pdo->prepare("INSERT INTO settings (tenant_id, setting_key, setting_value, setting_type) VALUES (?, ?, ?, 'NUMBER') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                $stmt->execute([$tenantId, 'receipt_copies', $data['receiptCopies']]);
            }
            
            $pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Configuration saved successfully'
            ];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Configuration failed: ' . $e->getMessage()
            ];
        }
    }
}
