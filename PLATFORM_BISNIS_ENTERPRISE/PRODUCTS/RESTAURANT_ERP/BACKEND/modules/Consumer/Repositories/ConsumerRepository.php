<?php

namespace Modules\Consumer\Repositories;

use Core\Database;

class ConsumerRepository
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->connect();
    }
    
    /**
     * Find consumer by ID
     */
    public function findById($consumerId, $tenantId)
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
        $stmt->execute(['customer_id' => $consumerId, 'tenant_id' => $tenantId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Find consumer by phone
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
     * Find consumer by email
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
     * Create consumer
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
     * Update consumer
     */
    public function update($consumerId, $data, $tenantId)
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
        
        $params = array_merge($data, ['customer_id' => $consumerId, 'tenant_id' => $tenantId]);
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Soft delete consumer
     */
    public function delete($consumerId, $tenantId)
    {
        $sql = "UPDATE customers 
                SET deleted_at = NOW() 
                WHERE customer_id = :customer_id AND tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['customer_id' => $consumerId, 'tenant_id' => $tenantId]);
    }
    
    /**
     * Get all consumers for tenant
     */
    public function findAll($tenantId, $limit = 100, $offset = 0)
    {
        $sql = "SELECT c.*, 
                       COUNT(DISTINCT o.order_id) as total_orders,
                       SUM(o.total_amount) as total_spent
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
     * Search consumers
     */
    public function search($tenantId, $query, $limit = 50)
    {
        $sql = "SELECT c.* 
                FROM customers c
                WHERE c.tenant_id = :tenant_id 
                AND c.deleted_at IS NULL
                AND (c.name LIKE :query OR c.phone LIKE :query OR c.email LIKE :query)
                ORDER BY c.name ASC
                LIMIT :limit";
        
        $searchQuery = "%$query%";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'query' => $searchQuery, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get consumer orders
     */
    public function getConsumerOrders($consumerId, $tenantId, $limit = 50)
    {
        $sql = "SELECT o.*, t.table_number
                FROM orders o
                LEFT JOIN tables t ON o.table_id = t.table_id
                WHERE o.customer_id = :customer_id 
                AND o.tenant_id = :tenantId 
                AND o.deleted_at IS NULL
                ORDER BY o.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customer_id' => $consumerId, 'tenantId' => $tenantId, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get consumer loyalty points
     */
    public function getLoyaltyPoints($consumerId, $tenantId)
    {
        $sql = "SELECT 
                    COALESCE(SUM(CASE WHEN transaction_type = 'EARN' THEN points ELSE 0 END), 0) as earned_points,
                    COALESCE(SUM(CASE WHEN transaction_type = 'REDEEM' THEN points ELSE 0 END), 0) as redeemed_points,
                    COALESCE(SUM(CASE WHEN transaction_type = 'EARN' THEN points ELSE -points END), 0) as balance
                FROM loyalty_points_transactions
                WHERE customer_id = :customer_id 
                AND tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['customer_id' => $consumerId, 'tenant_id' => $tenantId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get top consumers by spending
     */
    public function getTopConsumers($tenantId, $limit = 10, $startDate = null, $endDate = null)
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
}
