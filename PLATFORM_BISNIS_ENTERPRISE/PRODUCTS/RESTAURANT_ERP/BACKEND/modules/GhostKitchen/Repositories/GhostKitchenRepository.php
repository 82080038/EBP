<?php

namespace Modules\GhostKitchen\Repositories;

use Core\Database;

class GhostKitchenRepository
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->connect();
    }
    
    /**
     * Find all virtual brands for tenant
     */
    public function findAll($tenantId, $limit = 100, $offset = 0)
    {
        $sql = "SELECT vb.*, u.username as created_by_name
                FROM virtual_brands vb
                LEFT JOIN users u ON vb.created_by = u.user_id
                WHERE vb.tenant_id = :tenant_id 
                AND vb.deleted_at IS NULL
                ORDER BY vb.brand_name ASC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'limit' => $limit, 'offset' => $offset]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Find virtual brand by ID
     */
    public function findById($brandId, $tenantId)
    {
        $sql = "SELECT vb.*, u.username as created_by_name
                FROM virtual_brands vb
                LEFT JOIN users u ON vb.created_by = u.user_id
                WHERE vb.id = :brand_id 
                AND vb.tenant_id = :tenant_id 
                AND vb.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['brand_id' => $brandId, 'tenant_id' => $tenantId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Find virtual brand by code
     */
    public function findByCode($brandCode, $tenantId)
    {
        $sql = "SELECT vb.* 
                FROM virtual_brands vb
                WHERE vb.brand_code = :brand_code 
                AND vb.tenant_id = :tenant_id 
                AND vb.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['brand_code' => $brandCode, 'tenant_id' => $tenantId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Create virtual brand
     */
    public function create($data)
    {
        $sql = "INSERT INTO virtual_brands (tenant_id, brand_name, brand_code, brand_description, 
                                          brand_logo_url, brand_color_hex, cuisine_type, price_range,
                                          brand_status, target_audience, created_by, created_at)
                VALUES (:tenant_id, :brand_name, :brand_code, :brand_description,
                        :brand_logo_url, :brand_color_hex, :cuisine_type, :price_range,
                        :brand_status, :target_audience, :created_by, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update virtual brand
     */
    public function update($brandId, $data, $tenantId)
    {
        $setClause = [];
        foreach (array_keys($data) as $key) {
            if ($key !== 'id') {
                $setClause[] = "$key = :$key";
            }
        }
        $setClause[] = "updated_at = NOW()";
        
        $sql = "UPDATE virtual_brands SET " . implode(', ', $setClause) . " 
                WHERE id = :brand_id AND tenant_id = :tenant_id";
        
        $params = array_merge($data, ['brand_id' => $brandId, 'tenant_id' => $tenantId]);
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Update virtual brand status
     */
    public function updateStatus($brandId, $status, $tenantId)
    {
        $sql = "UPDATE virtual_brands 
                SET brand_status = :status, updated_at = NOW() 
                WHERE id = :brand_id AND tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['brand_id' => $brandId, 'status' => $status, 'tenant_id' => $tenantId]);
    }
    
    /**
     * Soft delete virtual brand
     */
    public function delete($brandId, $tenantId)
    {
        $sql = "UPDATE virtual_brands 
                SET deleted_at = NOW() 
                WHERE id = :brand_id AND tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['brand_id' => $brandId, 'tenant_id' => $tenantId]);
    }
    
    /**
     * Get virtual brands by status
     */
    public function getByStatus($tenantId, $status, $limit = 100)
    {
        $sql = "SELECT vb.*, u.username as created_by_name
                FROM virtual_brands vb
                LEFT JOIN users u ON vb.created_by = u.user_id
                WHERE vb.tenant_id = :tenant_id 
                AND vb.brand_status = :status
                AND vb.deleted_at IS NULL
                ORDER BY vb.brand_name ASC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId, 'status' => $status, 'limit' => $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get active virtual brands
     */
    public function getActive($tenantId)
    {
        $sql = "SELECT vb.*, u.username as created_by_name
                FROM virtual_brands vb
                LEFT JOIN users u ON vb.created_by = u.user_id
                WHERE vb.tenant_id = :tenant_id 
                AND vb.brand_status = 'active'
                AND vb.deleted_at IS NULL
                ORDER BY vb.brand_name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Count virtual brands by status
     */
    public function countByStatus($tenantId, $status = null)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM virtual_brands 
                WHERE tenant_id = :tenant_id 
                AND deleted_at IS NULL";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($status !== null) {
            $sql .= " AND brand_status = :status";
            $params['status'] = $status;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    /**
     * Get virtual brand menu items
     */
    public function getBrandMenuItems($brandId, $tenantId)
    {
        $sql = "SELECT vbmi.*, p.product_name, p.product_code
                FROM virtual_brand_menu_items vbmi
                LEFT JOIN products p ON vbmi.product_id = p.product_id
                WHERE vbmi.virtual_brand_id = :brand_id
                AND vbmi.tenant_id = :tenant_id
                AND vbmi.deleted_at IS NULL
                ORDER BY vbmi.display_order ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['brand_id' => $brandId, 'tenant_id' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get delivery platforms
     */
    public function getDeliveryPlatforms($tenantId)
    {
        $sql = "SELECT * 
                FROM delivery_platforms
                WHERE tenant_id = :tenant_id
                AND deleted_at IS NULL
                AND is_active = 1
                ORDER BY platform_name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get brand delivery platforms
     */
    public function getBrandDeliveryPlatforms($brandId, $tenantId)
    {
        $sql = "SELECT dp.*, vbpd.commission_rate, vbpd.is_active as brand_active
                FROM delivery_platforms dp
                INNER JOIN virtual_brand_platform_delivery vbpd ON dp.id = vbpd.platform_id
                WHERE vbpd.virtual_brand_id = :brand_id
                AND dp.tenant_id = :tenant_id
                AND dp.deleted_at IS NULL
                ORDER BY dp.platform_name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['brand_id' => $brandId, 'tenant_id' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
