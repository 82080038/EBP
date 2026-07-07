<?php

namespace Modules\Franchise\Repositories;

use Core\Database;

class FranchiseRepository
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->connect();
    }
    
    /**
     * Find all franchisees for tenant
     */
    public function findAll($tenantId, $limit = 100, $offset = 0)
    {
        $sql = "SELECT f.*, u.username as assigned_manager_name
                FROM franchisees f
                LEFT JOIN users u ON f.assigned_manager = u.user_id
                WHERE f.tenant_id = :tenant_id 
                AND f.deleted_at IS NULL
                ORDER BY f.franchisee_name ASC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'limit' => $limit, 'offset' => $offset]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Find franchisee by ID
     */
    public function findById($franchiseeId, $tenantId)
    {
        $sql = "SELECT f.*, u.username as assigned_manager_name
                FROM franchisees f
                LEFT JOIN users u ON f.assigned_manager = u.user_id
                WHERE f.id = :franchisee_id 
                AND f.tenant_id = :tenant_id 
                AND f.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['franchisee_id' => $franchiseeId, 'tenant_id' => $tenantId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Find franchisee by code
     */
    public function findByCode($franchiseeCode, $tenantId)
    {
        $sql = "SELECT f.* 
                FROM franchisees f
                WHERE f.franchisee_code = :franchisee_code 
                AND f.tenant_id = :tenant_id 
                AND f.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['franchisee_code' => $franchiseeCode, 'tenant_id' => $tenantId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Create franchisee
     */
    public function create($data)
    {
        $sql = "INSERT INTO franchisees (tenant_id, franchisee_name, franchisee_code, contact_person, 
                                       email, phone, address, city, state, country, postal_code,
                                       business_name, tax_id, business_license, franchisee_status,
                                       notes, assigned_manager, created_by, created_at)
                VALUES (:tenant_id, :franchisee_name, :franchisee_code, :contact_person,
                        :email, :phone, :address, :city, :state, :country, :postal_code,
                        :business_name, :tax_id, :business_license, :franchisee_status,
                        :notes, :assigned_manager, :created_by, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update franchisee
     */
    public function update($franchiseeId, $data, $tenantId)
    {
        $setClause = [];
        foreach (array_keys($data) as $key) {
            if ($key !== 'id') {
                $setClause[] = "$key = :$key";
            }
        }
        $setClause[] = "updated_at = NOW()";
        
        $sql = "UPDATE franchisees SET " . implode(', ', $setClause) . " 
                WHERE id = :franchisee_id AND tenant_id = :tenant_id";
        
        $params = array_merge($data, ['franchisee_id' => $franchiseeId, 'tenant_id' => $tenantId]);
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Update franchisee status
     */
    public function updateStatus($franchiseeId, $status, $tenantId)
    {
        $sql = "UPDATE franchisees 
                SET franchisee_status = :status, updated_at = NOW() 
                WHERE id = :franchisee_id AND tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['franchisee_id' => $franchiseeId, 'status' => $status, 'tenant_id' => $tenantId]);
    }
    
    /**
     * Soft delete franchisee
     */
    public function delete($franchiseeId, $tenantId)
    {
        $sql = "UPDATE franchisees 
                SET deleted_at = NOW() 
                WHERE id = :franchisee_id AND tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['franchisee_id' => $franchiseeId, 'tenant_id' => $tenantId]);
    }
    
    /**
     * Get franchisees by status
     */
    public function getByStatus($tenantId, $status, $limit = 100)
    {
        $sql = "SELECT f.*, u.username as assigned_manager_name
                FROM franchisees f
                LEFT JOIN users u ON f.assigned_manager = u.user_id
                WHERE f.tenant_id = :tenant_id 
                AND f.franchisee_status = :status
                AND f.deleted_at IS NULL
                ORDER BY f.franchisee_name ASC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'status' => $status, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get active franchisees
     */
    public function getActive($tenantId)
    {
        $sql = "SELECT f.*, u.username as assigned_manager_name
                FROM franchisees f
                LEFT JOIN users u ON f.assigned_manager = u.user_id
                WHERE f.tenant_id = :tenant_id 
                AND f.franchisee_status = 'active'
                AND f.deleted_at IS NULL
                ORDER BY f.franchisee_name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Count franchisees by status
     */
    public function countByStatus($tenantId, $status = null)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM franchisees 
                WHERE tenant_id = :tenant_id 
                AND deleted_at IS NULL";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($status !== null) {
            $sql .= " AND franchisee_status = :status";
            $params['status'] = $status;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    /**
     * Get franchise agreements
     */
    public function getAgreements($franchiseeId, $tenantId)
    {
        $sql = "SELECT * 
                FROM franchise_agreements
                WHERE franchisee_id = :franchisee_id
                AND tenant_id = :tenant_id
                AND deleted_at IS NULL
                ORDER BY start_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['franchisee_id' => $franchiseeId, 'tenant_id' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get franchise performance
     */
    public function getPerformance($franchiseeId, $tenantId, $limit = 12)
    {
        $sql = "SELECT * 
                FROM franchise_performance
                WHERE franchisee_id = :franchisee_id
                AND tenant_id = :tenant_id
                AND deleted_at IS NULL
                ORDER BY period_end DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['franchisee_id' => $franchiseeId, 'tenant_id' => $tenantId, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get franchise royalties
     */
    public function getRoyalties($franchiseeId, $tenantId, $limit = 12)
    {
        $sql = "SELECT * 
                FROM franchise_royalties
                WHERE franchisee_id = :franchisee_id
                AND tenant_id = :tenant_id
                AND deleted_at IS NULL
                ORDER BY due_date DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['franchisee_id' => $franchiseeId, 'tenant_id' => $tenantId, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
