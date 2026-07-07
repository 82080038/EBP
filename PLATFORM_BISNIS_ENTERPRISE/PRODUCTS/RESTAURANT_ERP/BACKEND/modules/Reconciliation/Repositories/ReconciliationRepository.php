<?php

namespace Modules\Reconciliation\Repositories;

use Core\Database;

class ReconciliationRepository
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->connect();
    }
    
    /**
     * Find all reconciliation transactions for tenant
     */
    public function findAll($tenantId, $limit = 100, $offset = 0)
    {
        $sql = "SELECT rt.*, rs.source_name
                FROM reconciliation_transactions rt
                LEFT JOIN reconciliation_sources rs ON rt.source_id = rs.source_id
                WHERE rt.tenant_id = :tenant_id 
                AND rt.deleted_at IS NULL
                ORDER BY rt.order_date DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'limit' => $limit, 'offset' => $offset]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Find reconciliation transaction by ID
     */
    public function findById($transactionId, $tenantId)
    {
        $sql = "SELECT rt.*, rs.source_name
                FROM reconciliation_transactions rt
                LEFT JOIN reconciliation_sources rs ON rt.source_id = rs.source_id
                WHERE rt.id = :transaction_id 
                AND rt.tenant_id = :tenant_id 
                AND rt.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['transaction_id' => $transactionId, 'tenant_id' => $tenantId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Create reconciliation transaction
     */
    public function create($data)
    {
        $sql = "INSERT INTO reconciliation_transactions (tenant_id, source_id, external_order_id, 
                                                         pos_order_id, processor_transaction_id, 
                                                         delivery_platform_order_id, delivery_platform_name,
                                                         order_date, order_amount, order_currency,
                                                         pos_amount, processor_amount, delivery_platform_amount,
                                                         reconciliation_status, match_confidence,
                                                         discrepancy_type, discrepancy_amount, discrepancy_notes,
                                                         manually_matched, matched_by, matched_at, match_notes,
                                                         created_by, created_at)
                VALUES (:tenant_id, :source_id, :external_order_id,
                        :pos_order_id, :processor_transaction_id,
                        :delivery_platform_order_id, :delivery_platform_name,
                        :order_date, :order_amount, :order_currency,
                        :pos_amount, :processor_amount, :delivery_platform_amount,
                        :reconciliation_status, :match_confidence,
                        :discrepancy_type, :discrepancy_amount, :discrepancy_notes,
                        :manually_matched, :matched_by, :matched_at, :match_notes,
                        :created_by, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update reconciliation transaction
     */
    public function update($transactionId, $data, $tenantId)
    {
        $setClause = [];
        foreach (array_keys($data) as $key) {
            if ($key !== 'id') {
                $setClause[] = "$key = :$key";
            }
        }
        $setClause[] = "updated_at = NOW()";
        
        $sql = "UPDATE reconciliation_transactions SET " . implode(', ', $setClause) . " 
                WHERE id = :transaction_id AND tenant_id = :tenant_id";
        
        $params = array_merge($data, ['transaction_id' => $transactionId, 'tenant_id' => $tenantId]);
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Update reconciliation status
     */
    public function updateStatus($transactionId, $status, $tenantId)
    {
        $sql = "UPDATE reconciliation_transactions 
                SET reconciliation_status = :status, updated_at = NOW() 
                WHERE id = :transaction_id AND tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['transaction_id' => $transactionId, 'status' => $status, 'tenant_id' => $tenantId]);
    }
    
    /**
     * Soft delete reconciliation transaction
     */
    public function delete($transactionId, $tenantId)
    {
        $sql = "UPDATE reconciliation_transactions 
                SET deleted_at = NOW() 
                WHERE id = :transaction_id AND tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['transaction_id' => $transactionId, 'tenant_id' => $tenantId]);
    }
    
    /**
     * Get transactions by status
     */
    public function getByStatus($tenantId, $status, $limit = 100)
    {
        $sql = "SELECT rt.*, rs.source_name
                FROM reconciliation_transactions rt
                LEFT JOIN reconciliation_sources rs ON rt.source_id = rs.source_id
                WHERE rt.tenant_id = :tenant_id 
                AND rt.reconciliation_status = :status
                AND rt.deleted_at IS NULL
                ORDER BY rt.order_date DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'status' => $status, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get transactions by date range
     */
    public function getByDateRange($tenantId, $startDate, $endDate)
    {
        $sql = "SELECT rt.*, rs.source_name
                FROM reconciliation_transactions rt
                LEFT JOIN reconciliation_sources rs ON rt.source_id = rs.source_id
                WHERE rt.tenant_id = :tenant_id 
                AND rt.order_date BETWEEN :start_date AND :end_date
                AND rt.deleted_at IS NULL
                ORDER BY rt.order_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'start_date' => $startDate, 'end_date' => $endDate]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get discrepancies
     */
    public function getDiscrepancies($tenantId, $limit = 100)
    {
        $sql = "SELECT rt.*, rs.source_name
                FROM reconciliation_transactions rt
                LEFT JOIN reconciliation_sources rs ON rt.source_id = rs.source_id
                WHERE rt.tenant_id = :tenant_id 
                AND rt.reconciliation_status = 'discrepancy'
                AND rt.deleted_at IS NULL
                ORDER BY rt.order_date DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Count transactions by status
     */
    public function countByStatus($tenantId, $status)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM reconciliation_transactions 
                WHERE tenant_id = :tenant_id 
                AND reconciliation_status = :status
                AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'status' => $status]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    /**
     * Sum discrepancy amount
     */
    public function sumDiscrepancyAmount($tenantId)
    {
        $sql = "SELECT COALESCE(SUM(ABS(discrepancy_amount)), 0) as total 
                FROM reconciliation_transactions 
                WHERE tenant_id = :tenant_id 
                AND reconciliation_status = 'discrepancy'
                AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    /**
     * Get reconciliation summary
     */
    public function getSummary($tenantId)
    {
        $sql = "SELECT 
                    reconciliation_status,
                    COUNT(*) as count,
                    COALESCE(SUM(ABS(discrepancy_amount)), 0) as total_discrepancy
                FROM reconciliation_transactions
                WHERE tenant_id = :tenant_id
                AND deleted_at IS NULL
                GROUP BY reconciliation_status";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get reconciliation sources
     */
    public function getSources($tenantId)
    {
        $sql = "SELECT * 
                FROM reconciliation_sources
                WHERE tenant_id = :tenant_id
                AND deleted_at IS NULL
                ORDER BY source_name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get reconciliation rules
     */
    public function getRules($tenantId)
    {
        $sql = "SELECT * 
                FROM reconciliation_rules
                WHERE tenant_id = :tenantId
                AND deleted_at IS NULL
                AND is_active = 1
                ORDER BY rule_name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
