<?php

namespace Modules\Payment\Repositories;

use Core\Database;

class PaymentRepository
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->connect();
    }
    
    /**
     * Find all payments for tenant
     */
    public function findAll($tenantId, $limit = 100, $offset = 0)
    {
        $sql = "SELECT p.*, o.order_number, o.total_amount as order_total
                FROM payments p
                INNER JOIN orders o ON p.order_id = o.order_id
                WHERE o.tenant_id = :tenant_id 
                AND p.deleted_at IS NULL
                ORDER BY p.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'limit' => $limit, 'offset' => $offset]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Find payment by ID
     */
    public function findById($paymentId, $tenantId)
    {
        $sql = "SELECT p.*, o.order_number, o.total_amount as order_total
                FROM payments p
                INNER JOIN orders o ON p.order_id = o.order_id
                WHERE p.payment_id = :payment_id 
                AND o.tenant_id = :tenant_id 
                AND p.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['payment_id' => $paymentId, 'tenant_id' => $tenantId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Find payments by order ID
     */
    public function findByOrderId($orderId, $tenantId)
    {
        $sql = "SELECT p.*, o.order_number
                FROM payments p
                INNER JOIN orders o ON p.order_id = o.order_id
                WHERE p.order_id = :order_id 
                AND o.tenant_id = :tenant_id 
                AND p.deleted_at IS NULL
                ORDER BY p.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['order_id' => $orderId, 'tenant_id' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Create payment
     */
    public function create($data)
    {
        $sql = "INSERT INTO payments (order_id, payment_method, amount, change_amount, 
                                     payment_status, transaction_id, gateway_response, 
                                     created_by, created_at)
                VALUES (:order_id, :payment_method, :amount, :change_amount,
                        :payment_status, :transaction_id, :gateway_response,
                        :created_by, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update payment
     */
    public function update($paymentId, $data, $tenantId)
    {
        $setClause = [];
        foreach (array_keys($data) as $key) {
            if ($key !== 'payment_id') {
                $setClause[] = "$key = :$key";
            }
        }
        $setClause[] = "updated_at = NOW()";
        
        $sql = "UPDATE payments p
                INNER JOIN orders o ON p.order_id = o.order_id
                SET " . implode(', ', $setClause) . " 
                WHERE p.payment_id = :payment_id AND o.tenant_id = :tenant_id";
        
        $params = array_merge($data, ['payment_id' => $paymentId, 'tenant_id' => $tenantId]);
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Update payment status
     */
    public function updateStatus($paymentId, $status, $tenantId)
    {
        $sql = "UPDATE payments p
                INNER JOIN orders o ON p.order_id = o.order_id
                SET p.payment_status = :status, p.updated_at = NOW() 
                WHERE p.payment_id = :payment_id AND o.tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['payment_id' => $paymentId, 'status' => $status, 'tenant_id' => $tenantId]);
    }
    
    /**
     * Soft delete payment
     */
    public function delete($paymentId, $tenantId)
    {
        $sql = "UPDATE payments p
                INNER JOIN orders o ON p.order_id = o.order_id
                SET p.deleted_at = NOW() 
                WHERE p.payment_id = :payment_id AND o.tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['payment_id' => $paymentId, 'tenant_id' => $tenantId]);
    }
    
    /**
     * Get payments by status
     */
    public function getByStatus($status, $tenantId, $limit = 100)
    {
        $sql = "SELECT p.*, o.order_number
                FROM payments p
                INNER JOIN orders o ON p.order_id = o.order_id
                WHERE p.payment_status = :status 
                AND o.tenant_id = :tenant_id 
                AND p.deleted_at IS NULL
                ORDER BY p.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['status' => $status, 'tenant_id' => $tenantId, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get payments by date range
     */
    public function getByDateRange($startDate, $endDate, $tenantId)
    {
        $sql = "SELECT p.*, o.order_number
                FROM payments p
                INNER JOIN orders o ON p.order_id = o.order_id
                WHERE p.created_at BETWEEN :start_date AND :end_date
                AND o.tenant_id = :tenant_id 
                AND p.deleted_at IS NULL
                ORDER BY p.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['start_date' => $startDate, 'end_date' => $endDate, 'tenant_id' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get payments by payment method
     */
    public function getByPaymentMethod($paymentMethod, $tenantId, $limit = 100)
    {
        $sql = "SELECT p.*, o.order_number
                FROM payments p
                INNER JOIN orders o ON p.order_id = o.order_id
                WHERE p.payment_method = :payment_method 
                AND o.tenant_id = :tenant_id 
                AND p.deleted_at IS NULL
                ORDER BY p.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['payment_method' => $paymentMethod, 'tenant_id' => $tenantId, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get total payments amount for date range
     */
    public function getTotalByDateRange($startDate, $endDate, $tenantId, $status = 'COMPLETED')
    {
        $sql = "SELECT SUM(p.amount) as total, COUNT(*) as count
                FROM payments p
                INNER JOIN orders o ON p.order_id = o.order_id
                WHERE p.created_at BETWEEN :start_date AND :end_date
                AND o.tenant_id = :tenant_id 
                AND p.payment_status = :status
                AND p.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['start_date' => $startDate, 'end_date' => $endDate, 'tenant_id' => $tenantId, 'status' => $status]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get payment summary by method
     */
    public function getSummaryByMethod($startDate, $endDate, $tenantId)
    {
        $sql = "SELECT p.payment_method, SUM(p.amount) as total, COUNT(*) as count
                FROM payments p
                INNER JOIN orders o ON p.order_id = o.order_id
                WHERE p.created_at BETWEEN :start_date AND :end_date
                AND o.tenant_id = :tenant_id 
                AND p.payment_status = 'COMPLETED'
                AND p.deleted_at IS NULL
                GROUP BY p.payment_method
                ORDER BY total DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['start_date' => $startDate, 'end_date' => $endDate, 'tenant_id' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
