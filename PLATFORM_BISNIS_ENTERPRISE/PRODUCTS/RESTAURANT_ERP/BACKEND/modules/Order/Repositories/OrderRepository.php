<?php

namespace Modules\Order\Repositories;

use Core\Database;

class OrderRepository
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->connect();
    }
    
    /**
     * Find all orders for tenant
     */
    public function findAll($tenantId, $limit = 100, $offset = 0)
    {
        $sql = "SELECT o.*, c.full_name as customer_name, t.table_number
                FROM orders o
                LEFT JOIN customers c ON o.customer_id = c.customer_id
                LEFT JOIN tables t ON o.table_id = t.table_id
                WHERE o.tenant_id = :tenant_id 
                AND o.deleted_at IS NULL
                ORDER BY o.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'limit' => $limit, 'offset' => $offset]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Find order by ID
     */
    public function findById($orderId, $tenantId)
    {
        $sql = "SELECT o.*, c.full_name as customer_name, t.table_number
                FROM orders o
                LEFT JOIN customers c ON o.customer_id = c.customer_id
                LEFT JOIN tables t ON o.table_id = t.table_id
                WHERE o.order_id = :order_id 
                AND o.tenant_id = :tenant_id 
                AND o.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['order_id' => $orderId, 'tenant_id' => $tenantId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Find order by order number
     */
    public function findByOrderNumber($orderNumber, $tenantId)
    {
        $sql = "SELECT o.*, c.full_name as customer_name, t.table_number
                FROM orders o
                LEFT JOIN customers c ON o.customer_id = c.customer_id
                LEFT JOIN tables t ON o.table_id = t.table_id
                WHERE o.order_number = :order_number 
                AND o.tenant_id = :tenant_id 
                AND o.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['order_number' => $orderNumber, 'tenant_id' => $tenantId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Create order
     */
    public function create($data)
    {
        $sql = "INSERT INTO orders (tenant_id, branch_id, customer_id, table_id, order_number, 
                                     order_type, subtotal, tax_amount, discount_amount, total_amount, 
                                     notes, status, created_by, created_at)
                VALUES (:tenant_id, :branch_id, :customer_id, :table_id, :order_number,
                        :order_type, :subtotal, :tax_amount, :discount_amount, :total_amount,
                        :notes, :status, :created_by, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update order
     */
    public function update($orderId, $data, $tenantId)
    {
        $setClause = [];
        foreach (array_keys($data) as $key) {
            if ($key !== 'order_id') {
                $setClause[] = "$key = :$key";
            }
        }
        $setClause[] = "updated_at = NOW()";
        
        $sql = "UPDATE orders SET " . implode(', ', $setClause) . " 
                WHERE order_id = :order_id AND tenant_id = :tenant_id";
        
        $params = array_merge($data, ['order_id' => $orderId, 'tenant_id' => $tenantId]);
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Update order status
     */
    public function updateStatus($orderId, $status, $tenantId)
    {
        $sql = "UPDATE orders 
                SET status = :status, updated_at = NOW() 
                WHERE order_id = :order_id AND tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['order_id' => $orderId, 'status' => $status, 'tenant_id' => $tenantId]);
    }
    
    /**
     * Soft delete order
     */
    public function delete($orderId, $tenantId)
    {
        $sql = "UPDATE orders 
                SET deleted_at = NOW() 
                WHERE order_id = :order_id AND tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['order_id' => $orderId, 'tenant_id' => $tenantId]);
    }
    
    /**
     * Get order items
     */
    public function getOrderItems($orderId)
    {
        $sql = "SELECT oi.*, p.product_name, p.product_code
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.product_id
                WHERE oi.order_id = :order_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get orders by status
     */
    public function getByStatus($status, $tenantId, $limit = 100)
    {
        $sql = "SELECT o.*, c.full_name as customer_name, t.table_number
                FROM orders o
                LEFT JOIN customers c ON o.customer_id = c.customer_id
                LEFT JOIN tables t ON o.table_id = t.table_id
                WHERE o.status = :status 
                AND o.tenant_id = :tenant_id 
                AND o.deleted_at IS NULL
                ORDER BY o.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['status' => $status, 'tenant_id' => $tenantId, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get orders by date range
     */
    public function getByDateRange($startDate, $endDate, $tenantId)
    {
        $sql = "SELECT o.*, c.full_name as customer_name, t.table_number
                FROM orders o
                LEFT JOIN customers c ON o.customer_id = c.customer_id
                LEFT JOIN tables t ON o.table_id = t.table_id
                WHERE o.created_at BETWEEN :start_date AND :end_date
                AND o.tenant_id = :tenant_id 
                AND o.deleted_at IS NULL
                ORDER BY o.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['start_date' => $startDate, 'end_date' => $endDate, 'tenant_id' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get orders by table
     */
    public function getByTable($tableId, $tenantId, $status = null)
    {
        $sql = "SELECT o.*, c.full_name as customer_name
                FROM orders o
                LEFT JOIN customers c ON o.customer_id = c.customer_id
                WHERE o.table_id = :table_id 
                AND o.tenant_id = :tenant_id 
                AND o.deleted_at IS NULL";
        
        $params = ['table_id' => $tableId, 'tenant_id' => $tenantId];
        
        if ($status !== null) {
            $sql .= " AND o.status = :status";
            $params['status'] = $status;
        }
        
        $sql .= " ORDER BY o.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get orders by customer
     */
    public function getByCustomer($customerId, $tenantId, $limit = 50)
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
     * Get active orders count
     */
    public function getActiveOrdersCount($tenantId)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM orders 
                WHERE tenant_id = :tenant_id 
                AND status IN ('PENDING', 'PROCESSING', 'READY')
                AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'];
    }
}
