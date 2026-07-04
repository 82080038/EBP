<?php

namespace App\Modules\Supplier\Models;

use App\Core\BaseModel;

class SupplierContract extends BaseModel
{
    protected $table = 'supplier_contracts';
    protected $primaryKey = 'id';
    protected $fillable = [
        'restaurant_id',
        'supplier_id',
        'contract_number',
        'contract_name',
        'contract_type',
        'start_date',
        'end_date',
        'contract_terms',
        'payment_terms',
        'contract_value',
        'contract_status',
        'contract_document_url',
        'created_by'
    ];

    /**
     * Get by supplier
     */
    public function getBySupplier($supplierId, $restaurantId)
    {
        $sql = "SELECT sc.*, u.username as created_by_name 
                FROM {$this->table} sc
                LEFT JOIN users u ON sc.created_by = u.id
                WHERE sc.supplier_id = ? AND sc.restaurant_id = ?
                ORDER BY sc.start_date DESC";
        return $this->db->query($sql, [$supplierId, $restaurantId])->fetchAll();
    }

    /**
     * Find by ID
     */
    public function findById($id, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$id, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Find by number
     */
    public function findByNumber($contractNumber, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE contract_number = ? AND restaurant_id = ?";
        $result = $this->db->query($sql, [$contractNumber, $restaurantId])->fetch();
        return $result ?: null;
    }

    /**
     * Get active contracts
     */
    public function getActive($restaurantId)
    {
        $sql = "SELECT sc.*, s.supplier_name 
                FROM {$this->table} sc
                LEFT JOIN suppliers s ON sc.supplier_id = s.id
                WHERE sc.restaurant_id = ? AND sc.contract_status = 'active'
                ORDER BY sc.end_date ASC";
        return $this->db->query($sql, [$restaurantId])->fetchAll();
    }

    /**
     * Count active
     */
    public function countActive($restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE restaurant_id = ? AND contract_status = 'active'";
        $result = $this->db->query($sql, [$restaurantId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get expiring soon
     */
    public function getExpiringSoon($restaurantId, $days = 30)
    {
        $sql = "SELECT sc.*, s.supplier_name 
                FROM {$this->table} sc
                LEFT JOIN suppliers s ON sc.supplier_id = s.id
                WHERE sc.restaurant_id = ? AND sc.contract_status = 'active'
                AND sc.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                ORDER BY sc.end_date ASC";
        return $this->db->query($sql, [$restaurantId, $days])->fetchAll();
    }
}
