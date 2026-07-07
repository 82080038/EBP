<?php

namespace Modules\Feedback\Repositories;

use Core\Database;

class FeedbackRepository
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->connect();
    }
    
    /**
     * Find all feedback for tenant
     */
    public function findAll($tenantId, $limit = 100, $offset = 0)
    {
        $sql = "SELECT f.*, c.name as customer_name, u.username as assigned_to_name
                FROM feedback f
                LEFT JOIN customers c ON f.customer_id = c.customer_id
                LEFT JOIN users u ON f.assigned_to = u.user_id
                WHERE f.tenant_id = :tenant_id 
                AND f.deleted_at IS NULL
                ORDER BY 
                    CASE f.priority
                        WHEN 'urgent' THEN 1
                        WHEN 'high' THEN 2
                        WHEN 'medium' THEN 3
                        WHEN 'low' THEN 4
                    END,
                    f.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'limit' => $limit, 'offset' => $offset]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Find feedback by ID
     */
    public function findById($feedbackId, $tenantId)
    {
        $sql = "SELECT f.*, c.name as customer_name, u.username as assigned_to_name
                FROM feedback f
                LEFT JOIN customers c ON f.customer_id = c.customer_id
                LEFT JOIN users u ON f.assigned_to = u.user_id
                WHERE f.id = :feedback_id 
                AND f.tenant_id = :tenant_id 
                AND f.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['feedback_id' => $feedbackId, 'tenant_id' => $tenantId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Create feedback
     */
    public function create($data)
    {
        $sql = "INSERT INTO feedback (tenant_id, customer_id, feedback_type, subject, message, 
                                     contact_email, contact_phone, feedback_source, 
                                     feedback_status, priority, assigned_to, created_by, created_at)
                VALUES (:tenant_id, :customer_id, :feedback_type, :subject, :message,
                        :contact_email, :contact_phone, :feedback_source,
                        :feedback_status, :priority, :assigned_to, :created_by, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update feedback
     */
    public function update($feedbackId, $data, $tenantId)
    {
        $setClause = [];
        foreach (array_keys($data) as $key) {
            if ($key !== 'id') {
                $setClause[] = "$key = :$key";
            }
        }
        $setClause[] = "updated_at = NOW()";
        
        $sql = "UPDATE feedback SET " . implode(', ', $setClause) . " 
                WHERE id = :feedback_id AND tenant_id = :tenant_id";
        
        $params = array_merge($data, ['feedback_id' => $feedbackId, 'tenant_id' => $tenantId]);
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Update feedback status
     */
    public function updateStatus($feedbackId, $status, $tenantId)
    {
        $sql = "UPDATE feedback 
                SET feedback_status = :status, updated_at = NOW() 
                WHERE id = :feedback_id AND tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['feedback_id' => $feedbackId, 'status' => $status, 'tenant_id' => $tenantId]);
    }
    
    /**
     * Soft delete feedback
     */
    public function delete($feedbackId, $tenantId)
    {
        $sql = "UPDATE feedback 
                SET deleted_at = NOW() 
                WHERE id = :feedback_id AND tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['feedback_id' => $feedbackId, 'tenant_id' => $tenantId]);
    }
    
    /**
     * Get feedback by type
     */
    public function getByType($tenantId, $type, $limit = 100)
    {
        $sql = "SELECT f.*, c.name as customer_name
                FROM feedback f
                LEFT JOIN customers c ON f.customer_id = c.customer_id
                WHERE f.tenant_id = :tenant_id 
                AND f.feedback_type = :type
                AND f.deleted_at IS NULL
                ORDER BY f.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'type' => $type, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get feedback by status
     */
    public function getByStatus($tenantId, $status, $limit = 100)
    {
        $sql = "SELECT f.*, c.name as customer_name
                FROM feedback f
                LEFT JOIN customers c ON f.customer_id = c.customer_id
                WHERE f.tenant_id = :tenant_id 
                AND f.feedback_status = :status
                AND f.deleted_at IS NULL
                ORDER BY f.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'status' => $status, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get feedback by priority
     */
    public function getByPriority($tenantId, $priority, $limit = 100)
    {
        $sql = "SELECT f.*, c.name as customer_name
                FROM feedback f
                LEFT JOIN customers c ON f.customer_id = c.customer_id
                WHERE f.tenant_id = :tenant_id 
                AND f.priority = :priority
                AND f.feedback_status != 'resolved'
                AND f.deleted_at IS NULL
                ORDER BY f.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'priority' => $priority, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Count feedback by status
     */
    public function countByStatus($tenantId, $status = null)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM feedback 
                WHERE tenant_id = :tenant_id 
                AND deleted_at IS NULL";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($status !== null) {
            $sql .= " AND feedback_status = :status";
            $params['status'] = $status;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    /**
     * Count feedback by priority
     */
    public function countByPriority($tenantId, $priority)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM feedback 
                WHERE tenant_id = :tenant_id 
                AND priority = :priority 
                AND feedback_status != 'resolved'
                AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'priority' => $priority]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    /**
     * Get feedback summary
     */
    public function getSummary($tenantId)
    {
        $sql = "SELECT 
                    feedback_status,
                    COUNT(*) as count
                FROM feedback
                WHERE tenant_id = :tenant_id
                AND deleted_at IS NULL
                GROUP BY feedback_status";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
