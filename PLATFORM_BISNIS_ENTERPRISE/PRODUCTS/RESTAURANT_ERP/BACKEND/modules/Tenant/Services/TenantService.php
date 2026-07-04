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
            $stmt = $pdo->prepare("INSERT INTO companies (company_code, company_name, address, phone, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$companyData['company_code'], $companyData['company_name'], $companyData['address'], $companyData['phone'], $companyData['status']]);
            $companyId = $pdo->lastInsertId();
            
            // Insert branch
            $stmt = $pdo->prepare("INSERT INTO branches (branch_code, branch_name, address, phone, is_main, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$branchData['branch_code'], $branchData['branch_name'], $branchData['address'], $branchData['phone'], $branchData['is_main'], $branchData['status']]);
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
}
