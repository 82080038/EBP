<?php

namespace Modules\Consumer\Services;

use Core\Database;
use Core\Transaction;
use Core\Audit;
use Modules\Consumer\Repositories\ConsumerRepository;

class ConsumerService
{
    private $repository;
    private $db;
    
    public function __construct()
    {
        $this->repository = new ConsumerRepository();
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all consumers for tenant
     */
    public function getAll($tenantId, $limit = 100, $offset = 0)
    {
        return $this->repository->findAll($tenantId, $limit, $offset);
    }
    
    /**
     * Get consumer by ID
     */
    public function getById($consumerId, $tenantId)
    {
        return $this->repository->findById($consumerId, $tenantId);
    }
    
    /**
     * Find consumer by phone
     */
    public function findByPhone($phone, $tenantId)
    {
        return $this->repository->findByPhone($phone, $tenantId);
    }
    
    /**
     * Find consumer by email
     */
    public function findByEmail($email, $tenantId)
    {
        return $this->repository->findByEmail($email, $tenantId);
    }
    
    /**
     * Create consumer
     */
    public function create($data, $tenantId, $userId)
    {
        Transaction::begin();
        
        try {
            // Generate customer code if not provided
            if (!isset($data['customer_code'])) {
                $data['customer_code'] = $this->generateCustomerCode($tenantId);
            }
            
            $data['tenant_id'] = $tenantId;
            $data['created_by'] = $userId;
            $data['status'] = $data['status'] ?? 'ACTIVE';
            
            $consumerId = $this->repository->create($data);
            
            Audit::log($tenantId, $userId, 'CONSUMER_CREATE', "Created consumer with ID: {$consumerId}");
            
            Transaction::commit();
            
            return [
                'success' => true,
                'consumer_id' => $consumerId
            ];
        } catch (\Exception $e) {
            Transaction::rollback();
            return [
                'success' => false,
                'message' => 'Failed to create consumer: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update consumer
     */
    public function update($consumerId, $data, $tenantId, $userId)
    {
        Transaction::begin();
        
        try {
            $data['updated_by'] = $userId;
            
            $result = $this->repository->update($consumerId, $data, $tenantId);
            
            Audit::log($tenantId, $userId, 'CONSUMER_UPDATE', "Updated consumer with ID: {$consumerId}");
            
            Transaction::commit();
            
            return [
                'success' => true
            ];
        } catch (\Exception $e) {
            Transaction::rollback();
            return [
                'success' => false,
                'message' => 'Failed to update consumer: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete consumer (soft delete)
     */
    public function delete($consumerId, $tenantId, $userId)
    {
        Transaction::begin();
        
        try {
            $result = $this->repository->delete($consumerId, $tenantId);
            
            Audit::log($tenantId, $userId, 'CONSUMER_DELETE', "Deleted consumer with ID: {$consumerId}");
            
            Transaction::commit();
            
            return [
                'success' => true
            ];
        } catch (\Exception $e) {
            Transaction::rollback();
            return [
                'success' => false,
                'message' => 'Failed to delete consumer: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Search consumers
     */
    public function search($tenantId, $query, $limit = 50)
    {
        return $this->repository->search($tenantId, $query, $limit);
    }
    
    /**
     * Get consumer orders
     */
    public function getConsumerOrders($consumerId, $tenantId, $limit = 50)
    {
        return $this->repository->getConsumerOrders($consumerId, $tenantId, $limit);
    }
    
    /**
     * Get consumer loyalty points
     */
    public function getLoyaltyPoints($consumerId, $tenantId)
    {
        return $this->repository->getLoyaltyPoints($consumerId, $tenantId);
    }
    
    /**
     * Get top consumers by spending
     */
    public function getTopConsumers($tenantId, $limit = 10, $startDate = null, $endDate = null)
    {
        return $this->repository->getTopConsumers($tenantId, $limit, $startDate, $endDate);
    }
    
    /**
     * Generate customer code
     */
    private function generateCustomerCode($tenantId)
    {
        $prefix = 'CUST';
        $timestamp = date('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 4));
        return "{$prefix}-{$tenantId}-{$timestamp}-{$random}";
    }
}
