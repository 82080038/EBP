<?php

namespace Modules\Customer\Repositories;

use Core\Database;

class CustomerRepository
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->connect();
    }
    
    /**
     * Find all customers for tenant
     */
    public function findAll($tenantId, $limit = 100, $offset = 0)
    {
        $sql = "SELECT c.*, 
                       COUNT(DISTINCT o.order_id) as total_orders,
                       SUM(o.total_amount) as total_spent,
                       MAX(o.created_at) as last_order_date
                FROM customers c
                LEFT JOIN orders o ON c.customer_id = o.customer_id AND o.status IN ('COMPLETED', 'PAID')
                WHERE c.tenant_id = :tenant_id 
                AND c.deleted_at IS NULL
                GROUP BY c.customer_id
                ORDER BY c.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'limit' => $limit, 'offset' => $offset]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Find customer by ID
     */
    public function findById($customerId, $tenantId)
    {
        $sql = "SELECT c.*, 
                       COUNT(DISTINCT o.order_id) as total_orders,
                       SUM(o.total_amount) as total_spent,
                       MAX(o.created_at) as last_order_date
                FROM customers c
                LEFT JOIN orders o ON c.customer_id = o.customer_id AND o.status IN ('COMPLETED', 'PAID')
                WHERE c.customer_id = :customer_id 
                AND c.tenant_id = :tenant_id 
                AND c.deleted_at IS NULL
                GROUP BY c.customer_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customer_id' => $customerId, 'tenant_id' => $tenantId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Find customer by code
     */
    public function findByCode($customerCode, $tenantId)
    {
        $sql = "SELECT c.* 
                FROM customers c
                WHERE c.customer_code = :customer_code 
                AND c.tenant_id = :tenant_id 
                AND c.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customer_code' => $customerCode, 'tenant_id' => $tenantId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Find customer by phone
     */
    public function findByPhone($phone, $tenantId)
    {
        $sql = "SELECT c.* 
                FROM customers c
                WHERE c.phone = :phone 
                AND c.tenant_id = :tenant_id 
                AND c.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['phone' => $phone, 'tenant_id' => $tenantId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Find customer by email
     */
    public function findByEmail($email, $tenantId)
    {
        $sql = "SELECT c.* 
                FROM customers c
                WHERE c.email = :email 
                AND c.tenant_id = :tenant_id 
                AND c.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email, 'tenant_id' => $tenantId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Create customer
     */
    public function create($data)
    {
        $sql = "INSERT INTO customers (tenant_id, customer_code, name, phone, email, 
                                     address, city, postal_code, notes, status, 
                                     created_by, created_at)
                VALUES (:tenant_id, :customer_code, :name, :phone, :email,
                        :address, :city, :postal_code, :notes, :status,
                        :created_by, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update customer
     */
    public function update($customerId, $data, $tenantId)
    {
        $setClause = [];
        foreach (array_keys($data) as $key) {
            if ($key !== 'customer_id') {
                $setClause[] = "$key = :$key";
            }
        }
        $setClause[] = "updated_at = NOW()";
        
        $sql = "UPDATE customers SET " . implode(', ', $setClause) . " 
                WHERE customer_id = :customer_id AND tenant_id = :tenant_id";
        
        $params = array_merge($data, ['customer_id' => $customerId, 'tenant_id' => $tenantId]);
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Soft delete customer
     */
    public function delete($customerId, $tenantId)
    {
        $sql = "UPDATE customers 
                SET deleted_at = NOW() 
                WHERE customer_id = :customer_id AND tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['customer_id' => $customerId, 'tenant_id' => $tenantId]);
    }
    
    /**
     * Search customers
     */
    public function search($tenantId, $query, $limit = 50)
    {
        $sql = "SELECT c.* 
                FROM customers c
                WHERE c.tenant_id = :tenant_id 
                AND c.deleted_at IS NULL
                AND (c.name LIKE :query OR c.phone LIKE :query OR c.email LIKE :query OR c.customer_code LIKE :query)
                ORDER BY c.name ASC
                LIMIT :limit";
        
        $searchQuery = "%$query%";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'query' => $searchQuery, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get customer orders
     */
    public function getCustomerOrders($customerId, $tenantId, $limit = 50)
    {
        $sql = "SELECT o.*, t.table_number
                FROM orders o
                LEFT JOIN tables t ON o.table_id = t.table_id
                WHERE o.customer_id = :customer_id 
                AND o.tenant_id = :tenant_id 
                AND o.deleted_at IS NULL
                ORDER BY o.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customer_id' => $customerId, 'tenant_id' => $tenantId, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get customer visits
     */
    public function getCustomerVisits($customerId, $tenantId, $limit = 50)
    {
        $sql = "SELECT cv.*, t.table_number
                FROM customer_visits cv
                LEFT JOIN tables t ON cv.table_id = t.table_id
                WHERE cv.customer_id = :customer_id 
                AND cv.tenant_id = :tenant_id 
                ORDER BY cv.visit_date DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customer_id' => $customerId, 'tenant_id' => $tenantId, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get customer preferences
     */
    public function getCustomerPreferences($customerId, $tenantId)
    {
        $sql = "SELECT cp.* 
                FROM customer_preferences cp
                WHERE cp.customer_id = :customer_id 
                AND cp.tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customer_id' => $customerId, 'tenant_id' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get customer tags
     */
    public function getCustomerTags($customerId, $tenantId)
    {
        $sql = "SELECT ct.*, t.tag_name, t.tag_color
                FROM customer_tags ct
                INNER JOIN tags t ON ct.tag_id = t.tag_id
                WHERE ct.customer_id = :customer_id 
                AND ct.tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customer_id' => $customerId, 'tenant_id' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get top customers by spending
     */
    public function getTopCustomers($tenantId, $limit = 10, $startDate = null, $endDate = null)
    {
        $sql = "SELECT c.*, 
                       COUNT(DISTINCT o.order_id) as total_orders,
                       SUM(o.total_amount) as total_spent
                FROM customers c
                INNER JOIN orders o ON c.customer_id = o.customer_id
                WHERE c.tenant_id = :tenant_id 
                AND c.deleted_at IS NULL
                AND o.status IN ('COMPLETED', 'PAID')";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($startDate && $endDate) {
            $sql .= " AND o.created_at BETWEEN :start_date AND :end_date";
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        }
        
        $sql .= " GROUP BY c.customer_id
                  ORDER BY total_spent DESC
                  LIMIT :limit";
        
        $params['limit'] = $limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get customer count by status
     */
    public function getCountByStatus($tenantId, $status = null)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM customers 
                WHERE tenant_id = :tenant_id 
                AND deleted_at IS NULL";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($status !== null) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'];
    }
}
